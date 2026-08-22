<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CategoryAttribute;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\DealCategory;
use App\Traits\ImageResizer;
use Illuminate\Support\Facades\Validator;
use Exception;

class DealCategoryController extends Controller
{
    use ImageResizer;

    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     try {
    //         // Retrieve all deal categories from the database
    //         $dealCategories = DealCategory::all();

    //         // Return a JSON response with the deal categories data
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Deal Categories retrieved successfully.',
    //             'data' => $dealCategories
    //         ], 200); // 200 OK

    //     } catch (\Illuminate\Database\QueryException $e) {

    //         // Return a JSON response specific to database errors
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Database query error occurred while retrieving deal categories.',
    //             'error' => 'A database error occurred.'
    //         ], 500); // 500 Internal Server Error

    //     } catch (Exception $e) {

    //         // Return a JSON response with a generic error message
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred while retrieving the deal categories.',
    //             'error' => 'An unexpected error occurred.'
    //         ], 500); // 500 Internal Server Error
    //     }
    // }
    
  public function index()
    {
        try {
            // Retrieve all deal categories from the database
            $dealCategories = DealCategory::where('is_active', 1)->where('parent_id',0)->with('children', 'attribute_list')->orderBy('sort', 'asc')->get();

            foreach ($dealCategories as $category) {

                if (!empty($category->lable)) {
                    $data = rtrim($category->lable, ','); // Remove trailing comma
                    $labelArray = explode(',', $data);    // Split the string by commas
                    $parsedLabels = [];

                    foreach ($labelArray as $lable) {
                        // Split each key-value pair by the delimiter `=>`
                        $parts = explode('=>', $lable);
                        if (count($parts) === 2) {
                            // Trim spaces and quotes, then assign to the associative array
                            $key = trim($parts[0]);
                            $value = trim($parts[1], ' "'); // Remove surrounding quotes if any
                            $parsedLabels[$key] = $value;
                        }
                    }

                    $category->lable = $parsedLabels;
                } else {
                    $category->lable = null;
                }
                
                
                
                if (!empty($category->visuality)) {
                    $data = rtrim($category->	visuality, ','); // Remove trailing comma
                    $labelArray = explode(',', $data);    // Split the string by commas
                    $parsedLabels = [];

                    foreach ($labelArray as $visuality) {
                        // Split each key-value pair by the delimiter `=>`
                        $parts = explode('=>', $visuality);
                        if (count($parts) === 2) {
                            // Trim spaces and quotes, then assign to the associative array
                            $key = trim($parts[0]);
                            $value = trim($parts[1], ' "'); // Remove surrounding quotes if any
                            $parsedLabels[$key] = $value;
                        }
                    }

                    $category->visuality = $parsedLabels;
                } else {
                    $category->visuality = null;
                }
               

            }


            // Return a JSON response with the deal categories data
            return response()->json([
                'success' => true,
                'message' => 'Deal Categories retrieved successfully.',
                'data' => $dealCategories
            ], 200); // 200 OK

        } catch (\Illuminate\Database\QueryException $e) {

            // Return a JSON response specific to database errors
            return response()->json([
                'success' => false,
                'message' => 'Database query error occurred while retrieving deal categories.',
                'error' => 'A database error occurred.'
            ], 500); // 500 Internal Server Error

        } catch (Exception $e) {

            // Return a JSON response with a generic error message
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the deal categories.',
                'error' => 'An unexpected error occurred.'
            ], 500); // 500 Internal Server Error
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the input data
            $validatedData = $request->validate([
                'category_name' => 'required|string|max:255',
                'category_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'is_active' => 'required|boolean',
                'deal_price' => 'required|numeric',
                'is_on_interest' => 'required|boolean',
                'description' => 'nullable|string',
            ]);

            // Handle file upload
            $imageName = null;
            if ($request->hasFile('category_image')) {
                $imageName = $this->image_resize($request->file('category_image'), 'category_image');
            } else {
                $imageName = 'default.webp';
            }

            // Create a new DealCategory entry
            $category = DealCategory::create([
                'category_image' => $imageName,
                'category_name' => $validatedData['category_name'],
                'is_active' => $validatedData['is_active'],
                'deal_price' => $validatedData['deal_price'],
                'is_on_interest' => $validatedData['is_on_interest'],
                'description' => $validatedData['description']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deal Category created successfully!',
                'data' => $category
            ], 201); // Created status code

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422); // Unprocessable Entity status code

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the deal category.',
                'error' => $e->getMessage()
            ], 500); // Internal Server Error
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            // Attempt to find the deal category by ID
            $dealCategory = DealCategory::find($id);

            // Check if the deal category exists
            if (!$dealCategory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deal Category not found.',
                    'data' => null
                ], 404); // Not Found status code
            }

            // Return a JSON response with the deal category data
            return response()->json([
                'success' => true,
                'message' => 'Deal Category retrieved successfully.',
                'data' => $dealCategory
            ], 200); // OK status code

        } catch (ModelNotFoundException $e) {

            // Return a JSON response if the deal category model is not found
            return response()->json([
                'success' => false,
                'message' => 'Deal Category not found.',
                'error' => 'The specified deal category does not exist.'
            ], 404); // Not Found status code

        } catch (\Exception $e) {

            // Return a JSON response with a generic error message
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the deal category.',
                'error' => $e->getMessage()
            ], 500); // Internal Server Error status code
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        try {
            // Find the existing category by ID
            $category = DealCategory::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deal Category not found.',
                    'data' => null
                ], 404); // Not Found status code
            }

            // Validate the input data
            $validatedData = $request->validate([
                'category_name' => 'required|string|max:255',
                'category_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'is_active' => 'required|boolean',
                'deal_price' => 'required|numeric',
                'is_on_interest' => 'required|boolean',
                'description' => 'nullable|string',
            ]);

            // Handle file upload
            if ($request->hasFile('category_image')) {
                $imageName = $this->image_resize($request->file('category_image'), 'category_image');
            } else {
                $imageName = $category->category_image; // Retain the existing image if no new image is provided
            }

            // Update the category properties
            $category->update([
                'category_image' => $imageName,
                'category_name' => $validatedData['category_name'],
                'is_active' => $validatedData['is_active'],
                'deal_price' => $validatedData['deal_price'],
                'is_on_interest' => $validatedData['is_on_interest'],
                'description' => $validatedData['description']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deal Category updated successfully!',
                'data' => $category
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422); // Unprocessable Entity status code

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the deal category.',
                'error' => $e->getMessage()
            ], 500); // Internal Server Error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $dealCategory = DealCategory::find($id);

            if (!$dealCategory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deal Category not found.',
                    'data' => null
                ], 404); // Not Found status code
            }

            $dealCategory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Deal Category deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the deal category.',
                'error' => $e->getMessage()
            ], 500); // Internal Server Error
        }
    }

    /**
     * Update the status of the specified resource.
     */
    public function statusChanges(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:0,1', // Ensure the status is either 0 or 1
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error.',
                    'errors' => $validator->errors()
                ], 422); // Unprocessable Entity status code
            }

            $dealCategory = DealCategory::findOrFail($id);

            // Update the status based on the provided value
            $dealCategory->is_active = $request->input('status');
            $dealCategory->save(); // Save the model to persist the changes in the database

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422); // Unprocessable Entity status code

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the status.',
                'error' => $e->getMessage()
            ], 500); // Internal Server Error
        }
    }
}
