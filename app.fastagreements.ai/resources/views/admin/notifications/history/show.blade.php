@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Campaign Details</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('notification-history.index') }}">History</a></li>
                    <li class="breadcrumb-item active">Campaign Log</li>
                </ol>
            </nav>
        </div>
        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('notification-history.index') }}" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left-square"></i> Back to History
            </a>
            <form action="{{ route('notification-history.resend', $log->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to resend this notification campaign to the same target group?');">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-lightning-charge me-1"></i> Resend Campaign
                </button>
            </form>
        </div>
    </div>

    <section class="section">
        <!-- Campaign Summary Card -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 text-dark">Campaign Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 180px;">Title</th>
                                <td><strong class="text-dark">{{ $log->title }}</strong></td>
                            </tr>
                            <tr>
                                <th>Notification Type</th>
                                <td><span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $log->notification_type)) }}</span></td>
                            </tr>
                            <tr>
                                <th>Sent By</th>
                                <td>{{ $log->sender->name ?? 'System' }} ({{ $log->sender->email ?? 'N/A' }})</td>
                            </tr>
                            <tr>
                                <th>Sent Date / Time</th>
                                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            @if($log->scheduled_at)
                                <tr>
                                    <th>Scheduled Time</th>
                                    <td><span class="text-warning fw-semibold"><i class="bi bi-clock me-1"></i> {{ $log->scheduled_at->format('Y-m-d H:i:s') }}</span></td>
                                </tr>
                            @endif
                            <tr>
                                <th>Status</th>
                                <td>
                                    @php
                                        $badgeClass = 'bg-secondary';
                                        if ($log->status === 'completed') { $badgeClass = 'bg-success'; }
                                        elseif ($log->status === 'failed') { $badgeClass = 'bg-danger'; }
                                        elseif ($log->status === 'sending') { $badgeClass = 'bg-info'; }
                                        elseif ($log->status === 'scheduled') { $badgeClass = 'bg-warning text-dark'; }
                                    @endphp
                                    <span class="badge {{ $badgeClass }} fw-bold">{{ strtoupper($log->status) }}</span>
                                </td>
                            </tr>
                        </table>

                        <div class="mt-4">
                            <h6 class="fw-bold text-dark">Campaign Message Body</h6>
                            <div class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                                {!! $log->message !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Campaign Stats Card -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold mb-4 text-dark text-start">Delivery Statistics</h5>

                        <div class="row g-3">
                            <div class="col-12 py-3 bg-light rounded border">
                                <span class="d-block text-muted small text-uppercase fw-semibold">Total Recipients</span>
                                <span class="d-block display-5 fw-bold text-dark my-1">{{ number_format($log->total_recipients) }}</span>
                            </div>
                            <div class="col-6 py-3 bg-light rounded border text-success">
                                <span class="d-block text-muted small text-uppercase fw-semibold">Success Count</span>
                                <span class="d-block h3 fw-bold my-1">{{ number_format($log->success_count) }}</span>
                            </div>
                            <div class="col-6 py-3 bg-light rounded border text-danger">
                                <span class="d-block text-muted small text-uppercase fw-semibold">Failed Count</span>
                                <span class="d-block h3 fw-bold my-1">{{ number_format($log->failed_count) }}</span>
                            </div>
                        </div>

                        <div class="mt-4 border rounded p-3 bg-light">
                            <h6 class="fw-bold mb-2 small text-dark text-start">Banner Image</h6>
                            @if($log->image)
                                <img src="{{ asset($log->image) }}" class="img-fluid rounded shadow-sm" style="max-height: 120px; object-fit: cover;">
                            @else
                                <span class="text-muted small">No image attached to this campaign.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recipient Log Card -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3 text-dark">Recipient Delivery Logs</h5>

                <!-- Search and Filters Form -->
                <form action="{{ route('notification-history.show', $log->id) }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Search by recipient name, mobile, email..." value="{{ $search }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Delivery Statuses</option>
                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="success" {{ $status === 'success' ? 'selected' : '' }}>Success</option>
                            <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex">
                        <button type="submit" class="btn btn-dark fw-semibold px-4 me-2">
                            <i class="bi bi-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('notification-history.show', $log->id) }}" class="btn btn-outline-secondary fw-semibold px-4">
                            Reset
                        </a>
                    </div>
                </form>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table align-middle table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">S.No</th>
                                <th>Recipient Name</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th style="max-width: 200px;">FCM Token Used</th>
                                <th class="text-center" style="width: 120px;">Status</th>
                                <th>Failure Reason / Response</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recipients as $idx => $recipient)
                                <tr>
                                    <td class="text-center">{{ $recipients->firstItem() + $idx }}</td>
                                    <td class="fw-semibold text-dark">{{ $recipient->customer->name ?? 'N/A' }}</td>
                                    <td>{{ $recipient->customer->mobile ?? 'N/A' }}</td>
                                    <td>{{ $recipient->customer->email ?? 'N/A' }}</td>
                                    <td class="text-muted small text-truncate" style="max-width: 200px;" title="{{ $recipient->fcm_token }}">
                                        {{ $recipient->fcm_token ?: 'No Token' }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $deliveryBadge = 'bg-secondary';
                                            if ($recipient->delivery_status === 'success') { $deliveryBadge = 'bg-success'; }
                                            elseif ($recipient->delivery_status === 'failed') { $deliveryBadge = 'bg-danger'; }
                                            elseif ($recipient->delivery_status === 'pending') { $deliveryBadge = 'bg-warning text-dark'; }
                                        @endphp
                                        <span class="badge {{ $deliveryBadge }} fw-bold">{{ strtoupper($recipient->delivery_status) }}</span>
                                    </td>
                                    <td class="small {{ $recipient->delivery_status === 'failed' ? 'text-danger' : 'text-muted' }}">
                                        @if($recipient->delivery_status === 'failed')
                                            <i class="bi bi-exclamation-circle me-1"></i> {{ $recipient->failure_reason }}
                                        @elseif(!empty($recipient->firebase_response))
                                            <span class="text-truncate d-inline-block" style="max-width: 250px;" title="{{ $recipient->firebase_response }}">{{ $recipient->firebase_response }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No recipient logs found matching current configuration.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-end mt-3">
                    {{ $recipients->links() }}
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
