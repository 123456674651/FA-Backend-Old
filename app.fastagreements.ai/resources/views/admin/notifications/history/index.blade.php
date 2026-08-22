@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Notification History</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Notification History</li>
                </ol>
            </nav>
        </div>
        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('notifications.send.index') }}" class="btn btn-dark">
                <i class="bi bi-plus-circle me-1"></i> Send New Notification
            </a>
        </div>
    </div>

    <section class="section">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Search and Filters Form -->
                <form action="{{ route('notification-history.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Search Campaigns</label>
                        <input type="text" name="search" class="form-control" placeholder="Search title, message..." value="{{ $search }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-semibold">Notification Type</label>
                        <select name="notification_type" class="form-select">
                            <option value="">All Types</option>
                            @foreach($types as $key => $val)
                                <option value="{{ $key }}" {{ $type == $key ? 'selected' : '' }}>{{ $val }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-semibold">Campaign Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="sending" {{ $status === 'sending' ? 'selected' : '' }}>Sending</option>
                            <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Date Range</label>
                        <div class="d-flex align-items-center">
                            <input type="date" name="from_date" class="form-control me-1" value="{{ $fromDate }}">
                            <span class="me-1 small text-muted">to</span>
                            <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex">
                        <button type="submit" class="btn btn-dark fw-semibold px-3 me-1 w-100">
                            <i class="bi bi-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('notification-history.index') }}" class="btn btn-outline-secondary fw-semibold px-3 w-100">
                            Reset
                        </a>
                    </div>
                </form>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table align-middle table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">ID</th>
                                <th>Campaign Title</th>
                                <th>Type</th>
                                <th class="text-center">Recipients</th>
                                <th class="text-center">Success</th>
                                <th class="text-center">Failed</th>
                                <th>Sent By</th>
                                <th>Delivery Date / Time</th>
                                <th class="text-center" style="width: 120px;">Status</th>
                                <th class="text-center" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td class="text-center">{{ $log->id }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $log->title }}</div>
                                        <div class="text-muted small text-truncate" style="max-width: 250px;">{{ strip_tags($log->message) }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $types[$log->notification_type] ?? $log->notification_type }}</span>
                                    </td>
                                    <td class="text-center fw-semibold text-dark">{{ number_format($log->total_recipients) }}</td>
                                    <td class="text-center text-success fw-semibold">{{ number_format($log->success_count) }}</td>
                                    <td class="text-center text-danger fw-semibold">{{ number_format($log->failed_count) }}</td>
                                    <td>{{ $log->sender->name ?? 'System' }}</td>
                                    <td>
                                        @if($log->status === 'scheduled')
                                            <span class="text-warning small fw-semibold"><i class="bi bi-clock me-1"></i> Scheduled: {{ $log->scheduled_at->format('Y-m-d H:i') }}</span>
                                        @else
                                            <span class="small text-muted">{{ $log->created_at->format('Y-m-d H:i') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $badgeClass = 'bg-secondary';
                                            if ($log->status === 'completed') { $badgeClass = 'bg-success'; }
                                            elseif ($log->status === 'failed') { $badgeClass = 'bg-danger'; }
                                            elseif ($log->status === 'sending') { $badgeClass = 'bg-info'; }
                                            elseif ($log->status === 'scheduled') { $badgeClass = 'bg-warning text-dark'; }
                                        @endphp
                                        <span class="badge {{ $badgeClass }} fw-bold">{{ strtoupper($log->status) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('notification-history.show', $log->id) }}" class="btn btn-sm btn-outline-dark" title="View details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <!-- Resend Trigger Form -->
                                            <form action="{{ route('notification-history.resend', $log->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to resend this notification campaign to the same target group?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Resend Notification">
                                                    <i class="bi bi-lightning"></i>
                                                </button>
                                            </form>
                                            <!-- Delete Log Form -->
                                            <form action="{{ route('notification-history.destroy', $log->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this campaign history log? This action is irreversible.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Log">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">No campaign logs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-end mt-3">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
