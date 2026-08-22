<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purpose;
use App\Traits\ImageResizer;
use Illuminate\Support\Facades\Validator;
use Exception;

class PurposeController extends Controller
{
    use ImageResizer;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Retrieve all purposes from the database
            $purposes = Purpose::all();

            // Check if purposes are found
            if ($purposes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No purposes found.',
                    'data' => null
                ], 404); // 404 Not Found
            }

            // Return a JSON response with the purposes data
            return response()->json([
                'success' => true,
                'message' => 'Purposes retrieved successfully.',
                'data' => $purposes
            ]);
        } catch (Exception $e) {

            // Return a JSON response with a generic error message
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the purposes.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $validator = Validator::make($request->all(), [
                'purpose_name' => 'required|string|max:255',
                'purpose_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_active' => 'required|boolean'
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }

            // Handle the image upload and resizing
            $imageName = $request->file('purpose_image')
                ? $this->image_resize($request->file('purpose_image'), 'purpose_image')
                : 'default.webp';

            // Create a new purpose record in the database
            $purpose = Purpose::create([
                'purpose_image' => $imageName,
                'purpose_name' => $request->input('purpose_name'),
                'is_active' => $request->input('is_active'),
            ]);

            // Return a JSON response with the created purpose data
            return response()->json([
                'success' => true,
                'message' => 'Purpose added successfully!',
                'purpose' => $purpose
            ], 201); // 201 Created

        } catch (Exception $e) {

            // Return a JSON response with a generic error message
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding the purpose.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            // Find the purpose record by ID
            $purpose = Purpose::find($id);

            // Check if the purpose exists
            if (!$purpose) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purpose not found.'
                ], 404); // 404 Not Found
            }

            // Return a JSON response with the purpose data
            return response()->json([
                'success' => true,
                'message' => 'Purpose details',
                'data' => $purpose
            ]);
        } catch (Exception $e) {

            // Return a JSON response with a generic error message
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the purpose.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate the incoming request data
            $validator = Validator::make($request->all(), [
                'purpose_name' => 'required|string|max:255',
                'purpose_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_active' => 'required|boolean'
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }

            // Find the purpose record by ID
            $purpose = Purpose::find($id);

            // Check if the purpose exists
            if (!$purpose) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purpose not found.'
                ], 404); // 404 Not Found
            }

            // Handle the image upload and resizing
            $imageName = $request->file('purpose_image')
                ? $this->image_resize($request->file('purpose_image'), 'purpose_image')
                : $purpose->purpose_image;

            // Update the purpose record
            $purpose->update([
                'purpose_image' => $imageName,
                'purpose_name' => $request->input('purpose_name'),
                'is_active' => $request->input('is_active'),
            ]);

            // Return a JSON response with the updated purpose data
            return response()->json([
                'success' => true,
                'message' => 'Purpose updated successfully!',
                'purpose' => $purpose
            ]);
        } catch (Exception $e) {

            // Return a JSON response with a generic error message
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the purpose.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $purpose = Purpose::find($id);

        if (!$purpose) {
            return response()->json([
                'success' => false,
                'message' => 'Purpose not found.'
            ], 404);
        }

        $purpose->delete();

        return response()->json([
            'success' => true,
            'message' => 'Purpose deleted successfully.'
        ]);
    }

    /**
     * Update the status of the specified resource.
     */
    public function statusChange(Request $request, $id)
    {
        try {
            // Validate the incoming request data
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:0,1',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422); // 422 Unprocessable Entity
            }

            // Find the purpose record by ID or throw a 404 exception if not found
            $purpose = Purpose::findOrFail($id);

            // Update the status
            $purpose->is_active = $request->input('status');
            $purpose->save();

            // Return a JSON response with the updated purpose data
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'purpose' => $purpose
            ]);
        } catch (Exception $e) {

            // Return a JSON response with a generic error message
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the status.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
}
