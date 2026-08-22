<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

class SubscriptionInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $plans = SubscriptionPlan::orderBy('name')->get();

        if ($request->ajax()) {
            $query = SubscriptionInvoice::with(['customer', 'subscriptionPlan'])
                ->orderBy('invoice_date', 'desc');

            if ($request->filled('from_date') && $request->filled('to_date')) {
                try {
                    $from = Carbon::parse($request->input('from_date'))->startOfDay();
                    $to = Carbon::parse($request->input('to_date'))->endOfDay();
                    $query->whereBetween('invoice_date', [$from, $to]);
                } catch (\Exception $e) {
                    // ignore invalid dates and return all invoices
                }
            }

            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->input('payment_status'));
            }

            if ($request->filled('subscription_plan_id')) {
                $query->where('subscription_plan_id', $request->input('subscription_plan_id'));
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('invoice_number', function ($invoice) {
                    return $invoice->invoice_number;
                })
                ->addColumn('customer_name', function ($invoice) {
                    return $invoice->customer?->name ?? 'N/A';
                })
                ->addColumn('plan_name', function ($invoice) {
                    return $invoice->subscriptionPlan?->name ?? 'N/A';
                })
                ->addColumn('amount', function ($invoice) {
                    return '₹ ' . number_format($invoice->amount, 2);
                })
                ->addColumn('payment_status', function ($invoice) {
                    $status = strtolower($invoice->payment_status);
                    $badgeClass = $status === 'paid' ? 'bg-success' : 'bg-warning';
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('payment_method', function ($invoice) {
                    return ucfirst($invoice->payment_method ?? 'N/A');
                })
                ->addColumn('invoice_date', function ($invoice) {
                    if ($invoice->invoice_date instanceof \DateTimeInterface) {
                        return $invoice->invoice_date->format('Y-m-d');
                    }
                    if (!empty($invoice->invoice_date)) {
                        try {
                            return Carbon::parse($invoice->invoice_date)->format('Y-m-d');
                        } catch (\Exception $e) {
                            return 'N/A';
                        }
                    }
                    return 'N/A';
                })
                ->addColumn('action', function ($invoice) {
                    $show = route('subscription-invoices.show', $invoice->id);
                    $viewPdf = route('subscription-invoices.view', $invoice->id);
                    $downloadPdf = route('subscription-invoices.download', $invoice->id);

                    return '<div class="btn-group" role="group" aria-label="Actions">'
                        . '<a href="' . $show . '" class="btn btn-info btn-sm" title="View"><i class="bi bi-eye"></i></a>'
                        . '<a href="' . $viewPdf . '" target="_blank" class="btn btn-secondary btn-sm" title="View Invoice"><i class="bi bi-file-earmark-pdf"></i></a>'
                        . '<a href="' . $downloadPdf . '" class="btn btn-primary btn-sm" title="Download Invoice"><i class="bi bi-download"></i></a>'
                        . '</div>';
                })
                ->rawColumns(['payment_status', 'action'])
                ->filterColumn('customer_name', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('plan_name', function ($query, $keyword) {
                    $query->whereHas('subscriptionPlan', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->make(true);
        }

        return view('admin.subscription_invoices.index', compact('plans'));
    }

    public function show($id)
    {
        $invoice = SubscriptionInvoice::with(['customer', 'subscriptionPlan'])->findOrFail($id);

        return view('admin.subscription_invoices.show', compact('invoice'));
    }

    public function viewPdf($id)
    {
        $invoice = SubscriptionInvoice::with(['customer', 'subscriptionPlan'])->findOrFail($id);
        $filename = 'invoice-' . $invoice->invoice_number . '.pdf';

        if (Schema::hasColumn('subscription_invoices', 'invoice_pdf') && !empty($invoice->invoice_pdf)) {
            $path = public_path($invoice->invoice_pdf);
            if (file_exists($path)) {
                return response()->file($path, ['Content-Disposition' => 'inline; filename="' . $filename . '"']);
            }
        }

        $pdf = PDF::loadView('admin.subscription_invoices.pdf', compact('invoice'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream($filename);
    }

    public function downloadPdf($id)
    {
        $invoice = SubscriptionInvoice::with(['customer', 'subscriptionPlan'])->findOrFail($id);
        $filename = 'invoice-' . $invoice->invoice_number . '.pdf';

        if (Schema::hasColumn('subscription_invoices', 'invoice_pdf') && !empty($invoice->invoice_pdf)) {
            $path = public_path($invoice->invoice_pdf);
            if (file_exists($path)) {
                return response()->download($path, $filename);
            }
        }

        $pdf = PDF::loadView('admin.subscription_invoices.pdf', compact('invoice'))
            ->setPaper('A4', 'portrait');

        return $pdf->download($filename);
    }
}
