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
    .button-color {
        background-color: #57BD13 !important;
        border-color: #57BD13 !important;
    }
    .button-color:hover {
        background-color: #4ca310 !important;
        border-color: #4ca310 !important;
    }
</style>

<main id="main" class="main">

    <div class="row mb-3">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>GST TR Report</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item">Reports</li>
                    <li class="breadcrumb-item active">GST TR Report</li>
                </ol>
            </nav>
        </div>

        <div class="col-lg-6 text-end pt-2">
            <button type="button" class="btn btn-outline-danger fw-bold me-2" onclick="printReport()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <button type="button" class="btn btn-danger fw-bold me-2" onclick="exportPdf()">
                <i class="bi bi-file-earmark-pdf me-1 text-white"></i> Export to PDF
            </button>
            <button type="button" class="btn button-color text-white fw-bold" onclick="exportExcel()">
                <i class="bi bi-file-earmark-excel me-1 text-white"></i> Export to Excel
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="card border-0 shadow-sm p-3 mb-0" style="border-radius: 10px;">
                <div class="text-muted small fw-semibold">Total Invoices</div>
                <h3 class="fw-bold text-dark mt-1 mb-0" id="summary-total-invoices">0</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="card border-0 shadow-sm p-3 mb-0" style="border-radius: 10px;">
                <div class="text-muted small fw-semibold text-primary">Total Taxable Amount</div>
                <h3 class="fw-bold text-primary mt-1 mb-0" id="summary-total-taxable">₹0.00</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="card border-0 shadow-sm p-3 mb-0" style="border-radius: 10px;">
                <div class="text-muted small fw-semibold text-success">Total GST</div>
                <h3 class="fw-bold text-success mt-1 mb-0" id="summary-total-gst">₹0.00</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="card border-0 shadow-sm p-3 mb-0" style="border-radius: 10px;">
                <div class="text-muted small fw-semibold text-dark">Grand Total</div>
                <h3 class="fw-bold text-dark mt-1 mb-0" id="summary-grand-total">₹0.00</h3>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-sm-12">
            <div class="card border-0 shadow-sm p-3 mb-0" style="border-radius: 10px;">
                <div class="text-muted small fw-semibold text-info">Total CGST</div>
                <h4 class="fw-bold text-info mt-1 mb-0" id="summary-total-cgst">₹0.00</h4>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class="card border-0 shadow-sm p-3 mb-0" style="border-radius: 10px;">
                <div class="text-muted small fw-semibold text-info">Total SGST</div>
                <h4 class="fw-bold text-info mt-1 mb-0" id="summary-total-sgst">₹0.00</h4>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class="card border-0 shadow-sm p-3 mb-0" style="border-radius: 10px;">
                <div class="text-muted small fw-semibold text-warning">Total IGST</div>
                <h4 class="fw-bold text-warning mt-1 mb-0" id="summary-total-igst">₹0.00</h4>
            </div>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body p-4">
            <h5 class="card-title p-0 mb-3 fw-bold"><i class="bi bi-filter"></i> Filters</h5>
            
            <form id="filterForm">
                <div class="row g-3">
                    <!-- From Date -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">From Date</label>
                        <input type="date" name="from_date" id="from_date" class="form-control">
                    </div>

                    <!-- To Date -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">To Date</label>
                        <input type="date" name="to_date" id="to_date" class="form-control">
                    </div>

                    <!-- Customer Dropdown (Select2) -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">Customer</label>
                        <select name="customer_id" id="filter-customer" class="form-select w-100">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->mobile ?? 'No Contact' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Invoice Number Search -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">Invoice Number</label>
                        <input type="text" name="invoice_number" id="invoice_number" class="form-control" placeholder="Search invoice...">
                    </div>

                    <!-- GST Type -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">GST Type</label>
                        <select name="gst_type" id="gst_type" class="form-select">
                            <option value="">All GST Types</option>
                            <option value="cgst">CGST</option>
                            <option value="sgst">SGST</option>
                            <option value="igst">IGST</option>
                        </select>
                    </div>

                    <!-- GST Percentage -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">GST Percentage</label>
                        <select name="gst_percentage" id="gst_percentage" class="form-select">
                            <option value="18">18% (Default)</option>
                            <option value="12">12%</option>
                            <option value="5">5%</option>
                            <option value="28">28%</option>
                            <option value="0">0%</option>
                        </select>
                    </div>

                    <!-- HSN Code -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">HSN Code</label>
                        <input type="text" name="hsn_code" id="hsn_code" class="form-control" placeholder="e.g. 9983">
                    </div>

                    <!-- Invoice Status (Payment Status) -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-semibold text-muted small">Invoice/Payment Status</label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 text-end">
                        <button type="button" id="btnReset" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </button>
                        <button type="button" id="btnFilter" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Filter Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Table Card -->
    <div class="card border-0 shadow-sm position-relative" style="border-radius: 12px; overflow: hidden;">
        <!-- Loading Spinner Overlay -->
        <div id="loadingOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none justify-content-center align-items-center" style="background: rgba(255, 255, 255, 0.7); z-index: 1050; border-radius: 12px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="gstTable" class="table table-hover w-100 align-middle" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">Sr.No</th>
                            <th>Invoice Date</th>
                            <th>Invoice No.</th>
                            <th>Customer Name</th>
                            <th>GSTIN</th>
                            <th>State</th>
                            <th>Place of Supply</th>
                            <th>HSN Code</th>
                            <th class="text-end">Taxable Amt</th>
                            <th>GST %</th>
                            <th class="text-end">CGST</th>
                            <th class="text-end">SGST</th>
                            <th class="text-end">IGST</th>
                            <th class="text-end">Total GST</th>
                            <th class="text-end">Invoice Total</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Custom Footer -->
        <div class="card-footer bg-light py-3 border-0 mt-3" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
            <div class="row text-muted small">
                <div class="col-md-4 text-center text-md-start">
                    <strong>Total Records:</strong> <span id="footer-total-records">0</span>
                </div>
                <div class="col-md-4 text-center">
                    <strong>Generated By:</strong> {{ auth()->user()->name }}
                </div>
                <div class="col-md-4 text-center text-md-end">
                    <strong>Generated Date & Time:</strong> {{ now()->format('Y-m-d H:i:s') }}
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Initialize Select2 on Customer Dropdown
    $('#filter-customer').select2({
        placeholder: "All Customers",
        allowClear: true
    });

    // Initialize DataTable
    const table = $('#gstTable').DataTable({
        processing: false, // Turn off Yajra's built-in processing indicator to use our own
        serverSide: true,
        searching: false, // Turn off general search since we have advanced filter controls
        ordering: true,
       pageLength: 100,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        ajax: {
            url: '{{ route('reports.gst-tr.index') }}',
            data: function (d) {
                d.from_date = $('#from_date').val();
                d.to_date = $('#to_date').val();
                d.customer_id = $('#filter-customer').val();
                d.invoice_number = $('#invoice_number').val();
                d.gst_type = $('#gst_type').val();
                d.gst_percentage = $('#gst_percentage').val();
                d.hsn_code = $('#hsn_code').val();
                d.payment_status = $('#payment_status').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'invoice_number', name: 'invoice_number' },
            { data: 'customer_name', name: 'customer_name', orderable: false },
            { data: 'customer_gstin', name: 'customer_gstin', orderable: false },
            { data: 'customer_state', name: 'customer_state', orderable: false },
            { data: 'place_of_supply', name: 'place_of_supply', orderable: false },
            { data: 'hsn_code', name: 'hsn_code', orderable: false },
            { data: 'taxable_amount', name: 'taxable_amount', className: 'text-end' },
            { data: 'gst_percentage', name: 'gst_percentage', orderable: false },
            { data: 'cgst_amount', name: 'cgst_amount', className: 'text-end', orderable: false },
            { data: 'sgst_amount', name: 'sgst_amount', className: 'text-end', orderable: false },
            { data: 'igst_amount', name: 'igst_amount', className: 'text-end', orderable: false },
            { data: 'total_gst', name: 'total_gst', className: 'text-end', orderable: false },
            { data: 'invoice_total', name: 'invoice_total', className: 'text-end' },
            { data: 'created_by', name: 'created_by', orderable: false }
        ],
        order: [[1, 'desc']], // Order by invoice date by default
        drawCallback: function(settings) {
            const json = settings.json;
            if (json) {
                // Update Summary Cards
                $('#summary-total-invoices').text(json.totalInvoicesCount);
                $('#summary-total-taxable').text('₹' + json.totalTaxableAmount);
                $('#summary-total-gst').text('₹' + json.totalGstSum);
                $('#summary-grand-total').text('₹' + json.totalInvoiceAmount);
                $('#summary-total-cgst').text('₹' + json.totalCgst);
                $('#summary-total-sgst').text('₹' + json.totalSgst);
                $('#summary-total-igst').text('₹' + json.totalIgst);

                // Update Footer Total Records
                $('#footer-total-records').text(json.totalInvoicesCount);
            }
        }
    });

    // Loading overlay handlers
    table.on('preXhr.dt', function () {
        $('#loadingOverlay').removeClass('d-none').addClass('d-flex');
    });
    table.on('draw.dt', function () {
        $('#loadingOverlay').removeClass('d-flex').addClass('d-none');
    });

    // Trigger Filtering
    $('#btnFilter').click(function() {
        table.draw();
    });

    // Reset Filters
    $('#btnReset').click(function() {
        $('#filterForm')[0].reset();
        $('#filter-customer').val('').trigger('change');
        table.draw();
    });
});

// Action buttons redirecting with current filter params
function exportExcel() {
    const params = $('#filterForm').serialize();
    window.location.href = "{{ route('reports.gst-tr.export.excel') }}?" + params;
}

function exportPdf() {
    const params = $('#filterForm').serialize();
    window.location.href = "{{ route('reports.gst-tr.export.pdf') }}?" + params;
}

function printReport() {
    const params = $('#filterForm').serialize();
    window.open("{{ route('reports.gst-tr.print') }}?" + params, '_blank');
}
</script>
@endsection
