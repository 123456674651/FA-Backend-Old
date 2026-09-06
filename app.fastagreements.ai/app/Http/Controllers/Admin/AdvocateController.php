<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advocate;
use App\Traits\UploadsToS3;
use App\Http\Requests\StoreAdvocateRequest;
use App\Http\Requests\UpdateAdvocateRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;

class AdvocateController extends Controller
{
    use UploadsToS3;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Advocate::query();

            // Apply custom filters
            if ($request->filled('search_name')) {
                $query->where('name', 'like', '%' . $request->search_name . '%');
            }
            if ($request->filled('search_lawyer_type')) {
                $query->where('lawyer_type', 'like', '%' . $request->search_lawyer_type . '%');
            }
            if ($request->filled('search_mobile')) {
                $query->where('mobile_number', 'like', '%' . $request->search_mobile . '%');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('image', function ($row) {
                    if ($row->image && Storage::disk('s3')->exists($row->image)) {
                        return '<div class="text-center"><img src="' . Storage::disk('s3')->url($row->image) . '" class="rounded-circle" width="50" height="50" style="object-fit: cover;"></div>';
                    }
                    return '<div class="text-center"><img src="' . asset('assets/img/profile-img.jpg') . '" class="rounded-circle" width="50" height="50" style="object-fit: cover;"></div>';
                })
                ->editColumn('price', function ($row) {
                    return '₹' . number_format($row->price, 2);
                })
                ->addColumn('is_verified', function ($row) {
                    if ($row->is_verified) {
                        return '<div class="text-center"><span class="badge bg-success"><i class="bi bi-patch-check-fill"></i> Verified</span></div>';
                    }
                    return '<div class="text-center"><span class="badge bg-secondary">Unverified</span></div>';
                })
                ->addColumn('status', function ($row) {
                    $csrfToken = csrf_token();
                    $route = route('advocates.status', $row->id);
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
                ->addColumn('action', function ($row) {
                    $showUrl = route('advocates.show', $row->id);
                    $editUrl = route('advocates.edit', $row->id);
                    $deleteUrl = route('advocates.destroy', $row->id);
                    $csrfToken = csrf_token();

                    return '<div class="text-center">
                        <a href="' . $showUrl . '" class="btn btn-info btn-sm text-white" title="View"><i class="bi bi-eye"></i></a>
                        <a href="' . $editUrl . '" class="btn btn-primary btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>
                        <a data-bs-toggle="modal" href="#delete_modal_' . $row->id . '" class="btn btn-danger btn-sm" title="Delete">
                            <i class="bi bi-trash"></i>
                        </a>
                        <div id="delete_modal_' . $row->id . '" class="modal fade text-start" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Confirmation</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete this Advocate? This action cannot be undone and you will lose all uploaded media files.</p>
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
                ->rawColumns(['image', 'is_verified', 'status', 'action'])
                ->make(true);
        }

        return view('admin.advocates.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.advocates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdvocateRequest $request)
    {
        try {
            $data = $request->validated();
            
            // Handle arrays/checkboxes
            $data['languages_known'] = $request->input('languages_known', []);
            $data['expertise'] = $request->input('expertise', []);
            $data['degree'] = $request->input('degree', []);
            $data['is_verified'] = $request->boolean('is_verified', false);
            $data['status'] = $request->boolean('status', true);
            $data['total_reviews'] = $request->input('total_reviews', 0);

            // Handle Image Upload
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $data['image'] = $this->uploadToS3($file, 'advocate/images', $filename);
            }

            // Handle Video Upload
            if ($request->hasFile('video')) {
                $file = $request->file('video');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $data['video'] = $this->uploadToS3($file, 'advocate/videos', $filename);
            }

            // Handle Document Upload
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $data['document'] = $this->uploadToS3($file, 'advocate/documents', $filename);
            }

            Advocate::create($data);

            return redirect()->route('advocates.index')->with('success', 'Advocate added successfully!');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error creating Advocate: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $advocate = Advocate::findOrFail($id);
        return view('admin.advocates.show', compact('advocate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $advocate = Advocate::findOrFail($id);
        return view('admin.advocates.edit', compact('advocate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdvocateRequest $request, $id)
    {
        try {
            $advocate = Advocate::findOrFail($id);
            $data = $request->validated();

            $data['languages_known'] = $request->input('languages_known', []);
            $data['expertise'] = $request->input('expertise', []);
            $data['degree'] = $request->input('degree', []);
            $data['is_verified'] = $request->boolean('is_verified', false);
            $data['status'] = $request->boolean('status', false);
            $data['total_reviews'] = $request->input('total_reviews', 0);

            // Update Image
            if ($request->hasFile('image')) {
                // Delete old image
                $this->deleteFromS3($advocate->image);
                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $data['image'] = $this->uploadToS3($file, 'advocate/images', $filename);
            }

            // Update Video
            if ($request->hasFile('video')) {
                // Delete old video
                $this->deleteFromS3($advocate->video);
                $file = $request->file('video');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $data['video'] = $this->uploadToS3($file, 'advocate/videos', $filename);
            }

            // Update Document
            if ($request->hasFile('document')) {
                // Delete old document
                $this->deleteFromS3($advocate->document);
                $file = $request->file('document');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $data['document'] = $this->uploadToS3($file, 'advocate/documents', $filename);
            }

            $advocate->update($data);

            return redirect()->route('advocates.index')->with('success', 'Advocate updated successfully!');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error updating Advocate: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $advocate = Advocate::findOrFail($id);

            // Delete associated files from S3
            $this->deleteFromS3($advocate->image);
            $this->deleteFromS3($advocate->video);
            $this->deleteFromS3($advocate->document);

            $advocate->delete();

            return redirect()->route('advocates.index')->with('success', 'Advocate deleted successfully!');
        } catch (Exception $e) {
            return redirect()->route('advocates.index')->with('error', 'Error deleting Advocate: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the status of the advocate.
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
            $advocate = Advocate::findOrFail($id);
            $advocate->status = $request->input('status');
            $advocate->save();

            return redirect()->back()->with('success', 'Status updated successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error updating status: ' . $e->getMessage());
        }
    }
}
