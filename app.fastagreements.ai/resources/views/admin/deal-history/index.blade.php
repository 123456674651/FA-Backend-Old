@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="container my-4">
        <h1 class="my-4">Deal History</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">Deal History</li>
            </ol>
        </nav>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($status == 0)
            <div class="alert alert-warning">
                {{ $message }}
            </div>
        @else
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title text-secondary">Deal Details</h5>
                        <a href="{{ route('deals.index') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Deal List
                        </a>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Person 1:</strong> <span class="text-muted">{{ $deal['person_1'] ?? 'N/A' }}</span></p>
                            <p class="mb-1"><strong>Person 2:</strong> <span class="text-muted">{{ $deal['person_2'] ?? 'N/A' }}</span></p>
                            <p class="mb-1"><strong>Sakshi:</strong> <span class="text-muted">{{ $deal['sakshi'] ?? 'N/A' }}</span></p>
                            <p class="mb-1"><strong>Purpose:</strong> <span class="text-muted">{{ $deal['purpose'] ?? 'N/A' }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Amount:</strong> <span class="text-muted">{{ number_format($deal['amount'], 2) ?? 'N/A' }} </span></p>
                            <p class="mb-1"><strong>Interest Rate:</strong> <span class="text-muted">{{ $deal['interest_rate'] ?? 'N/A' }}%</span></p>
                            <p class="mb-1"><strong>Currency:</strong> <span class="text-muted">{{ $deal['currency'] ?? 'N/A' }}</span></p>
                            <p class="mb-1"><strong>Payable Amount:</strong> <span class="text-muted">{{ number_format($deal['payable_amount'], 2) }} </span></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Interest Term (Months):</strong> <span class="text-muted">{{ $deal['interest_term_in_month'] }}</span></p>
                            <p class="mb-1"><strong>Start Date:</strong> <span class="text-muted">{{ $deal['start_date'] }}</span></p>
                        </div>
                    </div>

                    <!-- Table with hoverable rows -->
                    <h5 class="card-title text-secondary">Payment Schedule</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Month</th>
                                    <th>Due Date</th>
                                    <th>Amount Due</th>
                                    <th>Is Overdue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($deal['due_dates'] as $dueDate)
                                    <tr>
                                        <td>{{ $dueDate['month'] }}</td>
                                        <td>{{ $dueDate['due_date'] }}</td>
                                        <td>{{ number_format($dueDate['amount_due'], 2) }}</td>
                                        <td>{{ $dueDate['is_overdue'] ? 'Yes' : 'No' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</main>
@endsection

