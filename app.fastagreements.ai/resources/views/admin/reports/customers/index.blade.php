@extends('admin.layout.admin')

@section('content')
<!-- Select2 Requirements -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem !important;
        padding-top: 4px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>

<main id="main" class="main">

    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Customer Reports</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active">Customer Reports</li>
                </ol>
            </nav>
        </div>

        <div class="col-lg-6 text-end pt-2">
            <a href="{{ route('customer-reports.export', $filters) }}" class="btn button-color text-white fw-bold">
                <i class="bi bi-file-earmark-excel me-1 text-white"></i> Export to Excel
            </a>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body p-4">
            <h5 class="card-title p-0 mb-3 fw-bold"><i class="bi bi-filter"></i> Report Configuration</h5>
            
            <form method="GET" action="{{ route('customer-reports.index') }}" id="reportForm">
                <div class="row g-3">
                    <!-- Report Type (Required) -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">Report Type <span class="text-danger">*</span></label>
                        <select name="report_type" id="report_type" class="form-select" onchange="toggleSortByOption()">
                            <option value="new_users" {{ ($filters['report_type'] ?? '') === 'new_users' ? 'selected' : '' }}>New Users</option>
                            <option value="active_users" {{ ($filters['report_type'] ?? '') === 'active_users' ? 'selected' : '' }}>Active Users</option>
                            <option value="inactive_users" {{ ($filters['report_type'] ?? '') === 'inactive_users' ? 'selected' : '' }}>Inactive Users</option>
                            <option value="high_spending_users" {{ ($filters['report_type'] ?? '') === 'high_spending_users' ? 'selected' : '' }}>High Spending Users</option>
                        </select>
                    </div>

                    <!-- From Date -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $filters['from_date'] ?? '' }}">
                    </div>

                    <!-- To Date -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $filters['to_date'] ?? '' }}">
                    </div>

                    <!-- Search (Name, Mobile, Email) -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">Search (Name, Contact, Email)</label>
                        <input type="text" name="search" class="form-control" placeholder="Type keywords..." value="{{ $filters['search'] ?? '' }}">
                    </div>

                    <!-- State Dropdown -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">State</label>
                        <select name="state" id="filter-state" class="form-select">
                            <option value="">All States</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}" {{ ($filters['state'] ?? '') == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- City Dropdown -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">City</label>
                        <select name="city" id="filter-city" class="form-select">
                            <option value="">All Cities</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ ($filters['city'] ?? '') == $city->id ? 'selected' : '' }}>{{ $city->city }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Dropdown -->
                    <div class="col-md-2 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="1" {{ ($filters['status'] ?? '') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ ($filters['status'] ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <!-- Sort By -->
                    <div class="col-md-2 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">Sort By</label>
                        <select name="sort_by" id="sort_by" class="form-select">
                            <option value="">Default Sorting</option>
                            <option value="name" {{ ($filters['sort_by'] ?? '') === 'name' ? 'selected' : '' }}>Name</option>
                            <option value="registration_date" {{ ($filters['sort_by'] ?? '') === 'registration_date' ? 'selected' : '' }}>Registration Date</option>
                            <option value="total_spending" id="sort_total_spending_opt" {{ ($filters['sort_by'] ?? '') === 'total_spending' ? 'selected' : '' }}>Total Spending</option>
                        </select>
                    </div>

                    <!-- Sort Order -->
                    <div class="col-md-2 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">Sort Order</label>
                        <select name="sort_order" class="form-select">
                            <option value="desc" {{ ($filters['sort_order'] ?? '') === 'desc' ? 'selected' : '' }}>Descending</option>
                            <option value="asc" {{ ($filters['sort_order'] ?? '') === 'asc' ? 'selected' : '' }}>Ascending</option>
                        </select>
                    </div>

                    <!-- Pagination Size & Controls -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">Per Page</label>
                        <select name="per_page" class="form-select">
                            <option value="10" {{ ($filters['per_page'] ?? '') == 10 ? 'selected' : '' }}>10 per page</option>
                            <option value="25" {{ ($filters['per_page'] ?? '') == 25 ? 'selected' : '' }}>25 per page</option>
                            <option value="50" {{ ($filters['per_page'] ?? '') == 50 ? 'selected' : '' }}>50 per page</option>
                            <option value="100" {{ ($filters['per_page'] ?? '') == 100 ? 'selected' : '' }}>100 per page</option>
                        </select>
                    </div>

                    <div class="col-md-9 col-12 d-flex align-items-end mt-4 text-end justify-content-end">
                        <button type="submit" class="btn btn-dark btn-md fw-semibold px-4 me-2">
                            <i class="bi bi-search me-1"></i> Generate Report
                        </button>
                        <a href="{{ route('customer-reports.index') }}" class="btn btn-outline-secondary btn-md fw-semibold px-4">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Table Results -->
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Customer Report Results</h5>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered text-center align-middle" id="reportTable" style="font-size: 0.9rem;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Email</th>
                                        <th>State</th>
                                        <th>City</th>
                                        <th>Status</th>
                                        <th>Registration Date</th>
                                        <th>Total Agreements</th>
                                        <th>Total Spending</th>
                                        <th>Last Payment Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($paginated as $customer)
                                        <tr>
                                            <td>{{ $customer->id }}</td>
                                            <td class="fw-semibold text-dark">{{ $customer->name }}</td>
                                            <td>{{ $customer->mobile }}</td>
                                            <td>{{ $customer->email ?? 'N/A' }}</td>
                                            <td>{{ $customer->state ?? 'N/A' }}</td>
                                            <td>{{ $customer->city ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $customer->status ? 'success' : 'danger' }}">
                                                    {{ $customer->status ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>{{ $customer->registration_date ? date('Y-m-d H:i:s', strtotime($customer->registration_date)) : 'N/A' }}</td>
                                            <td class="fw-bold">{{ $customer->total_agreements }}</td>
                                            <td class="text-success fw-bold">₹{{ number_format($customer->total_spending, 2) }}</td>
                                            <td>{{ $customer->last_payment_date ? date('Y-m-d H:i:s', strtotime($customer->last_payment_date)) : 'N/A' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-4 text-muted">No records found matching current configuration.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Links -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small">
                                Showing {{ $paginated->firstItem() ?? 0 }} to {{ $paginated->lastItem() ?? 0 }} of {{ $paginated->total() }} entries
                            </div>
                            <div>
                                {{ $paginated->links('pagination::bootstrap-5') }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<script>
    function toggleSortByOption() {
        var reportType = document.getElementById('report_type').value;
        var sortOpt = document.getElementById('sort_total_spending_opt');
        if (reportType === 'high_spending_users') {
            sortOpt.style.display = 'block';
        } else {
            sortOpt.style.display = 'none';
            // If currently selected sort is total_spending, reset sort_by selection
            var sortBy = document.getElementById('sort_by');
            if (sortBy.value === 'total_spending') {
                sortBy.value = '';
            }
        }
    }
    
    // Call on load
    $(document).ready(function() {
        toggleSortByOption();
        
        // Initialize Select2 on State & City dropdowns
        $('#filter-state, #filter-city').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
