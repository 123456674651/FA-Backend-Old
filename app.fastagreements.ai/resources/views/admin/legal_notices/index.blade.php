@extends('admin.layout.admin')

@section('content')
    <main id="main" class="main">
        <div class="row">
            <div class="pagetitle col-lg-6 pt-2">
                <h1>Legal Notices</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Legal Notice List</li>
                    </ol>
                </nav>
            </div>
            <div class="pagetitle col-lg-6 text-end pt-2">

                <a href="{{ route('legal-notices.export-excel') }}" class="btn btn-success me-1">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </a>
                <a href="{{ route('legal-notices.export-pdf') }}" class="btn btn-danger">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="toast-container" style="position: fixed; top: 70px; right: 20px; z-index: 9999; display: none;">
            <div class="alert alert-success alert-dismissible fade show shadow" role="alert">
                <span id="toast-message"></span>
                <button type="button" class="btn-close"
                    onclick="document.getElementById('toast-container').style.display='none';"></button>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body p-4">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <!-- Filters -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label for="statusFilter" class="form-label font-weight-bold">Filter by Status</label>
                                    <select id="statusFilter" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Approved">Approved</option>
                                        <option value="Rejected">Rejected</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Closed">Closed</option>
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="legalNoticesTable" class="table table-bordered w-100">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 50px;">S.No</th>
                                            <th>Company Name</th>
                                            <th>Total Amount</th>
                                            <th>Amount Due</th>
                                            <th>Company Person</th>
                                            <th>My Company</th>
                                            <th class="text-center" style="width: 130px;">Status</th>
                                            <th>Created Date</th>
                                            <th class="text-center" style="width: 150px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- DataTables populate -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            const table = $('#legalNoticesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('legal-notices.index') }}',
                    data: function (d) {
                        d.status_filter = $('#statusFilter').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'company_name', name: 'company_name' },
                    { data: 'total_amount', name: 'total_amount' },
                    { data: 'amount_due', name: 'amount_due' },
                    { data: 'company_person_name', name: 'company_person_name' },
                    { data: 'my_company_name', name: 'my_company_name' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[7, 'desc']]
            });

            // Redraw table when filter changes
            $('#statusFilter').change(function () {
                table.draw();
            });

            // Handle AJAX status change
            $(document).on('change', '.status-selector', function () {
                const selectEl = $(this);
                const noticeId = selectEl.data('id');
                const oldStatus = selectEl.data('current');
                const newStatus = selectEl.val();

                if (confirm(`Are you sure you want to change status from '${oldStatus}' to '${newStatus}'?`)) {
                    $.ajax({
                        url: "{{ route('legal-notices.status', ':id') }}".replace(':id', noticeId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'PATCH',
                            status: newStatus
                        },
                        success: function (response) {
                            if (response.status) {
                                // Update current data attribute
                                selectEl.data('current', newStatus);

                                // Reset badge styling classes
                                selectEl.removeClass('bg-warning text-dark bg-success bg-danger bg-info bg-secondary text-white');

                                const badgeClasses = {
                                    'Pending': 'bg-warning text-dark',
                                    'Approved': 'bg-success text-white',
                                    'Rejected': 'bg-danger text-white',
                                    'In Progress': 'bg-info text-dark',
                                    'Closed': 'bg-secondary text-white'
                                };
                                selectEl.addClass(badgeClasses[newStatus] || 'bg-light');

                                // Show success alert
                                $('#toast-message').text(response.message);
                                $('#toast-container').fadeIn().delay(3000).fadeOut();
                            } else {
                                alert(response.message);
                                selectEl.val(oldStatus);
                            }
                        },
                        error: function (xhr) {
                            alert('An error occurred while updating status. Please try again.');
                            selectEl.val(oldStatus);
                        }
                    });
                } else {
                    // Revert value
                    selectEl.val(oldStatus);
                }
            });
        });
    </script>
@endsection