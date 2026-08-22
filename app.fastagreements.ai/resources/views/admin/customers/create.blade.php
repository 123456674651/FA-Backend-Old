@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Create New Customer</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>
    <!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <!-- Personal Details -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mobile">Mobile</label>
                                        <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mobile') }}" required>
                                        @error('mobile')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                                        @error('email')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <input type="text" class="form-control" id="address" name="address" value="{{ old('address') }}">
                                        @error('address')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Location Details -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="city_id">City</label>
                                        <input type="text" class="form-control" id="city_id" name="city_id" value="{{ old('city_id') }}">
                                        @error('city_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="state_id">State</label>
                                        <input type="text" class="form-control" id="state_id" name="state_id" value="{{ old('state_id') }}">
                                        @error('state_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country_id">Country</label>
                                        <input type="text" class="form-control" id="country_id" name="country_id" value="{{ old('country_id') }}">
                                        @error('country_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="per_address">Permanent Address</label>
                                        <input type="text" class="form-control" id="per_address" name="per_address" value="{{ old('per_address') }}">
                                        @error('per_address')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="per_city_id">Permanent City</label>
                                        <input type="text" class="form-control" id="per_city_id" name="per_city_id" value="{{ old('per_city_id') }}">
                                        @error('per_city_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="per_state_id">Permanent State</label>
                                        <input type="text" class="form-control" id="per_state_id" name="per_state_id" value="{{ old('per_state_id') }}">
                                        @error('per_state_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="per_country_id">Permanent Country</label>
                                        <input type="text" class="form-control" id="per_country_id" name="per_country_id" value="{{ old('per_country_id') }}">
                                        @error('per_country_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Identification and Images -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="person_image">Person Image</label>
                                        <input type="file" class="form-control" id="person_image" name="person_image">
                                        @error('person_image')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="aadhaar_card">Aadhaar Card</label>
                                        <input type="text" class="form-control" id="aadhaar_card" name="aadhaar_card" value="{{ old('aadhaar_card') }}">
                                        @error('aadhaar_card')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="is_aadhaar_verify">Aadhaar Verified</label>
                                        <select class="form-control" id="is_aadhaar_verify" name="is_aadhaar_verify">
                                            <option value="" disabled selected>Select Status</option>
                                            <option value="1" {{ old('is_aadhaar_verify') == '1' ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ old('is_aadhaar_verify') == '0' ? 'selected' : '' }}>No</option>
                                        </select>
                                        @error('is_aadhaar_verify')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="aadhaar_card_all_column">Aadhaar Card All Columns</label>
                                        <textarea class="form-control" id="aadhaar_card_all_column" name="aadhaar_card_all_column" rows="4">{{ old('aadhaar_card_all_column') }}</textarea>
                                        @error('aadhaar_card_all_column')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Payment Details -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="is_active">Active</label>
                                        <select class="form-control" id="is_active" name="is_active" required>
                                            <option value="" disabled selected>Select Status</option>
                                            <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('is_active')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="is_payment_details_configured">Payment Details Configured</label>
                                        <select class="form-control" id="is_payment_details_configured" name="is_payment_details_configured">
                                            <option value="" disabled selected>Select Status</option>
                                            <option value="1" {{ old('is_payment_details_configured') == '1' ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ old('is_payment_details_configured') == '0' ? 'selected' : '' }}>No</option>
                                        </select>
                                        @error('is_payment_details_configured')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Bank Details -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bank_name">Bank Name</label>
                                        <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ old('bank_name') }}">
                                        @error('bank_name')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="account_number">Account Number</label>
                                        <input type="text" class="form-control" id="account_number" name="account_number" value="{{ old('account_number') }}">
                                        @error('account_number')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ifsc">IFSC Code</label>
                                        <input type="text" class="form-control" id="ifsc" name="ifsc" value="{{ old('ifsc') }}">
                                        @error('ifsc')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="account_type">Account Type</label>
                                        <select class="form-control" id="account_type" name="account_type">
                                            <option value="" disabled selected>Select Account Type</option>
                                            <option value="savings" {{ old('account_type') == 'savings' ? 'selected' : '' }}>Savings</option>
                                            <option value="current" {{ old('account_type') == 'current' ? 'selected' : '' }}>Current</option>
                                        </select>
                                        @error('account_type')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="upi_id">UPI ID</label>
                                        <input type="text" class="form-control" id="upi_id" name="upi_id" value="{{ old('upi_id') }}">
                                        @error('upi_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="upi_image">UPI Image</label>
                                        <input type="file" class="form-control" id="upi_image" name="upi_image">
                                        @error('upi_image')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Location Tracking -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="last_lat">Last Latitude</label>
                                        <input type="text" class="form-control" id="last_lat" name="last_lat" value="{{ old('last_lat') }}">
                                        @error('last_lat')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="last_lon">Last Longitude</label>
                                        <input type="text" class="form-control" id="last_lon" name="last_lon" value="{{ old('last_lon') }}">
                                        @error('last_lon')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                    <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->
@endsection
