@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Invoice Details</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('subscription-invoices.index') }}">Subscription Invoices</a>
                    </li>
                    <li class="breadcrumb-item active">Invoice Details</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Invoice #{{ $invoice->invoice_number }}</h5>

                        @php
                            $gst = \App\Support\GstBreakdown::forCustomerState((float) $invoice->amount, $invoice->customer?->state?->name);
                            $ratePercent = \App\Support\GstBreakdown::formatRate($gst->rate);
                            $halfPercent = \App\Support\GstBreakdown::formatRate($gst->halfRate());
                        @endphp

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <h6>Customer Details</h6>
                                    <p><strong>Name:</strong> {{ $invoice->customer?->name ?? 'N/A' }}</p>
                                    <p><strong>Email:</strong> {{ $invoice->customer?->email ?? 'N/A' }}</p>
                                    <p><strong>Mobile:</strong> {{ $invoice->customer?->mobile ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <h6>Subscription Plan</h6>
                                    <p><strong>Name:</strong> {{ $invoice->subscriptionPlan?->name ?? 'N/A' }}</p>
                                    <p><strong>Price:</strong> ₹{{ number_format($invoice->subscriptionPlan?->price ?? 0, 2) }}
                                        <small class="text-muted">(incl. {{ $ratePercent }}% GST)</small>
                                    </p>
                                    <p><strong>Duration:</strong> {{ $invoice->subscriptionPlan?->duration_value ?? 'N/A' }} {{ ucfirst($invoice->subscriptionPlan?->duration_type ?? '') }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <h6>Invoice Details</h6>
                                    <p><strong>Amount:</strong> ₹{{ number_format($invoice->amount, 2) }}</p>
                                    @php
                                        $invoiceDate = 'N/A';
                                        if ($invoice->invoice_date instanceof \DateTimeInterface) {
                                            $invoiceDate = $invoice->invoice_date->format('Y-m-d');
                                        } elseif (!empty($invoice->invoice_date)) {
                                            try {
                                                $invoiceDate = \Illuminate\Support\Carbon::parse($invoice->invoice_date)->format('Y-m-d');
                                            } catch (\Exception $e) {
                                                $invoiceDate = 'N/A';
                                            }
                                        }
                                    @endphp
                                    <p><strong>Date:</strong> {{ $invoiceDate }}</p>
                                    <p><strong>Status:</strong> <span class="badge {{ strtolower($invoice->payment_status) == 'paid' ? 'bg-success' : 'bg-warning' }}">{{ ucfirst($invoice->payment_status) }}</span></p>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <h6>Payment Details</h6>
                                    <p><strong>Method:</strong> {{ ucfirst($invoice->payment_method ?? 'N/A') }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <h6>Tax Breakdown <small class="text-muted">({{ $gst->isIntraState ? 'Intra-state' : 'Inter-state' }})</small></h6>
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td>Taxable Value</td>
                                            <td class="text-end">₹{{ number_format($gst->taxable, 2) }}</td>
                                        </tr>
                                        @if($gst->isIntraState)
                                            <tr>
                                                <td>CGST ({{ $halfPercent }}%)</td>
                                                <td class="text-end">₹{{ number_format($gst->cgst, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>SGST ({{ $halfPercent }}%)</td>
                                                <td class="text-end">₹{{ number_format($gst->sgst, 2) }}</td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td>IGST ({{ $ratePercent }}%)</td>
                                                <td class="text-end">₹{{ number_format($gst->igst, 2) }}</td>
                                            </tr>
                                        @endif
                                        <tr class="fw-bold border-top">
                                            <td>Total Paid</td>
                                            <td class="text-end">₹{{ number_format($invoice->amount, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="{{ route('subscription-invoices.view', $invoice->id) }}" target="_blank" class="btn btn-secondary">
                                    <i class="bi bi-file-earmark-pdf"></i> View Invoice
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('subscription-invoices.index') }}" class="btn btn-outline-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
