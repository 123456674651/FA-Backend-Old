<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoryWarning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class CategoryWarningApiController extends Controller
{
    /**
     * Get active category warnings filtered by category_id and language_id.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:deal_categories,id',
            'language_id' => 'required|exists:languages,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $categoryId = $request->get('category_id');
            $languageId = $request->get('language_id');

            $warnings = CategoryWarning::with(['dealCategory', 'language'])
                ->where('deal_category_id', $categoryId)
                ->where('language_id', $languageId)
                ->where('status', true)
                ->orderBy('display_order', 'asc')
                ->get();

            if ($warnings->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No warnings found for the selected category and language.',
                    'data' => []
                ], 404);
            }

            $formattedWarnings = $warnings->map(function ($warning) {
                return [
                    'id' => $warning->id,
                    'category_id' => (int) $warning->deal_category_id,
                    'category_name' => $warning->dealCategory ? $warning->dealCategory->category_name : '',
                    'language_id' => (int) $warning->language_id,
                    'language_name' => $warning->language ? $warning->language->language_name : '',
                    'title' => $warning->title,
                    'description' => $warning->description,
                    'image' => $warning->image ? asset($warning->image) : null,
                    'display_order' => (int) $warning->display_order,
                    'status' => (bool) $warning->status,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Warnings fetched successfully.',
                'data' => $formattedWarnings
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching warnings: ' . $e->getMessage()
            ], 500);
        }
    }
}
