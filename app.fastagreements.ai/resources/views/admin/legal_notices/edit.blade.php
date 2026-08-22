@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Edit Legal Notice</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('legal-notices.index') }}">Legal Notices</a></li>
                    <li class="breadcrumb-item active">Edit Legal Notice</li>
                </ol>
            </nav>
        </div>
        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('legal-notices.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left-square"></i> Back
            </a>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body p-4">
                        <form action="{{ route('legal-notices.update', $notice->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <h5 class="card-title p-0 mb-3">Opponent Company Details</h5>
                            <hr class="mt-0 mb-3">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name', $notice->company_name) }}" required>
                                    @error('company_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="company_person_name" class="form-label">Contact Person Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('company_person_name') is-invalid @enderror" id="company_person_name" name="company_person_name" value="{{ old('company_person_name', $notice->company_person_name) }}" required>
                                    @error('company_person_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="company_person_designation" class="form-label">Contact Person Designation <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('company_person_designation') is-invalid @enderror" id="company_person_designation" name="company_person_designation" value="{{ old('company_person_designation', $notice->company_person_designation) }}" required>
                                    @error('company_person_designation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="company_address" class="form-label">Company Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('company_address') is-invalid @enderror" id="company_address" name="company_address" rows="2" required>{{ old('company_address', $notice->company_address) }}</textarea>
                                    @error('company_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <h5 class="card-title p-0 mt-4 mb-3">My Company Details</h5>
                            <hr class="mt-0 mb-3">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="my_company_name" class="form-label">My Company Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('my_company_name') is-invalid @enderror" id="my_company_name" name="my_company_name" value="{{ old('my_company_name', $notice->my_company_name) }}" required>
                                    @error('my_company_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="my_company_business_nature" class="form-label">My Company Business Nature <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('my_company_business_nature') is-invalid @enderror" id="my_company_business_nature" name="my_company_business_nature" value="{{ old('my_company_business_nature', $notice->my_company_business_nature) }}" required>
                                    @error('my_company_business_nature')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <h5 class="card-title p-0 mt-4 mb-3">Financial Information</h5>
                            <hr class="mt-0 mb-3">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="total_amount" class="form-label">Total Amount (₹) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('total_amount') is-invalid @enderror" id="total_amount" name="total_amount" value="{{ old('total_amount', $notice->total_amount) }}" required>
                                    @error('total_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="amount_due" class="form-label">Amount Due (₹) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('amount_due') is-invalid @enderror" id="amount_due" name="amount_due" value="{{ old('amount_due', $notice->amount_due) }}" required>
                                    @error('amount_due')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Notice</button>
                                <a href="{{ route('legal-notices.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
