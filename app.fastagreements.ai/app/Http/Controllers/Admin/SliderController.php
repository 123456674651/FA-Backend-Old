<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Slider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Yajra\DataTables\DataTables;

class SliderController extends Controller
{
    // Display a listing of the sliders
    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(Slider::query()) // Use the Slider model
                ->addIndexColumn()
                ->addColumn('image', function ($slider) {
                    return '<div class="text-center"><img src="' . asset('admin/images/sliders/' . $slider->image) . '" width="50" height="50"></div>';
                })      
                ->addColumn('status', function ($slider) {
                    $csrfToken = csrf_token();
                    $route = route('sliders.toggleStatus', $slider->id); // Update route for toggling status
                    $buttonClass = $slider->status ? 'outline-success' : 'outline-danger'; // Button class based on status
                    $buttonText = $slider->status ? 'Active' : 'Inactive'; // Button text based on status
                    $newStatus = $slider->status ? 0 : 1; // New status to be set
                
                    return '<div class="text-center">
                        <form action="' . $route . '" method="POST" style="display:inline;">
                            <input type="hidden" name="_token" value="' . $csrfToken . '">
                            <input type="hidden" name="_method" value="PATCH"> <!-- Use PATCH for status change -->
                            <button type="submit" class="btn btn-' . $buttonClass . ' btn-sm">
                                ' . $buttonText . '
                            </button>
                            <input type="hidden" name="status" value="' . $newStatus . '"> <!-- Hidden field for new status -->
                        </form>
                    </div>';
                })    
                ->addColumn('action', function ($slider) {
                    $editUrl = route('sliders.edit', $slider->id);
                    $deleteUrl = route('sliders.destroy', $slider->id);
                    $csrfToken = csrf_token();

                    return '<div class="text-center">
                    <a href="' . $editUrl . '" class="edit btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i></a>
                    <a data-bs-toggle="modal" href="#delete_modal_' . $slider->id . '" class="btn btn-danger btn-sm" title="Delete">
                        <i class="bi bi-trash"></i>
                    </a>
                    <div id="delete_modal_' . $slider->id . '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                ->rawColumns(['image', 'status', 'action'])
                ->make(true);
        }

        return view('admin.sliders.index'); // Return the index view for sliders
    }



    // Show the form for creating a new slider
    public function create()
    {
        return view('admin.sliders.create');
    }

    // Store a newly created slider
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'required|image', // Validate the image file
                'expire_date' => 'required|date|after:today',
                'slider_type' => 'required|string|in:onboarding,home', // Validate type
            ]);

            // Handle file upload and image resizing
            if ($request->file('image')) {
                // Store the uploaded image
                $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
                $destinationPath = public_path('admin/images/sliders');
                $request->file('image')->move($destinationPath, $imageName);
            } else {
                return redirect()->back()->with('error', 'Image upload failed!!!!');
            }

            // Create the slider
            $slider = Slider::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'] ?? null,
                'image' => $imageName, // Use the processed image name
                'expire_date' => $validatedData['expire_date'],
                'slider_type' => $validatedData['slider_type'], // Add slider type
                'status' => 1, // Active by default
            ]);

            return redirect()->route('sliders.index')->with('success', 'Slider created successfully');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to create slider: ' . $e->getMessage());
        }
    }

    // Show the form for editing a specific slider
    public function edit($id)
    {
        try {
            $slider = Slider::findOrFail($id);
            return view('admin.sliders.edit', compact('slider'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('sliders.index')->with('error', 'Slider not found');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch slider: ' . $e->getMessage());
        }
    }

    // Update a slider
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image',
                'expire_date' => 'required|date|after:today',
                'slider_type' => 'required|string|in:onboarding,home',
            ]);

            $slider = Slider::findOrFail($id);

            if ($request->file('image')) {
                $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
                $destinationPath = public_path('admin/images/sliders');
                $request->file('image')->move($destinationPath, $imageName);
                $validatedData['image'] = $imageName; // Store the new image name
            }

            $slider->update($validatedData); // Handle image upload separately

            return redirect()->route('sliders.index')->with('success', 'Slider updated successfully');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (ModelNotFoundException $e) {
            return redirect()->route('sliders.index')->with('error', 'Slider not found');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to update slider: ' . $e->getMessage());
        }
    }

    // Delete a slider
    public function destroy($id)
    {
        try {
            $slider = Slider::findOrFail($id);

            // Get the path to the image
            $imagePath = public_path('admin/images/sliders/' . $slider->image);

            // Check if the image file exists and delete it
            if (file_exists($imagePath)) {
                unlink($imagePath); // Delete the file
            }

            // Delete the slider from the database
            $slider->delete();

            return redirect()->route('sliders.index')->with('success', 'Slider deleted successfully');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('sliders.index')->with('error', 'Slider not found');
        } catch (Exception $e) {
            return redirect()->route('sliders.index')->with('error', 'Failed to delete slider: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Request $request, $id)
{
    try {
        $slider = Slider::findOrFail($id); // Fetch the slider

        // Validate and update the status
        $slider->status = $request->input('status');
        $slider->save();

        return redirect()->route('sliders.index')->with('success', 'Slider status updated successfully');
    } catch (ModelNotFoundException $e) {
        return redirect()->route('sliders.index')->with('error', 'Slider not found');
    } catch (Exception $e) {
        return redirect()->back()->with('error', 'Failed to update slider status: ' . $e->getMessage());
    }
}

}
