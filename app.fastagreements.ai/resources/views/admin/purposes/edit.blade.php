@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">

    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Edit Purpose</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('') }}">Home</a></li>
                    <li class="breadcrumb-item">Purpose</li>
                    <li class="breadcrumb-item active">Edit Purpose</li>
                </ol>
            </nav>
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2 justify-content-center">
            <a href="{{ route('purposes.index') }}">
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
                        <h5 class="card-title">Edit Purpose</h5>
                        
                        <!-- General Form Elements -->
                        <form action="{{ route('purposes.update', $purpose->id) }}" id="contactForm" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <label for="customFile1" class="col-sm-2 col-form-label">Purpose Image</label>
                                <div class="col-sm-10">
                                    <div class="mb-4 d-flex ">
                                        <img id="selectedImage" src="{{ asset('admin/images/purpose_image_thumb/' . $purpose->purpose_image) }}" alt="purpose image" style="width: 100px;" />
                                    </div>
                                    <div class="d-flex ">
                                        <div class="btn button-color btn-rounded" >
                                            <label class="form-label text-white m-1" for="customFile1">Choose file</label>
                                            <input type="file" class="form-control d-none" id="customFile1" name="purpose_image" onchange="displaySelectedImage(event, 'selectedImage')" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="purpose_name" class="col-sm-2 col-form-label">Purpose Name</label>
                                <div class="col-sm-10">
                                    <input class="form-control" id="purpose_name" type="text" name="purpose_name" value="{{ $purpose->purpose_name }}" placeholder="Enter Purpose Name" >
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="is_active" class="col-sm-2 col-form-label">Is Active</label>
                                <div class="col-sm-3">
                                    <select class="form-select" aria-label="Default select example" name="is_active">
                                        <option value="1" {{ $purpose->is_active == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ $purpose->is_active == 0 ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-10 offset-sm-2">
                                    <button type="submit" class="btn button-color text-white">Update Purpose</button>
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

<script>
function displaySelectedImage(event, id) {
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById(id);
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
