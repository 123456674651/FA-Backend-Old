@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <!-- Page Header -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6 pt-2">
            <h1 class="fw-bold text-dark mb-1" style="font-size: 28px;">Advocate Management</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">Manage all advocates from one place.</p>
        </div>
        <div class="col-md-6 text-md-end pt-2">
            <a href="{{ route('advocates.create') }}" class="btn btn-primary px-4 py-2" style="background-color: #0d6efd; border-color: #0d6efd; border-radius: 8px; font-weight: 600;">
                <i class="bi bi-plus-circle-fill me-2"></i>Add Advocate
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <div class="row">
        <div class="col-lg-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 8px;">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 8px;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    </div>

    <section class="section">
        <!-- Search Filters Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="searchName" class="form-label fw-bold small text-secondary">Search by Name</label>
                        <div class="search-icon-wrapper">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchName" class="form-control filter-input" placeholder="Search advocate name...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="searchLawyerType" class="form-label fw-bold small text-secondary">Search by Lawyer Type</label>
                        <div class="search-icon-wrapper">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchLawyerType" class="form-control filter-input" placeholder="Search lawyer type...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="searchMobile" class="form-label fw-bold small text-secondary">Search by Mobile Number</label>
                        <div class="search-icon-wrapper">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchMobile" class="form-control filter-input" placeholder="Search mobile number...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card border-0 shadow-sm" style="border-radius: 10px; overflow: hidden;">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="advocatesTable" class="table table-hover w-100 align-middle">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Image</th>
                                <th>Name</th>
                                <th>Lawyer Type</th>
                                <th class="text-center">Experience</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Consultation Time</th>
                                <th class="text-center">Reviews</th>
                                <th class="text-center">Verified</th>
                                <th class="text-center">Status</th>
                                <th>Mobile</th>
                                <th class="text-center" style="width: 150px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Populated by DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
/* Filter Styles */
.search-icon-wrapper {
    position: relative;
}
.search-icon-wrapper .bi-search {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ea7ad;
    font-size: 14px;
    pointer-events: none;
}
.filter-input {
    height: 45px !important;
    border-radius: 8px !important;
    padding-left: 40px !important;
    border: 1px solid #dee2e6;
    font-size: 14px;
    background-color: #fdfdfd;
    transition: all 0.2s ease-in-out;
}
.filter-input:focus {
    background-color: #fff;
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
}

/* Datatable Styles */
#advocatesTable {
    border-collapse: separate !important;
    border-spacing: 0 !important;
}
#advocatesTable thead th {
    background-color: #f8f9fa !important;
    font-weight: 700 !important;
    font-size: 15px !important;
    color: #495057 !important;
    vertical-align: middle !important;
    border-bottom: 2px solid #dee2e6 !important;
    padding: 15px 12px !important;
    text-transform: none;
}
#advocatesTable tbody tr {
    height: 65px;
    transition: background-color 0.2s ease-in-out;
}
#advocatesTable tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02) !important;
}
#advocatesTable tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.005);
}
#advocatesTable tbody td {
    vertical-align: middle !important;
    padding: 12px !important;
    border-bottom: 1px solid #efefef !important;
}

/* Image inside circular box with white border and shadow */
#advocatesTable tbody td img.rounded-circle, 
#advocatesTable tbody td img {
    border-radius: 50% !important;
    border: 2px solid #fff !important;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
    width: 50px !important;
    height: 50px !important;
    object-fit: cover !important;
    display: inline-block !important;
}

/* Verified Badge styling */
#advocatesTable .badge.bg-success {
    background-color: rgba(25, 135, 84, 0.1) !important;
    color: #198754 !important;
    border-radius: 30px !important;
    padding: 6px 14px !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    display: inline-block !important;
    border: none !important;
}
#advocatesTable .badge.bg-secondary {
    background-color: rgba(108, 117, 125, 0.1) !important;
    color: #6c757d !important;
    border-radius: 30px !important;
    padding: 6px 14px !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    display: inline-block !important;
    border: none !important;
}

/* Price Styling */
#advocatesTable td.text-success {
    font-weight: 700 !important;
    color: #198754 !important;
    font-size: 15.5px !important;
}

