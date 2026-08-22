<?php

// namespace App\Http\Controllers\Admin;

// use Illuminate\Http\Request;
// use App\Http\Controllers\Controller;
// use App\Models\Deal;
// use Carbon\Carbon;
// use Illuminate\Database\Eloquent\ModelNotFoundException;
// use Exception;

// class DealHistoryController extends Controller
// {

//     public function index()
//     {
//         $deals = Deal::all(); // Fetch all deals
//         return view('admin.deal-history.list', compact('deals'));
//     }

//     /**
//      * Display the deal history for a given deal ID.
//      */
//     public function showDealHistory(string $id)
// {
//     try {
//         $deal = Deal::findOrFail($id);

//         $payableAmount = $deal->payable_amount;
//         $termInMonths = $deal->interest_term_in_month;
//         $startDate = new Carbon($deal->created_at);
//         $dueDates = [];
//         $currentDate = Carbon::now();

//         for ($i = 1; $i <= $termInMonths; $i++) {
//             $dueDate = $startDate->copy()->addMonths($i);
//             $amountDue = round($payableAmount / $termInMonths, 2);

//             $isOverdue = $currentDate->greaterThan($dueDate);
//             $actualAmountDue = $isOverdue ? $amountDue : $amountDue;

//             $dueDates[] = [
//                 'month' => $i,
//                 'due_date' => $dueDate->format('Y-m-d'),
//                 'amount_due' => $actualAmountDue,
//                 'is_overdue' => $isOverdue ? 1 : 0,
//             ];
//         }

//         return view('admin.deal-history.index', [
//             'status' => $deal->status,
//             'message' => 'Deal history retrieved successfully.',
//             'deal' => [
//                 'deal_id' => $deal->id,
//                 'payable_amount' => $payableAmount,
//                 'interest_term_in_month' => $termInMonths,
//                 'start_date' => $startDate->format('Y-m-d'),
//                 'due_dates' => $dueDates,
//             ],
//         ]);
//     } catch (ModelNotFoundException $e) {
//         return view('admin.deal-history.index', [
//             'status' => 0,
//             'message' => 'Deal not found',
//         ])->with('error', 'Deal not found');
//     } catch (Exception $e) {
//         return view('admin.deal-history.index', [
//             'status' => 0,
//             'message' => 'An error occurred while retrieving the deal history.',
//             'error' => $e->getMessage()
//         ]);
//     }
// }

// }
