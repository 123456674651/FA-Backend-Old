@extends('admin.layout.admin')

@section('content')
    <main id="main" class="main">
        <div class="row">
            <div class="pagetitle col-lg-6 pt-2">
                <h1>Deal Management</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active">Deal Management</li>
                    </ol>
                </nav>
            </div>

            <div class="pagetitle col-lg-6 text-end pt-2">
                <a href="{{ route('deals.create') }}">
                    <button type="button" class="btn button-color text-white">
                        <i class="bi bi-plus-circle text-white"></i> Add Deal
                    </button>
                </a>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Deal List</h5>
                            <table class="table table-striped table-bordered" id="dealsTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Person 1</th>
                                        <th>Person 2</th>
                                        <th>Amount</th>
                                        <th>Purpose</th>
                                        <th>Actions</th>
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

        <!-- DataTables Scripts -->
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#dealsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('deals.index') }}',
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'person_1',
                            name: 'person_1',
                            className: 'text-center'
                        },
                        {
                            data: 'person_2',
                            name: 'person_2',
                            className: 'text-center'
                        },
                        {
                            data: 'payable_amount',
                            name: 'payable_amount',
                            className: 'text-center'
                        },
                        {
                            data: 'purpose',
                            name: 'purpose',
                            className: 'text-center'
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        }
                    ],
                    autoWidth: false
                });
            });
        </script>


        <!-- Custom CSS -->
        <style>
            #dealsTable {
                width: 100%;
            }

            #dealsTable .text-center {
                text-align: center;
            }

            .table-container {
                overflow-x: auto;
            }
        </style>
    </main>
@endsection
