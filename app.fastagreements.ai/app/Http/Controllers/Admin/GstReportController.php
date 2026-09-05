<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\GstBreakdown;
use App\Models\SubscriptionInvoice;
use App\Models\Customer;
use App\Models\State;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class GstReportController extends Controller
{
    /**
     * Display the report search page or return Datatables AJAX response.
     */
    public function index(Request $request)
    {
        //Gate::authorize('gst-tr-report-view');

        if ($request->ajax()) {
            $query = $this->buildFilteredQuery($request);

            $gstRate = $this->resolveRate($request);

            // Clone query to compute aggregates
            $totalsQuery = clone $query;
            $invoicesForTotals = $totalsQuery->select('id', 'customer_id', 'amount')
                ->with(['customer.state:id,name'])
                ->get();

            $totals = $this->sumBreakdowns($invoicesForTotals, $gstRate);
            $totalInvoicesCount = $invoicesForTotals->count();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('invoice_date', function ($row) {
                    if ($row->invoice_date instanceof \DateTimeInterface) {
                        return $row->invoice_date->format('Y-m-d');
                    }
                    return $row->invoice_date ? Carbon::parse($row->invoice_date)->format('Y-m-d') : 'N/A';
                })
                ->addColumn('invoice_number', function ($row) {
                    return $row->invoice_number;
                })
                ->addColumn('customer_name', function ($row) {
                    return $row->customer?->name ?? 'N/A';
                })
                ->addColumn('customer_gstin', function ($row) {
                    return $row->customer?->gst_number ?? 'N/A';
                })
                ->addColumn('customer_state', function ($row) {
                    return $row->customer?->state?->name ?? 'N/A';
                })
                ->addColumn('place_of_supply', function ($row) {
                    return $row->customer?->state?->name ?? 'N/A';
                })
                ->addColumn('hsn_code', function () {
                    return GstBreakdown::HSN_CODE;
                })
                ->addColumn('taxable_amount', function ($row) use ($gstRate) {
                    return number_format($this->breakdownFor($row, $gstRate)->taxable, 2);
                })
                ->addColumn('gst_percentage', function () use ($gstRate) {
                    return GstBreakdown::formatRate($gstRate) . '%';
                })
                ->addColumn('cgst_amount', function ($row) use ($gstRate) {
                    return number_format($this->breakdownFor($row, $gstRate)->cgst, 2);
                })
                ->addColumn('sgst_amount', function ($row) use ($gstRate) {
                    return number_format($this->breakdownFor($row, $gstRate)->sgst, 2);
                })
                ->addColumn('igst_amount', function ($row) use ($gstRate) {
                    return number_format($this->breakdownFor($row, $gstRate)->igst, 2);
                })
                ->addColumn('total_gst', function ($row) use ($gstRate) {
                    return number_format($this->breakdownFor($row, $gstRate)->totalTax(), 2);
                })
                ->addColumn('invoice_total', function ($row) {
                    return number_format($row->amount, 2);
                })
                ->addColumn('created_by', function () {
                    return 'System';
                })
                ->with([
                    'totalInvoiceAmount' => number_format($totals['amount'], 2),
                    'totalTaxableAmount' => number_format($totals['taxable'], 2),
                    'totalCgst' => number_format($totals['cgst'], 2),
                    'totalSgst' => number_format($totals['sgst'], 2),
                    'totalIgst' => number_format($totals['igst'], 2),
                    'totalGstSum' => number_format($totals['tax'], 2),
                    'totalInvoicesCount' => $totalInvoicesCount,
                ])
                ->make(true);
        }

        $customers = Customer::where('is_active', 1)->orderBy('name')->get();
        return view('admin.reports.gst_tr', compact('customers'));
    }

    /**
     * Export the filtered GST TR report to Excel compatible CSV.
     */
    public function exportExcel(Request $request)
    {
        //Gate::authorize('gst-tr-report-export');

        $query = $this->buildFilteredQuery($request);
        $invoices = $query->get();

        $gstRate = $this->resolveRate($request);

        $headers = [
            'Sr. No.',
            'Invoice Date',
            'Invoice Number',
            'Customer Name',
            'Customer GSTIN',
            'Customer State',
            'Place of Supply',
            'HSN Code',
            'Taxable Amount',
            'GST %',
            'CGST Amount',
            'SGST Amount',
            'IGST Amount',
            'Total GST',
            'Invoice Total',
            'Created By'
        ];

        $filename = 'gst_tr_report_' . date('Ymd_His') . '.csv';

        return new StreamedResponse(function () use ($headers, $invoices, $gstRate) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers);

            $index = 1;
            foreach ($invoices as $row) {
                $gst = $this->breakdownFor($row, $gstRate);

                $dateStr = 'N/A';
                if ($row->invoice_date) {
                    $dateStr = $row->invoice_date instanceof \DateTimeInterface
                        ? $row->invoice_date->format('Y-m-d')
                        : Carbon::parse($row->invoice_date)->format('Y-m-d');
                }

                fputcsv($handle, [
                    $index++,
                    $dateStr,
                    $row->invoice_number,
                    $row->customer?->name ?? 'N/A',
                    $row->customer?->gst_number ?? 'N/A',
                    $row->customer?->state?->name ?? 'N/A',
                    $row->customer?->state?->name ?? 'N/A',
                    GstBreakdown::HSN_CODE,
                    $gst->taxable,
                    GstBreakdown::formatRate($gstRate) . '%',
                    $gst->cgst,
                    $gst->sgst,
                    $gst->igst,
                    $gst->totalTax(),
                    $gst->amount,
                    'System'
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
    }

    /**
     * Export the filtered GST TR report to PDF.
     */
    public function exportPdf(Request $request)
    {
        //Gate::authorize('gst-tr-report-export');

        $query = $this->buildFilteredQuery($request);
        $invoices = $query->limit(500)->get();

        $gstRate = $this->resolveRate($request);
        $filters = $request->all();

        $totals = $this->sumBreakdowns($invoices, $gstRate);
        $totalInvoiceAmount = $totals['amount'];
        $totalTaxableAmount = $totals['taxable'];
        $totalCgst = $totals['cgst'];
        $totalSgst = $totals['sgst'];
        $totalIgst = $totals['igst'];
        $totalGstSum = $totals['tax'];

        $pdf = Pdf::loadView('admin.reports.gst_tr_pdf', compact(
            'invoices',
            'gstRate',
            'filters',
            'totalInvoiceAmount',
            'totalTaxableAmount',
            'totalCgst',
            'totalSgst',
            'totalIgst',
            'totalGstSum'
        ))->setPaper('A4', 'landscape');

        return $pdf->download('gst_tr_report_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Generate print view for GST TR report.
     */
    public function print(Request $request)
    {
        Gate::authorize('gst-tr-report-print');

        $query = $this->buildFilteredQuery($request);
        $invoices = $query->get();

        $gstRate = $this->resolveRate($request);
        $filters = $request->all();

        $totals = $this->sumBreakdowns($invoices, $gstRate);
        $totalInvoiceAmount = $totals['amount'];
        $totalTaxableAmount = $totals['taxable'];
        $totalCgst = $totals['cgst'];
        $totalSgst = $totals['sgst'];
        $totalIgst = $totals['igst'];
        $totalGstSum = $totals['tax'];

        return view('admin.reports.gst_tr_print', compact(
            'invoices',
            'gstRate',
            'filters',
            'totalInvoiceAmount',
            'totalTaxableAmount',
            'totalCgst',
            'totalSgst',
            'totalIgst',
            'totalGstSum'
        ));
    }

    /**
     * The rate to report at: whatever the user typed into the filter,
     * otherwise the rate configured in settings.
     */
    private function resolveRate(Request $request): float
    {
        return $request->filled('gst_percentage')
            ? (float) $request->input('gst_percentage')
            : GstBreakdown::defaultRate();
    }

    /**
     * Every figure in this report comes from GstBreakdown, so a row here can
     * never disagree with the same invoice's PDF by a paisa.
     */
    private function breakdownFor($invoice, float $rate): GstBreakdown
    {
        return GstBreakdown::forCustomerState(
            (float) $invoice->amount,
            $invoice->customer?->state?->name,
            $rate
        );
    }

    /**
     * Totals are summed from the per-invoice rounded figures, not computed
     * afresh from the gross, so the footer equals the column above it.
     *
     * @return array{amount: float, taxable: float, cgst: float, sgst: float, igst: float, tax: float}
     */
    private function sumBreakdowns($invoices, float $rate): array
    {
        $totals = ['amount' => 0.0, 'taxable' => 0.0, 'cgst' => 0.0, 'sgst' => 0.0, 'igst' => 0.0, 'tax' => 0.0];

        foreach ($invoices as $invoice) {
            $gst = $this->breakdownFor($invoice, $rate);

            $totals['amount'] += $gst->amount;
            $totals['taxable'] += $gst->taxable;
            $totals['cgst'] += $gst->cgst;
            $totals['sgst'] += $gst->sgst;
            $totals['igst'] += $gst->igst;
            $totals['tax'] += $gst->totalTax();
        }

        return array_map(fn ($v) => round($v, 2), $totals);
    }

    /**
     * Build filtered Eloquent query based on request parameters.
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = SubscriptionInvoice::with(['customer.state'])
            ->orderBy('invoice_date', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->input('to_date'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->input('invoice_number') . '%');
        }

        $companyState = GstBreakdown::companyState();
        if ($request->filled('gst_type')) {
            $gstType = $request->input('gst_type');
            if ($gstType === 'cgst' || $gstType === 'sgst') {
                // Customers with no state on record are treated as intra-state
                // by GstBreakdown, so they belong in this filter too.
                $query->where(function ($q) use ($companyState) {
                    $q->whereHas('customer.state', function ($sq) use ($companyState) {
                        $sq->where('name', $companyState);
                    })->orWhereDoesntHave('customer.state');
                });
            } elseif ($gstType === 'igst') {
                $query->whereHas('customer.state', function ($q) use ($companyState) {
                    $q->where('name', '!=', $companyState);
                });
            }
        }

        // Since HSN code is statically computed to 9983, if the user filters for HSN code that is not empty and not 9983, force empty results
        if ($request->filled('hsn_code') && trim($request->input('hsn_code')) !== '9983') {
            $query->whereRaw('1 = 0');
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        return $query;
    }
}
