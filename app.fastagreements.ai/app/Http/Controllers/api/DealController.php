<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\Aggriment; // Assuming you have an Agreement model

class DealController extends Controller
{
    /**
     * Display the deal history for a given deal ID.
     */
    public function showDealHistory(string $id)
    {
        try {
            // Find the deal record by ID
            $deal = Deal::findOrFail($id);

            // Calculate due dates
            $payableAmount = $deal->payable_amount;
            $termInMonths = $deal->interest_term_in_month;
            $startDate = new Carbon($deal->created_at); // Use the deal's creation date
            $dueDates = [];
            $currentDate = Carbon::now(); // Get the current date

            for ($i = 1; $i <= $termInMonths; $i++) {
                $dueDate = $startDate->copy()->addMonths($i);
                $amountDue = round($payableAmount / $termInMonths, 2); // Round to 2 decimal places
                
                // Check if the due date has passed
                $isOverdue = $currentDate->greaterThan($dueDate);
                $actualAmountDue = $isOverdue ? $amountDue : $amountDue; // You may adjust this logic if overdue amounts should be different

                $dueDates[] = [
                    'month' => $i,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'amount_due' => $actualAmountDue,
                    'is_overdue' => $isOverdue ? 1 : 0, // Convert boolean to integer
                ];
            }

            // Return a JSON response with the due dates and starting date
            return response()->json([
                'status' => $deal->status, // Replace true with 1
                'message' => 'Deal history retrieved successfully.',
                'data' => [
                    'deal_id' => $deal->id,
                    'payable_amount' => $payableAmount,
                    'interest_term_in_month' => $termInMonths,
                    'start_date' => $startDate->format('Y-m-d'), // Include the starting date
                    'due_dates' => $dueDates,
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            // Handle case where deal is not found
            return response()->json([
                'status' => 0, // Replace false with 0
                'message' => 'Deal not found',
            ], 404); // 404 Not Found
        } catch (Exception $e) {
            // Handle other types of exceptions
            return response()->json([
                'status' => 0, // Replace false with 0
                'message' => 'An error occurred while retrieving the deal history.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
  public function partyWiseAggrimentsApi(Request $request, $id): JsonResponse
{
    
    try {

        // ✅ Pagination values
        $perPage = min(max((int) $request->input('per_page', 10), 1), 100);
        $page    = $request->page ?? 1;
        $isDraft = $request->boolean('is_draft');

        if ((int) $request->user()->id !== (int) $id) {
            return response()->json([
                'status' => false,
                'message' => 'You can only view your own agreements.',
            ], 403);
        }

        // ✅ Fetch paginated agreements
        $agreements = Aggriment::with(['party1', 'party2', 'category'])
            ->where(function ($query) use ($id) {
                $query->where('party_1_id', $id)
                      ->orWhere('party_2_id', $id);
            })
            ->where('is_draft', $isDraft)
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // ✅ Empty check
        if ($agreements->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No agreements found for this party.',
                'data'    => []
            ], 404);
        }

        // ✅ Success response with pagination meta
        return response()->json([
            'status'     => true,
            'message'    => 'Party-wise agreements fetched successfully.',
            'data'       => $agreements->items(),

            'pagination' => [
                'current_page' => $agreements->currentPage(),
                'per_page'     => $agreements->perPage(),
                'total'        => $agreements->total(),
                'last_page'    => $agreements->lastPage(),
                'from'         => $agreements->firstItem(),
                'to'           => $agreements->lastItem(),
            ]
        ], 200);

    } catch (\Throwable $e) {

        Log::error('PartyWiseAggriments API Error', [
            'party_id' => $id,
            'error'    => $e->getMessage()
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'Internal server error.'
        ], 500);
    }
}

 
}
