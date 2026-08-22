@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">

    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>User Subscription Management</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="breadcrumb-item active">User Subscriptions</li>
                </ol>
            </nav>
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('customer-subscriptions.create') }}">
                <button type="button" class="btn button-color text-white">
                    <i class="bi bi-plus-circle text-white"></i> Add Subscription
                </button>
            </a>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Subscription List</h5>

                        <table class="table table-striped table-bordered" id="subscriptionTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Plan</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- DataTables --}}
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#subscriptionTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('customer-subscriptions.index') }}',
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false, className:'text-center' },
                    { data: 'customer', className:'text-center' },
                    { data: 'plan', className:'text-center' },
                    { data: 'start_date', className:'text-center' },
                    { data: 'end_date', className:'text-center' },
                    { data: 'status', orderable:false, searchable:false, className:'text-center' },
                    { data: 'actions', orderable:false, searchable:false, className:'text-center' }
                ]
            });
        });
    </script>

    <style>
        #subscriptionTable { width: 100%; }
        #subscriptionTable .text-center { text-align: center; }
    </style>

</main>
@endsection
