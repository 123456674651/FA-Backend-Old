@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>CMS Pages</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">CMS Pages</li>
                </ol>
            </nav>
        </div>
        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('cms-pages.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create New
            </a>
        </div>
    </div>
    <!-- End Page Title -->

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

                        <div class="table-responsive">
                            <table id="cmsPagesTable" class="table table-bordered w-100">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 50px;">S.No</th>
                                        <th class="text-center" style="width: 80px;">Featured Image</th>
                                        <th>Title</th>
                                        <th>Slug</th>
                                        <th class="text-center" style="width: 100px;">Status</th>
                                        <th>Created Date</th>
                                        <th class="text-center" style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will populate this table -->
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
$(document).ready(function() {
    $('#cmsPagesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('cms-pages.index') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'featured_image', name: 'featured_image', orderable: false, searchable: false },
            { data: 'title', name: 'title' },
            { data: 'slug', name: 'slug' },
            { data: 'status', name: 'status' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[5, 'desc']] // Order by Created Date desc by default
    });
});
</script>
@endsection
