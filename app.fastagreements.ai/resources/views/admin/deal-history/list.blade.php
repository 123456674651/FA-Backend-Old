{{-- @extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Deal History</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active">Deal Management</li>
                </ol>
            </nav>
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2">
        </div>
    </div>
    <!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Deal List</h5>
                        
                        <!-- Table with hoverable rows -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="dealsTable">
                                <thead>
                                    <tr>
                                        <th>Deal ID</th>
                                        <th>Payable Amount</th>
                                        <th>Interest Term (Months)</th>
                                        <th>Start Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($deals as $deal)
                                        <tr>
                                            <td>{{ $deal->id }}</td>
                                            <td>${{ number_format($deal->payable_amount, 2) }}</td>
                                            <td>{{ $deal->interest_term_in_month }}</td>
                                            <td>{{ $deal->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <a href="{{ route('admin.deal-history', $deal->id) }}" class="btn btn-primary">
                                                    View History
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- End Table -->

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- DataTables Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dealsTable').DataTable({
                processing: true,
                serverSide: false, // Set to true if you are using server-side processing
                autoWidth: false,
                responsive: true,
                order: [[0, 'desc']], // Sort by the first column (Deal ID) in descending order
            });
        });
    </script>

    <!-- Custom CSS -->
    <style>
        /* Ensure the table takes full width */
        #dealsTable {
            width: 100%;
        }

        /* Center the actions column */
        #dealsTable td, #dealsTable th {
            text-align: center;
        }

        /* Ensure table container doesn't overflow */
        .table-responsive {
            overflow-x: auto;
        }
    </style>

</main>
@endsection --}}
