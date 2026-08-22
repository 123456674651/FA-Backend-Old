@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Notification Templates</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Templates</li>
                </ol>
            </nav>
        </div>
        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('notification-templates.create') }}" class="btn btn-dark btn-md fw-semibold px-4">
                <i class="bi bi-plus-circle me-1"></i> Create New Template
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

                <!-- Search and Filters Form -->
                <form action="{{ route('notification-templates.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Search by title, subject, message..." value="{{ $search }}">
                    </div>
                    <div class="col-md-3">
                        <select name="notification_type" class="form-select">
                            <option value="">All Types</option>
                            @foreach($types as $key => $val)
                                <option value="{{ $key }}" {{ $type == $key ? 'selected' : '' }}>{{ $val }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex">
                        <button type="submit" class="btn btn-dark fw-semibold px-4 me-2">
                            <i class="bi bi-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('notification-templates.index') }}" class="btn btn-outline-secondary fw-semibold px-4">
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
                                <th style="width: 100px;">Image</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Subject</th>
                                <th class="text-center" style="width: 120px;">Status</th>
                                <th class="text-center" style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $temp)
                                <tr>
                                    <td class="text-center">{{ $temp->id }}</td>
                                    <td class="text-center">
                                        @if($temp->image)
                                            <img src="{{ asset($temp->image) }}" class="rounded shadow-sm" style="width: 60px; height: 40px; object-fit: cover;">
                                        @else
                                            <span class="text-muted small">No Image</span>
                                        @endif
                                    </td>
                                    <td class="fw-semibold text-dark">{{ $temp->title }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $types[$temp->notification_type] ?? $temp->notification_type }}</span>
                                    </td>
                                    <td>{{ $temp->subject ?: '-' }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('notification-templates.toggle', $temp->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $temp->status ? 'btn-success' : 'btn-danger' }} fw-bold px-2 py-1">
                                                {{ $temp->status ? 'Active' : 'Inactive' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('notification-templates.show', $temp->id) }}" class="btn btn-sm btn-outline-dark" title="View details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('notification-templates.edit', $temp->id) }}" class="btn btn-sm btn-outline-dark" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('notification-templates.destroy', $temp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No notification templates found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-end mt-3">
                    {{ $templates->links() }}
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
