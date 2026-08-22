<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Http\Requests\StoreCmsPageRequest;
use App\Http\Requests\UpdateCmsPageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class CmsPageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(CmsPage::query())
                ->addIndexColumn()
                ->addColumn('featured_image', function ($cmsPage) {
                    if ($cmsPage->featured_image) {
                        return '<div class="text-center"><img src="' . asset('storage/cms/' . $cmsPage->featured_image) . '" width="50" height="50" style="object-fit: cover; border-radius: 4px;"></div>';
                    }
                    return '<div class="text-center"><span class="text-muted">-</span></div>';
                })
                ->addColumn('status', function ($cmsPage) {
                    $badgeClass = $cmsPage->status === 'Active' ? 'bg-success' : 'bg-danger';
                    return '<div class="text-center"><span class="badge ' . $badgeClass . '">' . $cmsPage->status . '</span></div>';
                })
                ->addColumn('created_at', function ($cmsPage) {
                    return $cmsPage->created_at ? $cmsPage->created_at->format('Y-m-d H:i:s') : '-';
                })
                ->addColumn('action', function ($cmsPage) {
                    $showUrl = route('cms-pages.show', $cmsPage->id);
                    $editUrl = route('cms-pages.edit', $cmsPage->id);
                    $deleteUrl = route('cms-pages.destroy', $cmsPage->id);
                    $csrfToken = csrf_token();

                    return '<div class="text-center">
                        <a href="' . $showUrl . '" class="btn btn-info btn-sm text-white" title="View"><i class="bi bi-eye"></i></a>
                        <a href="' . $editUrl . '" class="btn btn-primary btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>
                        <a data-bs-toggle="modal" href="#delete_modal_' . $cmsPage->id . '" class="btn btn-danger btn-sm" title="Delete">
                            <i class="bi bi-trash"></i>
                        </a>
                        <div id="delete_modal_' . $cmsPage->id . '" class="modal fade text-start" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Confirmation</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-start">
                                        <p>Are you sure you want to delete this CMS Page? This action will soft-delete the page and you will be unable to access it.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                })
                ->rawColumns(['featured_image', 'status', 'action'])
                ->make(true);
        }

        return view('admin.cms_pages.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.cms_pages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCmsPageRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            
            // Handle Featured Image Upload
            if ($request->hasFile('featured_image')) {
                // Ensure directory exists
                if (!Storage::disk('public')->exists('cms')) {
                    Storage::disk('public')->makeDirectory('cms');
                }
                
                $file = $request->file('featured_image');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('cms', $filename, 'public');
                $data['featured_image'] = $filename;
            }

            // Set auditing fields
            $data['created_by'] = auth()->id();

            // Create record
            CmsPage::create($data);

            DB::commit();

            return redirect()->route('cms-pages.index')->with('success', 'CMS Page created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error creating CMS Page: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cmsPage = CmsPage::with(['creator', 'updater'])->findOrFail($id);
        return view('admin.cms_pages.show', compact('cmsPage'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $cmsPage = CmsPage::findOrFail($id);
        return view('admin.cms_pages.edit', compact('cmsPage'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCmsPageRequest $request, $id)
    {
        $cmsPage = CmsPage::findOrFail($id);
        
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Handle Featured Image Upload and deletion of old one
            if ($request->hasFile('featured_image')) {
                // Ensure directory exists
                if (!Storage::disk('public')->exists('cms')) {
                    Storage::disk('public')->makeDirectory('cms');
                }

                // Delete old image
                if ($cmsPage->featured_image) {
                    Storage::disk('public')->delete('cms/' . $cmsPage->featured_image);
                }

                $file = $request->file('featured_image');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('cms', $filename, 'public');
                $data['featured_image'] = $filename;
            }

            // Set auditing fields
            $data['updated_by'] = auth()->id();

            // Update record
            $cmsPage->update($data);

            DB::commit();

            return redirect()->route('cms-pages.index')->with('success', 'CMS Page updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error updating CMS Page: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cmsPage = CmsPage::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Delete associated image file
            if ($cmsPage->featured_image) {
                Storage::disk('public')->delete('cms/' . $cmsPage->featured_image);
                $cmsPage->featured_image = null;
                $cmsPage->save();
            }

            // Soft delete record
            $cmsPage->delete();

            DB::commit();

            return redirect()->route('cms-pages.index')->with('success', 'CMS Page deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('cms-pages.index')->with('error', 'Error deleting CMS Page: ' . $e->getMessage());
        }
    }

    /**
     * Upload an image from the rich text editor.
     */
    public function uploadEditorImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        try {
            if ($request->hasFile('file')) {
                if (!Storage::disk('public')->exists('cms/editor')) {
                    Storage::disk('public')->makeDirectory('cms/editor');
                }

                $file = $request->file('file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('cms/editor', $filename, 'public');

                $url = asset('storage/cms/editor/' . $filename);

                return response()->json([
                    'location' => $url,
                    'url' => $url,
                    'uploaded' => true
                ]);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['error' => 'Image upload failed.'], 400);
    }
}
