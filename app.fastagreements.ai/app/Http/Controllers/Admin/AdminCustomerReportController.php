<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CustomerReportService;
use App\Models\State;
use App\Models\City;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminCustomerReportController extends Controller
{
    protected $reportService;

    public function __construct(CustomerReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the customer reports table with filter options.
     */
    public function index(Request $request)
    {
        $states = State::orderBy('name')->get();
        $cities = City::orderBy('city')->get();

        $reportType = $request->input('report_type', 'new_users');
        $perPage = (int) $request->input('per_page', 100);

        $filters = $request->all();
        $filters['report_type'] = $reportType;

        $query = $this->reportService->getCustomerReportQuery($filters);
        $paginated = $query->paginate($perPage)->withQueryString();

        return view('admin.reports.customers.index', compact('paginated', 'states', 'cities', 'filters'));
    }

    /**
     * Export the filtered customer reports to Excel compatible CSV.
     */
    public function export(Request $request)
    {
        $reportType = $request->input('report_type', 'new_users');
        $filters = $request->all();
        $filters['report_type'] = $reportType;

        $query = $this->reportService->getCustomerReportQuery($filters);
        $customers = $query->get();

        $headers = [
            'Customer ID',
            'Name',
            'Mobile',
            'Email',
            'State',
            'City',
            'Status',
            'Registration Date',
            'Total Agreements',
            'Total Spending',
            'Last Payment Date'
        ];

        $filename = 'customer_report_' . $reportType . '_' . date('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($headers, $customers) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for correct Excel encoding display of special characters
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, $headers);

            foreach ($customers as $customer) {
                fputcsv($handle, [
                    $customer->id,
                    $customer->name,
                    $customer->mobile,
                    $customer->email,
                    $customer->state ?? 'N/A',
                    $customer->city ?? 'N/A',
                    $customer->status ? 'Active' : 'Inactive',
                    $customer->registration_date ? date('Y-m-d H:i:s', strtotime($customer->registration_date)) : 'N/A',
                    $customer->total_agreements,
                    round($customer->total_spending, 2),
                    $customer->last_payment_date ? date('Y-m-d H:i:s', strtotime($customer->last_payment_date)) : 'N/A'
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);

        return $response;
    }
}
