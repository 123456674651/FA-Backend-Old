@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Create Page</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('pages.index') }}">Page List</a></li>
                    <li class="breadcrumb-item active">Create Page</li>
                </ol>
            </nav>
        </div>
        <div class="pagetitle col-lg-6 text-end pt-2 justify-content-center">
            <a href="{{ route('pages.index') }}">
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
                        <form action="{{ route('pages.store') }}" method="POST">
                            @csrf
                            <!-- Page Title -->
                            <div class="mb-3">
                                <label for="page_title" class="form-label">Page Title</label>
                                <input type="text" class="form-control @error('page_title') is-invalid @enderror" id="page_title" name="page_title" value="{{ old('page_title') }}" required>
                                @error('page_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Page Slug (readonly) -->
                            <div class="mb-3">
                                <label for="page_slug" class="form-label">Page Slug</label>
                                <input type="text" class="form-control @error('page_slug') is-invalid @enderror" id="page_slug" name="page_slug" value="{{ old('page_slug') }}" readonly>
                                @error('page_slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Page Details -->
                            <div class="mb-3">
                                <label for="page_details" class="form-label">Page Details</label>
                                <input type="text" class="form-control @error('page_details') is-invalid @enderror" id="page_details" name="page_details" value="{{ old('page_details') }}" required>
                                @error('page_details')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-control @error('status') is-invalid @enderror">
                                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('pages.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->

<!-- TinyMCE Editor -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        tinymce.init({
            selector: '#description',
            plugins: 'lists link image table code',
            toolbar: 'undo redo | formatselect | bold italic | bullist numlist outdent indent | link image',
            height: 300
        });

        // Auto-generate slug from page title
        const titleInput = document.getElementById('page_title');
        const slugInput = document.getElementById('page_slug');

        titleInput.addEventListener('input', function() {
            const slug = titleInput.value
                .toLowerCase()
                .replace(/[^\w\s]/g, '')  // Remove non-word characters
                .replace(/\s+/g, '-')      // Replace spaces with dashes
                .replace(/--+/g, '-');     // Replace multiple dashes with a single one
            slugInput.value = slug;
        });
    });
</script>
@endsection
