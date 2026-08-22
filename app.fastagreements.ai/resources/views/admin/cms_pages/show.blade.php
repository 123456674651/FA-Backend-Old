@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>CMS Page Details</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('cms-pages.index') }}">CMS Pages</a></li>
                    <li class="breadcrumb-item active">View CMS Page</li>
                </ol>
            </nav>
        </div>
        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('cms-pages.index') }}" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left-square"></i> Back
            </a>
            <a href="{{ route('cms-pages.edit', $cmsPage->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> Edit
            </a>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h2 class="card-title p-0 m-0">{{ $cmsPage->title }}</h2>
                            <span class="badge {{ $cmsPage->status === 'Active' ? 'bg-success' : 'bg-danger' }} fs-6">
                                {{ $cmsPage->status }}
                            </span>
                        </div>
                        
                        <p class="text-muted mb-4"><strong>Slug:</strong> <code>{{ $cmsPage->slug }}</code></p>

                        @if($cmsPage->featured_image)
                            <div class="mb-4">
                                <img src="{{ asset('storage/cms/' . $cmsPage->featured_image) }}" alt="Featured Image" class="img-fluid rounded shadow-sm" style="max-height: 350px; width: auto; object-fit: contain;">
                            </div>
                        @endif

                        @if($cmsPage->short_description)
                            <div class="mb-4">
                                <h5>Short Description</h5>
                                <div class="p-3 bg-light rounded text-secondary">{{ $cmsPage->short_description }}</div>
                            </div>
                        @endif

                        <div class="mb-4">
                            <h5>Description</h5>
                            <div class="p-3 border rounded bg-white">
                                {!! $cmsPage->description !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- SEO Metadata Card -->
                <div class="card">
                    <div class="card-body p-4">
                        <h4 class="card-title p-0 mb-3">SEO Metadata</h4>
                        <hr class="mt-0">

                        <div class="mb-3">
                            <strong>Meta Title:</strong>
                            <p class="text-secondary">{{ $cmsPage->meta_title ?: '-' }}</p>
                        </div>

                        <div class="mb-3">
                            <strong>Meta Keywords:</strong>
                            <p class="text-secondary">{{ $cmsPage->meta_keywords ?: '-' }}</p>
                        </div>

                        <div class="mb-3">
                            <strong>Meta Description:</strong>
                            <p class="text-secondary">{{ $cmsPage->meta_description ?: '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Page Information Card -->
                <div class="card">
                    <div class="card-body p-4">
                        <h4 class="card-title p-0 mb-3">Information</h4>
                        <hr class="mt-0">

                        <div class="mb-2">
                            <strong>Created By:</strong>
                            <p class="text-secondary">{{ $cmsPage->creator ? $cmsPage->creator->name : 'System' }}</p>
                        </div>

                        <div class="mb-2">
                            <strong>Created At:</strong>
                            <p class="text-secondary">{{ $cmsPage->created_at ? $cmsPage->created_at->format('Y-m-d H:i:s') : '-' }}</p>
                        </div>

                        <div class="mb-2">
                            <strong>Updated By:</strong>
                            <p class="text-secondary">{{ $cmsPage->updater ? $cmsPage->updater->name : '-' }}</p>
                        </div>

                        <div class="mb-2">
                            <strong>Updated At:</strong>
                            <p class="text-secondary">{{ $cmsPage->updated_at ? $cmsPage->updated_at->format('Y-m-d H:i:s') : '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
