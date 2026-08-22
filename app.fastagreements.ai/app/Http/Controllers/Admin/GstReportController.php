<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

            $companyState = trim(strtolower(setting('company_state') ?? 'Gujarat'));
            $gstRate = (float) $request->input('gst_percentage', 18);

            // Clone query to compute aggregates
            $totalsQuery = clone $query;
            $invoicesForTotals = $totalsQuery->select('id', 'customer_id', 'amount')
                ->with(['customer.state:id,name'])
                ->get();

            $totalInvoiceAmount = 0;
            $totalTaxableAmount = 0;
            $totalCgst = 0;
            $totalSgst = 0;
            $totalIgst = 0;
            $totalGstSum = 0;
            $totalInvoicesCount = $invoicesForTotals->count();

            foreach ($invoicesForTotals as $inv) {
                $amount = (float) $inv->amount;
                $taxable = $amount / (1 + ($gstRate / 100));
                $gstVal = $amount - $taxable;

                $custState = $inv->customer && $inv->customer->state ? trim(strtolower($inv->customer->state->name)) : '';
                $isSame = ($custState === $companyState);

                $totalInvoiceAmount += $amount;
                $totalTaxableAmount += $taxable;
                $totalGstSum += $gstVal;

                if ($isSame) {
                    $totalCgst += $gstVal / 2;
                    $totalSgst += $gstVal / 2;
                } else {
                    $totalIgst += $gstVal;
                }
            }

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
                    return '9983';
                })
                ->addColumn('taxable_amount', function ($row) use ($gstRate) {
                    $taxable = (float) $row->amount / (1 + ($gstRate / 100));
                    return number_format($taxable, 2);
                })
                ->addColumn('gst_percentage', function () use ($gstRate) {
                    return number_format($gstRate, 0) . '%';
                })
                ->addColumn('cgst_amount', function ($row) use ($gstRate, $companyState) {
                    $amount = (float) $row->amount;
                    $taxable = $amount / (1 + ($gstRate / 100));
                    $gstVal = $amount - $taxable;
                    $custState = $row->customer && $row->customer->state ? trim(strtolower($row->customer->state->name)) : '';
                    return ($custState === $companyState) ? number_format($gstVal / 2, 2) : '0.00';
                })
                ->addColumn('sgst_amount', function ($row) use ($gstRate, $companyState) {
                    $amount = (float) $row->amount;
                    $taxable = $amount / (1 + ($gstRate / 100));
                    $gstVal = $amount - $taxable;
                    $custState = $row->customer && $row->customer->state ? trim(strtolower($row->customer->state->name)) : '';
                    return ($custState === $companyState) ? number_format($gstVal / 2, 2) : '0.00';
                })
                ->addColumn('igst_amount', function ($row) use ($gstRate, $companyState) {
                    $amount = (float) $row->amount;
                    $taxable = $amount / (1 + ($gstRate / 100));
                    $gstVal = $amount - $taxable;
                    $custState = $row->customer && $row->customer->state ? trim(strtolower($row->customer->state->name)) : '';
                    return ($custState !== $companyState) ? number_format($gstVal, 2) : '0.00';
                })
                ->addColumn('total_gst', function ($row) use ($gstRate) {
                    $amount = (float) $row->amount;
                    $taxable = $amount / (1 + ($gstRate / 100));
                    return number_format($amount - $taxable, 2);
                })
                ->addColumn('invoice_total', function ($row) {
                    return number_format($row->amount, 2);
                })
                ->addColumn('created_by', function () {
                    return 'System';
                })
                ->with([
                    'totalInvoiceAmount' => number_format($totalInvoiceAmount, 2),
                    'totalTaxableAmount' => number_format($totalTaxableAmount, 2),
                    'totalCgst' => number_format($totalCgst, 2),
                    'totalSgst' => number_format($totalSgst, 2),
                    'totalIgst' => number_format($totalIgst, 2),
                    'totalGstSum' => number_format($totalGstSum, 2),
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

        $companyState = trim(strtolower(setting('company_state') ?? 'Gujarat'));
        $gstRate = (float) $request->input('gst_percentage', 18);

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

        return new StreamedResponse(function () use ($headers, $invoices, $gstRate, $companyState) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers);

            $index = 1;
            foreach ($invoices as $row) {
                $amount = (float) $row->amount;
                $taxable = $amount / (1 + ($gstRate / 100));
                $gstVal = $amount - $taxable;
                $custState = $row->customer && $row->customer->state ? trim(strtolower($row->customer->state->name)) : '';
                $isSame = ($custState === $companyState);

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
                    '9983',
                    round($taxable, 2),
                    $gstRate . '%',
                    $isSame ? round($gstVal / 2, 2) : 0,
                    $isSame ? round($gstVal / 2, 2) : 0,
                    !$isSame ? round($gstVal, 2) : 0,
                    round($gstVal, 2),
                    round($amount, 2),
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

        $companyState = trim(strtolower(setting('company_state') ?? 'Gujarat'));
        $gstRate = (float) $request->input('gst_percentage', 18);
        $filters = $request->all();

        $totalInvoiceAmount = 0;
        $totalTaxableAmount = 0;
        $totalCgst = 0;
        $totalSgst = 0;
        $totalIgst = 0;
        $totalGstSum = 0;

        foreach ($invoices as $inv) {
            $amount = (float) $inv->amount;
            $taxable = $amount / (1 + ($gstRate / 100));
            $gstVal = $amount - $taxable;
            $custState = $inv->customer && $inv->customer->state ? trim(strtolower($inv->customer->state->name)) : '';
            $isSame = ($custState === $companyState);

            $totalInvoiceAmount += $amount;
            $totalTaxableAmount += $taxable;
            $totalGstSum += $gstVal;

            if ($isSame) {
                $totalCgst += $gstVal / 2;
                $totalSgst += $gstVal / 2;
            } else {
                $totalIgst += $gstVal;
            }
        }

        $pdf = Pdf::loadView('admin.reports.gst_tr_pdf', compact(
            'invoices',
            'gstRate',
            'companyState',
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

        $companyState = trim(strtolower(setting('company_state') ?? 'Gujarat'));
        $gstRate = (float) $request->input('gst_percentage', 18);
        $filters = $request->all();

        $totalInvoiceAmount = 0;
        $totalTaxableAmount = 0;
        $totalCgst = 0;
        $totalSgst = 0;
        $totalIgst = 0;
        $totalGstSum = 0;

        foreach ($invoices as $inv) {
            $amount = (float) $inv->amount;
            $taxable = $amount / (1 + ($gstRate / 100));
            $gstVal = $amount - $taxable;
            $custState = $inv->customer && $inv->customer->state ? trim(strtolower($inv->customer->state->name)) : '';
            $isSame = ($custState === $companyState);

            $totalInvoiceAmount += $amount;
            $totalTaxableAmount += $taxable;
            $totalGstSum += $gstVal;

            if ($isSame) {
                $totalCgst += $gstVal / 2;
                $totalSgst += $gstVal / 2;
            } else {
                $totalIgst += $gstVal;
            }
        }

        return view('admin.reports.gst_tr_print', compact(
            'invoices',
            'gstRate',
            'companyState',
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

        $companyState = trim(strtolower(setting('company_state') ?? 'Gujarat'));
        if ($request->filled('gst_type')) {
            $gstType = $request->input('gst_type');
            if ($gstType === 'cgst' || $gstType === 'sgst') {
                $query->whereHas('customer.state', function ($q) use ($companyState) {
                    $q->where('name', $companyState);
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
