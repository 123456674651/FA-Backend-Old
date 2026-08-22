@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Template Details</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('notification-templates.index') }}">Templates</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </nav>
        </div>
        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('notification-templates.index') }}" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left-square"></i> Back
            </a>
            <a href="{{ route('notification-templates.edit', $template->id) }}" class="btn btn-dark">
                <i class="bi bi-pencil"></i> Edit Template
            </a>
        </div>
    </div>

    <section class="section">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-8">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width: 200px;">Title</th>
                                <td>{{ $template->title }}</td>
                            </tr>
                            <tr>
                                <th>Type</th>
                                <td><span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $template->notification_type)) }}</span></td>
                            </tr>
                            <tr>
                                <th>Subject</th>
                                <td>{{ $template->subject ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge bg-{{ $template->status ? 'success' : 'danger' }}">
                                        {{ $template->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Created Date</th>
                                <td>{{ $template->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        </table>

                        <div class="mt-4">
                            <h5 class="fw-bold mb-2">Message Body Preview</h5>
                            <div class="border rounded p-3 bg-light" style="min-height: 100px;">
                                {!! $template->message !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded p-3 text-center bg-light">
                            <h6 class="fw-bold mb-3">Banner Image</h6>
                            @if($template->image)
                                <img src="{{ asset($template->image) }}" class="img-fluid rounded shadow-sm" style="max-height: 250px;">
                            @else
                                <div class="py-5 text-muted">
                                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                                    <p class="mt-2 small">No image uploaded for this template.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
