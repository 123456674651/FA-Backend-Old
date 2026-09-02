<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\DealCategory;
use App\Models\Language;
use App\Models\CategoryAttribute;
use App\Services\WordDocumentConverter;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DocumentController extends Controller
{
    /**
     * Templates are always stored as .docx because every consumer — variable
     * extraction, the admin preview, and PhpWord's TemplateProcessor — reads
     * the OOXML zip. A legacy .doc is converted on the way in rather than
     * renamed, which is what used to break these uploads.
     */
    private const TEMPLATE_EXTENSION = 'docx';

    /**
     * What libmagic may report for a Word file. It is deliberately loose —
     * different libmagic builds call a Word 97-2003 binary msword,
     * vnd.ms-office, CDFV2 or plain octet-stream — because the real content
     * gate is WordDocumentConverter, which insists on an OOXML zip or an
     * OLE2/RTF container before anything is stored.
     */
    private const TEMPLATE_MIME_TYPES = [
        'application/msword',
        'application/vnd.ms-office',
        'application/vnd.ms-word',
        'application/x-msword',
        'application/x-ole-storage',
        'application/CDFV2',
        'application/rtf',
        'text/rtf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/zip',
        'application/octet-stream',
    ];

    public function __construct(private readonly WordDocumentConverter $converter)
    {
    }

    /** @return array<int, string> */
    private static function templateRules(bool $required): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'extensions:doc,docx',
            'mimetypes:' . implode(',', self::TEMPLATE_MIME_TYPES),
            'max:10240',
        ];
    }

    /**
     * Store an uploaded template as a valid .docx, converting a legacy .doc.
     *
     * @throws ValidationException when the upload is not a readable Word file
     */
    private function storeTemplate(UploadedFile $upload, string $folderPath, string $fileName): void
    {
        $source = $upload->getRealPath();

        try {
            $docx = $this->converter->ensureDocx($source);
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages(['document' => $e->getMessage()]);
        }

        try {
            if (Storage::putFileAs($folderPath, new File($docx), $fileName) === false) {
                throw ValidationException::withMessages([
                    'document' => 'The document could not be saved on the server. Please try again.',
                ]);
            }
        } finally {
            $this->converter->discard($docx);
        }
    }

    public function index()
    {
        $documents = Document::with('category', 'language')->get();
        return view('admin.documents.index', compact('documents'));
    }

    public function create()
    {
        $categories = DealCategory::all();
        $languages = Language::where('is_active', 1)->orderBy('language_name')->get();
        return view('admin.documents.create', compact('categories', 'languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'document'    => self::templateRules(true),
            'category_id' => 'required|exists:deal_categories,id',
            'language_id' => 'required|exists:languages,id',
        ]);

        $category = DealCategory::findOrFail($request->category_id);
        $language = Language::findOrFail($request->language_id);

        $folderPath = "uploads/{$category->category_name}/{$language->language_name}";

        if (!Storage::exists($folderPath)) {
            Storage::makeDirectory($folderPath);
        }

        $fileName = $category->category_name . '_' . $language->language_name . '.' . self::TEMPLATE_EXTENSION;
        $fullPath = $folderPath . '/' . $fileName;

        // Overwrites any existing template, and only once conversion has
        // succeeded — a rejected .doc leaves the working template in place.
        $this->storeTemplate($request->file('document'), $folderPath, $fileName);

        Document::updateOrCreate(
            ['category_id' => $request->category_id, 'language_id' => $request->language_id],
            ['file_path' => $fullPath]
        );

        return back()->with('success', 'Document uploaded successfully!');
    }

    public function uploadDocx(Request $request)
    {
        $request->validate([
            'document'        => self::templateRules(true),
            'category_id'     => 'required|exists:deal_categories,id',
            'sub_category_id' => 'nullable|exists:deal_categories,id',
            'language_id'     => 'required|exists:languages,id',
        ]);

        $language = Language::findOrFail($request->language_id);

        // Use sub category if provided, otherwise fall back to parent category
        $categoryId = $request->filled('sub_category_id')
            ? $request->sub_category_id
            : $request->category_id;

        $category   = DealCategory::findOrFail($categoryId);
        $parentName = null;

        if ($request->filled('sub_category_id')) {
            $parent     = DealCategory::find($category->parent_id);
            $parentName = $parent ? $parent->category_name : 'General';
            $folderPath = "uploads/{$parentName}/{$category->category_name}/{$language->language_name}";
        } else {
            $folderPath = "uploads/{$category->category_name}/{$language->language_name}";
        }

        $fileName = $category->category_name . '_' . $language->language_name . '.' . self::TEMPLATE_EXTENSION;

        if (!Storage::exists($folderPath)) {
            Storage::makeDirectory($folderPath);
        }

        $fullPath = $folderPath . '/' . $fileName;

        $this->storeTemplate($request->file('document'), $folderPath, $fileName);

        Document::updateOrCreate(
            ['category_id' => $categoryId, 'language_id' => $request->language_id],
            ['file_path'   => $fullPath]
        );

        $variables = $this->extractVariables(Storage::path($fullPath), $categoryId, $request->boolean('extract_attributes', false));

        return response()->json([
            'success'   => true,
            'message'   => $request->boolean('extract_attributes', false)
                ? 'File uploaded. ' . count($variables) . ' variable(s) found and saved.'
                : 'File uploaded successfully. Attribute extraction skipped.',
            'variables' => $variables,
            'path'      => $fullPath,
        ]);
    }

    private function extractVariables(string $filePath, ?int $categoryId, bool $saveAttributes = true): array
    {
        $variables = [];

        // 👇 Add variables you want to skip here
        $skipVariables = [
            'date',
            'party_1',
            'party_2',
            'party_no_1',
            'party_no_2',
            'location',
            'amount',
            'deposit_amount',
            'duration',
            'start_at',
            'end_at',
            'party1age',
            'party2age',
            'occupation_1',
            'occupation_2',
            'address_1',
            'address_2',
            'party_1_adhar_front',
            'party_1_adhar_back',
            '1image',
            '2image',
            'party_1_signature',
            'party_2_signature',
            'guarantor_1',
            'guarantor_2',
            'guarantor_3',
            'guarantor_4',
            'guarantor_1_no',
            'guarantor_2_no',
            'guarantor_3_no',
            'guarantor_4_no',
        ];

        try {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                $xml = $zip->getFromName('word/document.xml');
                $zip->close();

                $text = strip_tags($xml);

                preg_match_all('/\$([a-zA-Z0-9_]+)\$/', $text, $matches);
                $found = array_unique($matches[1]);

                foreach ($found as $varName) {

                    // ❌ Skip unwanted variables
                    if (in_array(strtolower($varName), $skipVariables)) {
                        continue;
                    }

                    $variables[] = '$' . $varName . '$';
                    $attrCode    = '@' . strtolower($varName);

                    if ($categoryId && $saveAttributes) {
                        CategoryAttribute::firstOrCreate(
                            ['category_id' => $categoryId, 'attribute_code' => $attrCode],
                            [
                                'attribute_name'   => ucwords(str_replace('_', ' ', $varName)),
                                'attribute_values' => '',
                                'input_type'       => 1,
                                'default_value'    => '',
                                'sort_order'       => 0,
                                'is_required'      => 0,
                                'is_active'        => 1,
                            ]
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            // ignore
        }

        return $variables;
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'language_id'     => 'required|exists:languages,id',
            'sub_category_id' => 'nullable|exists:deal_categories,id',
            'document'        => self::templateRules(false),
        ]);

        $document   = Document::findOrFail($id);
        $language   = Language::findOrFail($request->language_id);
        $categoryId = $request->filled('sub_category_id')
            ? $request->sub_category_id
            : $request->input('category_id', $document->category_id);

        $category = DealCategory::findOrFail($categoryId);

        // Build folder path
        $parent     = DealCategory::find($category->parent_id);
        $parentName = $parent ? $parent->category_name : $category->category_name;
        $folderPath = $parent
            ? "uploads/{$parentName}/{$category->category_name}/{$language->language_name}"
            : "uploads/{$category->category_name}/{$language->language_name}";

        $fileName = $category->category_name . '_' . $language->language_name . '.' . self::TEMPLATE_EXTENSION;
        $fullPath = $folderPath . '/' . $fileName;

        if ($request->hasFile('document')) {
            if (!Storage::exists($folderPath)) {
                Storage::makeDirectory($folderPath);
            }

            // Write the new template first: if a legacy .doc turns out to be
            // unconvertible this throws, and the old file is still there.
            $this->storeTemplate($request->file('document'), $folderPath, $fileName);

            // Remove the superseded file only once the new one is in place.
            if ($document->file_path && $document->file_path !== $fullPath) {
                Storage::delete($document->file_path);
            }
        } else {
            $fullPath = $document->file_path; // keep existing
        }

        $document->update([
            'category_id' => $categoryId,
            'language_id' => $request->language_id,
            'file_path'   => $fullPath,
        ]);

        $variables = [];
        if ($request->boolean('extract_attributes', false) && Storage::exists($fullPath)) {
            $variables = $this->extractVariables(Storage::path($fullPath), $categoryId, true);
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Document updated successfully.' . (count($variables) ? ' ' . count($variables) . ' variable(s) saved.' : ''),
            'variables' => $variables,
        ]);
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        Storage::delete($document->file_path);
        $document->delete();
        return back()->with('success', 'Document deleted successfully.');
    }

    public function download($id)
    {
        $document = Document::findOrFail($id);
        if (!Storage::exists($document->file_path)) {
            return back()->with('error', 'File not found on server.');
        }
        return Storage::download($document->file_path);
    }

    public function preview($id)
    {
        $document = Document::findOrFail($id);

        if (!Storage::exists($document->file_path)) {
            return response()->json(['success' => false, 'html' => '']);
        }

        $filePath = Storage::path($document->file_path);
        $html = '';

        try {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                $xml = $zip->getFromName('word/document.xml');
                $zip->close();

                // Parse paragraphs and runs from XML
                $dom = new \DOMDocument();
                @$dom->loadXML($xml);
                $xpath = new \DOMXPath($dom);
                $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

                $paragraphs = $xpath->query('//w:p');
                foreach ($paragraphs as $para) {
                    $paraText = '';
                    $isBold = false;
                    $isCenter = false;

                    // Check paragraph alignment
                    $jc = $xpath->query('.//w:jc', $para);
                    if ($jc->length > 0) {
                        $val = $jc->item(0)->getAttribute('w:val');
                        if ($val === 'center') $isCenter = true;
                    }

                    // Get runs
                    $runs = $xpath->query('.//w:r', $para);
                    foreach ($runs as $run) {
                        $bold = $xpath->query('.//w:b', $run);
                        $runBold = $bold->length > 0;

                        $texts = $xpath->query('.//w:t', $run);
                        $runText = '';
                        foreach ($texts as $t) {
                            $runText .= $t->nodeValue;
                        }

                        if ($runBold && $runText) {
                            $paraText .= '<strong>' . e($runText) . '</strong>';
                        } else {
                            $paraText .= e($runText);
                        }
                    }

                    if (trim($paraText) === '') {
                        $html .= '<p>&nbsp;</p>';
                    } else {
                        $align = $isCenter ? ' style="text-align:center;"' : '';
                        $html .= '<p' . $align . '>' . $paraText . '</p>';
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'html' => '']);
        }

        return response()->json(['success' => true, 'html' => $html]);
    }
}
