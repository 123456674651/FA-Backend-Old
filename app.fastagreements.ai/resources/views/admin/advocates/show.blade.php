@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6 pt-2">
            <h1 class="fw-bold text-dark mb-1" style="font-size: 28px;">Advocate Details</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">Detailed profile view of the advocate.</p>
        </div>
        <div class="col-md-6 text-md-end pt-2">
            <a href="{{ route('advocates.edit', $advocate->id) }}" class="btn btn-primary px-4 py-2 me-2" style="background-color: #0d6efd; border-color: #0d6efd; border-radius: 8px;">
                <i class="bi bi-pencil-square me-2"></i>Edit Profile
            </a>
            <a href="{{ route('advocates.index') }}" class="btn btn-secondary px-4 py-2" style="border-radius: 8px;">
                <i class="bi bi-arrow-left-square me-2"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Profile Detail Panel -->
    <section class="section profile">
        <div class="row">
            <!-- Top Header Card -->
            <div class="col-lg-12 mb-4">
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 10px;">
                    <!-- Banner background -->
                    <div class="profile-banner-bg" style="height: 120px; background: linear-gradient(135deg, #0d6efd 0%, #4f46e5 100%);"></div>
                    <div class="card-body p-4 position-relative">
                        <!-- Profile Image and Info Header -->
                        <div class="d-flex flex-column flex-md-row align-items-center align-items-md-end text-center text-md-start" style="margin-top: -65px;">
                            <div class="mb-3 mb-md-0 me-md-4 position-relative">
                                @if($advocate->image && file_exists(public_path($advocate->image)))
                                    <img src="{{ asset($advocate->image) }}" alt="Profile" class="rounded-circle border border-4 border-white shadow" style="width: 130px; height: 130px; object-fit: cover; background-color: #fff;">
                                @else
                                    <img src="{{ asset('assets/img/profile-img.jpg') }}" alt="Profile" class="rounded-circle border border-4 border-white shadow" style="width: 130px; height: 130px; object-fit: cover; background-color: #fff;">
                                @endif
                            </div>
                            <div class="flex-grow-1 mb-2">
                                <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2">
                                    <h2 class="fw-bold text-dark mb-0 fs-3">{{ $advocate->name }}</h2>
                                    @if($advocate->is_verified)
                                        <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-1.5 fw-semibold" style="font-size: 13px;"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
                                    @else
                                        <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary px-3 py-1.5 fw-semibold" style="font-size: 13px;">Unverified</span>
                                    @endif
                                </div>
                                <p class="text-secondary mb-0 fw-medium mt-1" style="font-size: 16px;">{{ $advocate->lawyer_type }}</p>
                            </div>
                        </div>

                        <!-- Quick metrics row -->
                        <div class="row g-3 mt-4 pt-3 border-top text-center text-md-start">
                            <div class="col-md-3 col-6">
                                <div class="p-3 border rounded bg-light bg-opacity-50">
                                    <div class="text-muted small mb-1"><i class="bi bi-wallet2 me-1.5 text-success"></i>Consultation Price</div>
                                    <h4 class="fw-bold text-success mb-0" style="font-size: 20px;">₹{{ number_format($advocate->price, 0) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 border rounded bg-light bg-opacity-50">
                                    <div class="text-muted small mb-1"><i class="bi bi-briefcase-fill me-1.5 text-primary"></i>Experience</div>
                                    <h4 class="fw-bold text-primary mb-0" style="font-size: 20px;">{{ $advocate->experience }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 border rounded bg-light bg-opacity-50">
                                    <div class="text-muted small mb-1"><i class="bi bi-clock-fill me-1.5 text-info"></i>Consultation Time</div>
                                    <h4 class="fw-bold text-info mb-0" style="font-size: 20px;">{{ $advocate->consultation_time }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 border rounded bg-light bg-opacity-50">
                                    <div class="text-muted small mb-1"><i class="bi bi-star-fill me-1.5 text-warning"></i>Total Reviews</div>
                                    <h4 class="fw-bold text-dark mb-0" style="font-size: 20px;">{{ $advocate->total_reviews }} Reviews</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-Column Information -->
            <div class="col-xl-6">
                <!-- About -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold text-dark mb-3" style="font-size: 18px;"><i class="bi bi-person-lines-fill me-2 text-primary"></i>About</h5>
                        <p class="text-secondary mb-0 leading-relaxed" style="font-size: 15px; line-height: 1.6;">{{ $advocate->about }}</p>
                    </div>
                </div>

                <!-- Office Address & Mobile -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold text-dark mb-3" style="font-size: 18px;"><i class="bi bi-geo-alt-fill me-2 text-primary"></i>Office & Contact Info</h5>
                        <div class="mb-4">
                            <div class="text-muted small mb-1">Office Address</div>
                            <p class="text-dark fw-medium mb-0" style="font-size: 15px; line-height: 1.5;">{{ $advocate->address }}</p>
                        </div>
                        <div>
                            <div class="text-muted small mb-1">Mobile Number</div>
                            <p class="mb-0"><a href="tel:{{ $advocate->mobile_number }}" class="fw-bold text-primary fs-5"><i class="bi bi-telephone-fill me-1.5 text-muted small"></i>{{ $advocate->mobile_number }}</a></p>
                        </div>
                    </div>
                </div>

                <!-- Document Download Box -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold text-dark mb-3" style="font-size: 18px;"><i class="bi bi-file-earmark-check-fill me-2 text-primary"></i>Verification Document</h5>
                        @if($advocate->document && file_exists(public_path($advocate->document)))
                            <div class="p-3 border rounded d-flex align-items-center justify-content-between bg-light">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-2 me-3"></i>
                                    <div class="text-truncate" style="max-width: 250px;">
                                        <div class="fw-semibold text-dark text-truncate">{{ basename($advocate->document) }}</div>
                                        <div class="text-muted small">Verification Certificate</div>
                                    </div>
                                </div>
                                <a href="{{ asset($advocate->document) }}" download class="btn btn-success px-4" style="border-radius: 8px;">
                                    <i class="bi bi-cloud-arrow-down-fill me-1.5"></i>Download
                                </a>
                            </div>
                        @else
                            <div class="alert alert-light border text-center p-3 mb-0" style="border-radius: 8px;">
                                <span class="text-muted small"><i class="bi bi-file-earmark-x me-1.5"></i>No document uploaded.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <!-- Skills / Qualifications -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold text-dark mb-3" style="font-size: 18px;"><i class="bi bi-award-fill me-2 text-primary"></i>Qualifications & Skills</h5>
                        
                        <!-- Degrees -->
                        <div class="mb-4">
                            <div class="text-muted small mb-2 fw-semibold">Degrees & Education</div>
                            @if(is_array($advocate->degree) && count($advocate->degree) > 0)
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($advocate->degree as $deg)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded" style="font-size: 13px; font-weight: 600;">{{ $deg }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted small">No degrees configured.</span>
                            @endif
                        </div>

                        <!-- Expertise -->
                        <div class="mb-4">
                            <div class="text-muted small mb-2 fw-semibold">Areas of Expertise</div>
                            @if(is_array($advocate->expertise) && count($advocate->expertise) > 0)
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($advocate->expertise as $exp)
                                        <span class="badge bg-info bg-opacity-10 text-info-emphasis px-3 py-2 rounded" style="font-size: 13px; font-weight: 600;">{{ $exp }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted small">No expertise configured.</span>
                            @endif
                        </div>

                        <!-- Languages -->
                        <div>
                            <div class="text-muted small mb-2 fw-semibold">Languages Known</div>
                            @if(is_array($advocate->languages_known) && count($advocate->languages_known) > 0)
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($advocate->languages_known as $lang)
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded" style="font-size: 13px; font-weight: 600;">{{ $lang }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted small">No languages configured.</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Video Player Preview -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold text-dark mb-3" style="font-size: 18px;"><i class="bi bi-play-btn-fill me-2 text-primary"></i>Introductory Video</h5>
                        @if($advocate->video && file_exists(public_path($advocate->video)))
                            <div class="ratio ratio-16x9 overflow-hidden rounded shadow-sm border border-2 border-white">
                                <video width="100%" controls class="rounded">
                                    <source src="{{ asset($advocate->video) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        @else
                            <div class="alert alert-light border text-center p-4 mb-0" style="border-radius: 8px;">
                                <i class="bi bi-play-circle text-muted fs-1 d-block mb-2"></i>
                                <span class="text-muted small">No introductory video uploaded.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
