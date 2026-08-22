@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">

    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Edit Video</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('') }}">Home</a></li>
                    <li class="breadcrumb-item">Video Management</li>
                    <li class="breadcrumb-item active">Edit Video</li>
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
                        <h5 class="card-title">Edit Video</h5>
                        
                        <!-- General Form Elements -->
                        <form action="{{ route('videos.update', $video->id) }}" id="videoForm" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            {{-- <div class="row mb-3">
                                <label for="videoFile" class="col-sm-2 col-form-label">Video File</label>
                                <div class="col-sm-10">
                                    <input type="file" class="form-control" id="videoFile" name="video_file" accept="video/*" />
                                    @if($video->file_name)
                                        <p>Current video: <a href="{{ asset('admin/video/' . $video->file_name) }}" target="_blank">Watch Video</a></p>
                                    @endif
                                </div>
                            </div> --}}

                            <div class="row mb-3">
                                <label for="videoFile" class="col-sm-2 col-form-label">Video File</label>
                                <div class="col-sm-10">
                                    <input type="file" class="form-control" id="videoFile" name="video_file" accept="video/*" />
                                    
                                    @if($video->file_name)
                                        <!-- Existing video link -->
                                        <p>Current video: <a href="{{ asset('admin/video/' . $video->file_name) }}" target="_blank">Watch Video</a></p>
                                    @endif
                                    
                                    <!-- Video player for selected video -->
                                    <video id="videoPreview" width="320" height="240" controls style="display:none;">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <label for="title" class="col-sm-2 col-form-label">Title</label>
                                <div class="col-sm-10">
                                    <input class="form-control" id="title" type="text" name="title" value="{{ old('title', $video->title) }}" placeholder="Enter Video Title">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="description" class="col-sm-2 col-form-label">Description</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" id="description" name="description" placeholder="Enter Description" style="height: 100px">{{ old('description', $video->description) }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="tags" class="col-sm-2 col-form-label">Tags</label>
                                <div class="col-sm-10">
                                    <input class="form-control" id="tags" type="text" name="tags" value="{{ old('tags', $video->tags) }}" placeholder="Enter Tags (comma separated)">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-10 offset-sm-2">
                                    <button type="submit" class="btn button-color text-white">Update Video</button>
                                </div>
                            </div>
                        </form>
                        <!-- End General Form Elements -->

                    </div>
                </div>

            </div>
        </div>
    </section>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const videoForm = document.getElementById('videoForm');

            document.getElementById('videoFile').addEventListener('change', function(event) {
                var file = event.target.files[0];
                var videoPreview = document.getElementById('videoPreview');
                
                if (file) {
                    var url = URL.createObjectURL(file);
                    videoPreview.src = url;
                    videoPreview.style.display = 'block'; // Show video player
                } else {
                    videoPreview.style.display = 'none'; // Hide video player if no file
                }
            });
            
            videoForm.addEventListener('submit', function (event) {
                // Get the form fields
                const videoFile = document.getElementById('videoFile').files[0];
                const title = document.getElementById('title').value.trim();
                const description = document.getElementById('description').value.trim();
                const tags = document.getElementById('tags').value.trim();
        
                // Clear previous error messages
                clearErrors();
        
                // Validate video file
                if (videoFile) {
                    if (!['video/mp4', 'video/avi', 'video/mkv'].includes(videoFile.type)) {
                        showError('videoFile', 'Invalid video format. Only MP4, AVI, and MKV are allowed.');
                        event.preventDefault();
                    } else if (videoFile.size > 20971520) { // 20MB in bytes
                        showError('videoFile', 'Video file size should not exceed 20MB.');
                        event.preventDefault();
                    }
                }
        
                // Validate title
                if (!title) {
                    showError('title', 'Title is required.');
                    event.preventDefault();
                }
        
                // Validate tags (optional)
                if (tags && !/^[\w\s,]+$/.test(tags)) {
                    showError('tags', 'Tags can only contain letters, numbers, spaces, and commas.');
                    event.preventDefault();
                }
        
                function showError(fieldId, message) {
                    const field = document.getElementById(fieldId);
                    const error = document.createElement('div');
                    error.className = 'text-danger';
                    error.textContent = message;
                    field.parentElement.appendChild(error);
                }
        
                function clearErrors() {
                    document.querySelectorAll('.text-danger').forEach(el => el.remove());
                }
            });
        });
    </script>

</main>
@endsection
