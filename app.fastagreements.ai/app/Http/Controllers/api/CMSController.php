<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Http\Resources\CmsPageResource;
use Illuminate\Http\Request;
use Exception;

class CMSController extends Controller
{
    /**
     * Display a listing of active CMS pages.
     */
    public function index()
    {
        try {
            $pages = CmsPage::where('status', 'Active')->get();

            return response()->json([
                'status' => true,
                'message' => 'CMS Pages fetched successfully.',
                'data' => CmsPageResource::collection($pages)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching CMS pages.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified active CMS page by slug.
     */
    public function show($slug)
    {
        try {
            $page = CmsPage::where('slug', $slug)
                ->where('status', 'Active')
                ->first();

            if (!$page) {
                return response()->json([
                    'status' => false,
                    'message' => 'CMS page not found.'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'CMS page fetched successfully.',
                'data' => new CmsPageResource($page)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching the CMS page.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
