@extends('admin.layout.admin')

@section('content')
    <main id="main" class="main">

        <div class="row">
            <div class="pagetitle col-lg-6 pt-2">
                <h1>Create Subscription Plan</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ url('/') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('subscription-plans.index') }}">Subscription Plans</a>
                        </li>
                        <li class="breadcrumb-item active">Create Plan</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card p-2 pt-4">
                        <div class="card-body">

                            <form action="{{ route('subscription-plans.store') }}" method="POST">
                                @csrf

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Plan Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                            placeholder="Enter Plan Name">
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Price</label>
                                        <input type="number" step="0.01" name="price" class="form-control"
                                            value="{{ old('price') }}" placeholder="Enter Price">
                                        @error('price')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Duration Type</label>
                                        <select name="duration_type" class="form-control">
                                            <option value="">Select Duration Type</option>
                                            <option value="monthly" {{ old('duration_type') == 'monthly' ? 'selected' : '' }}>
                                                Monthly</option>
                                            <option value="yearly" {{ old('duration_type') == 'yearly' ? 'selected' : '' }}>
                                                Yearly</option>
                                            <option value="per_agreement" {{ old('duration_type') == 'per_agreement' ? 'selected' : '' }}>
                                                Per Agreement</option>
                                        </select>
                                        @error('duration_type')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Duration Value</label>
                                        <input type="number" name="duration_value" class="form-control"
                                            value="{{ old('duration_value', 1) }}" placeholder="Eg: 1">
                                        @error('duration_value')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Agreement Limit</label>
                                        <input type="number" name="agreement_limit" class="form-control"
                                            value="{{ old('agreement_limit') }}" placeholder="Enter agreement limit">
                                        @error('agreement_limit')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Validity Days</label>
                                        <input type="number" name="validity_days" class="form-control"
                                            value="{{ old('validity_days') }}" placeholder="Enter validity in days">
                                        @error('validity_days')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">OTP Coverage</label>
                                        <select name="otp_mode" class="form-control">
                                            <option value="" {{ old('otp_mode', '') === '' ? 'selected' : '' }}>
                                                Covers both (with and without OTP)</option>
                                            <option value="with_otp" {{ old('otp_mode') === 'with_otp' ? 'selected' : '' }}>
                                                With OTP only</option>
                                            <option value="without_otp" {{ old('otp_mode') === 'without_otp' ? 'selected' : '' }}>
                                                Without OTP only</option>
                                        </select>
                                        <small class="text-muted">
                                            A "With OTP only" plan also covers agreements created without OTP.
                                            A "Without OTP only" plan does not cover OTP agreements.
                                        </small>
                                        @error('otp_mode')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Features</label>
                                        <textarea name="features" class="form-control"
                                            placeholder="Enter plan features (optional)">{{ old('features') }}</textarea>
                                        @error('features')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <select name="is_active" class="form-control">
                                            <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('is_active')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-12 text-end mt-3">
                                        <button type="submit" class="btn btn-primary">Create Plan</button>
                                        <a href="{{ route('subscription-plans.index') }}"
                                            class="btn btn-secondary">Cancel</a>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>
@endsection