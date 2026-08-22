@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">

    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Add Purpose</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('') }}">Home</a></li>
                    <li class="breadcrumb-item">Purpose</li>
                    <li class="breadcrumb-item active">Add Purpose</li>
                </ol>
            </nav>
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2 justify-content-center">
            <a href="{{ route('purposes.index') }}">
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
                        <h5 class="card-title">Create New Purpose</h5>
                        
                        <!-- General Form Elements -->
                        <form action="{{ route('purposes.store') }}" id="contactForm" method="post" enctype="multipart/form-data">
                            @csrf

                            <div class="row mb-3">
                                <label for="customFile1" class="col-sm-2 col-form-label">Purpose Image</label>
                                <div class="col-sm-10">
                                    <div class="mb-4 d-flex justify-content-centerx">
                                        <img id="selectedImage" src="{{ asset('assets/img/logo/logo.jpeg') }}" alt="purpose image" style="width: 100px;" />
                                    </div>
                                    <div class="d-flex justify-content-centerx">
                                        <div class="btn button-color btn-rounded" >
                                            <label class="btn btn-secondary text-white me-2" for="customFile1">Choose file</label>
                                            <input type="file" class="form-control d-none" id="customFile1" name="purpose_image" onchange="displaySelectedImage(event, 'selectedImage')" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="purpose_name" class="col-sm-2 col-form-label">Purpose Name</label>
                                <div class="col-sm-10">
                                    <input class="form-control" id="purpose_name" type="text" name="purpose_name" placeholder="Enter Purpose Name" >
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="is_active" class="col-sm-2 col-form-label">Is Active</label>
                                <div class="col-sm-3">
                                    <select class="form-select" aria-label="Default select example" name="is_active" >
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-10 offset-sm-2">
                                    <button type="submit" class="btn btn-dark text-white me-2">Submit Form</button>
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
