<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Page;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;


class PageController extends Controller
{
  
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     if (request()->ajax()) {
    //         return DataTables::of(Page::query())
    //             ->addIndexColumn()
    //             ->addColumn('action', function ($page) {
    //                 $editUrl = route('pages.edit', $page->id);
    //                 $deleteUrl = route('pages.destroy', $page->id);
    //                 $csrfToken = csrf_token();

    //                 return '<div class="text-center">
    //                     <a href="' . $editUrl . '" class="edit btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i></a>
    //                     <a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal_' . $page->id . '" class="btn btn-danger btn-sm" title="Delete">
    //                         <i class="bi bi-trash"></i>
    //                     </a>
    //                     <!-- Modal -->
    //                     <div id="delete_modal_' . $page->id . '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    //                         <div class="modal-dialog">
    //                             <div class="modal-content">
    //                                 <div class="modal-header">
    //                                     <h4 class="modal-title">Confirmation</h4>
    //                                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    //                                 </div>
    //                                 <div class="modal-body">
    //                                     <p>Are you sure you want to delete this item? This action cannot be undone and you will be unable to recover any data.</p>
    //                                 </div>
    //                                 <div class="modal-footer">
    //                                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    //                                     <form action="' . $deleteUrl . '" method="POST" style="display:inline;">
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
    //             ->editColumn('status', function ($page) {
    //                 return $page->status === 'Active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
    //             })
    //             ->rawColumns(['action', 'status'])
    //             ->make(true);
    //     }

    //     return view('admin.pages.index');
    // }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(Page::query())
                ->addIndexColumn()
                ->addColumn('action', function ($page) {
                    // Your existing action buttons
                    $editUrl = route('pages.edit', $page->id);
                    $deleteUrl = route('pages.destroy', $page->id);
                    $csrfToken = csrf_token();
    
                    return '<div class="text-center">
                    <a href="' . $editUrl . '" class="edit btn btn-primary btn-sm">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <a data-bs-toggle="modal" href="#delete_modal_' . $page->id . '" class="btn btn-danger btn-sm" title="Delete">
                        <i class="bi bi-trash"></i>
                    </a>
                    <div id="delete_modal_' . $page->id . '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                
                })
                ->editColumn('status', function ($page) {
                    $csrfToken = csrf_token();
                    $route = route('pages.status_change', $page->id);
                    $buttonClass = $page->status ? 'outline-success' : 'outline-danger';
                    $buttonText = $page->status ? 'Active' : 'Inactive';
                    $newStatus = $page->status ? 0 : 1;
                
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
                
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
    
        return view('admin.pages.index');
    }
    
    


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
      
        // Validate the incoming request data
        $request->validate([
            'page_title' => 'required|string|max:255',
            'page_details' => 'required|string',
            'status' => 'required|in:Active,Inactive',
            'description' => 'nullable|string',
        ]);

        // Generate the slug based on the page title
        $slug = Str::slug($request->input('page_title'));

        // Create a new page record in the database
        Page::create([
            'page_title' => $request->input('page_title'),
            'page_slug' => $slug,
            'page_details' => $request->input('page_details'),
            'status' => $request->input('status'),
            'description' => $request->input('description'),
        ]);

        // Redirect to the index page with a success message
        return redirect()->route('pages.index')->with('success', 'Page created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Page $page)
    {
        return view('pages.show', compact('page'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $page)
    {
      
        // Validate the incoming request data
        $request->validate([
            'page_title' => 'required|string|max:255',
            'page_details' => 'required|string',
            'status' => 'required|in:Active,Inactive',
            'description' => 'nullable|string',
        ]);

        // Generate the slug based on the page title
        // Here we assume slug should be updated with title change. If slug should remain unchanged,
        // remove slug generation logic and use $page->page_slug
        $slug = Str::slug($request->input('page_title'));

        // Update the existing page record in the database
        $page->update([
            'page_title' => $request->input('page_title'),
            'page_slug' => $slug,
            'page_details' => $request->input('page_details'),
            'status' => $request->input('status'),
            'description' => $request->input('description'),
        ]);

        // Redirect to the index page with a success message
        return redirect()->route('pages.index')->with('success', 'Page updated successfully!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('pages.index')->with('success', 'Page deleted successfully!');
    }

    public function statusChange(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'status' => 'required|in:0,1', // Ensure the status is either 0 or 1
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    // Find the page by ID and update status
    $page = Page::findOrFail($id);
    $page->status = $request->input('status'); // Change to is_active
    $page->save(); // Persist the changes

    return redirect()->back()->with('success', 'Status updated successfully!');
}



}
