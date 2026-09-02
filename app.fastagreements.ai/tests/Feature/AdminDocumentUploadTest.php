<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\Support\WordFixtures;
use Tests\TestCase;

/**
 * Admins were able to pick a .doc in the upload dialog and the upload appeared
 * to succeed, but the file was stored verbatim under a .docx name. Everything
 * downstream reads the OOXML zip, so the template extracted zero variables,
 * previewed blank, and failed agreement generation with "ZipArchive error: 19".
 */
class AdminDocumentUploadTest extends TestCase
{
    use DatabaseTransactions;

    private WordFixtures $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->fixtures = new WordFixtures(storage_path('framework/testing/admin-upload'));
    }

    protected function tearDown(): void
    {
        $this->fixtures->cleanup();

        parent::tearDown();
    }

    public function test_a_legacy_doc_template_is_stored_as_a_usable_docx(): void
    {
        [$categoryId, $languageId, $folder] = $this->category('DocUpload Rent', 'DocUpload English');

        $response = $this->actingAs($this->admin())->post(route('documents.upload_docx'), [
            'category_id' => $categoryId,
            'language_id' => $languageId,
            'extract_attributes' => '1',
            'document' => $this->upload($this->legacyDoc('Signed on $date$ by $tenant_name$'), 'template.doc'),
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $stored = $folder . '/DocUpload Rent_DocUpload English.docx';
        Storage::assertExists($stored);

        // The point of the fix: what lands on disk is a real OOXML package,
        // not an OLE2 binary wearing a .docx name.
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open(Storage::path($stored)));
        $this->assertNotFalse($zip->locateName('word/document.xml'));
        $zip->close();

        // $date$ is on the skip list, so only the custom placeholder is saved.
        $response->assertJson(['variables' => ['$tenant_name$']]);
        $this->assertSame(
            1,
            DB::table('category_attributes')
                ->where('category_id', $categoryId)
                ->where('attribute_code', '@tenant_name')
                ->count()
        );
    }

    public function test_a_docx_template_still_uploads_unchanged(): void
    {
        [$categoryId, $languageId, $folder] = $this->category('DocUpload Sale', 'DocUpload Hindi');

        $source = $this->fixtures->docx('Sold on $date$ to $buyer_name$');

        $this->actingAs($this->admin())->post(route('documents.upload_docx'), [
            'category_id' => $categoryId,
            'language_id' => $languageId,
            'extract_attributes' => '1',
            'document' => $this->upload($source, 'template.docx'),
        ])->assertOk()->assertJson(['success' => true, 'variables' => ['$buyer_name$']]);

        $stored = $folder . '/DocUpload Sale_DocUpload Hindi.docx';
        Storage::assertExists($stored);
        $this->assertSame(File::get($source), Storage::get($stored));
    }

    public function test_a_file_that_is_not_a_word_document_is_rejected_with_a_usable_message(): void
    {
        [$categoryId, $languageId, $folder] = $this->category('DocUpload Loan', 'DocUpload Gujarati');

        $corrupt = storage_path('framework/testing/admin-upload/corrupt.doc');
        File::put($corrupt, random_bytes(4096));

        $this->actingAs($this->admin())
            ->postJson(route('documents.upload_docx'), [
                'category_id' => $categoryId,
                'language_id' => $languageId,
                'document' => $this->upload($corrupt, 'template.doc'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('document');

        Storage::assertMissing($folder . '/DocUpload Loan_DocUpload Gujarati.docx');
    }

    public function test_a_rejected_replacement_leaves_the_existing_template_in_place(): void
    {
        [$categoryId, $languageId, $folder] = $this->category('DocUpload Lease', 'DocUpload Marathi');
        $stored = $folder . '/DocUpload Lease_DocUpload Marathi.docx';

        $this->actingAs($this->admin())->post(route('documents.upload_docx'), [
            'category_id' => $categoryId,
            'language_id' => $languageId,
            'document' => $this->upload($this->fixtures->docx('Original $date$'), 'good.docx'),
        ])->assertOk();

        $original = Storage::get($stored);

        $corrupt = storage_path('framework/testing/admin-upload/bad.doc');
        File::put($corrupt, random_bytes(2048));

        $this->actingAs($this->admin())->postJson(route('documents.upload_docx'), [
            'category_id' => $categoryId,
            'language_id' => $languageId,
            'document' => $this->upload($corrupt, 'bad.doc'),
        ])->assertStatus(422);

        $this->assertSame($original, Storage::get($stored));
    }

    public function test_conversions_do_not_leave_temporary_files_behind(): void
    {
        [$categoryId, $languageId] = $this->category('DocUpload Loan2', 'DocUpload Tamil');

        // The real path, not the faked disk: the converter writes its scratch
        // directory with storage_path(), so a leak would accumulate on the
        // server every time an admin uploads a .doc.
        $scratch = storage_path('app/doc-conversions');
        File::deleteDirectory($scratch);

        $this->actingAs($this->admin())->post(route('documents.upload_docx'), [
            'category_id' => $categoryId,
            'language_id' => $languageId,
            'document' => $this->upload($this->legacyDoc('Paid $date$'), 'template.doc'),
        ])->assertOk();

        $this->assertSame([], File::directories($scratch));
    }

    private function admin(): User
    {
        return User::first() ?? User::create([
            'name' => 'Admin',
            'email' => 'admin-document-upload-test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /** @return array{0: int, 1: int, 2: string} */
    private function category(string $categoryName, string $languageName): array
    {
        $categoryId = DB::table('deal_categories')->insertGetId([
            'category_name' => $categoryName,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $languageId = DB::table('languages')->insertGetId([
            'language_name' => $languageName,
            'language_code' => substr(md5($languageName), 0, 5),
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$categoryId, $languageId, "uploads/{$categoryName}/{$languageName}"];
    }

    private function upload(string $path, string $clientName): UploadedFile
    {
        // Copied because UploadedFile in test mode still moves the file.
        $copy = $path . '.upload';
        File::copy($path, $copy);

        return new UploadedFile($copy, $clientName, null, null, true);
    }

    private function legacyDoc(string $text): string
    {
        $path = $this->fixtures->legacyDoc($text);

        if ($path === null) {
            $this->markTestSkipped('LibreOffice is not installed, so a legacy .doc fixture cannot be produced.');
        }

        return $path;
    }
}
