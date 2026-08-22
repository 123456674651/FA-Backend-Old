@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Subscription Invoices</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Subscription Invoices</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Subscription Invoice List</h5>
                        <form id="invoiceFilters" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">From Date</label>
                                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">To Date</label>
                                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status" class="form-control">
                                    <option value="">All</option>
                                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Subscription Plan</label>
                                <select name="subscription_plan_id" class="form-control">
                                    <option value="">All Plans</option>
                                    @foreach ($plans as $plan)
                                        <option value="{{ $plan->id }}" {{ request('subscription_plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('subscription-invoices.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>

                        <table class="table table-striped table-bordered" id="invoicesTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Invoice Number</th>
                                    <th>Customer Name</th>
                                    <th>Plan Name</th>
                                    <th>Amount</th>
                                    <th>Payment Status</th>
                                    <th>Payment Method</th>
                                    <th>Invoice Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function () {
            $('#invoicesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('subscription-invoices.index') }}',
                    data: function (d) {
                        d.from_date = $('input[name=from_date]').val();
                        d.to_date = $('input[name=to_date]').val();
                        d.payment_status = $('select[name=payment_status]').val();
                        d.subscription_plan_id = $('select[name=subscription_plan_id]').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'invoice_number', name: 'invoice_number', className: 'text-center' },
                    { data: 'customer_name', name: 'customer_name', className: 'text-center' },
                    { data: 'plan_name', name: 'plan_name', className: 'text-center' },
                    { data: 'amount', name: 'amount', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'payment_status', name: 'payment_status', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'payment_method', name: 'payment_method', className: 'text-center' },
                    { data: 'invoice_date', name: 'invoice_date', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                ],
                autoWidth: false,
            });

            $('#invoiceFilters').on('submit', function (e) {
                e.preventDefault();
                $('#invoicesTable').DataTable().ajax.reload();
            });
        });
    </script>
</main>
@endsection
