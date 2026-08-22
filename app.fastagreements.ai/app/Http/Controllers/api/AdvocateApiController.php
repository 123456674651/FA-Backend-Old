<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advocate;
use Illuminate\Http\Request;
use Exception;

class AdvocateApiController extends Controller
{
    /**
     * Display a listing of the active advocates.
     */
    public function index()
    {
        try {
            $advocates = Advocate::where('status', 1)
                ->orderBy('id', 'desc') // Latest first
                ->get()
                ->map(function ($advocate) {
                    return $this->formatAdvocate($advocate);
                });

            return response()->json([
                'status' => true,
                'message' => 'Advocate list fetched successfully.',
                'data' => $advocates
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching the advocate list.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified advocate.
     */
    public function show($id)
    {
        try {
            $advocate = Advocate::where('status', 1)->find($id);

            if (!$advocate) {
                return response()->json([
                    'status' => false,
                    'message' => 'Advocate not found.'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Advocate details fetched successfully.',
                'data' => $this->formatAdvocate($advocate)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching the advocate details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format advocate data for API response.
     */
    protected function formatAdvocate(Advocate $advocate)
    {
        return [
            'id' => $advocate->id,
            'image' => $advocate->image ? asset($advocate->image) : null,
            'name' => $advocate->name,
            'lawyer_type' => $advocate->lawyer_type,
            'is_verified' => (bool) $advocate->is_verified,
            'price' => (float) $advocate->price,
            'consultation_time' => $advocate->consultation_time,
            'total_reviews' => (int) $advocate->total_reviews,
            'experience' => $advocate->experience ? (int) preg_replace('/[^0-9]/', '', $advocate->experience) : 0,
            'about' => $advocate->about,
            'languages_known' => is_array($advocate->languages_known) ? $advocate->languages_known : [],
            'video' => $advocate->video ? asset($advocate->video) : null,
            'document' => $advocate->document ? asset($advocate->document) : null,
            'expertise' => is_array($advocate->expertise) ? $advocate->expertise : [],
            'degree' => is_array($advocate->degree) ? $advocate->degree : [],
            'address' => $advocate->address,
            'mobile_number' => (string) $advocate->mobile_number
        ];
    }
}