/* Status toggler layout */
#advocatesTable .btn-outline-success {
    background-color: #d1e7dd !important;
    border-color: #a3cfbb !important;
    color: #0f5132 !important;
    border-radius: 30px !important;
    padding: 4px 14px !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    width: auto !important;
    height: auto !important;
    display: inline-block !important;
    box-shadow: none !important;
    transition: all 0.2s ease;
}
#advocatesTable .btn-outline-success:hover {
    background-color: #198754 !important;
    border-color: #198754 !important;
    color: #fff !important;
}
#advocatesTable .btn-outline-danger {
    background-color: #f8d7da !important;
    border-color: #f1aeb5 !important;
    color: #842029 !important;
    border-radius: 30px !important;
    padding: 4px 14px !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    width: auto !important;
    height: auto !important;
    display: inline-block !important;
    box-shadow: none !important;
    transition: all 0.2s ease;
}
#advocatesTable .btn-outline-danger:hover {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    color: #fff !important;
}

/* Force action buttons into single line */
#advocatesTable tbody td:last-child {
    white-space: nowrap !important;
    width: 140px !important;
    text-align: center !important;
}
#advocatesTable tbody td:last-child .modal {
    white-space: normal !important;
}
#advocatesTable tbody td:last-child > div > .btn {
    border-radius: 8px !important;
    width: 32px !important;
    height: 32px !important;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin: 0 3px !important;
    transition: all 0.2s ease-in-out;
    box-shadow: none !important;
}
#advocatesTable tbody td:last-child > div > .btn:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
}
#advocatesTable tbody td:last-child > div > .btn-info {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
}
#advocatesTable tbody td:last-child > div > .btn-primary {
    background-color: #4f46e5 !important;
    border-color: #4f46e5 !important;
}
#advocatesTable tbody td:last-child > div > .btn-danger {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
}

/* Pagination Customizations */
.dataTables_wrapper .dataTables_paginate {
    margin-top: 20px !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0 !important;
    margin: 0 2px !important;
    border: none !important;
    background: transparent !important;
}
.dataTables_wrapper .dataTables_paginate .pagination {
    display: inline-flex;
    border-radius: 8px;
}
.dataTables_wrapper .dataTables_paginate .page-link {
    border-radius: 8px !important;
    margin: 0 2px;
    border: 1px solid #dee2e6;
    color: #495057;
    padding: 6px 12px;
    font-weight: 500;
    transition: all 0.2s ease;
}
.dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
    color: white !important;
}
.dataTables_wrapper .dataTables_paginate .page-link:hover {
    background-color: #f1f3f5 !important;
    color: #0d6efd !important;
}
</style>
@endsection

@section('js')
<script>
$(document).ready(function() {
    const table = $('#advocatesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('advocates.index') }}',
            data: function (d) {
                d.search_name = $('#searchName').val();
                d.search_lawyer_type = $('#searchLawyerType').val();
                d.search_mobile = $('#searchMobile').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle fw-semibold text-secondary' },
            { data: 'image', name: 'image', orderable: false, searchable: false, className: 'text-center align-middle' },
            { 
                data: 'name', 
                name: 'name', 
                className: 'align-middle',
                render: function(data, type, row) {
                    const name = row.name ? row.name : data;
                    const lawyerType = row.lawyer_type ? row.lawyer_type : '';
                    return '<div style="font-size: 16px; font-weight: 700; color: #212529;">' + name + '</div>' +
                           '<div class="text-secondary small">' + lawyerType + '</div>';
                }
            },
            { data: 'lawyer_type', name: 'lawyer_type', className: 'align-middle text-muted' },
            { 
                data: 'experience', 
                name: 'experience', 
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    return '<span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill" style="font-size: 13px; font-weight: 600;">' + data + '</span>';
                }
            },
            { data: 'price', name: 'price', className: 'text-center align-middle fw-bold text-success' },
            { 
                data: 'consultation_time', 
                name: 'consultation_time', 
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    return '<span class="text-dark small" style="font-weight: 500;"><i class="bi bi-clock-fill text-muted me-1.5"></i>' + data + '</span>';
                }
            },
            { 
                data: 'total_reviews', 
                name: 'total_reviews', 
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    return '<span class="text-dark small" style="font-weight: 500;"><i class="bi bi-star-fill text-warning me-1.5"></i>' + data + ' Reviews</span>';
                }
            },
            { data: 'is_verified', name: 'is_verified', className: 'text-center align-middle' },
            { data: 'status', name: 'status', className: 'text-center align-middle' },
            { data: 'mobile_number', name: 'mobile_number', className: 'align-middle fw-medium' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle' }
        ],
        order: [[2, 'asc']],
        language: {
            paginate: {
                next: '<i class="bi bi-chevron-right"></i>',
                previous: '<i class="bi bi-chevron-left"></i>'
            }
        }
    });

    // Redraw table when filters change
    $('#searchName, #searchLawyerType, #searchMobile').on('keyup change', function() {
        table.draw();
    });
});
</script>
@endsection
