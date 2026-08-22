@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Add New Language</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('languages.index') }}">Language List</a></li>
                    <li class="breadcrumb-item active">Add New Language</li>
                </ol>
            </nav>
        </div>
        <div class="pagetitle col-lg-6 text-end pt-2 justify-content-center">
            <a href="{{ route('languages.index') }}">
                <button type="button" class="btn button-color text-white">
                    <i class="bi bi-arrow-left-square text-white"></i> Back
                </button>
            </a>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('languages.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="language_name" class="form-label">Language Name</label>
                                <input type="text" class="form-control @error('language_name') is-invalid @enderror"
                                    id="language_name" name="language_name" value="{{ old('language_name') }}" required>
                                @error('language_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="language_code" class="form-label">Language Code</label>
                                <input type="text" class="form-control @error('language_code') is-invalid @enderror"
                                    id="language_code" name="language_code" value="{{ old('language_code') }}" required>
                                @error('language_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="language_in_guj" class="form-label">Language in GUJ</label>
                                <input type="text" class="form-control @error('language_in_guj') is-invalid @enderror"
                                    id="language_in_guj" name="language_in_guj" value="{{ old('language_in_guj') }}"
                                    required>
                                @error('language_in_guj')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('languages.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->
@stop