@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="pagetitle mb-4">
        <div class="row align-items-center">
            <div class="col-md-6 pt-2">
                <h1 class="fw-bold text-dark mb-1" style="font-size: 28px;">User Details</h1>
            </div>
            <div class="col-md-6 text-md-end pt-2">
                <a href="{{ route('users.index') }}" class="btn button-color text-white">
                    <i class="bi bi-arrow-left-square text-white"></i> Back
                </a>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card border-0 shadow-sm" style="border-radius:10px;">
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <img src="{{ $user->profile_picture ? asset($user->profile_picture) : asset('assets/img/logo/logo.jpeg') }}" class="img-thumbnail rounded" style="max-height:150px; object-fit:cover;">
                    </div>
                    <div class="col-md-9">
                        <h4>{{ $user->name }}</h4>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>Mobile:</strong> {{ $user->mobile ?? 'N/A' }}</p>
                        <p><strong>Status:</strong> {!! $user->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' !!}</p>
                        <p><strong>Created:</strong> {{ $user->created_at }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
