@extends('admin.layout.admin')

@section('content')
    <main id="main" class="main">

        <div class="row">
            <div class="pagetitle col-lg-6 pt-2">
                <h1>Customer List</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Home</a></li>
                        <li class="breadcrumb-item active">Customer List</li>
                    </ol>
                </nav>
            </div>

            <div class="pagetitle col-lg-6 text-end pt-2">
                <a href="{{ route('customers.create') }}">
                    <button type="button" class="btn button-color text-white">
                        <i class="bi bi-plus-square"></i> Add Customer
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
                            <h5 class="card-title">Customer List</h5>

                            <!-- Table with hoverable rows -->
                            <table class="table table-striped table-bordered" id="customerTable">
                                <thead>
                                    <tr>
                                        <th class="text-center align-middle" style="width: 5%;">Sr#</th>
                                        <th class="text-center align-middle">Name</th>
                                        <th class="text-center align-middle">Mobile</th>
                                        <th class="text-center align-middle">Email</th>
                                        <th class="text-center align-middle">City</th>
                                        <th class="text-center align-middle">State</th>
                                        <th class="text-center align-middle">Country</th>
                                        <th class="text-center align-middle">Status</th>
                                        <th class="text-center align-middle">Actions</th>
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
            $(document).ready(function () {
                $('#customerTable').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 50,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    ajax: '{{ route('customers.index') }}',
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                        { data: 'name', name: 'name', className: 'text-center' },
                        { data: 'mobile', name: 'mobile', className: 'text-center' },
                        { data: 'email', name: 'email', className: 'text-center' },
                        { data: 'city', name: 'city', className: 'text-center' },
                        { data: 'state', name: 'state', className: 'text-center' },
                        { data: 'country', name: 'country', className: 'text-center' },
                        { data: 'is_active', name: 'is_active', className: 'text-center' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    autoWidth: false // This ensures the table uses full width of its container
                });

                // Handle delete button click
                $('#customerTable').on('click', '.delete-button', function () {
                    var id = $(this).data('id');
                    if (confirm('Are you sure you want to delete this customer?')) {
                        $.ajax({
                            url: '{{ url('customers') }}/' + id,
                            type: 'DELETE',
                            success: function (response) {
                                $('#customerTable').DataTable().ajax.reload();
                            },
                            error: function (response) {
                                alert('Failed to delete the customer.');
                            }
                        });
                    }
                });
            });

        </script>

        <!-- Custom CSS -->
        <style>
            /* Ensure the table takes full width */
            #customerTable {
                width: 100%;
            }

            /* Center the actions column */
            #customerTable .text-center {
                text-align: center;
            }

            /* Ensure table container doesn't overflow */
            .table-container {
                overflow-x: auto;
            }
        </style>

    </main>
@endsection