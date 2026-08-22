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
                                <div class="col-md-4">
                                    <strong>Price</strong>
                                    <p>₹ {{ number_format($plan->price, 2) }}</p>
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