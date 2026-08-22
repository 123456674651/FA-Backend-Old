@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">

    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Edit Slider</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('') }}">Home</a></li>
                    <li class="breadcrumb-item">Slider Management</li>
                    <li class="breadcrumb-item active">Edit Slider</li>
                </ol>
            </nav>
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2 justify-content-center">
            <a href="{{ route('sliders.index') }}">
                <button type="button" class="btn button-color text-white">
                    <i class="bi bi-arrow-left-square text-white"></i> Back
                </button>
            </a>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Edit Slider</h5>

                        <form action="{{ route('sliders.update', $slider->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <label for="title" class="col-sm-2 col-form-label">Title</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $slider->title) }}" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="description" class="col-sm-2 col-form-label">Description</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $slider->description) }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="expire_date" class="col-sm-2 col-form-label">Expire Date</label>
                                <div class="col-sm-10">
                                    <input type="date" class="form-control" id="expire_date" name="expire_date" value="{{ old('expire_date', $slider->expire_date) }}" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="slider_type" class="col-sm-2 col-form-label">Slider Type</label>
                                <div class="col-sm-10">
                                    <select name="slider_type" class="form-control" required>
                                        <option value="home" {{ $slider->slider_type == 'home' ? 'selected' : '' }}>Home</option>
                                        <option value="onboarding" {{ $slider->slider_type == 'onboarding' ? 'selected' : '' }}>Onboarding</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="image" class="col-sm-2 col-form-label">Image</label>
                                <div class="col-sm-10">
                                    <input type="file" class="form-control" id="image" name="image">
                                    <small>Leave blank if you don't want to change the image.</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-10 offset-sm-2">
                                    <button type="submit" class="btn button-color text-white">Update Slider</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </section>

</main>
@endsection
