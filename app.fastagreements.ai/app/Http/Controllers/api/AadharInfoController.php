<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Models\AadharInfo;

class AadharInfoController extends Controller
{
    /**
     * Store new Aadhar information.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'required|integer',
                'aadhaar_number' => [
                    'required',
                    'numeric',
                    'regex:/^\d{12}$/',
                ],
                'status' => 'nullable|string|max:250',
                'message' => 'nullable|string|max:250',
                'email' => 'nullable|regex:/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/|max:250',
                'care_of' => 'nullable|string|max:250',
                'name' => 'required|string|max:250',
                'year_of_birth' => 'required|string|max:4',
                'gender' => 'required|string|max:250',
                'ref_id' => 'required|string|max:250',
                'mobile_hash' => 'required|string|max:250',
                'address' => 'required|string|max:250',
                'dob' => 'nullable|string|max:500',
                'photo_link' => 'nullable|string',
                'house' => 'nullable|string|max:1000',
                'landmark' => 'nullable|string|max:200',
                'pincode' => 'nullable|string|max:20',
                'po' => 'nullable|string|max:1000',
                'state' => 'nullable|string|max:200',
                'street' => 'nullable|string|max:150',
                'subdist' => 'nullable|string|max:1020',
                'vtc' => 'nullable|string|max:1050',
                'country' => 'nullable|string|max:1024',
                'dist' => 'nullable|string|max:500',
            ]);

            $aadharInfo = AadharInfo::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Aadhar information stored successfully.',
                'data' => $aadharInfo
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                // 'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error storing Aadhar info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while storing Aadhar information.',
                'error' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    /**
     * Retrieve Aadhar information by ID.
     */
    public function show($id)
    {
        try {
            $aadharInfo = AadharInfo::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $aadharInfo
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Aadhar information not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving Aadhar info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving Aadhar information.',
                'error' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    /**
     * Update Aadhar information.
     */
    public function update(Request $request, $id)
    {
        try {
            $aadharInfo = AadharInfo::findOrFail($id);

            $validatedData = $request->validate([
                'user_id' => 'sometimes|required|integer',
                'aadhaar_number' => [
                    'sometimes',
                    'numeric',
                    'required',
                    'regex:/^\d{12}$/',
                ],
                'status' => 'nullable|string|max:250',
                'message' => 'nullable|string|max:250',
                'email' => 'nullable|regex:/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/|max:250',
                'care_of' => 'nullable|string|max:250',
                'name' => 'sometimes|required|string|max:250',
                'year_of_birth' => 'sometimes|required|string|max:4',
                'gender' => 'sometimes|required|string|max:250',
                'ref_id' => 'sometimes|required|string|max:250',
                'mobile_hash' => 'sometimes|required|string|max:250',
                'address' => 'sometimes|required|string|max:250',
                'dob' => 'nullable|string|max:500',
                'photo_link' => 'nullable|string',
                'house' => 'nullable|string|max:1000',
                'landmark' => 'nullable|string|max:200',
                'pincode' => 'nullable|string|max:20',
                'po' => 'nullable|string|max:1000',
                'state' => 'nullable|string|max:200',
                'street' => 'nullable|string|max:150',
                'subdist' => 'nullable|string|max:1020',
                'vtc' => 'nullable|string|max:1050',
                'country' => 'nullable|string|max:1024',
                'dist' => 'nullable|string|max:500',
            ]);

            $aadharInfo->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Aadhar information updated successfully.',
                'data' => $aadharInfo
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Aadhar information not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating Aadhar info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating Aadhar information.',
                'error' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    /**
     * Delete Aadhar information.
     */
    public function destroy($id)
    {
        try {
            $aadharInfo = AadharInfo::findOrFail($id);
            $aadharInfo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Aadhar information deleted successfully.'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Aadhar information not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting Aadhar info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting Aadhar information.',
                'error' => 'An unexpected error occurred.'
            ], 500);
        }
    }
}
