@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6 pt-2">
            <h1 class="fw-bold text-dark mb-1" style="font-size: 28px;">Edit Advocate</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">Modify the advocate's profile details below.</p>
        </div>
        <div class="col-md-6 text-md-end pt-2">
            <a href="{{ route('advocates.index') }}" class="btn btn-secondary px-4 py-2" style="border-radius: 8px;">
                <i class="bi bi-arrow-left-square me-2"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Form Section -->
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm" style="border-radius: 10px;">
                    <div class="card-body p-4 pb-0">
                        <form action="{{ route('advocates.update', $advocate->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <h5 class="card-title p-0 mb-3" style="font-size: 18px; font-weight: 700; color: #333;">Personal & Professional Details</h5>
                            <hr class="mt-0 mb-4" style="border-color: #dee2e6;">

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="name" class="form-label fw-bold" style="color: #495057;">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $advocate->name) }}" placeholder="Enter advocate's full name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label for="lawyer_type" class="form-label fw-bold" style="color: #495057;">Lawyer Type <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('lawyer_type') is-invalid @enderror" id="lawyer_type" name="lawyer_type" value="{{ old('lawyer_type', $advocate->lawyer_type) }}" required>
                                    @error('lawyer_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <label for="price" class="form-label fw-bold" style="color: #495057;">Consultation Price (₹) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $advocate->price) }}" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-4">
                                    <label for="consultation_time" class="form-label fw-bold" style="color: #495057;">Consultation Time <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('consultation_time') is-invalid @enderror" id="consultation_time" name="consultation_time" value="{{ old('consultation_time', $advocate->consultation_time) }}" required>
                                    @error('consultation_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-4">
                                    <label for="experience" class="form-label fw-bold" style="color: #495057;">Experience <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('experience') is-invalid @enderror" id="experience" name="experience" value="{{ old('experience', $advocate->experience) }}" required>
                                    @error('experience')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="mobile_number" class="form-label fw-bold" style="color: #495057;">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('mobile_number') is-invalid @enderror" id="mobile_number" name="mobile_number" value="{{ old('mobile_number', $advocate->mobile_number) }}" required>
                                    @error('mobile_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-4 d-flex align-items-center mt-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_verified" name="is_verified" value="1" {{ old('is_verified', $advocate->is_verified) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark" for="is_verified" style="margin-left: 10px;">Verified Advocate</label>
                                    </div>
                                </div>

                                <div class="col-md-3 mb-4 d-flex align-items-center mt-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ old('status', $advocate->status) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark" for="status" style="margin-left: 10px;">Active Status</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="about" class="form-label fw-bold" style="color: #495057;">About <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('about') is-invalid @enderror" id="about" name="about" required>{{ old('about', $advocate->about) }}</textarea>
                                    @error('about')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label for="address" class="form-label fw-bold" style="color: #495057;">Office Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" required>{{ old('address', $advocate->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <h5 class="card-title p-0 mt-4 mb-3" style="font-size: 18px; font-weight: 700; color: #333;">Selection Fields</h5>
                            <hr class="mt-0 mb-4" style="border-color: #dee2e6;">

                            <!-- Languages Known -->
                            <div class="mb-4">
                                <label class="form-label fw-bold d-block mb-2 text-dark">Languages Known</label>
                                <div class="checkbox-box-container p-3 border rounded bg-light">
                                    <div class="row row-cols-2 row-cols-md-4 g-2">
                                        @php
                                            $currentLangs = is_array($advocate->languages_known) ? $advocate->languages_known : [];
                                        @endphp
                                        @foreach(['English', 'Hindi', 'Gujarati', 'Marathi', 'Tamil', 'Telugu'] as $lang)
                                            <div class="col">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="languages_known[]" id="lang_{{ $lang }}" value="{{ $lang }}" {{ in_array($lang, old('languages_known', $currentLangs)) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-dark fw-medium small" for="lang_{{ $lang }}">{{ $lang }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('languages_known')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Expertise -->
                            <div class="mb-4">
                                <label class="form-label fw-bold d-block mb-2 text-dark">Areas of Expertise</label>
                                <div class="checkbox-box-container p-3 border rounded bg-light">
                                    <div class="row row-cols-2 row-cols-md-4 g-2">
                                        @php
                                            $currentExpertise = is_array($advocate->expertise) ? $advocate->expertise : [];
                                        @endphp
                                        @foreach(['Criminal Case', 'Family Case', 'Civil Case', 'Property Case', 'Divorce', 'Labour Law', 'Consumer Court', 'GST', 'Company Law', 'Banking', 'Insurance'] as $exp)
                                            <div class="col">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="expertise[]" id="exp_{{ Str::slug($exp) }}" value="{{ $exp }}" {{ in_array($exp, old('expertise', $currentExpertise)) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-dark fw-medium small" for="exp_{{ Str::slug($exp) }}">{{ $exp }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('expertise')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Degree -->
                            <div class="mb-4">
                                <label class="form-label fw-bold d-block mb-2 text-dark">Degrees / Qualifications</label>
                                <div class="checkbox-box-container p-3 border rounded bg-light">
                                    <div class="row row-cols-2 row-cols-md-4 g-2">
                                        @php
                                            $currentDegrees = is_array($advocate->degree) ? $advocate->degree : [];
                                        @endphp
                                        @foreach(['B.Com', 'LL.B', 'LL.M', 'B.A. LL.B', 'BBA LL.B', 'M.Com', 'PhD Law'] as $deg)
                                            <div class="col">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="degree[]" id="deg_{{ Str::slug($deg) }}" value="{{ $deg }}" {{ in_array($deg, old('degree', $currentDegrees)) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-dark fw-medium small" for="deg_{{ Str::slug($deg) }}">{{ $deg }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('degree')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <h5 class="card-title p-0 mt-4 mb-3" style="font-size: 18px; font-weight: 700; color: #333;">Media Uploads</h5>
                            <hr class="mt-0 mb-4" style="border-color: #dee2e6;">

                            <div class="row">
                                <!-- Image Upload -->
                                <div class="col-md-4 mb-4">
                                    <label class="form-label fw-bold text-dark">Profile Image</label>
                                    <div class="upload-box text-center p-4 border border-2 rounded position-relative">
                                        <i class="bi bi-image-fill text-primary" style="font-size: 38px; display: block; margin-bottom: 8px;"></i>
                                        <label for="image" class="form-label fw-semibold text-primary cursor-pointer mb-1 d-block">Replace Profile Image</label>
                                        <span class="text-muted small d-block">JPG, JPEG, PNG, WEBP (Max 5MB)</span>
                                        <input type="file" id="image" name="image" accept="image/*" class="d-none" onchange="previewImage(event)">
                                        
                                        <div id="imagePreviewContainer" class="mt-3 {{ ($advocate->image && file_exists(public_path($advocate->image))) ? '' : 'd-none' }}">
                                            <img id="imagePreview" src="{{ ($advocate->image && file_exists(public_path($advocate->image))) ? asset($advocate->image) : '#' }}" class="img-thumbnail rounded shadow-sm" style="max-height: 150px; object-fit: cover;">
                                        </div>
                                    </div>
                                    @error('image')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Video Upload -->
                                <div class="col-md-4 mb-4">
                                    <label class="form-label fw-bold text-dark">Introductory Video</label>
                                    <div class="upload-box text-center p-4 border border-2 rounded position-relative">
                                        <i class="bi bi-play-btn-fill text-danger" style="font-size: 38px; display: block; margin-bottom: 8px;"></i>
                                        <label for="video" class="form-label fw-semibold text-danger cursor-pointer mb-1 d-block">Replace MP4 Video</label>
                                        <span class="text-muted small d-block">MP4 Only (Max 50MB)</span>
                                        <input type="file" id="video" name="video" accept="video/mp4" class="d-none" onchange="previewVideo(event)">
                                        
                                        <div id="videoPreviewContainer" class="mt-3 {{ ($advocate->video && file_exists(public_path($advocate->video))) ? '' : 'd-none' }}">
                                            <video id="videoPreview" width="100%" controls class="rounded border shadow-sm" style="max-height: 150px;">
                                                <source src="{{ ($advocate->video && file_exists(public_path($advocate->video))) ? asset($advocate->video) : '' }}" type="video/mp4">
                                            </video>
                                        </div>
                                    </div>
                                    @error('video')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Document Upload -->
                                <div class="col-md-4 mb-4">
                                    <label class="form-label fw-bold text-dark">Verification Document</label>
                                    <div class="upload-box text-center p-4 border border-2 rounded position-relative">
                                        <i class="bi bi-file-earmark-pdf-fill text-info" style="font-size: 38px; display: block; margin-bottom: 8px;"></i>
                                        <label for="document" class="form-label fw-semibold text-info cursor-pointer mb-1 d-block">Replace Certificate</label>
                                        <span class="text-muted small d-block">PDF, DOC, DOCX (Max 10MB)</span>
                                        <input type="file" id="document" name="document" accept=".pdf,.doc,.docx" class="d-none" onchange="previewDocument(event)">
                                        
                                        <div id="documentNameContainer" class="mt-3 {{ ($advocate->document && file_exists(public_path($advocate->document))) ? '' : 'd-none' }}">
                                            @if($advocate->document && file_exists(public_path($advocate->document)))
                                                <div class="mb-2">
                                                    <a href="{{ asset($advocate->document) }}" download class="btn btn-outline-dark btn-sm rounded px-3">
                                                        <i class="bi bi-cloud-arrow-down-fill me-1"></i>Download Existing
                                                    </a>
                                                </div>
                                            @endif
                                            <span class="badge bg-secondary p-2 rounded"><i class="bi bi-file-earmark-check-fill me-1"></i><span id="documentName">{{ ($advocate->document) ? basename($advocate->document) : 'filename.pdf' }}</span></span>
                                        </div>
                                    </div>
                                    @error('document')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <label for="total_reviews" class="form-label fw-bold" style="color: #495057;">Total Reviews</label>
                                    <input type="number" class="form-control @error('total_reviews') is-invalid @enderror" id="total_reviews" name="total_reviews" value="{{ old('total_reviews', $advocate->total_reviews) }}">
                                    @error('total_reviews')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Sticky Footer Form Actions -->
                            <div class="form-actions-footer text-end">
                                <button type="submit" class="btn btn-success px-5 py-2.5 me-2 shadow-sm" style="background-color: #198754; border-color: #198754; border-radius: 8px; font-weight: 600;">
                                    <i class="bi bi-save-fill me-2"></i>Save Changes
                                </button>
                                <a href="{{ route('advocates.index') }}" class="btn btn-secondary px-5 py-2.5" style="border-radius: 8px; font-weight: 600;">
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
.checkbox-box-container {
    border-color: #e9ecef !important;
}
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
function previewImage(event) {
    const fileInput = event.target;
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImage = document.getElementById('imagePreview');
    
    if (fileInput.files && fileInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            previewImage.src = e.target.result;
            previewContainer.classList.remove('d-none');
        };
        reader.readAsDataURL(fileInput.files[0]);
    }
}

function previewVideo(event) {
    const fileInput = event.target;
    const previewContainer = document.getElementById('videoPreviewContainer');
    const previewVideo = document.getElementById('videoPreview');
    
    if (fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        const blobURL = URL.createObjectURL(file);
        previewVideo.src = blobURL;
        previewContainer.classList.remove('d-none');
    }
}

function previewDocument(event) {
    const fileInput = event.target;
    const nameContainer = document.getElementById('documentNameContainer');
    const nameSpan = document.getElementById('documentName');
    
    if (fileInput.files && fileInput.files[0]) {
        nameSpan.textContent = fileInput.files[0].name;
        nameContainer.classList.remove('d-none');
    }
}
</script>
@endsection
