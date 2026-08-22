<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Language;
use Exception;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexv1()
    {
        try {
            $languages = Language::where('is_active', 1)->orderBy('language_name')->get();

            $payload = $languages->map(function ($lang) {
                return [
                    'id' => $lang->id,
                    'language_name' => $lang->language_name,
                    'language_code' => $lang->language_code,
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => 'Languages fetched successfully.',
                'data' => $payload
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching languages.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
  
   public function index()
    {
        try {
            // Retrieve all languages
            $languages = Language::all();

            if ($languages->isEmpty()) {
                // If no languages are found, return a 404 response
                return response()->json([
                    'success' => false,
                    'message' => 'No languages found.',
                    'data' => null
                ], 404);
            }

            // Return the language data with a success response
            return response()->json([
                'success' => true,
                'message' => 'Languages retrieved successfully.',
                'data' => $languages
            ]);
        } catch (\Exception $e) {
            // Handle any other exceptions
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the languages.',
                'error' => $e->getMessage()
            ], 500); // Internal Server Error status code
        }
    }

  
   
}
