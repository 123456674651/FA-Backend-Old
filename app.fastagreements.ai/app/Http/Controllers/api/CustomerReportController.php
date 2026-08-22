<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CustomerReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerReportController extends Controller
{
    protected $reportService;

    public function __construct(CustomerReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'report_type' => 'required|string|in:new_users,active_users,inactive_users,high_spending_users',
            'from_date'   => 'nullable|date',
            'to_date'     => 'nullable|date|after_or_equal:from_date',
            'search'      => 'nullable|string',
            'state'       => 'nullable|string',
            'city'       => 'nullable|string',
            'status'      => 'nullable|in:0,1',
            'per_page'    => 'nullable|integer|min:1|max:100',
            'sort_by'     => 'nullable|string|in:name,registration_date,total_spending',
            'sort_order'  => 'nullable|string|in:asc,desc'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $perPage = (int) $request->input('per_page', 10);
        
        try {
            $query = $this->reportService->getCustomerReportQuery($request->all());
            $paginated = $query->paginate($perPage);

            $items = collect($paginated->items())->map(function ($customer) {
                return [
                    'id'                => $customer->id,
                    'name'              => $customer->name,
                    'mobile'            => $customer->mobile,
                    'email'             => $customer->email,
                    'state'             => $customer->state ?? 'N/A',
                    'city'              => $customer->city ?? 'N/A',
                    'status'            => $customer->status ? 'Active' : 'Inactive',
                    'registration_date' => $customer->registration_date ? date('Y-m-d H:i:s', strtotime($customer->registration_date)) : 'N/A',
                    'total_agreements'  => (int) $customer->total_agreements,
                    'total_spending'    => round($customer->total_spending, 2),
                    'last_payment_date' => $customer->last_payment_date ? date('Y-m-d H:i:s', strtotime($customer->last_payment_date)) : null
                ];
            });

            return response()->json([
                'status' => true,
                'data'   => $items,
                'pagination' => [
                    'total'        => $paginated->total(),
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'per_page'     => $paginated->perPage()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch report data: ' . $e->getMessage()
            ], 500);
        }
    }
}
