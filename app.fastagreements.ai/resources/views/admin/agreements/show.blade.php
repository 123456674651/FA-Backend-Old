@extends('admin.layout.admin')

@section('content')
@php
    $categoryName = $agreement->category ? $agreement->category->category_name : '';
    $subCategoryName = $agreement->subCategory ? $agreement->subCategory->category_name : '';
    $isCarOrBike = (stripos($categoryName, 'car') !== false ||
        stripos($categoryName, 'bike') !== false ||
        stripos($subCategoryName, 'car') !== false ||
        stripos($subCategoryName, 'bike') !== false);
@endphp
<main id="main" class="main">

    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Agreement Details</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('agreements.index') }}">Agreement List</a></li>
                    <li class="breadcrumb-item active">Agreement Details</li>
                </ol>
            </nav>
        </div>
        <div class="col-lg-6 text-end pt-2">
            <a href="{{ route('agreements.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="section profile">
        <div class="row">
            <!-- Left Column -->
            <div class="col-xl-6">
                <!-- General Information -->
                <div class="card">
                    <div class="card-body pt-3 profile-overview">
                        <h5 class="card-title">General Information</h5>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">ID</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">{{ $agreement->id }}</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Agreement Name (Ref No)</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">
                                <span class="badge bg-secondary">{{ $agreement->reference_no ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Remark</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">
                                {{ $agreement->reference_remark ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Category</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">
                                {{ $agreement->category->category_name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Sub Category</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">
                                {{ $agreement->subCategory->category_name ?? 'N/A' }}
                            </div>
                        </div>



                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Language</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">
                                {{ $agreement->language->language_name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Purpose</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">{{ $agreement->purpose ?? 'N/A' }}
                            </div>
                        </div>



                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Agreement Status</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">
                                @if($agreement->agreement_status == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-warning text-dark">Inactive</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms & Repayment -->
                <div class="card">
                    <div class="card-body pt-3 profile-overview">
                        <h5 class="card-title">Terms & Repayment</h5>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Agreement Date</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">
                                {{ $agreement->agreement_date ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Start Date</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">{{ $agreement->start_date ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">End Date</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">{{ $agreement->end_date ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Period</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">
                                {{ $agreement->period ? $agreement->period . ' Months' : 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Repayment Term</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">
                                {{ $agreement->repayment_term ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financials & Logistics -->
                <div class="card">
                    <div class="card-body pt-3 profile-overview">
                        <h5 class="card-title">Financials & Logistics</h5>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Amount</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">
                                {{ $agreement->amount ? number_format($agreement->amount, 2) : 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Address</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">{{ $agreement->address ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Location</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">{{ $agreement->location ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Security</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">{{ $agreement->security ?? 'N/A' }}
                            </div>
                        </div>


                        @php
                            $guarantorVal = trim($agreement->guarantor ?? '');
                            $guarantorNumVal = trim($agreement->guarantor_number ?? '');

                            $guarantors = $guarantorVal !== '' ? array_map('trim', explode(',', $guarantorVal)) : [];
                            $guarantorNumbers = $guarantorNumVal !== '' ? array_map('trim', explode(',', $guarantorNumVal)) : [];
                            $maxCount = max(count($guarantors), count($guarantorNumbers));
                        @endphp

                        @if($maxCount > 0)
                            @for($i = 0; $i < $maxCount; $i++)
                                @php
                                    $name = $guarantors[$i] ?? '';
                                    $number = $guarantorNumbers[$i] ?? '';
                                    $displayVal = '';
                                    if ($name && $number) {
                                        $displayVal = $name . ' (' . $number . ')';
                                    } elseif ($name) {
                                        $displayVal = $name;
                                    } elseif ($number) {
                                        $displayVal = $number;
                                    }
                                @endphp
                                @if($displayVal)
                                    <div class="row mb-2">
                                        <div class="col-lg-4 col-md-4 label text-muted">Guarantor {{ $i + 1 }}</div>
                                        <div class="col-lg-8 col-md-8 text-dark fw-semibold">{{ $displayVal }}</div>
                                    </div>
                                @endif
                            @endfor
                        @else
                            <div class="row mb-2">
                                <div class="col-lg-4 col-md-4 label text-muted">Guarantor</div>
                                <div class="col-lg-8 col-md-8 text-dark fw-semibold">N/A</div>
                            </div>
                        @endif

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Note / Remarks</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">{{ $agreement->note ?? 'N/A' }}</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Documents</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">
                                @if($agreement->documents)
                                    <a href="{{ asset('agreement_pdfs/' . $agreement->documents) }}" target="_blank"
                                        class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-file-earmark-arrow-down"></i> View Documents
                                    </a>
                                @else
                                    <span class="text-muted">No attachments</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-xl-6">
                <!-- Party 1 Details -->
                <div class="card">
                    <div class="card-body pt-3 profile-overview">
                        <h5 class="card-title">Party 1 (First Party)</h5>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Customer ID</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">{{ $agreement->party_1_id ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Name</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">{{ $agreement->party1->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Age</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">{{ $agreement->party_1_age ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Business</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">
                                {{ $agreement->party_1_business ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mt-3 g-2 text-center">
                            <!-- Photo -->
                            <div class="col-sm-6 col-12">
                                <div class="p-2 border rounded bg-light">
                                    <span class="d-block small text-muted fw-bold mb-2">Photo</span>
                                    @if($agreement->party_1_image)
                                        <img src="{{ asset('admin/images/person_images/' . $agreement->party_1_image) }}"
                                            class="img-fluid rounded mb-2" style="max-height: 80px; object-fit: cover;">
                                        <a href="{{ asset('admin/images/person_images/' . $agreement->party_1_image) }}"
                                            target="_blank" class="btn btn-xs btn-dark d-block">View Full</a>
                                    @else
                                        <span class="text-muted small d-block py-3">No Image</span>
                                    @endif
                                </div>
                            </div>
                            <!-- Signature -->
                            <div class="col-sm-6 col-12">
                                <div class="p-2 border rounded bg-light">
                                    <span class="d-block small text-muted fw-bold mb-2">Signature</span>
                                    @if($agreement->party_1_signature)
                                        <img src="data:image/png;base64,{{ $agreement->party_1_signature }}"
                                            class="img-fluid rounded mb-2 bg-white p-1 border"
                                            style="max-height: 80px; object-fit: contain;">
                                        <a href="data:image/png;base64,{{ $agreement->party_1_signature }}" target="_blank"
                                            class="btn btn-xs btn-dark d-block">View Full</a>
                                    @else
                                        <span class="text-muted small d-block py-3">No Signature</span>
                                    @endif
                                </div>
                            </div>
                            <!-- Aadhaar Front -->
                            <div class="col-sm-6 col-12">
                                <div class="p-2 border rounded bg-light">
                                    <span class="d-block small text-muted fw-bold mb-2">Aadhaar Front</span>
                                    @if($agreement->party_1_adhar_front)
                                        <img src="{{ asset('admin/images/adhar_images/' . $agreement->party_1_adhar_front) }}"
                                            class="img-fluid rounded mb-2" style="max-height: 80px; object-fit: contain;">
                                        <a href="{{ asset('admin/images/adhar_images/' . $agreement->party_1_adhar_front) }}"
                                            target="_blank" class="btn btn-xs btn-dark d-block">View Full</a>
                                    @else
                                        <span class="text-muted small d-block py-3">No Aadhaar Front</span>
                                    @endif
                                </div>
                            </div>
                            <!-- Aadhaar Back -->
                            <div class="col-sm-6 col-12">
                                <div class="p-2 border rounded bg-light">
                                    <span class="d-block small text-muted fw-bold mb-2">Aadhaar Back</span>
                                    @if($agreement->party_1_adhar_back)
                                        <img src="{{ asset('admin/images/adhar_images/' . $agreement->party_1_adhar_back) }}"
                                            class="img-fluid rounded mb-2" style="max-height: 80px; object-fit: contain;">
                                        <a href="{{ asset('admin/images/adhar_images/' . $agreement->party_1_adhar_back) }}"
                                            target="_blank" class="btn btn-xs btn-dark d-block">View Full</a>
                                    @else
                                        <span class="text-muted small d-block py-3">No Aadhaar Back</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Party 2 Details -->
                <div class="card">
                    <div class="card-body pt-3 profile-overview">
                        <h5 class="card-title">Party 2 (Second Party)</h5>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Customer ID</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">{{ $agreement->party_2_id ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Name</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">{{ $agreement->party2->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Age</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">{{ $agreement->party_2_age ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Business</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">
                                {{ $agreement->party_2_business ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mt-3 g-2 text-center">
                            <!-- Photo -->
                            <div class="col-sm-6 col-12">
                                <div class="p-2 border rounded bg-light">
                                    <span class="d-block small text-muted fw-bold mb-2">Photo</span>
                                    @if($agreement->party_2_image)
                                        <img src="{{ asset('admin/images/person_images/' . $agreement->party_2_image) }}"
                                            class="img-fluid rounded mb-2" style="max-height: 80px; object-fit: cover;">
                                        <a href="{{ asset('admin/images/person_images/' . $agreement->party_2_image) }}"
                                            target="_blank" class="btn btn-xs btn-dark d-block">View Full</a>
                                    @else
                                        <span class="text-muted small d-block py-3">No Image</span>
                                    @endif
                                </div>
                            </div>
                            <!-- Signature -->
                            <div class="col-sm-6 col-12">
                                <div class="p-2 border rounded bg-light">
                                    <span class="d-block small text-muted fw-bold mb-2">Signature</span>
                                    @if($agreement->party_2_signature)
                                        <img src="data:image/png;base64,{{ $agreement->party_2_signature }}"
                                            class="img-fluid rounded mb-2 bg-white p-1 border"
                                            style="max-height: 80px; object-fit: contain;">
                                        <a href="data:image/png;base64,{{ $agreement->party_2_signature }}" target="_blank"
                                            class="btn btn-xs btn-dark d-block">View Full</a>
                                    @else
                                        <span class="text-muted small d-block py-3">No Signature</span>
                                    @endif
                                </div>
                            </div>
                            <!-- Aadhaar Front -->
                            <div class="col-sm-6 col-12">
                                <div class="p-2 border rounded bg-light">
                                    <span class="d-block small text-muted fw-bold mb-2">Aadhaar Front</span>
                                    @if($agreement->party_2_adhar_front)
                                        <img src="{{ asset('admin/images/adhar_images/' . $agreement->party_2_adhar_front) }}"
                                            class="img-fluid rounded mb-2" style="max-height: 80px; object-fit: contain;">
                                        <a href="{{ asset('admin/images/adhar_images/' . $agreement->party_2_adhar_front) }}"
                                            target="_blank" class="btn btn-xs btn-dark d-block">View Full</a>
                                    @else
                                        <span class="text-muted small d-block py-3">No Aadhaar Front</span>
                                    @endif
                                </div>
                            </div>
                            <!-- Aadhaar Back -->
                            <div class="col-sm-6 col-12">
                                <div class="p-2 border rounded bg-light">
                                    <span class="d-block small text-muted fw-bold mb-2">Aadhaar Back</span>
                                    @if($agreement->party_2_adhar_back)
                                        <img src="{{ asset('admin/images/adhar_images/' . $agreement->party_2_adhar_back) }}"
                                            class="img-fluid rounded mb-2" style="max-height: 80px; object-fit: contain;">
                                        <a href="{{ asset('admin/images/adhar_images/' . $agreement->party_2_adhar_back) }}"
                                            target="_blank" class="btn btn-xs btn-dark d-block">View Full</a>
                                    @else
                                        <span class="text-muted small d-block py-3">No Aadhaar Back</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Photos -->
                @if($isCarOrBike)
                    <div class="card">
                        <div class="card-body pt-3 profile-overview">
                            <h5 class="card-title">Vehicle Photos</h5>

                            <div class="row g-2 text-center">
                                <!-- Front -->
                                <div class="col-sm-6 col-12">
                                    <div class="p-2 border rounded bg-light">
                                        <span class="d-block small text-muted fw-bold mb-2">Front Side</span>
                                        @if($agreement->vehicle_front_side)
                                            <img src="{{ asset('admin/images/vehicle_images/' . $agreement->vehicle_front_side) }}"
                                                class="img-fluid rounded mb-2" style="max-height: 80px; object-fit: cover;">
                                            <a href="{{ asset('admin/images/vehicle_images/' . $agreement->vehicle_front_side) }}"
                                                target="_blank" class="btn btn-xs btn-dark d-block">View Full</a>
                                        @else
                                            <span class="text-muted small d-block py-3">No Photo</span>
                                        @endif
                                    </div>
                                </div>
                                <!-- Back -->
                                <div class="col-sm-6 col-12">
                                    <div class="p-2 border rounded bg-light">
                                        <span class="d-block small text-muted fw-bold mb-2">Back Side</span>
                                        @if($agreement->vehicle_back_side)
                                            <img src="{{ asset('admin/images/vehicle_images/' . $agreement->vehicle_back_side) }}"
                                                class="img-fluid rounded mb-2" style="max-height: 80px; object-fit: cover;">
                                            <a href="{{ asset('admin/images/vehicle_images/' . $agreement->vehicle_back_side) }}"
                                                target="_blank" class="btn btn-xs btn-dark d-block">View Full</a>
                                        @else
                                            <span class="text-muted small d-block py-3">No Photo</span>
                                        @endif
                                    </div>
                                </div>
                                <!-- Left -->
                                <div class="col-sm-6 col-12">
                                    <div class="p-2 border rounded bg-light">
                                        <span class="d-block small text-muted fw-bold mb-2">Left Side</span>
                                        @if($agreement->vehicle_left_side)
                                            <img src="{{ asset('admin/images/vehicle_images/' . $agreement->vehicle_left_side) }}"
                                                class="img-fluid rounded mb-2" style="max-height: 80px; object-fit: cover;">
                                            <a href="{{ asset('admin/images/vehicle_images/' . $agreement->vehicle_left_side) }}"
                                                target="_blank" class="btn btn-xs btn-dark d-block">View Full</a>
                                        @else
                                            <span class="text-muted small d-block py-3">No Photo</span>
                                        @endif
                                    </div>
                                </div>
                                <!-- Right -->
                                <div class="col-sm-6 col-12">
                                    <div class="p-2 border rounded bg-light">
                                        <span class="d-block small text-muted fw-bold mb-2">Right Side</span>
                                        @if($agreement->vehicle_right_side)
                                            <img src="{{ asset('admin/images/vehicle_images/' . $agreement->vehicle_right_side) }}"
                                                class="img-fluid rounded mb-2" style="max-height: 80px; object-fit: cover;">
                                            <a href="{{ asset('admin/images/vehicle_images/' . $agreement->vehicle_right_side) }}"
                                                target="_blank" class="btn btn-xs btn-dark d-block">View Full</a>
                                        @else
                                            <span class="text-muted small d-block py-3">No Photo</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- System Audit -->
                <div class="card">
                    <div class="card-body pt-3 profile-overview">
                        <h5 class="card-title">System Timestamps</h5>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Created At</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">
                                {{ $agreement->created_at ? date('Y-m-d H:i:s', strtotime($agreement->created_at)) : 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label text-muted">Updated At</div>
                            <div class="col-lg-8 col-md-8 text-dark fw-semibold">
                                {{ $agreement->updated_at ? date('Y-m-d H:i:s', strtotime($agreement->updated_at)) : 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>
@stop