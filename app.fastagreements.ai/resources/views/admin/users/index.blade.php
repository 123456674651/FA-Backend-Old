@extends('admin.layout.layout')

@section('css')
    <!-- Datatable CSS & Custom Typography -->
    <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css" rel="stylesheet"
        type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/custom-datatables.css') }}?v={{ time() }}" rel="stylesheet" type="text/css">
    <style>
        /* Page Loader Styles */
        #page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            z-index: 999999;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .custom-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(10, 179, 156, 0.2);
            border-top-color: #0ab39c;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
@endsection

@section('content')
    <!-- Page Loader -->
    <div id="page-loader">
        <div class="custom-spinner"></div>
    </div>

    <main id="main" class="main">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Admins</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
                            <li class="breadcrumb-item active">Users</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Manage Admins</h5>
                        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                            <i class="ri-add-line align-middle me-1"></i> Add Admin
                        </a>
                    </div>
                    <div class="card-body">
                        <table id="usersTable" class="gridjs-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('js')
    <!-- Datatable Scripts -->
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('users.data') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                },
                initComplete: function(settings, json) {
                    // Hide loader once the datatable data is fully loaded
                    $('#page-loader').fadeOut('slow');
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle fw-semibold text-secondary' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'role', name: 'role' },
                    { data: 'status', name: 'status', className: 'text-center' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[5, 'desc']]
            });
        });
    </script>
    <script>
        // Delegate click for status toggle
        $(document).on('click', '.toggle-status', function (e) {
            e.preventDefault();
            var el = $(this);
            var id = el.data('id');
            var status = parseInt(el.data('status'));
            var newStatus = status === 1 ? 0 : 1;
            var url = '{{ url('users') }}' + '/' + id + '/status';

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (json.success) {
                        // reload the table row to reflect change
                        $('#usersTable').DataTable().ajax.reload(null, false);
                    } else {
                        alert('Could not update status');
                    }
                }).catch(function () {
                    alert('Error updating status');
                });
        });
    </script>
@endsection