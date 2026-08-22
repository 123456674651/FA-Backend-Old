<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DealCategory;
use App\Traits\ImageResizer;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\MyFormRequest;
use App\Models\CategoryAttribute;
use Yajra\DataTables\DataTables;

class DealCategoryController extends Controller
{
    use ImageResizer;

    /**
     * Display a listing of the resource.
     */


    public function index(Request $request)
    {
        $parentId = $request->get('parent_id');

        if ($request->ajax()) {
            if ($parentId) {
                $dealCategories = DealCategory::where('parent_id', $parentId)->withCount('children')->get();
            } else {
                $dealCategories = DealCategory::where(function ($query) {
                    $query->where('parent_id', 0)->orWhereNull('parent_id');
                })->withCount('children')->get();
            }

            return Datatables::of($dealCategories)
                ->addIndexColumn()
                ->addColumn('logo', function ($dealCategory) {
                    return '<div class="text-center">
                                 <img src="' . asset('admin/images/category_image_thumb/' . $dealCategory->category_image) . '" width="50" height="50">
                             </div>';
                })
                ->addColumn('is_on_interest', function ($dealCategory) {
                    return $dealCategory->is_on_interest ? 'Yes' : 'No';
                })
                ->addColumn('status', function ($dealCategory) {
                    $csrfToken = csrf_token();
                    $route = route('status_changes', $dealCategory->id);
                    $buttonClass = $dealCategory->is_active ? 'badge bg-success text-white' : 'badge bg-danger text-white';
                    $buttonText = $dealCategory->is_active ? 'Active' : 'Inactive';
                    $newStatus = $dealCategory->is_active ? 0 : 1;

                    return '<div class="text-center">
                    <form action="' . $route . '" method="POST" style="display:inline;">
                        <input type="hidden" name="_token" value="' . $csrfToken . '">
                        <input type="hidden" name="_method" value="PATCH">
                        <button type="submit" class="btn btn-' . $buttonClass . ' btn-sm">
                            ' . $buttonText . '
                        </button>
                        <input type="hidden" name="status" value="' . $newStatus . '">
                    </form>
                </div>';
                })
                ->addColumn('action', function ($dealCategory) use ($parentId) {
                    $editUrl = route('deal_categories.edit', $dealCategory->id);
                    $deleteUrl = route('deal_categories.destroy', $dealCategory->id);
                    $attributeUrl = route('category_attribute', $dealCategory->id);
                    $manageAttributesUrl = route('manageAttributes', $dealCategory->id);
                    $warningsUrl = route('category-warnings.index', ['category_id' => $dealCategory->id]);

                    $csrfToken = csrf_token();

                    $buttons = '<div class="text-center">
                                 <a href="' . $editUrl . '" class="edit btn btn-primary btn-sm" title="Edit">
                                     <i class="bi bi-pencil-square"></i>
                                 </a>
                                  
                                 <a href="' . $warningsUrl . '" class="edit btn btn-secondary btn-sm" title="Warnings">
                                     <i class="bi bi-shield-exclamation"></i>
                                 </a>';

                    $buttons .= '  <a href="' . $manageAttributesUrl . '" class="edit btn btn-info btn-sm text-white" title="Manage Attributes">
                                     <i class="bi bi-gear"></i>
                                 </a>
                                 <div id="delete_modal_' . $dealCategory->id . '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                                                 <form action="' . $deleteUrl . '" method="POST" style="display:inline;">
                                                     <input type="hidden" name="_token" value="' . $csrfToken . '">
                                                     <input type="hidden" name="_method" value="DELETE">
                                                     <button type="submit" class="btn btn-danger">Yes, delete it!</button>
                                                 </form>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>';

                    return $buttons;
                })
                ->addColumn('sub_category', function ($dealCategory) use ($parentId) {
                    if (!$parentId && $dealCategory->children_count > 0) {
                        $subAgreementsUrl = route('deal_categories.index', ['parent_id' => $dealCategory->id]);
                        return '<div class="text-center">
                                    <a href="' . $subAgreementsUrl . '" class="btn btn-outline-secondary btn-sm text-dark" title="View Sub Agreements">
                                        <i class="bi bi-list-nested"></i> View Sub Agreements
                                    </a>
                                </div>';
                    }
                    return '-';
                })
                ->rawColumns(['logo', 'action', 'status', 'sub_category'])
                ->make(true);
        }

        $parentCategory = null;
        if ($parentId) {
            $parentCategory = DealCategory::findOrFail($parentId);
        }

        return view('admin.deal_categories.index', compact('parentCategory', 'parentId'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.deal_categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MyFormRequest $request)
    {

        // Initialize $imageName
        $imageName = null;

        // Handle file upload
        if ($request->file('category_image')) {
            $imageName = $this->image_resize($request->file('category_image'), 'category_image');
        } else {
            $imageName = 'default.webp';
        }

        if ($imageName) {
            $category = new DealCategory();
            $category->category_image = $imageName;
            $category->category_name = $request->input('category_name');
            $category->is_active = $request->input('is_active');
            $category->deal_price = $request->input('deal_price');
            $category->is_on_interest = $request->input('is_on_interest');
            $category->description = $request->input('description');
            $category->description = $request->input('description');
            $category->parent_id = $request->input('parent_id');
            $category->save();


            return redirect()->route('deal_categories.index')->with('success', 'Deal Category added successfully!!!!');
        } else {
            return redirect()->back()->with('error', 'Image upload failed!!!!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        return view('admin.deal_categories.show', compact('dealCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $dealCategory = DealCategory::find($id);
        $dealCategories = DealCategory::where('is_active', 1)->pluck('category_name', 'id');

        return view('admin.deal_categories.edit', compact('dealCategory', 'dealCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MyFormRequest $request, $id)
    {
        // Find the existing category by ID
        $category = DealCategory::find($id);

        // Check if category exists
        if (!$category) {
            return redirect()->back()->with('error', 'Deal Category not found!!!!');
        }

        // Handle file upload
        if ($request->file('category_image')) {
            $imageName = $this->image_resize($request->file('category_image'), 'category_image');
        } else {
            $imageName = $category->category_image; // Retain the existing image if no new image is provided
        }

        // Update the category properties
        $category->category_image = $imageName;

        $category->category_name = $request->input('category_name');
        $category->is_active = $request->input('is_active');
        $category->deal_price = $request->input('deal_price');
        $category->is_on_interest = $request->input('is_on_interest');
        $category->description = $request->input('description');
        $category->lable = $request->input('lable');
        $category->parent_id = $request->input('parent_id') ?? 0;
        $category->save();

        return redirect()->route('deal_categories.index')->with('success', 'Deal Category updated successfully!!!!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $dealCategory = DealCategory::find($id);

        if (!$dealCategory) {
            return redirect()->back()->with('error', 'Deal Category not found!!!!');
        }

        $dealCategory->delete();

        return redirect()->route('deal_categories.index')
            ->with('success', 'Deal Category deleted successfully.');
    }

    public function status_changes(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1', // Ensure the status is either 0 or 1
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Find the DealCategory by ID
        $dealCategory = DealCategory::findOrFail($id);

        // Update the status based on the provided value
        $dealCategory->is_active = $request->input('status');
        $dealCategory->save();  // Save the model to persist the changes in the database

        return redirect()->back()->with('success', 'Status updated successfully..!!');
    }

    public function category_attribute($id)
    {
        $category = DealCategory::findOrFail($id);
        $data = CategoryAttribute::with('dealCategory')
            ->where('category_id', $id)
            ->orderByRaw('ISNULL(sort_order), sort_order ASC')
            ->get();


        return view('admin.attribute.index', compact('data', 'category'));
    }

    public function reorder(Request $request)
    {
        // dd($request->all());
        if (!$request->has('sort_order') || !is_array($request->sort_order)) {
            return response()->json(['message' => 'Invalid data'], 400);
        }

        foreach ($request->sort_order as $order) {
            $attribute = CategoryAttribute::find($order['id']);
            if ($attribute) {
                $attribute->update(['sort_order' => $order['sort_order']]);
            }
        }

        return response()->json(['message' => 'Update Successfully'], 200);
    }


    // public function category_attribute($id)
// {
//     $data = CategoryAttribute::with('dealCategory')->where('category_id', $id)->get();

    //     if (request()->ajax()) {
//         return DataTables::of($data)
//             ->addIndexColumn()
//             ->addColumn('category_name', function ($row) {
//                 return $row->dealCategory ? $row->dealCategory->category_name : 'N/A';
//             })
//             ->addColumn('attribute_name', function ($row) {
//                 return $row->attribute_name ? $row->attribute_name : 'N/A';
//             })
//             ->addColumn('action', function ($attribute) {
//                 $editUrl = route('attribute.edit', $attribute->id);
//                 $deleteUrl = route('attribute.delete', $attribute->id);
//                 $csrfToken = csrf_token();
//                 return '<div class="text-center">
//                     <a href="' . $editUrl . '" class="edit btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i></a>
//                     <a data-bs-toggle="modal" href="#delete_modal_' . $attribute->id . '" class="btn btn-danger btn-sm" title="Delete">
//                         <i class="bi bi-trash"></i>
//                     </a>
//                     <div id="delete_modal_' . $attribute->id . '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
//                         <div class="modal-dialog">
//                             <div class="modal-content">
//                                 <div class="modal-header">
//                                     <h4 class="modal-title">Confirmation</h4>
//                                     <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
//                                 </div>
//                                 <div class="modal-body">
//                                     <p>Are you sure you want to delete this item? This action cannot be undone and you will be unable to recover any data.</p>
//                                 </div>
//                                 <div class="modal-footer">
//                                     <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancel</button>
//                                     <form action="' .  $deleteUrl . '" method="POST">
//                                         <input type="hidden" name="_token" value="' . $csrfToken . '">
//                                         <input type="hidden" name="_method" value="DELETE">
//                                         <button type="submit" class="btn btn-danger">Yes, delete it!</button>
//                                     </form>
//                                 </div>
//                             </div>
//                         </div>
//                     </div>
//                 </div>';
//             })
//             ->rawColumns(['action'])
//             ->make(true);
//     }

    //     return view('admin.attribute.index', compact('id')); 
// }

    public function template($id)
    {
        if (request()->ajax()) {
            $data = CategoryLanguage::with('dealCategory', 'languages')->where('category_id', $id)->get(); // Eager load relationship
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('category_name', function ($row) {
                    return $row->dealCategory ? $row->dealCategory->category_name : 'N/A';
                })
                ->addColumn('language_name', function ($row) {
                    return $row->languages ? $row->languages->language_name : 'N/A';
                })
                ->addColumn('action', function ($template) {
                    $editUrl = route('template.edit', $template->id);
                    $deleteUrl = route('template.delete', $template->id);

                    $csrfToken = csrf_token();

                    return '<div class="text-center">
                    <a href="' . $editUrl . '" class="edit btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i></a>
                    <a data-bs-toggle="modal" href="#delete_modal_' . $template->id . '" class="btn btn-danger btn-sm" title="Delete">
                        <i class="bi bi-trash"></i>
                    </a>
                    <div id="delete_modal_' . $template->id . '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                                    <form action="' . $deleteUrl . '" method="POST">
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
                ->addColumn('template', function ($dealCategory) {
                    $duplicateUrl = route('emplate.duplicate.entry', $dealCategory->id);

                    // Example of a column with a delete button, adjust based on need
                    return '<div class="text-center">
                    <a data-bs-toggle="modalx" href="http://127.0.0.1:8000/api/rent_agreement?persone_1_id=38&persone_2_id=38&rent=5000&deposite=15000&start_date=16-11-2024&duration=11&view=1&lang=' . $dealCategory->language_code . '&category_id=' . $dealCategory->category_id . '&language_id=' . $dealCategory->language_id . '" class="btn btn-danger btn-sm" title="Delete">
                        <i class="bi bi-eye"></i>
                    </a>

                     <a href="' . $duplicateUrl . '" data-bs-toggle="modalx"   class="btn btn-warning btn-sm" title="Delete">
                        <i class="bi bi-copy"></i>
                    </a>

                    

                </div>';
                })

                ->rawColumns(['action', 'template'])
                ->make(true);
        }
        return view('admin.templates.index', compact('id'));
    }

    // public function manageAttributes($id)
    // {
    //   $category = DealCategory::where('parent_id', $id)->get();

    // $attributes = CategoryAttribute::where('category_id', $id)->get();
    // Get all documents for sub-categories of this parent
    // $subCategoryIds = $category->pluck('id')->toArray();

    // $documents = \App\Models\Document::with(['language', 'category'])
    //->whereIn('category_id', $subCategoryIds)
    // ->latest()
    ////   ->get();

    //   return view('admin.deal_categories.manage_attributes', compact('category', 'attributes', 'documents', 'id'));
    // }

    public function manageAttributes($id)
    {
        $category = DealCategory::where('parent_id', $id)->get();
        $attributes = CategoryAttribute::where('category_id', $id)->get();

        $subCategoryIds = $category->pluck('id')->toArray();

        // Include parent category itself so documents uploaded without a sub category also show
        $allCategoryIds = array_merge([$id], $subCategoryIds);

        $documents = \App\Models\Document::with(['language', 'category'])
            ->whereIn('category_id', $allCategoryIds)
            ->latest()
            ->get();

        // Build uploaded combos for both parent and sub categories
        $uploadedCombos = $documents->map(fn($d) => $d->category_id . '_' . $d->language_id)->toArray();

        return view('admin.deal_categories.manage_attributes', compact('category', 'attributes', 'documents', 'id', 'uploadedCombos'));
    }
}
