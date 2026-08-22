@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Slider List</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active">Slider List</li>
                </ol>
            </nav>
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('sliders.create') }}">
                <button type="button" class="btn button-color text-white">
                    <i class="bi bi-plus-square"></i> Add
                </button>
            </a>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card p-2 pt-4">
                    <div class="card-body">
                        <table class="table data-table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle" style="width: 5%;">Sr#</th>
                                    <th class="text-center align-middle">Title</th>
                                    <th class="text-center align-middle">Image</th> <!-- New Column for Image -->
                                    <th class="text-center align-middle">Expire Date</th>
                                    <th class="text-center align-middle">Status</th> <!-- New Column for Status -->
                                    <th class="text-center align-middle">Actions</th>
                                </tr>
                            </thead>
                            
                            <tbody>
                                <!-- DataTables will populate this -->
                            </tbody>
                        </table>
                        
                        <!-- End Table with striped rows -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->

<!-- DataTables CSS and JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('sliders.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'title', name: 'title' },
                { data: 'image', name: 'image', orderable: false, searchable: false }, // New Column for Image
                { data: 'expire_date', name: 'expire_date' },
                { data: 'status', name: 'status', orderable: false, searchable: false }, // New Column for Status
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });
    </script>
    
@endsection
