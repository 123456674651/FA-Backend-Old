@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Pages</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">Page List</li>
                </ol>
            </nav>
        </div>
        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('pages.create') }}" class="btn btn-primary">
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
                        <table id="pagesTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">Title</th>
                                    <th class="text-center">Details</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Actions</th>
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
    </section>
</main><!-- End #main -->

<!-- DataTables JavaScript -->
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>

<!-- Initialize DataTable -->
<script>
$(document).ready(function() {
    $('#pagesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('pages.index') }}',
        columns: [
            { data: 'page_title', name: 'page_title' },
            { data: 'page_details', name: 'page_details' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false } // Ensure this is set correctly
        ]
    });
});

</script>
@endsection
