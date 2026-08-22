<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Language;
use App\Models\DealCategory;
use App\Models\CategoryAttribute;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Response;


class AttributeController extends Controller
{
    public function index()
    {
        $data = CategoryAttribute::with('dealCategory')->get();

        if (request()->ajax()) {
            $data = CategoryAttribute::with('dealCategory')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('category_name', function ($row) {
                    return $row->dealCategory ? $row->dealCategory->category_name : 'N/A';
                })
                ->addColumn('attribute_name', function ($row) {
                    return $row->attribute_name ? $row->attribute_name : 'N/A';
                })

                ->addColumn('action', function ($attribute) {
                    $editUrl = route('attribute.edit', $attribute->id);
                    $deleteUrl = route('attribute.delete', $attribute->id);
                    $csrfToken = csrf_token();
                    return '<div class="text-center">
                        <a href="' . $editUrl . '" class="edit btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i></a>
                        <a data-bs-toggle="modal" href="#delete_modal_' . $attribute->id . '" class="btn btn-danger btn-sm" title="Delete">
                            <i class="bi bi-trash"></i>
                        </a>
                        <div id="delete_modal_' . $attribute->id . '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Confirmation</h4>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete this item? This action cannot be undone and you will be unable to recover any data.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancel</button>
                                        <form action="' .  $deleteUrl . '" method="POST">
                                            <input type="hidden" name="_token" value="' . $csrfToken . '">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger">Yes, delete it!</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.attribute.index');
    }

    public function create()
    {

        $dealCategories = DealCategory::all();
        $languages = Language::where('is_active', 1)->orderBy('language_name')->get();


        return view('admin.attribute.create', compact('dealCategories', 'languages',));
    }

    public function edit($id)
    {

        $categoryAttribute = CategoryAttribute::find($id);
        $dealCategories = DealCategory::all();
        $languages = Language::where('is_active', 1)->orderBy('language_name')->get();


        return view('admin.attribute.edit', compact('dealCategories', 'languages','categoryAttribute'));
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'attribute_name' => 'required',
            'attribute_value' => 'required',
            'input_type' => 'required',
            'default_value' => 'required',

        ]);

        // If validation fails, redirect back with errors
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $convertedCode = '@' . strtolower(str_replace(' ', '_', $request->attribute_name));

        $categorie_attribute = new CategoryAttribute;
        $categorie_attribute->category_id = $request->category_id;
        $categorie_attribute->attribute_name = $request->attribute_name;
                $categorie_attribute->attribute_code = $convertedCode;
        $categorie_attribute->attribute_values = $request->attribute_value;
        $categorie_attribute->input_type = $request->input_type;
        $categorie_attribute->default_value = $request->default_value;
        $categorie_attribute->sort_order = $request->category_id;
        $categorie_attribute->is_required = $request->is_required;
        $categorie_attribute->save();

        return redirect()->back()->with('success', 'Attribute added successfully!');
    }


    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'attribute_name' => 'required',
            'attribute_value' => 'required',
            'input_type' => 'required',
            'default_value' => 'required',

        ]);


        // If validation fails, redirect back with errors
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $categorie_attribute = CategoryAttribute::find($request->id);
        $categorie_attribute->category_id = $request->category_id;
        $categorie_attribute->attribute_name = $request->attribute_name;
              $categorie_attribute->attribute_code = $request->attribute_code ?? $categorie_attribute->attribute_code;
        $categorie_attribute->attribute_values = $request->attribute_value;
        $categorie_attribute->input_type = $request->input_type;
        $categorie_attribute->default_value = $request->default_value;
        $categorie_attribute->sort_order = $request->category_id;
        $categorie_attribute->is_required = $request->is_required;
        $categorie_attribute->save();

        return redirect()->back()->with('success', 'Attribute added successfully!');
    }
  
  
    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $count = CategoryAttribute::whereIn('id', $request->ids)->delete();
        return response()->json(['message' => $count . ' attribute(s) deleted successfully.']);
    }


    public function list(Request $request)
    {

        $attributes = CategoryAttribute::where('is_active', 1)->where('category_id', $request->category_id)->orderBy('sort_order', 'desc')->get();
        foreach ($attributes as $attribute) {
            $array = $attribute->attribute_values;

            if (!is_array($attribute->attribute_values)  &&  $attribute->input_type == 2) {
                $array = explode(',', $attribute->attribute_values);
            };

            $attribute->attribute_values = $array;
        }

        return response()->json([
            'data' => $attributes
        ]);
    }


    public function delete($id)
{
    $originalEntry = CategoryAttribute::find($id);
    if (!$originalEntry) {
        return redirect()->back()->with('error', 'Entry not found.');
    }

    $originalEntry->delete();

    return redirect()->back()->with('success', 'Entry deleted successfully!');
}

      public function export($categoryId)
    {
        $attributes = CategoryAttribute::with('dealCategory')
            ->where('category_id', $categoryId)
            ->orderByRaw('ISNULL(sort_order), sort_order ASC')
            ->get();

        $headers = [
            'id', 'category_id', 'category_name', 'attribute_name', 'attribute_code',
            'attribute_values', 'input_type', 'input_type_name', 'default_value',
            'sort_order', 'is_required', 'is_active', 'created_at', 'updated_at'
        ];

        $csvContent = implode(',', $headers) . "\n";

        foreach ($attributes as $attr) {
            $csvContent .= implode(',', [
                $attr->id,
                $attr->category_id,
                '"' . str_replace('"', '""', $attr->dealCategory->category_name ?? '') . '"',
                '"' . str_replace('"', '""', $attr->attribute_name) . '"',
                '"' . str_replace('"', '""', $attr->attribute_code) . '"',
                '"' . str_replace('"', '""', $attr->attribute_values ?? '') . '"',
                $attr->input_type,
                '"' . str_replace('"', '""', $attr->input_type_name) . '"',
                '"' . str_replace('"', '""', $attr->default_value ?? '') . '"',
                $attr->sort_order,
                $attr->is_required,
                $attr->is_active,
                '"' . $attr->created_at . '"',
                '"' . $attr->updated_at . '"',
            ]) . "\n";
        }

        return Response::make($csvContent, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attributes_category_' . $categoryId . '.csv"',
        ]);
    }

    public function import(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'category_id'   => 'required|integer|exists:deal_categories,id',
            // 'import_file'   => 'required|file|mimes:csv,xlsx,xls',
        ]);

        $file = $request->file('import_file');
        // dd($file);
        $categoryId = $request->category_id;

        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // skip header row

        $imported = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) continue;

            [$attrName, $attrValues, $inputType, $defaultValue, $sortOrder, $isRequired, $isActive] = array_pad($row, 7, null);

            if (empty(trim($attrName))) continue;

            $convertedCode = '@' . strtolower(str_replace(' ', '_', trim($attrName)));

            CategoryAttribute::create([
                'category_id'       => $categoryId,
                'attribute_name'    => trim($attrName),
                'attribute_code'    => $convertedCode,
                'attribute_values'  => trim($attrValues ?? ''),
                'input_type'        => intval($inputType ?? 1),
                'default_value'     => trim($defaultValue ?? ''),
                'sort_order'        => intval($sortOrder ?? 0),
                'is_required'       => intval($isRequired ?? 0),
                'is_active'         => intval($isActive ?? 1),
            ]);
            $imported++;
        }
        fclose($handle);

        return redirect()->back()->with('success', $imported . ' attribute(s) imported successfully!');
    }
}
