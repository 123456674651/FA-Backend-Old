<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AgreementReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class AgreementReportController extends Controller
{
    protected $reportService;

    public function __construct(AgreementReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Fetch the agreement report data.
     */
    public function index(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'report_type' => 'required|string|in:daily,monthly,yearly,category_wise,language_wise,state_wise,city_wise,user_wise,advocate_wise,revenue_wise,cancelled,failed',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'category_id' => 'nullable|integer',
            'language_id' => 'nullable|integer',
            'state_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'customer_id' => 'nullable|integer',
            'advocate_id' => 'nullable|integer',
            'status' => 'nullable|integer',
            'sort_by' => 'nullable|string|in:created_date,created_at,agreement_number,customer_name,advocate_name,revenue,total_agreements',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $reportType = $request->input('report_type');
        $perPage = (int) $request->input('per_page', 100);
        $hasAdvocateColumn = Schema::hasColumn('agreements', 'advocate_id');

        try {
            $query = $this->reportService->getAgreementReportQuery($request->all());
            $paginated = $query->paginate($perPage);

            $isGrouped = in_array($reportType, [
                'category_wise',
                'language_wise',
                'state_wise',
                'city_wise',
                'user_wise',
                'advocate_wise'
            ]);

            $items = collect($paginated->items())->map(function ($row) use ($reportType, $hasAdvocateColumn) {
                if ($reportType === 'revenue_wise') {
                    return [
                        'group_name' => $row->group_name ?? 'N/A',
                        'total_agreements' => (int) $row->total_agreements,
                        'total_revenue' => round((float) $row->total_revenue, 2),
                        'average_revenue' => round((float) $row->average_revenue, 2),
                        'highest_revenue' => round((float) $row->highest_revenue, 2),
                        'lowest_revenue' => round((float) $row->lowest_revenue, 2)
                    ];
                } elseif (in_array($reportType, ['category_wise', 'language_wise', 'state_wise', 'city_wise'])) {
                    return [
                        'group_name' => $row->group_name ?? 'N/A',
                        'total_agreements' => (int) $row->total_agreements
                    ];
                } elseif ($reportType === 'user_wise') {
                    return [
                        'group_name' => $row->group_name ?? 'N/A',
                        'mobile' => $row->mobile ?? 'N/A',
                        'email' => $row->email ?? 'N/A',
                        'total_agreements' => (int) $row->total_agreements
                    ];
                } elseif ($reportType === 'advocate_wise') {
                    return [
                        'group_name' => $row->group_name ?? 'N/A',
                        'mobile' => $row->mobile ?? 'N/A',
                        'email' => $row->email ?? 'N/A',
                        'total_agreements' => (int) $row->total_agreements
                    ];
                } else {
                    // Normal Reports: daily, monthly, yearly, cancelled, failed
                    $statusText = 'Inactive';
                    if ($row->agreement_status == 1) {
                        $statusText = 'Active';
                    } elseif ($row->agreement_status == 2) {
                        $statusText = 'Failed';
                    } elseif ($row->agreement_status == 0) {
                        $statusText = 'Cancelled';
                    }

                    // Advocate Name dynamic resolution
                    $advocateName = 'N/A';
                    if ($hasAdvocateColumn && isset($row->advocate)) {
                        $advocateName = $row->advocate->name ?? 'N/A';
                    }

                    return [
                        'id' => $row->id,
                        'agreement_number' => $row->reference_no ?? 'N/A',
                        'customer_name' => $row->party1->name ?? 'N/A',
                        'party_2_name' => $row->party2->name ?? 'N/A',
                        'advocate_name' => $advocateName,
                        'category_name' => $row->category->category_name ?? 'N/A',
                        'language' => $row->language->language_name ?? 'N/A',
                        'state' => $row->state_name ?? 'N/A',
                        'city' => $row->city_name ?? 'N/A',
                        'agreement_amount' => round((float) $row->amount, 2),
                        'status' => $statusText,
                        'created_date' => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : 'N/A'
                    ];
                }
            });

            $response = [
                'status' => true,
                'data' => $items,
                'pagination' => [
                    'total' => $paginated->total(),
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage()
                ]
            ];

            // If revenue_wise report, calculate and add overall summary figures
            if ($reportType === 'revenue_wise') {
                $summaryQuery = $this->reportService->getAgreementReportQuery($request->all());

                // Fetch the stats from DB directly
                $revenueStats = DB::table(DB::raw("({$summaryQuery->toSql()}) as sub"))
                    ->mergeBindings($summaryQuery->getQuery())
                    ->select([
                        DB::raw('COUNT(sub.id) as count'),
                        DB::raw('COALESCE(SUM(sub.amount), 0) as total'),
                        DB::raw('COALESCE(AVG(sub.amount), 0) as average'),
                        DB::raw('COALESCE(MAX(sub.amount), 0) as highest'),
                        DB::raw('COALESCE(MIN(sub.amount), 0) as lowest')
                    ])
                    ->first();

                $response['summary'] = [
                    'total_agreements' => (int) $revenueStats->count,
                    'total_revenue' => round((float) $revenueStats->total, 2),
                    'average_revenue' => round((float) $revenueStats->average, 2),
                    'highest_revenue' => round((float) $revenueStats->highest, 2),
                    'lowest_revenue' => round((float) $revenueStats->lowest, 2)
                ];
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch agreement report: ' . $e->getMessage()
            ], 500);
        }
    }
}
