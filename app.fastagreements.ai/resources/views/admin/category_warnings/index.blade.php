@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <!-- Breadcrumb & Header -->
    <div class="pagetitle mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="background: none; padding: 0;">
                @if($category->parent_id)
                    <li class="breadcrumb-item"><a href="{{ route('deal_categories.index') }}">Agreement Warnings</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('deal_categories.index', ['parent_id' => $category->parent_id]) }}">{{ $category->parent->category_name ?? 'Parent' }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Warnings</li>
                @else
                    <li class="breadcrumb-item"><a href="{{ route('deal_categories.index') }}">Deal Categories</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Warnings</li>
                @endif
            </ol>
        </nav>
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="fw-bold text-dark mb-1" style="font-size: 28px;">{{ $category->category_name }} - Warnings</h1>
                <p class="text-muted mb-0" style="font-size: 14px;">Manage all warnings for this deal category.</p>
            </div>
            <div class="col-md-6 text-md-end pt-2">
                @if($category->parent_id)
                    <a href="{{ route('deal_categories.index', ['parent_id' => $category->parent_id]) }}" class="btn btn-secondary px-4 py-2" style="border-radius: 8px;">
                        <i class="bi bi-arrow-left-square me-2"></i>Back to Sub Agreements
                    </a>
                @else
                    <a href="{{ route('deal_categories.index') }}" class="btn btn-secondary px-4 py-2" style="border-radius: 8px;">
                        <i class="bi bi-arrow-left-square me-2"></i>Back to Categories
                    </a>
                @endif
            </div>
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
        <!-- Table Card -->
        <div class="card border-0 shadow-sm" style="border-radius: 10px; overflow: hidden;">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="warningsTable" class="table table-hover w-100 align-middle">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 80px;">#</th>
                                <th>Language</th>
                                <th>Title</th>
                                <th class="text-center" style="width: 120px;">Display Order</th>
                                <th class="text-center" style="width: 150px;">Status</th>
                                <th>Created Date</th>
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
/* Datatable Styles */
#warningsTable {
    border-collapse: separate !important;
    border-spacing: 0 !important;
}
#warningsTable thead th {
    background-color: #f8f9fa !important;
    font-weight: 700 !important;
    font-size: 15px !important;
    color: #495057 !important;
    vertical-align: middle !important;
    border-bottom: 2px solid #dee2e6 !important;
    padding: 15px 12px !important;
}
#warningsTable tbody tr {
    height: 65px;
    transition: background-color 0.2s ease-in-out;
}
#warningsTable tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02) !important;
}
#warningsTable tbody td {
    vertical-align: middle !important;
    padding: 12px !important;
    border-bottom: 1px solid #efefef !important;
}
</style>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $('#warningsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('category-warnings.index') }}',
            data: function (d) {
                d.category_id = '{{ $category->id }}';
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle fw-semibold text-secondary' },
            { data: 'language', name: 'language', className: 'align-middle fw-semibold text-primary' },
            { data: 'title', name: 'title', className: 'align-middle fw-bold text-dark' },
            { data: 'display_order', name: 'display_order', className: 'text-center align-middle fw-semibold text-secondary' },
            { data: 'status', name: 'status', className: 'text-center align-middle' },
            { data: 'warning_created_at', name: 'warning_created_at', className: 'align-middle text-secondary' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle' }
        ],
        order: [[3, 'asc']],
        language: {
            paginate: {
                next: '<i class="bi bi-chevron-right"></i>',
                previous: '<i class="bi bi-chevron-left"></i>'
            }
        }
    });
});
</script>
@endsection
