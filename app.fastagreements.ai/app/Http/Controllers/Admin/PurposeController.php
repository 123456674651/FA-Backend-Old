<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purpose;
use App\Traits\ImageResizer;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class PurposeController extends Controller
{
    use ImageResizer;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $purposes = Purpose::all();
            return Datatables::of($purposes)->addIndexColumn()
                ->addColumn('image', function ($purpose) {
                    return '<div class="text-center"><img src="' . asset('admin/images/purpose_image_thumb/' . $purpose->purpose_image) . '" width="50" height="50"></div>';
                })
                ->addColumn('status', function ($purpose) {
                    $csrfToken = csrf_token();
                    $route = route('status_changes_purpose', $purpose->id);
                    $buttonClass = $purpose->is_active ? 'outline-success' : 'outline-danger';
                    $buttonText = $purpose->is_active ? 'Active' : 'Inactive';
                    $newStatus = $purpose->is_active ? 0 : 1;

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
                ->addColumn('action', function ($purpose) {
                    $editUrl = route('purposes.edit', $purpose->id);
                    $deleteUrl = route('purposes.destroy', $purpose->id);
                    $csrfToken = csrf_token();

                    return '<div class="text-center">
                    <a href="' . $editUrl . '" class="edit btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i></a>
                    <a data-bs-toggle="modal" href="#delete_modal_' . $purpose->id . '" class="btn btn-danger btn-sm" title="Delete">
                        <i class="bi bi-trash"></i>
                    </a>
                    <div id="delete_modal_' . $purpose->id . '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                ->rawColumns(['image', 'status', 'action'])
                ->make(true);
        }

        return view('admin.purposes.index');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.purposes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $imageName = null;

        // Handle file upload

        if ($request->file('purpose_image')) {
            $imageName = $this->image_resize($request->file('purpose_image'), 'purpose_image');
        } else {
            $imageName = 'default.webp';
        }

        if ($imageName) {
            $purpose = new Purpose();
            $purpose->purpose_image = $imageName;
            $purpose->purpose_name = $request->input('purpose_name');
            $purpose->is_active = $request->input('is_active');
            $purpose->save();


            return redirect()->route('purposes.index')->with('success', 'Deal Category added successfully!!!!');
        } else {
            return redirect()->back()->with('error', 'Image upload failed!!!!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('purposes.show', compact('purpose'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $purpose = Purpose::find($id);
        return view('admin.purposes.edit', compact('purpose'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $purpose = Purpose::find($id);

        if (!$purpose) {
            return redirect()->route('purposes.index')->with('error', 'Purpose not found.');
        }

        $imageName = $purpose->purpose_image; // Keep the existing image

        // Handle file upload if there's a new image
        if ($request->file('purpose_image')) {
            $imageName = $this->image_resize($request->file('purpose_image'), 'purpose_image');
        }

        if ($imageName) {
            $purpose->purpose_image = $imageName;
            $purpose->purpose_name = $request->input('purpose_name');
            $purpose->is_active = $request->input('is_active');
            $purpose->save();

            return redirect()->route('purposes.index')->with('success', 'Purpose updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Image upload failed.');
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $purpose = Purpose::find($id);
        $purpose->delete();
        return redirect()->route('purposes.index')->with('success', 'Purpose deleted successfully.');
    }

    public function status_changes(request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1', // Ensure the status is either 0 or 1
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $purpose = Purpose::findOrFail($id);

        // Update the status based on the provided value
        $purpose->is_active = $request->input('status');
        $purpose->save();  // Save the model to persist the changes in the database

        return redirect()->back()->with('success', 'Status updated successfully..!!');
    }
}
