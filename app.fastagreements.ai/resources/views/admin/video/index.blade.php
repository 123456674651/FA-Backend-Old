@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">

    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Video Management</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active">Video Management</li>
                </ol>
            </nav>
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('videos.create') }}">
                <button type="button" class="btn button-color text-white">
                    <i class="bi bi-plus-circle text-white"></i> Add Video
                </button>
            </a>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Video List</h5>
                        
                        <!-- Table with hoverable rows -->
                        <table class="table table-striped table-bordered" id="videoTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Tags</th>
                                    <th>Video</th>
                                    <th>Actions</th>
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
    <script>
        $(document).ready(function() {
            $('#videoTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('videos.index') }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false , className: 'text-center'},
                    { data: 'title', name: 'title', className: 'text-center' },
                    { data: 'description', name: 'description', className: 'text-center' },
                    { data: 'tags', name: 'tags', className: 'text-center' },
                    { data: 'file_name', name: 'file_name', render: function(data, type, row) {
                        return `<video width="120" controls>
                                    <source src="{{ asset('admin/video/') }}/${data}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>`;
                    }, className: 'text-center'},
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                autoWidth: false // This ensures the table uses full width of its container
            });
        });
    </script>

    <!-- Custom CSS -->
    <style>
        /* Ensure the table takes full width */
        #videoTable {
            width: 100%;
        }

        /* Center the actions column */
        #videoTable .text-center {
            text-align: center;
        }

        /* Ensure table container doesn't overflow */
        .table-container {
            overflow-x: auto;
        }
    </style>

</main>
@endsection
