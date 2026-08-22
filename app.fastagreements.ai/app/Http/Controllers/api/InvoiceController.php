<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionInvoice;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class InvoiceController extends Controller
{
    /**
     * Get the PDF URL for a subscription invoice.
     * Generates and saves the PDF if not already done.
     */
    public function getInvoicePdfUrl($id)
    {
        try {
            $invoice = SubscriptionInvoice::with(['customer', 'subscriptionPlan'])->find($id);

            if (!$invoice) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

            $filename = 'invoice-' . $invoice->invoice_number . '.pdf';
            $relativeFolder = 'assets/pdfs/invoices';
            $publicFolder = public_path($relativeFolder);

            // Ensure the directory exists
            if (!File::exists($publicFolder)) {
                File::makeDirectory($publicFolder, 0755, true, true);
            }

            $filePath = $relativeFolder . '/' . $filename;
            $fullPath = public_path($filePath);

            // Check if file already exists in database column and disk
            if (Schema::hasColumn('subscription_invoices', 'invoice_pdf') && !empty($invoice->invoice_pdf)) {
                $existingPath = public_path($invoice->invoice_pdf);
                if (file_exists($existingPath)) {
                    return response()->json([
                        'status' => 'success',
                        'url' => asset($invoice->invoice_pdf),
                        'filename' => basename($invoice->invoice_pdf),
                    ]);
                }
            }

            // Generate and save the PDF
            $pdf = PDF::loadView('admin.subscription_invoices.pdf', compact('invoice'))
                ->setPaper('A4', 'portrait');

            $pdf->getDomPDF()->set_option("isFontSubsettingEnabled", true);

            File::put($fullPath, $pdf->output());

            // Save the path if column exists
            if (Schema::hasColumn('subscription_invoices', 'invoice_pdf')) {
                $invoice->invoice_pdf = $filePath;
                $invoice->save();
            }

            $fileUrl = asset($filePath);

            return response()->json([
                'status' => 'success',
                'url' => $fileUrl,
                'filename' => $filename,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while generating invoice PDF.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * View/stream the invoice PDF directly in the browser.
     */
    public function viewPdf($id)
    {
        try {
            $invoice = SubscriptionInvoice::with(['customer', 'subscriptionPlan'])->find($id);

            if (!$invoice) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

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

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while streaming invoice PDF.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the invoice PDF file directly.
     */
    public function downloadPdf($id)
    {
        try {
            $invoice = SubscriptionInvoice::with(['customer', 'subscriptionPlan'])->find($id);

            if (!$invoice) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

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

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while downloading invoice PDF.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
