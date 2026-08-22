@extends('admin.layout.admin')

@section('content')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Select2 Bootstrap 5 compatibility adjustments */
    .select2-container--default .select2-selection--single {
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        height: 38px !important;
        padding: 5px 12px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #212529 !important;
        line-height: 26px !important;
        padding-left: 0 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 10px !important;
    }

    .select2-container .select2-selection--single .select2-selection__clear {
        margin-right: 20px !important;
    }
</style>
<main id="main" class="main">

    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Agreement List</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active">Agreement List</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <!-- Advanced Filters -->
    <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body p-4">
            <h5 class="card-title p-0 mb-3 fw-bold"><i class="bi bi-filter"></i> Filters</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted small">Category</label>
                    <select id="filter-category" class="form-select">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted small">Party</label>
                    <select id="filter-party" class="form-select">
                        <option value="">Select Party</option>
                        @foreach($parties as $party)
                            <option value="{{ $party->id }}">{{ $party->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-semibold text-muted small">Date From</label>
                    <input type="date" id="filter-date-from" class="form-control">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-semibold text-muted small">Date To</label>
                    <input type="date" id="filter-date-to" class="form-control">
                </div>
                <div class="col-12 text-end mt-4">
                    <button type="button" id="btn-search" class="btn btn-dark btn-md fw-semibold px-4 me-2">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                    <button type="button" id="btn-reset" class="btn btn-outline-secondary btn-md fw-semibold px-4">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Agreement List</h5>

                        <!-- Table with hoverable rows -->
                        <table class="table table-striped table-bordered" id="agreementTable">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle" style="width: 5%;">Sr#</th>
                                    <th class="text-start align-start">Agreement Name</th>
                                    <th class="text-start align-middle">Party 1 Name</th>
                                    <th class="text-start align-middle">Party 2 Name</th>
                                    <th class="text-start align-middle">Plan</th>
                                    <th class="text-center align-middle">Price</th>
                                    <th class="text-center align-middle">Created Date</th>
                                    <th class="text-center align-middle" style="width: 15%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will populate this table -->
                            </tbody>
                        </table>
                        <!-- End Table -->

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- DataTables Scripts -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Customer Profile Modal -->
    <div class="modal fade" id="customerProfileModal" tabindex="-1" aria-labelledby="customerProfileModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                <div class="modal-header bg-dark text-white border-0 py-3"
                    style="border-top-left-radius: 14px; border-top-right-radius: 14px;">
                    <h5 class="modal-title fw-bold" id="customerProfileModalLabel"><i
                            class="bi bi-person-badge me-2"></i> Party Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <img id="cust-profile-pic" src="{{ asset('assets/img/profile-img.jpg') }}" alt="Profile Pic"
                            class="rounded-circle shadow-sm border"
                            style="width: 110px; height: 110px; object-fit: cover;">
                        <h4 class="fw-bold mt-3 mb-1 text-dark" id="cust-name">N/A</h4>
                        <span class="badge bg-secondary" id="cust-role">Customer / Party</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 border-bottom pb-2">
                            <span class="text-muted small fw-semibold d-block text-start">Mobile</span>
                            <span class="text-dark fw-bold d-block text-start" id="cust-mobile">N/A</span>
                        </div>
                        <div class="col-12 border-bottom pb-2">
                            <span class="text-muted small fw-semibold d-block text-start">Email</span>
                            <span class="text-dark fw-bold d-block text-start" id="cust-email">N/A</span>
                        </div>
                        <div class="col-12 border-bottom pb-2">
                            <span class="text-muted small fw-semibold d-block text-start">Address</span>
                            <span class="text-dark fw-semibold d-block text-start" id="cust-address">N/A</span>
                        </div>
                        <div class="col-12">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mt-2 text-start"><i
                                    class="bi bi-bank me-1"></i> Bank & Payment Details</h6>
                            <div class="row g-2 text-start">
                                <div class="col-6">
                                    <span class="text-muted small d-block">Bank Name</span>
                                    <span class="text-dark fw-bold small" id="cust-bank-name">N/A</span>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted small d-block">Account No</span>
                                    <span class="text-dark fw-bold small" id="cust-bank-account">N/A</span>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted small d-block">IFSC Code</span>
                                    <span class="text-dark fw-bold small" id="cust-bank-ifsc">N/A</span>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted small d-block">UPI ID</span>
                                    <span class="text-dark fw-bold small" id="cust-upi-id">N/A</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 py-3">
                    <button type="button" class="btn btn-secondary px-4 fw-semibold"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</main>
@stop

@section('js')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize Select2 dropdowns
            $('#filter-category').select2({
                placeholder: 'Select Category',
                allowClear: true,
                width: '100%'
            });

            $('#filter-party').select2({
                placeholder: 'Select Party',
                allowClear: true,
                width: '100%'
            });

            var table = $('#agreementTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 100,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                ajax: {
                    url: '{{ route('agreements.index') }}',
                    data: function (d) {
                        d.category_id = $('#filter-category').val();
                        d.party_id = $('#filter-party').val();
                        d.date_from = $('#filter-date-from').val();
                        d.date_to = $('#filter-date-to').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'agreement_name', name: 'agreement_name', className: 'text-start align-start', orderable: false },
                    { data: 'party_1_name', name: 'party_1_name', className: 'text-start align-middle', orderable: false },
                    { data: 'party_2_name', name: 'party_2_name', className: 'text-start align-middle', orderable: false },
                    { data: 'plan_name', name: 'plan_name', className: 'text-start align-middle', orderable: false, searchable: false },
                    { data: 'plan_price', name: 'plan_price', className: 'text-center align-middle', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at', className: 'text-center align-middle' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle' }
                ],
                autoWidth: false
            });

            // Filter button event handlers
            $('#btn-search').on('click', function () {
                table.draw();
            });

            $('#btn-reset').on('click', function () {
                $('#filter-category').val('').trigger('change');
                $('#filter-party').val('').trigger('change');
                $('#filter-date-from').val('');
                $('#filter-date-to').val('');
                table.draw();
            });

            // Click handler for party name triggers
            $(document).on('click', '.view-customer-trigger', function (e) {
                e.preventDefault();
                var customerId = $(this).data('id');
                if (!customerId) return;

                // Show loading states
                $('#cust-name').text('Loading...');
                $('#cust-mobile').text('Loading...');
                $('#cust-email').text('Loading...');
                $('#cust-address').text('Loading...');
                $('#cust-bank-name').text('Loading...');
                $('#cust-bank-account').text('Loading...');
                $('#cust-bank-ifsc').text('Loading...');
                $('#cust-upi-id').text('Loading...');
                $('#cust-profile-pic').attr('src', '{{ asset("assets/img/profile-img.jpg") }}');

                var modal = new bootstrap.Modal(document.getElementById('customerProfileModal'));
                modal.show();

                $.ajax({
                    url: '{{ route("customers.show", ":id") }}'.replace(':id', customerId),
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.success && response.data) {
                            var cust = response.data;
                            $('#cust-name').text(cust.name || 'N/A');
                            $('#cust-mobile').text(cust.mobile || 'N/A');
                            $('#cust-email').text(cust.email || 'N/A');
                            $('#cust-address').text(cust.address || 'N/A');
                            $('#cust-bank-name').text(cust.bank_name || 'N/A');
                            $('#cust-bank-account').text(cust.account_number || 'N/A');
                            $('#cust-bank-ifsc').text(cust.ifsc || 'N/A');
                            $('#cust-upi-id').text(cust.upi_id || 'N/A');

                            if (cust.person_image_url) {
                                $('#cust-profile-pic').attr('src', cust.person_image_url);
                            } else {
                                $('#cust-profile-pic').attr('src', '{{ asset("assets/img/profile-img.jpg") }}');
                            }
                        } else {
                            alert('Failed to load customer profile details.');
                            modal.hide();
                        }
                    },
                    error: function () {
                        alert('Error loading profile.');
                        modal.hide();
                    }
                });
            });
        });
    </script>
@endsection