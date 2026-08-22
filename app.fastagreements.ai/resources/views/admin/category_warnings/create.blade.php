@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <!-- Breadcrumb & Header -->
    <div class="pagetitle mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="background: none; padding: 0;">
                <li class="breadcrumb-item"><a href="{{ route('deal_categories.index') }}">Deal Categories</a></li>
                <li class="breadcrumb-item"><a href="{{ route('category-warnings.index', ['category_id' => $category->id]) }}">Warnings</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add Warning</li>
            </ol>
        </nav>
        <div class="row align-items-center">
            <div class="col-md-6 pt-2">
                <h1 class="fw-bold text-dark mb-1" style="font-size: 28px;">Add Category Warning</h1>
                <p class="text-muted mb-0" style="font-size: 14px;">Create a new warning for the <strong>{{ $category->category_name }}</strong> category.</p>
            </div>
            <div class="col-md-6 text-md-end pt-2">
                <a href="{{ route('category-warnings.index', ['category_id' => $category->id]) }}" class="btn btn-secondary px-4 py-2" style="border-radius: 8px;">
                    <i class="bi bi-arrow-left-square me-2"></i>Back to Warnings
                </a>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm" style="border-radius: 10px;">
                    <div class="card-body p-4 pb-0">
                        <form action="{{ route('category-warnings.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Context category ID -->
                            <input type="hidden" name="deal_category_id" value="{{ $category->id }}">

                            <h5 class="card-title p-0 mb-3" style="font-size: 18px; font-weight: 700; color: #333;">Warning Settings</h5>
                            <hr class="mt-0 mb-4" style="border-color: #dee2e6;">

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="language_id" class="form-label fw-bold" style="color: #495057;">Language <span class="text-danger">*</span></label>
                                    <select class="form-select @error('language_id') is-invalid @enderror" id="language_id" name="language_id" required>
                                        <option value="" disabled selected>Select Language</option>
                                        @foreach($languages as $language)
                                            <option value="{{ $language->id }}" {{ old('language_id', $selectedLanguageId) == $language->id ? 'selected' : '' }}>{{ $language->language_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('language_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label for="title" class="form-label fw-bold" style="color: #495057;">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="Enter warning title" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <label for="description" class="form-label fw-bold" style="color: #495057;">Warning <span class="text-danger">*</span></label>
                                    <textarea class="form-control tinymce-editor @error('description') is-invalid @enderror" id="description" name="description" placeholder="Write warning detail...">{!! old('description') !!}</textarea>
                                    @error('description')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row align-items-center">
                                <div class="col-md-6 mb-4">
                                    <label for="display_order" class="form-label fw-bold" style="color: #495057;">Display Order</label>
                                    <input type="number" class="form-control @error('display_order') is-invalid @enderror" id="display_order" name="display_order" value="{{ old('display_order', 0) }}" required>
                                    @error('display_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-4 d-flex align-items-center mt-3 justify-content-md-start">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ old('status', 1) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark" for="status" style="margin-left: 10px;">Status</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Sticky Footer Form Actions -->
                            <div class="form-actions-footer text-end">
                                <button type="submit" class="btn btn-primary px-5 py-2.5 me-2 shadow-sm" style="background-color: #0d6efd; border-color: #0d6efd; border-radius: 8px; font-weight: 600;">
                                    <i class="bi bi-save-fill me-2"></i>Save Warning
                                </button>
                                <a href="{{ route('category-warnings.index', ['category_id' => $category->id]) }}" class="btn btn-secondary px-5 py-2.5" style="border-radius: 8px; font-weight: 600;">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
/* Input Field Formatting */
.form-control, .form-select {
    height: 45px;
    border-radius: 8px;
    border: 1px solid #ced4da;
    transition: all 0.2s ease-in-out;
}
.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    background-color: #fff;
}
textarea.form-control {
    height: auto !important;
    min-height: 120px !important;
}

/* Upload Container Styling */
.upload-box {
    border-style: dashed !important;
    border-color: #dee2e6 !important;
    background-color: #fafbfc;
    transition: all 0.2s ease-in-out;
    border-radius: 8px;
}
.upload-box:hover {
    border-color: #0d6efd !important;
    background-color: rgba(13, 110, 253, 0.02);
}
.cursor-pointer {
    cursor: pointer;
}

/* Checkbox boxes */
.form-check-input {
    cursor: pointer;
}
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

/* Sticky Action Footer */
.form-actions-footer {
    position: sticky;
    bottom: 0;
    background-color: #fff;
    border-top: 1px solid #dee2e6;
    padding: 20px 24px;
    margin-left: -1.5rem;
    margin-right: -1.5rem;
    margin-bottom: 0;
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
    z-index: 99;
    box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: '.tinymce-editor',
        height: 320,
        menubar: true,
        plugins: ['advlist','autolink','link','image','lists','charmap','preview','anchor','searchreplace','visualblocks','code','fullscreen','insertdatetime','table','wordcount'],
        toolbar: 'undo redo | bold italic underline | fontfamily fontsize | forecolor backcolor | heading paragraph | bullist numlist | table | link image | alignleft aligncenter alignright | fullscreen code removeformat',
        toolbar_mode: 'wrap',
        content_style: 'body { font-family:Arial, sans-serif; font-size:14px; }',
        setup: function(editor) {
            editor.on('change', function() {
                editor.save();
            });
        }
    });
});
</script>
@endsection
