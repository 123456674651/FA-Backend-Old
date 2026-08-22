<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryWarning;
use App\Models\DealCategory;
use App\Models\Language;
use App\Http\Requests\StoreCategoryWarningRequest;
use App\Http\Requests\UpdateCategoryWarningRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Exception;

class CategoryWarningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categoryId = $request->get('category_id');
        if (!$categoryId) {
            return redirect()->route('deal_categories.index')->with('error', 'Select a Deal Category first.');
        }

        $category = DealCategory::findOrFail($categoryId);

        if ($request->ajax()) {
            $query = Language::select(
                    'languages.id as language_id',
                    'languages.language_name',
                    'deal_category_warnings.id as warning_id',
                    'deal_category_warnings.title',
                    'deal_category_warnings.display_order',
                    'deal_category_warnings.status',
                    'deal_category_warnings.created_at as warning_created_at'
                )
                ->leftJoin('deal_category_warnings', function ($join) use ($categoryId) {
                    $join->on('deal_category_warnings.language_id', '=', 'languages.id')
                         ->where('deal_category_warnings.deal_category_id', $categoryId);
                })
                ->where('languages.is_active', 1)
                ->orderBy('languages.language_name');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('language', function ($row) {
                    return $row->language_name;
                })
                ->addColumn('title', function ($row) {
                    return $row->title ?? '';
                })
                ->addColumn('display_order', function ($row) {
                    return $row->display_order !== null ? $row->display_order : '';
                })
                ->addColumn('status', function ($row) {
                    if ($row->warning_id === null) {
                        return '';
                    }
                    $csrfToken = csrf_token();
                    $route = route('category-warnings.status', $row->warning_id);
                    $buttonClass = $row->status ? 'outline-success' : 'outline-danger';
                    $buttonText = $row->status ? 'Active' : 'Inactive';
                    $newStatus = $row->status ? 0 : 1;

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
                ->editColumn('warning_created_at', function ($row) {
                    return $row->warning_created_at ? date('Y-m-d H:i:s', strtotime($row->warning_created_at)) : '';
                })
                ->addColumn('action', function ($row) use ($categoryId) {
                    $buttons = '<div class="text-start">';
                    if ($row->warning_id) {
                        $editUrl = route('category-warnings.edit', $row->warning_id);
                    } else {
                        $editUrl = route('category-warnings.create', ['category_id' => $categoryId, 'language_id' => $row->language_id]);
                    }
                    $buttons .= '<a href="' . $editUrl . '" class="edit btn btn-primary btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>';
                    if ($row->warning_id) {
                        $deleteUrl = route('category-warnings.destroy', $row->warning_id);
                        $csrfToken = csrf_token();
                        $buttons .= ' <a data-bs-toggle="modal" href="#delete_modal_' . $row->warning_id . '" class="btn btn-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></a>';
                        $buttons .= '<div id="delete_modal_' . $row->warning_id . '" class="modal fade text-start" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">'
                            . '<div class="modal-dialog">'
                            . '<div class="modal-content">'
                            . '<div class="modal-header">'
                            . '<h4 class="modal-title">Confirmation</h4>'
                            . '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
                            . '</div>'
                            . '<div class="modal-body">'
                            . '<p>Are you sure you want to delete this Category Warning? This action cannot be undone and the warning will be permanently removed.</p>'
                            . '</div>'
                            . '<div class="modal-footer">'
                            . '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>'
                            . '<form action="' . $deleteUrl . '" method="POST" style="display:inline;">'
                            . '<input type="hidden" name="_token" value="' . $csrfToken . '">'
                            . '<input type="hidden" name="_method" value="DELETE">'
                            . '<button type="submit" class="btn btn-danger">Yes, delete it!</button>'
                            . '</form>'
                            . '</div>'
                            . '</div>'
                            . '</div>'
                            . '</div>';
                    }
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('admin.category_warnings.index', compact('category'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $categoryId = $request->get('category_id');
        if (!$categoryId) {
            return redirect()->route('deal_categories.index')->with('error', 'Select a Deal Category first.');
        }

        $category = DealCategory::findOrFail($categoryId);
        $languages = Language::where('is_active', 1)->orderBy('language_name')->get();
        $selectedLanguageId = $request->get('language_id');

        return view('admin.category_warnings.create', compact('category', 'languages', 'selectedLanguageId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryWarningRequest $request)
    {
        try {
            $data = $request->validated();
            $data['status'] = $request->has('status') ? (bool) $request->input('status') : true;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'warning_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('category_warnings'), $filename);
                $data['image'] = 'category_warnings/' . $filename;
            }

            $existingWarning = CategoryWarning::where('deal_category_id', $data['deal_category_id'])
                ->where('language_id', $data['language_id'])
                ->first();

            if ($existingWarning) {
                $existingWarning->update($data);
            } else {
                CategoryWarning::create($data);
            }

            return redirect()->route('category-warnings.index', ['category_id' => $data['deal_category_id']])
                ->with('success', 'Category Warning saved successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error creating warning: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $warning = CategoryWarning::findOrFail($id);
        $category = $warning->dealCategory;
        $languages = Language::where('is_active', 1)->orderBy('language_name')->get();

        return view('admin.category_warnings.edit', compact('warning', 'category', 'languages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryWarningRequest $request, $id)
    {
        try {
            $warning = CategoryWarning::findOrFail($id);
            $data = $request->validated();
            $data['status'] = $request->has('status') ? (bool) $request->input('status') : false;

            $duplicateWarning = CategoryWarning::where('deal_category_id', $warning->deal_category_id)
                ->where('language_id', $data['language_id'])
                ->where('id', '!=', $warning->id)
                ->first();

            if ($duplicateWarning) {
                return redirect()->back()->withErrors(['language_id' => 'A warning already exists for this language.'])->withInput();
            }

            if ($request->hasFile('image')) {
                // Delete old image
                if ($warning->image && file_exists(public_path($warning->image))) {
                    @unlink(public_path($warning->image));
                }

                $file = $request->file('image');
                $filename = 'warning_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('category_warnings'), $filename);
                $data['image'] = 'category_warnings/' . $filename;
            }

            $warning->update($data);

            return redirect()->route('category-warnings.index', ['category_id' => $warning->deal_category_id])
                ->with('success', 'Category Warning updated successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error updating warning: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $warning = CategoryWarning::findOrFail($id);
            $categoryId = $warning->deal_category_id;

            // Delete image file from disk
            if ($warning->image && file_exists(public_path($warning->image))) {
                @unlink(public_path($warning->image));
            }

            $warning->delete();

            return redirect()->route('category-warnings.index', ['category_id' => $categoryId])
                ->with('success', 'Category Warning deleted successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error deleting warning: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the status of the category warning.
     */
    public function toggleStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $warning = CategoryWarning::findOrFail($id);
            $warning->status = $request->input('status');
            $warning->save();

            return redirect()->back()->with('success', 'Status updated successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error updating status: ' . $e->getMessage());
        }
    }
}
