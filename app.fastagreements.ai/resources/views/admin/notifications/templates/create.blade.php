@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Create Notification Template</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('notification-templates.index') }}">Templates</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('notification-templates.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left-square"></i> Back
            </a>
        </div>
    </div>

    <section class="section">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('notification-templates.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <!-- Title -->
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required placeholder="e.g. Festive Offer Campaign">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Type -->
                        <div class="col-md-6 mb-3">
                            <label for="notification_type" class="form-label fw-semibold">Notification Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('notification_type') is-invalid @enderror" id="notification_type" name="notification_type" required>
                                <option value="" disabled selected>Select Type</option>
                                @foreach($types as $key => $val)
                                    <option value="{{ $key }}" {{ old('notification_type') === $key ? 'selected' : '' }}>{{ $val }}</option>
                                @endforeach
                            </select>
                            @error('notification_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Subject -->
                        <div class="col-md-12 mb-3">
                            <label for="subject" class="form-label fw-semibold">Subject <span class="text-muted">(Optional)</span></label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" placeholder="e.g. Special Offer Just For You!">
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Message (TinyMCE) -->
                    <div class="mb-3">
                        <label for="message" class="form-label fw-semibold">Message / Body <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message">{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <!-- Image Upload -->
                        <div class="col-md-6 mb-3">
                            <label for="image_file" class="form-label fw-semibold">Banner Image <span class="text-muted">(Optional, max 2MB)</span></label>
                            <input type="file" class="form-control @error('image_file') is-invalid @enderror" id="image_file" name="image_file" accept="image/*" onchange="previewImage(event);">
                            @error('image_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <img id="img_preview" src="" alt="Selected Image Preview" style="max-height: 150px; display: none;" class="img-thumbnail mt-2">
                        </div>

                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="1" {{ old('status', '1') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-dark fw-semibold px-4 me-2">
                            <i class="bi bi-save me-1"></i> Save Template
                        </button>
                        <a href="{{ route('notification-templates.index') }}" class="btn btn-outline-secondary fw-semibold px-4">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>
@endsection

@section('js')
<!-- TinyMCE Editor -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        tinymce.init({
            selector: '#message',
            plugins: 'lists link image table code',
            toolbar: 'undo redo | formatselect | bold italic | bullist numlist outdent indent | link | code',
            height: 300
        });
    });

    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('img_preview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endsection
