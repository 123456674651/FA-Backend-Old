<?php

namespace Tests\Unit;

use App\Services\WordDocumentConverter;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\WordFixtures;
use Tests\TestCase;

/**
 * No database — the converter only touches the filesystem and LibreOffice.
 */
class WordDocumentConverterTest extends TestCase
{
    private string $workDir;

    private WordFixtures $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workDir = storage_path('framework/testing/word-converter');
        File::deleteDirectory($this->workDir);
        $this->fixtures = new WordFixtures($this->workDir);
    }

    protected function tearDown(): void
    {
        $this->fixtures->cleanup();

        parent::tearDown();
    }

    public function test_an_ooxml_document_is_recognised(): void
    {
        $this->assertTrue((new WordDocumentConverter())->isDocx($this->fixtures->docx('Date: $date$')));
    }

    public function test_a_legacy_binary_doc_is_not_mistaken_for_a_docx(): void
    {
        // The whole bug: this file uploads fine and used to be stored under a
        // .docx name, but it is an OLE2 container, not a zip.
        $this->assertFalse((new WordDocumentConverter())->isDocx($this->legacyDoc('Date: $date$')));
    }

    public function test_a_zip_without_a_word_document_is_not_a_docx(): void
    {
        $path = $this->workDir . '/plain.zip';
        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::CREATE);
        $zip->addFromString('readme.txt', 'not a word file');
        $zip->close();

        $this->assertFalse((new WordDocumentConverter())->isDocx($path));
    }

    public function test_a_legacy_doc_converts_to_a_real_docx_keeping_its_placeholders(): void
    {
        $converter = new WordDocumentConverter();
        $converted = $converter->convertToDocx($this->legacyDoc('Date: $date$ paid by $party_1$'));

        try {
            $this->assertTrue($converter->isDocx($converted));
            $this->assertSame('docx', strtolower(pathinfo($converted, PATHINFO_EXTENSION)));

            // The placeholders are the point of the upload — a conversion that
            // dropped or mangled them would leave an unusable template.
            $this->assertEqualsCanonicalizing(['date', 'party_1'], $this->placeholdersIn($converted));
        } finally {
            $converter->discard($converted);
        }
    }

    public function test_an_already_valid_docx_is_returned_untouched(): void
    {
        $docx = $this->fixtures->docx('Date: $date$');

        $this->assertSame($docx, (new WordDocumentConverter())->ensureDocx($docx));
    }

    public function test_discarding_a_conversion_removes_its_temporary_directory(): void
    {
        $converter = new WordDocumentConverter();
        $converted = $converter->convertToDocx($this->legacyDoc('Date: $date$'));
        $directory = dirname($converted);

        $converter->discard($converted);

        $this->assertDirectoryDoesNotExist($directory);
    }

    public function test_discarding_leaves_a_file_the_converter_did_not_create(): void
    {
        $docx = $this->fixtures->docx('Date: $date$');

        (new WordDocumentConverter())->discard($docx);

        $this->assertFileExists($docx);
    }

    public function test_conversion_fails_loudly_when_the_binary_is_missing(): void
    {
        $converter = new WordDocumentConverter($this->workDir . '/no-such-soffice');

        $this->expectException(RuntimeException::class);

        $converter->convertToDocx($this->legacyDoc('Date: $date$'));
    }

    public function test_a_file_that_is_not_a_word_document_is_refused(): void
    {
        // LibreOffice's plain-text fallback filter will "convert" anything it
        // is handed, so without a container check a corrupt upload would become
        // a well-formed .docx full of nonsense.
        $path = $this->workDir . '/corrupt.doc';
        File::put($path, random_bytes(4096));

        $this->expectException(RuntimeException::class);

        (new WordDocumentConverter())->convertToDocx($path);
    }

    public function test_an_rtf_saved_as_doc_is_still_accepted(): void
    {
        // Word writes RTF under a .doc name, so refusing it would reject a file
        // the admin can legitimately open in Word.
        $path = $this->workDir . '/legacy.doc';
        File::put($path, '{\rtf1\ansi Date: $date$}');

        $converter = new WordDocumentConverter();
        $converted = $converter->convertToDocx($path);

        try {
            $this->assertSame(['date'], $this->placeholdersIn($converted));
        } finally {
            $converter->discard($converted);
        }
    }

    /** @return array<int, string> */
    private function placeholdersIn(string $docxPath): array
    {
        $zip = new \ZipArchive();
        $zip->open($docxPath);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        preg_match_all('/\$([a-zA-Z0-9_]+)\$/', strip_tags((string) $xml), $matches);

        return array_values(array_unique($matches[1]));
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
