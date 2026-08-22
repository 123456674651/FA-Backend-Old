<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Page;
use Exception;
use Illuminate\Validation\ValidationException;


class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Retrieve all pages from the database
            $pages = Page::all();

            // Return all pages data in JSON format
            return response()->json([
                'message' => 'List of all pages',
                'data' => $pages
            ]);
        } catch (Exception $e) {

            // Return a JSON response with an error message
            return response()->json([
                'message' => 'An error occurred while retrieving the list of pages.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

  public function indexlegal()
    {
        try {
            // Retrieve all pages from the database
            $pages = Page::where('is_legal', 1)->get();

            // Return all pages data in JSON format
            return response()->json([
                'message' => 'List of all pages',
                'data' => $pages
            ]);
        } catch (Exception $e) {

            // Return a JSON response with an error message
            return response()->json([
                'message' => 'An error occurred while retrieving the list of pages.',
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
            $validatedData = $request->validate([
                'page_title' => 'required|string|max:255',
                'page_details' => 'required|string',
                'status' => 'required|boolean',
                'description' => 'nullable|string',
            ]);

            // Generate the slug based on the page title
            $slug = Str::slug($validatedData['page_title']);

            // Ensure the slug is unique
            $existingPage = Page::where('page_slug', $slug)->first();
            if ($existingPage) {
                return response()->json([
                    'message' => 'A page with this slug already exists.',
                ], 409); // 409 Conflict
            }

            // Create a new page record in the database
            $page = Page::create([
                'page_title' => $validatedData['page_title'],
                'page_slug' => $slug,
                'page_details' => $validatedData['page_details'],
                'status' => $validatedData['status'],
                'description' => $validatedData['description'],
            ]);

            // Return a JSON response with the created page data
            return response()->json([
                'message' => 'Page created successfully!',
                'data' => $page
            ], 201); // 201 Created

        } catch (Exception $e) {

            // Return a JSON response with an error message
            return response()->json([
                'message' => 'An error occurred while creating the page.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Page $page)
    {
        try {
            // Return the page data as a JSON response
            return response()->json([
                'message' => 'Page retrieved successfully!',
                'data' => $page
            ]);
        } catch (Exception $e) {

            // Return a JSON response with an error message
            return response()->json([
                'message' => 'An error occurred while retrieving the page.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $page)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'page_title' => 'required|string|max:255',
                'page_details' => 'required|string',
                'status' => 'required|boolean',
                'description' => 'nullable|string',
            ]);

            // Generate the new slug based on the page title
            $newSlug = Str::slug($validatedData['page_title']);

            // Check if the new slug is unique if it has changed
            if ($newSlug !== $page->page_slug) {
                $existingPage = Page::where('page_slug', $newSlug)->first();
                if ($existingPage) {
                    return response()->json([
                        'message' => 'A page with this slug already exists.',
                    ], 409); // 409 Conflict
                }
            }

            // Update the existing page record in the database
            $page->update([
                'page_title' => $validatedData['page_title'],
                'page_slug' => $newSlug,
                'page_details' => $validatedData['page_details'],
                'status' => $validatedData['status'],
                'description' => $validatedData['description'],
            ]);

            // Return a JSON response with the updated page data
            return response()->json([
                'message' => 'Page updated successfully!',
                'data' => $page
            ]);
        } catch (ValidationException $e) {
            // Return validation errors with a 422 status code
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors() // Returns detailed validation errors
            ], 422); // 422 Unprocessable Entity

        } catch (Exception $e) {

            // Return a JSON response with a generic error message
            return response()->json([
                'message' => 'An error occurred while updating the page.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $page)
    {
        try {
            // Delete the page record from the database
            $page->delete();

            // Return a JSON response with a success message
            return response()->json([
                'message' => 'Page deleted successfully!'
            ]);
        } catch (Exception $e) {

            // Return a JSON response with an error message
            return response()->json([
                'message' => 'An error occurred while deleting the page.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
}
