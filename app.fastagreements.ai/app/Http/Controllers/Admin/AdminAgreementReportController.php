<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AgreementReportService;
use App\Models\State;
use App\Models\City;
use App\Models\DealCategory;
use App\Models\Language;
use App\Models\Customer;
use App\Models\Advocate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminAgreementReportController extends Controller
{
    protected $reportService;

    public function __construct(AgreementReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the agreement reports page.
     */
    public function index(Request $request)
    {
        $states = State::orderBy('name')->get();
        $cities = City::orderBy('city')->get();

        $categories = DealCategory::where('is_active', 1)
            ->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->orderBy('category_name')
            ->get();

        $languages = Language::orderBy('language_name')->get();
        $customers = Customer::where('is_active', 1)->orderBy('name')->get();
        $advocates = Advocate::where('status', 1)->orderBy('name')->get();

        $filters = $request->all();
        $filters['report_type'] = $request->input('report_type', 'daily');
        $perPage = (int) $request->input('per_page', 100);

        $query = $this->reportService->getAgreementReportQuery($filters);
        $paginated = $query->paginate($perPage)->withQueryString();

        $hasAdvocateColumn = Schema::hasColumn('agreements', 'advocate_id');

        $summaryStats = null;
        if ($filters['report_type'] === 'revenue_wise') {
            $summaryQuery = $this->reportService->getAgreementReportQuery($filters);
            $summaryStats = DB::table(DB::raw("({$summaryQuery->toSql()}) as sub"))
                ->mergeBindings($summaryQuery->getQuery())
                ->select([
                    DB::raw('COUNT(sub.id) as count'),
                    DB::raw('COALESCE(SUM(sub.amount), 0) as total'),
                    DB::raw('COALESCE(AVG(sub.amount), 0) as average'),
                    DB::raw('COALESCE(MAX(sub.amount), 0) as highest'),
                    DB::raw('COALESCE(MIN(sub.amount), 0) as lowest')
                ])
                ->first();
        }

        return view('admin.reports.agreements.index', compact(
            'paginated',
            'states',
            'cities',
            'categories',
            'languages',
            'customers',
            'advocates',
            'filters',
            'summaryStats',
            'hasAdvocateColumn'
        ));
    }

    /**
     * Export report data to CSV/Excel.
     */
    public function export(Request $request)
    {
        $filters = $request->all();
        $reportType = $filters['report_type'] ?? 'daily';

        $query = $this->reportService->getAgreementReportQuery($filters);
        $rows = $query->get();

        $hasAdvocateColumn = Schema::hasColumn('agreements', 'advocate_id');

        // Formulate headers & data mapping depending on report type
        if ($reportType === 'revenue_wise') {
            $headers = [
                'Group Name',
                'Total Agreements',
                'Total Revenue (INR)',
                'Average Revenue (INR)',
                'Highest Revenue (INR)',
                'Lowest Revenue (INR)'
            ];
        } elseif (in_array($reportType, ['category_wise', 'language_wise', 'state_wise', 'city_wise'])) {
            $headers = ['Group Name', 'Total Agreements'];
        } elseif (in_array($reportType, ['user_wise', 'advocate_wise'])) {
            $headers = ['Name', 'Mobile', 'Email', 'Total Agreements'];
        } else {
            $headers = [
                'ID', 'Party 1', 'Party 2', 'Category', 'Language',
                'Plan', 'Price', 'Agreement Amount', 'Date', 'View'
            ];
        }

        $filename = 'agreement_report_' . $reportType . '_' . date('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($headers, $rows, $reportType, $hasAdvocateColumn) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compliance
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                if ($reportType === 'revenue_wise') {
                    fputcsv($handle, [
                        $row->group_name ?? 'N/A',
                        (int) $row->total_agreements,
                        round((float) $row->total_revenue, 2),
                        round((float) $row->average_revenue, 2),
                        round((float) $row->highest_revenue, 2),
                        round((float) $row->lowest_revenue, 2)
                    ]);
                } elseif (in_array($reportType, ['category_wise', 'language_wise', 'state_wise', 'city_wise'])) {
                    fputcsv($handle, [
                        $row->group_name ?? 'N/A',
                        (int) $row->total_agreements
                    ]);
                } elseif (in_array($reportType, ['user_wise', 'advocate_wise'])) {
                    fputcsv($handle, [
                        $row->group_name ?? 'N/A',
                        $row->mobile ?? 'N/A',
                        $row->email ?? 'N/A',
                        (int) $row->total_agreements
                    ]);
                } else {
                    $subscription = \App\Models\CustomerSubscription::with('plan')
                        ->where('customer_id', $row->party_1_id)
                        ->orderBy('id', 'desc')
                        ->first();
                    $planName = ($subscription && $subscription->plan) ? $subscription->plan->name : 'No Plan';
                    $planPrice = ($subscription && $subscription->plan) ? round((float) $subscription->plan->price, 2) : 'N/A';
                    $viewUrl = route('agreements.show', $row->id);

                    fputcsv($handle, [
                        $row->id,
                        $row->party1->name ?? 'N/A',
                        $row->party2->name ?? 'N/A',
                        $row->category ? ($row->category->category_name . ($row->subCategory ? ' - ' . $row->subCategory->category_name : '')) : 'N/A',
                        $row->language->language_name ?? 'N/A',
                        $planName,
                        $planPrice,
                        round((float) $row->amount, 2),
                        $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : 'N/A',
                        $viewUrl
                    ]);
                }
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

    /**
     * Export report data to PDF.
     */
    public function pdf(Request $request)
    {
        $filters = $request->all();
        $reportType = $filters['report_type'] ?? 'daily';

        $query = $this->reportService->getAgreementReportQuery($filters);
        // Eager load if it is a list report
        if (!in_array($reportType, ['category_wise', 'language_wise', 'state_wise', 'city_wise', 'user_wise', 'advocate_wise', 'revenue_wise'])) {
            $query->with(['party1', 'party2', 'category', 'language']);
        }
        $data = $query->limit(500)->get(); // Limit to 500 records to prevent out-of-memory errors on PDF engine

        $hasAdvocateColumn = Schema::hasColumn('agreements', 'advocate_id');

        $summaryStats = null;
        if ($reportType === 'revenue_wise') {
            $summaryQuery = $this->reportService->getAgreementReportQuery($filters);
            $summaryStats = DB::table(DB::raw("({$summaryQuery->toSql()}) as sub"))
                ->mergeBindings($summaryQuery->getQuery())
                ->select([
                    DB::raw('COUNT(sub.id) as count'),
                    DB::raw('COALESCE(SUM(sub.amount), 0) as total'),
                    DB::raw('COALESCE(AVG(sub.amount), 0) as average'),
                    DB::raw('COALESCE(MAX(sub.amount), 0) as highest'),
                    DB::raw('COALESCE(MIN(sub.amount), 0) as lowest')
                ])
                ->first();
        }

        $pdf = Pdf::loadView('admin.reports.agreements.pdf', compact(
            'data',
            'reportType',
            'filters',
            'summaryStats',
            'hasAdvocateColumn'
        ));

        return $pdf->download('agreement_report_' . $reportType . '_' . date('Ymd_His') . '.pdf');
    }
}
