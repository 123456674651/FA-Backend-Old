@extends('admin.layout.admin')

@section('content')
    <main id="main" class="main">

        <div class="row">
            <div class="pagetitle col-lg-6 pt-2">
                <h1>Subscription Plan Details</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ url('/') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('subscription-plans.index') }}">Subscription Plans</a>
                        </li>
                        <li class="breadcrumb-item active">Plan Details</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card p-2 pt-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title">{{ $plan->name }}</h5>
                                <a href="{{ route('subscription-plans.index') }}" class="btn btn-secondary btn-sm">Back to
                                    Plans</a>
                            </div>

                            <div class="row gy-3">
                                @php
                                    // Plan prices are stored GST-inclusive; the split shown here is
                                    // the intra-state one, which is what most customers see.
                                    $gst = new \App\Support\GstBreakdown((float) $plan->price, true);
                                    $ratePercent = \App\Support\GstBreakdown::formatRate($gst->rate);
                                    $halfPercent = \App\Support\GstBreakdown::formatRate($gst->halfRate());
                                @endphp

                                <div class="col-md-4">
                                    <strong>Price</strong>
                                    <p class="mb-1">₹ {{ number_format($plan->price, 2) }}
                                        <small class="text-muted">(incl. {{ $ratePercent }}% GST)</small>
                                    </p>
                                    <small class="text-muted d-block">
                                        Taxable ₹{{ number_format($gst->taxable, 2) }}
                                        + CGST {{ $halfPercent }}% ₹{{ number_format($gst->cgst, 2) }}
                                        + SGST {{ $halfPercent }}% ₹{{ number_format($gst->sgst, 2) }}
                                    </small>
                                    <small class="text-muted d-block">Out of state: IGST {{ $ratePercent }}%, same total.</small>
                                </div>

                                <div class="col-md-4">
                                    <strong>Duration</strong>
                                    <p>{{ $plan->duration_value }} {{ ucfirst($plan->duration_type) }}</p>
                                </div>

                                <div class="col-md-4">
                                    <strong>Agreement Limit</strong>
                                    <p>{{ $plan->agreement_limit ?? 'N/A' }}</p>
                                </div>

                                <div class="col-md-4">
                                    <strong>Validity Days</strong>
                                    <p>{{ $plan->validity_days ?? 'N/A' }}</p>
                                </div>

                                <div class="col-md-4">
                                    <strong>Status</strong>
                                    <p>
                                        @if($plan->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="col-md-4">
                                    <strong>Created Date</strong>
                                    <p>{{ $plan->created_at ? $plan->created_at->format('Y-m-d') : 'N/A' }}</p>
                                </div>

                                <div class="col-12">
                                    <strong>Features</strong>
                                    <p class="mb-0">{{ $plan->features ?: 'None specified' }}</p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('subscription-plans.edit', $plan->id) }}"
                                    class="btn btn-primary me-2">Edit Plan</a>
                                <a href="{{ route('subscription-plans.index') }}" class="btn btn-outline-secondary">Back to
                                    List</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>
@endsection