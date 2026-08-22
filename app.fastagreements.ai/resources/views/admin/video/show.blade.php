@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">

    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>View Video</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('') }}">Home</a></li>
                    <li class="breadcrumb-item">Video Management</li>
                    <li class="breadcrumb-item active">View Video</li>
                </ol>
            </nav>
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2 justify-content-center">
            <a href="{{ route('videos.index') }}">
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
                        <h5 class="card-title">Video Details</h5>

                        <!-- Video Playback -->
                        @if($video->file_name)
                            <div class="mb-3">
                                <video width="640" height="360" controls>
                                    <source src="{{ asset('admin/video/' . $video->file_name) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        @else
                            <p>No video available to play.</p>
                        @endif

                        <!-- Video Details -->
                        <div class="row mb-3">
                            <label for="title" class="col-sm-2 col-form-label">Title</label>
                            <div class="col-sm-10">
                                <p>{{ $video->title }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="description" class="col-sm-2 col-form-label">Description</label>
                            <div class="col-sm-10">
                                <p>{{ $video->description }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tags" class="col-sm-2 col-form-label">Tags</label>
                            <div class="col-sm-10">
                                <p>{{ $video->tags }}</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

</main>
@endsection
