@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">

    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Subscription Plan Management</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Subscription Plans</li>
                </ol>
            </nav>
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('subscription-plans.create') }}">
                <button type="button" class="btn button-color text-white">
                    <i class="bi bi-plus-circle text-white"></i> Add Plan
                </button>
            </a>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Subscription Plan List</h5>

                        <table class="table table-striped table-bordered" id="plansTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Plan Name</th>
                                    <th>Price</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- DataTables loads data --}}
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- DataTables Scripts --}}
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#plansTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('subscription-plans.index') }}',
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'name',
                        name: 'name',
                        className: 'text-center'
                    },
                    {
                        data: 'price',
                        name: 'price',
                        className: 'text-center'
                    },
                    {
                        data: 'duration',
                        name: 'duration',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false,
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

    {{-- Custom CSS --}}
    <style>
        #plansTable {
            width: 100%;
        }

        #plansTable .text-center {
            text-align: center;
        }
    </style>

</main>
@endsection
