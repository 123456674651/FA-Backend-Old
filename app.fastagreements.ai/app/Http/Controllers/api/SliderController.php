<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Slider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class SliderController extends Controller
{
    // Create a new slider
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'required|image',
                'expire_date' => 'required|date|after:today',
                'slider_type' => 'required|string|in:onboarding,home', // Validate type
            ]);

            // Store the uploaded image
            $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
            $destinationPath = public_path('admin/images/sliders'); // Updated path
            $request->file('image')->move($destinationPath, $imageName);

            // Create the slider
            $slider = Slider::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'] ?? null,
                'image' =>  $imageName,
                'expire_date' => $validatedData['expire_date'],
                'slider_type' => $validatedData['slider_type'], // Add slider type
                'status' => 1, // Active by default
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Slider created successfully',
                'data' => $slider,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create slider',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Display all sliders
    public function index()
    {
        try {
            // Fetch sliders based on type
            $homeSliders = Slider::where('slider_type', 'home')->where('status', 1)->where('expire_date', '>', now())->get();
            $onboardingSliders = Slider::where('slider_type', 'onboarding')->where('status', 1)->where('expire_date', '>', now())->get();

            // Structure the response
            return response()->json([
                'success' => true,
                'message' => 'Sliders retrieved successfully.',
                'data' => [
                    'home' => $homeSliders,
                    'onboarding' => $onboardingSliders,
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sliders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Show a specific slider by ID
    public function show($id)
    {
        try {
            $slider = Slider::findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Slider retrieved successfully.',
                'data' => $slider,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Slider not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch slider',
                'error' => $e->getMessage(),
            ], 500);
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

            if ($request->hasFile('image')) {
                $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
                $destinationPath = public_path('admin/images/sliders'); // Updated path
                $request->file('image')->move($destinationPath, $imageName);
                $validatedData['image'] = $imageName; // Update the image path
            }

            $slider->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Slider updated successfully.',
                'data' => $slider,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Slider not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update slider',
                'error' => $e->getMessage(),
            ], 500);
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

            $slider->delete();

            return response()->json([
                'success' => true,
                'message' => 'Slider deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Slider not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete slider',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
