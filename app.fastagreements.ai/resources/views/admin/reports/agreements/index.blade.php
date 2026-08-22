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
            <h1>Agreement Reports</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active">Agreement Reports</li>
                </ol>
            </nav>
        </div>

        <div class="col-lg-6 text-end pt-2">
            <button type="button" class="btn btn-outline-danger fw-bold me-2" onclick="printReport()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('agreement-reports.pdf', $filters) }}" class="btn btn-danger fw-bold me-2">
                <i class="bi bi-file-earmark-pdf me-1 text-white"></i> Export to PDF
            </a>
            <a href="{{ route('agreement-reports.export', $filters) }}" class="btn button-color text-white fw-bold">
                <i class="bi bi-file-earmark-excel me-1 text-white"></i> Export to Excel
            </a>
        </div>
    </div>

    <!-- Revenue Report Summary Cards -->
    @if(($filters['report_type'] ?? '') === 'revenue_wise' && $summaryStats)
        <div class="row g-3 mb-4">
            <div class="col-md-2-4 col-sm-6 col-12">
                <div class="card border-0 shadow-sm p-3" style="border-radius: 10px;">
                    <div class="text-muted small fw-semibold">Agreement Count</div>
                    <h3 class="fw-bold text-dark mt-1 mb-0">{{ number_format($summaryStats->count) }}</h3>
                </div>
            </div>
            <div class="col-md-2-4 col-sm-6 col-12">
                <div class="card border-0 shadow-sm p-3" style="border-radius: 10px;">
                    <div class="text-muted small fw-semibold text-success">Total Revenue</div>
                    <h3 class="fw-bold text-success mt-1 mb-0">₹{{ number_format($summaryStats->total, 2) }}</h3>
                </div>
            </div>
            <div class="col-md-2-4 col-sm-6 col-12">
                <div class="card border-0 shadow-sm p-3" style="border-radius: 10px;">
                    <div class="text-muted small fw-semibold text-primary">Average Value</div>
                    <h3 class="fw-bold text-primary mt-1 mb-0">₹{{ number_format($summaryStats->average, 2) }}</h3>
                </div>
            </div>
            <div class="col-md-2-4 col-sm-6 col-12">
                <div class="card border-0 shadow-sm p-3" style="border-radius: 10px;">
                    <div class="text-muted small fw-semibold text-info">Highest Value</div>
                    <h3 class="fw-bold text-info mt-1 mb-0">₹{{ number_format($summaryStats->highest, 2) }}</h3>
                </div>
            </div>
            <div class="col-md-2-4 col-sm-6 col-12">
                <div class="card border-0 shadow-sm p-3" style="border-radius: 10px;">
                    <div class="text-muted small fw-semibold text-danger">Lowest Value</div>
                    <h3 class="fw-bold text-danger mt-1 mb-0">₹{{ number_format($summaryStats->lowest, 2) }}</h3>
                </div>
            </div>
        </div>
        <style>
            @media (min-width: 768px) {
                .col-md-2-4 {
                    flex: 0 0 20%;
                    max-width: 20%;
                }
            }
        </style>
    @endif

    <!-- Advanced Filters -->
    <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body p-4">
            <h5 class="card-title p-0 mb-3 fw-bold"><i class="bi bi-filter"></i> Report Configuration</h5>
            
            <form method="GET" action="{{ route('agreement-reports.index') }}" id="reportForm">
                <div class="row g-3">
                    <!-- Report Type (Required) -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">Report Type <span class="text-danger">*</span></label>
                        <select name="report_type" id="report_type" class="form-select" onchange="onReportTypeChange()">
                            <option value="daily" {{ ($filters['report_type'] ?? '') === 'daily' ? 'selected' : '' }}>Daily Agreements</option>
                            <option value="monthly" {{ ($filters['report_type'] ?? '') === 'monthly' ? 'selected' : '' }}>Monthly Agreements</option>
                            <option value="yearly" {{ ($filters['report_type'] ?? '') === 'yearly' ? 'selected' : '' }}>Yearly Agreements</option>
                            <option value="category_wise" {{ ($filters['report_type'] ?? '') === 'category_wise' ? 'selected' : '' }}>Category Wise Report</option>
                            <option value="language_wise" {{ ($filters['report_type'] ?? '') === 'language_wise' ? 'selected' : '' }}>Language Wise Report</option>
                            <option value="state_wise" {{ ($filters['report_type'] ?? '') === 'state_wise' ? 'selected' : '' }}>State Wise Report</option>
                            <option value="city_wise" {{ ($filters['report_type'] ?? '') === 'city_wise' ? 'selected' : '' }}>City Wise Report</option>
                            <option value="user_wise" {{ ($filters['report_type'] ?? '') === 'user_wise' ? 'selected' : '' }}>User Wise Report</option>
                            <option value="advocate_wise" {{ ($filters['report_type'] ?? '') === 'advocate_wise' ? 'selected' : '' }}>Advocate Wise Report</option>
                            <option value="revenue_wise" {{ ($filters['report_type'] ?? '') === 'revenue_wise' ? 'selected' : '' }}>Revenue Wise Report</option>
                            <option value="cancelled" {{ ($filters['report_type'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled Agreements</option>
                            <option value="failed" {{ ($filters['report_type'] ?? '') === 'failed' ? 'selected' : '' }}>Failed Agreements</option>
                        </select>
                    </div>

                    <!-- Revenue Group By (Only visible for revenue_wise) -->
                    <div class="col-md-3 col-sm-6 col-12" id="revenue_group_by_div" style="display: none;">
                        <label class="form-label fw-semibold text-muted small">Revenue Group By</label>
                        <select name="revenue_group_by" id="revenue_group_by" class="form-select">
                            <option value="day" {{ ($filters['revenue_group_by'] ?? '') === 'day' ? 'selected' : '' }}>Day</option>
                            <option value="month" {{ ($filters['revenue_group_by'] ?? '') === 'month' ? 'selected' : '' }}>Month</option>
                            <option value="year" {{ ($filters['revenue_group_by'] ?? '') === 'year' ? 'selected' : '' }}>Year</option>
                            <option value="category" {{ ($filters['revenue_group_by'] ?? '') === 'category' ? 'selected' : '' }}>Category</option>
                            <option value="advocate" {{ ($filters['revenue_group_by'] ?? '') === 'advocate' ? 'selected' : '' }}>Advocate</option>
                        </select>
                    </div>

                    <!-- From Date / Specific Date -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small" id="from_date_label">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $filters['from_date'] ?? '' }}">
                    </div>

                    <!-- To Date -->
                    <div class="col-md-3 col-sm-6 col-12" id="to_date_div">
                        <label class="form-label fw-semibold text-muted small">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $filters['to_date'] ?? '' }}">
                    </div>

                    <!-- Search (Agreement No, Customer Name, Mobile) -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">Search (Number, Customer, Mobile)</label>
                        <input type="text" name="search" class="form-control" placeholder="Type keywords..." value="{{ $filters['search'] ?? '' }}">
                    </div>

                    <!-- Category Dropdown -->
                    <div class="col-md-3 col-sm-6 col-12 filter-dropdown-wrapper">
                        <label class="form-label fw-semibold text-muted small">Category</label>
                        <select name="category_id" id="filter-category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Language Dropdown -->
                    <div class="col-md-3 col-sm-6 col-12 filter-dropdown-wrapper">
                        <label class="form-label fw-semibold text-muted small">Language</label>
                        <select name="language_id" id="filter-language" class="form-select">
                            <option value="">All Languages</option>
                            @foreach($languages as $language)
                                <option value="{{ $language->id }}" {{ ($filters['language_id'] ?? '') == $language->id ? 'selected' : '' }}>{{ $language->language_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- State Dropdown -->
                    <div class="col-md-3 col-sm-6 col-12 filter-dropdown-wrapper">
                        <label class="form-label fw-semibold text-muted small">State</label>
                        <select name="state_id" id="filter-state" class="form-select">
                            <option value="">All States</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}" {{ ($filters['state_id'] ?? '') == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- City Dropdown -->
                    <div class="col-md-3 col-sm-6 col-12 filter-dropdown-wrapper">
                        <label class="form-label fw-semibold text-muted small">City</label>
                        <select name="city_id" id="filter-city" class="form-select">
                            <option value="">All Cities</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ ($filters['city_id'] ?? '') == $city->id ? 'selected' : '' }}>{{ $city->city }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Customer Dropdown -->
                    <div class="col-md-3 col-sm-6 col-12 filter-dropdown-wrapper">
                        <label class="form-label fw-semibold text-muted small">Customer</label>
                        <select name="customer_id" id="filter-customer" class="form-select">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ ($filters['customer_id'] ?? '') == $customer->id ? 'selected' : '' }}>{{ $customer->name }} ({{ $customer->mobile }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Advocate Dropdown -->
                    @if($hasAdvocateColumn)
                        <div class="col-md-3 col-sm-6 col-12 filter-dropdown-wrapper">
                            <label class="form-label fw-semibold text-muted small">Advocate</label>
                            <select name="advocate_id" id="filter-advocate" class="form-select">
                                <option value="">All Advocates</option>
                                @foreach($advocates as $advocate)
                                    <option value="{{ $advocate->id }}" {{ ($filters['advocate_id'] ?? '') == $advocate->id ? 'selected' : '' }}>{{ $advocate->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Status Dropdown -->
                    <div class="col-md-2 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="1" {{ ($filters['status'] ?? '') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ ($filters['status'] ?? '') === '0' ? 'selected' : '' }}>Cancelled / Inactive</option>
                            <option value="2" {{ ($filters['status'] ?? '') === '2' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>

                    <!-- Sort By -->
                    <div class="col-md-2 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">Sort By</label>
                        <select name="sort_by" id="sort_by" class="form-select">
                            <option value="">Default Sorting</option>
                            <option value="created_date" {{ ($filters['sort_by'] ?? '') === 'created_date' ? 'selected' : '' }}>Created Date</option>
                            <option value="agreement_number" {{ ($filters['sort_by'] ?? '') === 'agreement_number' ? 'selected' : '' }}>Agreement Number</option>
                            <option value="customer_name" {{ ($filters['sort_by'] ?? '') === 'customer_name' ? 'selected' : '' }}>Customer Name</option>
                            @if($hasAdvocateColumn)
                                <option value="advocate_name" {{ ($filters['sort_by'] ?? '') === 'advocate_name' ? 'selected' : '' }}>Advocate Name</option>
                            @endif
                            <option value="revenue" {{ ($filters['sort_by'] ?? '') === 'revenue' ? 'selected' : '' }}>Revenue/Amount</option>
                            <option value="total_agreements" {{ ($filters['sort_by'] ?? '') === 'total_agreements' ? 'selected' : '' }}>Total Agreements</option>
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

                    <!-- Pagination Size -->
                    <div class="col-md-2 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">Per Page</label>
                        <select name="per_page" class="form-select">
                            <option value="10" {{ ($filters['per_page'] ?? 100) == 10 ? 'selected' : '' }}>10 per page</option>
                            <option value="25" {{ ($filters['per_page'] ?? 100) == 25 ? 'selected' : '' }}>25 per page</option>
                            <option value="50" {{ ($filters['per_page'] ?? 100) == 50 ? 'selected' : '' }}>50 per page</option>
                            <option value="100" {{ ($filters['per_page'] ?? 100) == 100 ? 'selected' : '' }}>100 per page</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="col-md-12 col-12 d-flex align-items-end mt-4 justify-content-end">
                        <button type="submit" class="btn btn-dark btn-md fw-semibold px-4 me-2">
                            <i class="bi bi-search me-1"></i> Generate Report
                        </button>
                        <a href="{{ route('agreement-reports.index') }}" class="btn btn-outline-secondary btn-md fw-semibold px-4">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Table Results -->
    <section class="section" id="printableArea">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                            <h5 class="card-title p-0 m-0">Agreement Report Results</h5>
                            <div class="badge bg-secondary p-2">{{ ucwords(str_replace('_', ' ', $filters['report_type'] ?? 'daily')) }}</div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered text-center align-middle" id="reportTable" style="font-size: 0.9rem;">
                                @php
                                    $reportType = $filters['report_type'] ?? 'daily';
                                @endphp

                                @if($reportType === 'revenue_wise')
                                    <thead>
                                        <tr>
                                            <th>Group Name</th>
                                            <th>Total Agreements</th>
                                            <th>Total Revenue (INR)</th>
                                            <th>Average Revenue (INR)</th>
                                            <th>Highest Revenue (INR)</th>
                                            <th>Lowest Revenue (INR)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($paginated as $row)
                                            <tr>
                                                <td class="fw-semibold text-dark">{{ $row->group_name ?? 'N/A' }}</td>
                                                <td>{{ number_format($row->total_agreements) }}</td>
                                                <td class="text-success fw-bold">₹{{ number_format($row->total_revenue, 2) }}</td>
                                                <td class="text-primary">₹{{ number_format($row->average_revenue, 2) }}</td>
                                                <td class="text-info">₹{{ number_format($row->highest_revenue, 2) }}</td>
                                                <td class="text-danger">₹{{ number_format($row->lowest_revenue, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">No records found matching current configuration.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                @elseif(in_array($reportType, ['category_wise', 'language_wise', 'state_wise', 'city_wise']))
                                    <thead>
                                        <tr>
                                            <th>Group Name</th>
                                            <th>Total Agreements</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($paginated as $row)
                                            <tr>
                                                <td class="fw-semibold text-dark">{{ $row->group_name ?? 'N/A' }}</td>
                                                <td class="fw-bold">{{ number_format($row->total_agreements) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center py-4 text-muted">No records found matching current configuration.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                @elseif(in_array($reportType, ['user_wise', 'advocate_wise']))
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Mobile</th>
                                            <th>Email</th>
                                            <th>Total Agreements</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($paginated as $row)
                                            <tr>
                                                <td class="fw-semibold text-dark">{{ $row->group_name ?? 'N/A' }}</td>
                                                <td>{{ $row->mobile ?? 'N/A' }}</td>
                                                <td>{{ $row->email ?? 'N/A' }}</td>
                                                <td class="fw-bold">{{ number_format($row->total_agreements) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">No records found matching current configuration.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                @else
                                    {{-- Standard details reports (daily, monthly, yearly, cancelled, failed) --}}
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th class="text-start">Party 1</th>
                                            <th class="text-start">Party 2</th>
                                            <th>Category</th>
                                            <th>Language</th>
                                            <th class="text-start">Plan</th>
                                            <th>Price</th>
                                            <th>Agreement Amount</th>
                                            <th>Date</th>
                                            <th>View</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($paginated as $row)
                                            @php
                                                $subscription = \App\Models\CustomerSubscription::with('plan')
                                                    ->where('customer_id', $row->party_1_id)
                                                    ->orderBy('id', 'desc')
                                                    ->first();
                                                $planName = ($subscription && $subscription->plan) ? $subscription->plan->name : 'No Plan';
                                                $planPrice = ($subscription && $subscription->plan) ? '₹' . number_format($subscription->plan->price, 2) : 'N/A';
                                            @endphp
                                            <tr>
                                                <td>{{ $row->id }}</td>
                                                <td class="fw-semibold text-dark text-start">
                                                    @if($row->party1)
                                                        <a href="{{ route('agreements.show', $row->id) }}">{{ $row->party1->name }}</a>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td class="text-start">{{ $row->party2->name ?? 'N/A' }}</td>
                                                <td>{{ $row->category ? ($row->category->category_name . ($row->subCategory ? ' - ' . $row->subCategory->category_name : '')) : 'N/A' }}</td>
                                                <td>{{ $row->language->language_name ?? 'N/A' }}</td>
                                                <td class="text-start">{{ $planName }}</td>
                                                <td>{{ $planPrice }}</td>
                                                <td class="text-success fw-bold">₹{{ number_format($row->amount, 2) }}</td>
                                                <td>{{ $row->created_at ? $row->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                                <td>
                                                    <a href="{{ route('agreements.show', $row->id) }}" class="btn btn-dark btn-sm"><i class="bi bi-eye"></i> View</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-4 text-muted">No records found matching current configuration.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                @endif
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

<!-- Client Side Interactive Scripts -->
<script>
    function onReportTypeChange() {
        var reportType = document.getElementById('report_type').value;
        var revenueDiv = document.getElementById('revenue_group_by_div');
        var fromDateLabel = document.getElementById('from_date_label');
        var toDateDiv = document.getElementById('to_date_div');

        // Hide/Show Group By option for Revenue
        if (reportType === 'revenue_wise') {
            revenueDiv.style.display = 'block';
        } else {
            revenueDiv.style.display = 'none';
        }

        // Adjust date labels depending on selection
        if (reportType === 'daily') {
            fromDateLabel.innerHTML = 'Specific Date';
            toDateDiv.style.display = 'none';
        } else if (reportType === 'monthly') {
            fromDateLabel.innerHTML = 'Select Month (via date)';
            toDateDiv.style.display = 'none';
        } else if (reportType === 'yearly') {
            fromDateLabel.innerHTML = 'Select Year (via date)';
            toDateDiv.style.display = 'none';
        } else {
            fromDateLabel.innerHTML = 'From Date';
            toDateDiv.style.display = 'block';
        }
    }

    function printReport() {
        var printContents = document.getElementById('printableArea').innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = "<html><head><title>Agreement Report</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'></head><body><div class='container mt-5'>" + printContents + "</div></body></html>";
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload(); // Reload to restore JavaScript handlers
    }

    $(document).ready(function() {
        onReportTypeChange();

        // Initialize Select2 on dropdown elements
        $('#filter-state, #filter-city, #filter-category, #filter-language, #filter-customer, #filter-advocate').select2({
            placeholder: "Select option",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
