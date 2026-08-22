@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">

    <div class="pagetitle pt-2">
        <h1>Add Subscription</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer-subscriptions.index') }}">Subscriptions</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body pt-4">

                <form action="{{ route('customer-subscriptions.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label>Customer</label>
                        <select name="customer_id" class="form-control">
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Subscription Plan</label>
                        <select name="subscription_plan_id" class="form-control">
                            <option value="">Select Plan</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <button class="btn btn-primary">Save</button>
                    <a href="{{ route('customer-subscriptions.index') }}" class="btn btn-secondary">Back</a>

                </form>

            </div>
        </div>
    </section>

</main>
@endsection
