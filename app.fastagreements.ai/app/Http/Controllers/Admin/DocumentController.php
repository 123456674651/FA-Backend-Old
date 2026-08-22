<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\DealCategory;
use App\Models\Language;
use App\Models\CategoryAttribute;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
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
            'document'    => 'required|mimes:doc,docx|max:10240',
            'category_id' => 'required|exists:deal_categories,id',
            'language_id' => 'required|exists:languages,id',
        ]);

        $category = DealCategory::findOrFail($request->category_id);
        $language = Language::findOrFail($request->language_id);

        $folderPath = "uploads/{$category->category_name}/{$language->language_name}";

        if (!Storage::exists($folderPath)) {
            Storage::makeDirectory($folderPath);
        }

        $fileName = $category->category_name . '_' . $language->language_name . '.docx';
        $fullPath = $folderPath . '/' . $fileName;

        // Replace if exists
        if (Storage::exists($fullPath)) {
            Storage::delete($fullPath);
        }

        $request->file('document')->storeAs($folderPath, $fileName);

        Document::updateOrCreate(
            ['category_id' => $request->category_id, 'language_id' => $request->language_id],
            ['file_path' => $fullPath]
        );

        return back()->with('success', 'Document uploaded successfully!');
    }

    public function uploadDocx(Request $request)
    {
        $request->validate([
            'document'        => 'required|mimes:doc,docx|max:10240',
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
            $fileName   = $category->category_name . '_' . $language->language_name . '.docx';
        } else {
            $folderPath = "uploads/{$category->category_name}/{$language->language_name}";
            $fileName   = $category->category_name . '_' . $language->language_name . '.docx';
        }

        if (!Storage::exists($folderPath)) {
            Storage::makeDirectory($folderPath);
        }

        $fullPath = $folderPath . '/' . $fileName;

        if (Storage::exists($fullPath)) {
            Storage::delete($fullPath);
        }

        $request->file('document')->storeAs($folderPath, $fileName);

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
            'document'        => 'nullable|mimes:doc,docx|max:10240',
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

        $fileName = $category->category_name . '_' . $language->language_name . '.docx';
        $fullPath = $folderPath . '/' . $fileName;

        if ($request->hasFile('document')) {
            // Remove old file if path changed
            if ($document->file_path && $document->file_path !== $fullPath) {
                Storage::delete($document->file_path);
            }
            if (!Storage::exists($folderPath)) {
                Storage::makeDirectory($folderPath);
            }
            if (Storage::exists($fullPath)) {
                Storage::delete($fullPath);
            }
            $request->file('document')->storeAs($folderPath, $fileName);
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
