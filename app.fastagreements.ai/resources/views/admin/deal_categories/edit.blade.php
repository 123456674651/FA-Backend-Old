@extends('admin.layout.admin')

@section('content')
    <main id="main" class="main">

        <div class="row">
            <div class="pagetitle col-lg-6 pt-2">
                <h1>Edit Agreement Category</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('') }}">Home</a></li>
                        <li class="breadcrumb-item">Agreement Category</li>
                        <li class="breadcrumb-item active">Edit Agreement Category</li>
                    </ol>
                </nav>
            </div>

            <div class="pagetitle col-lg-6 text-end pt-2 justify-content-center">
                <a href="{{ route('deal_categories.index') }}">
                    <button type="button" class="btn btn-secondary text-white me-2">
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
                            <h5 class="card-title">Edit Deal Category</h5>

                            <!-- General Form Elements -->
                            <form action="{{ route('deal_categories.update', $dealCategory->id) }}" id="contactForm"
                                method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row mb-3">
                                    <label for="customFile1" class="col-sm-2 col-form-label">Category Image</label>
                                    <div class="col-sm-10">
                                        <div class="mb-4 d-flex justify-content-centerx">
                                            <img id="selectedImage"
                                                src="{{ asset('admin/images/category_image_thumb/' . $dealCategory->category_image) }}"
                                                alt="category image" style="width: 100px;" />
                                        </div>
                                        <div class="d-flex justify-content-centerx">
                                            <div class="btn button-color btn-rounded">
                                                <label class="btn btn-dark text-white me-2" for="customFile1">Choose
                                                    file</label>
                                                <input type="file" class="form-control d-none" id="customFile1"
                                                    name="category_image"
                                                    onchange="displaySelectedImage(event, 'selectedImage')" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="category_name" class="col-sm-2 col-form-label">Category Name</label>
                                    <div class="col-sm-10">
                                        <input class="form-control" id="category_name" type="text" name="category_name"
                                            value="{{ $dealCategory->category_name }}" placeholder="Enter Category Name">
                                    </div>
                                </div>
                                
                                   <div class="row mb-3">
                                    <label for="is_on_interest" class="col-sm-2 col-form-label">Parent Category</label>
                                    <div class="col-sm-3">
                                        <select class="form-select" aria-label="Default select example"
                                            name="parent_id">
                                            <option value="0">Select Parent Category</option>
                                            @foreach($dealCategories as $id => $name)
                                            <option value="{{$id}}" {{ $dealCategory->parent_id == $id ? 'selected' : ''  }}>{{$name}}</option>}}>{{$name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                
                                

                                <div class="row mb-3">
                                    <label for="is_active" class="col-sm-2 col-form-label">Is Active</label>
                                    <div class="col-sm-3">
                                        <select class="form-select" aria-label="Default select example" name="is_active">
                                            <option value="1" {{ $dealCategory->is_active ? 'selected' : '' }}>Active
                                            </option>
                                            <option value="0" {{ !$dealCategory->is_active ? 'selected' : '' }}>
                                                Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="deal_price" class="col-sm-2 col-form-label">Deal Price</label>
                                    <div class="col-sm-10">
                                        <input class="form-control" id="deal_price" type="number" step="0.01"
                                            name="deal_price" value="{{ $dealCategory->deal_price }}"
                                            placeholder="Enter Deal Price">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="is_on_interest" class="col-sm-2 col-form-label">Is On Interest</label>
                                    <div class="col-sm-3">
                                        <select class="form-select" aria-label="Default select example"
                                            name="is_on_interest">
                                            <option value="1" {{ $dealCategory->is_on_interest ? 'selected' : '' }}>Yes
                                            </option>
                                            <option value="0" {{ !$dealCategory->is_on_interest ? 'selected' : '' }}>No
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="description" class="col-sm-2 col-form-label">Description</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" id="description" name="description" placeholder="Enter Description"
                                            style="height: 100px">{{ $dealCategory->description }}</textarea>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="labels" class="col-sm-2 col-form-label">Labels</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" id="lable" name="lable" placeholder="Enter lables"
                                            style="height: 100px">{{ $dealCategory->lable }}</textarea>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="Icoe Text" class="col-sm-2 col-form-label">Icon Text</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" id="icon_text" name="icon_text" placeholder="Enter lables"
                                            style="height: 100px">{{ $dealCategory->icon_text }}</textarea>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-10 offset-sm-2">
                                        <button type="submit" class="btn btn-dark text-white me-2">Update</button>
                                    </div>
                                </div>
                            </form>
                            <!-- End General Form Elements -->

                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main>
@endsection
