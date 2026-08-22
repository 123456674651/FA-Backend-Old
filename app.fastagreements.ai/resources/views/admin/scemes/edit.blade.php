{{-- @extends('admin.layout.admin')

@section('content')
<main id="main" class="main">

    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Edit Scheme</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('') }}">Home</a></li>
                    <li class="breadcrumb-item">Scheme</li>
                    <li class="breadcrumb-item active">Edit Scheme</li>
                </ol>
            </nav>
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2 justify-content-center">
            <a href="{{ route('scemes.index') }}">
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
                        <h5 class="card-title">Edit Scheme</h5>
                        
                        <!-- General Form Elements -->
                        <form action="{{ route('scemes.update', $sceme->id) }}" method="post">
                            @csrf
                            @method('PUT')

                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td><label for="emi_pay_method" class="form-label">EMI Pay Method</label></td>
                                        <td><input class="form-control" id="emi_pay_method" type="text" name="emi_pay_method" value="{{ $sceme->emi_pay_method }}" placeholder="Enter EMI Pay Method" required></td>
                                    </tr>

                                    <tr>
                                        <td><label for="is_active" class="form-label">Is Active</label></td>
                                        <td>
                                            <select class="form-select" id="is_active" name="is_active">
                                                <option value="1" {{ $sceme->is_active ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ !$sceme->is_active ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="2" class="text-center">
                                            <button type="submit" class="btn button-color text-white">Submit Form</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>
                        <!-- End General Form Elements -->

                    </div>
                </div>

            </div>
        </div>
    </section>

</main>
@endsection --}}


@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">

    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Edit Scheme</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('') }}">Home</a></li>
                    <li class="breadcrumb-item">Scheme</li>
                    <li class="breadcrumb-item active">Edit Scheme</li>
                </ol>
            </nav>
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2 justify-content-center">
            <a href="{{ route('scemes.index') }}">
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
                        <h5 class="card-title">Edit Scheme</h5>
                        
                        <!-- General Form Elements -->
                        <form action="{{ route('scemes.update', $sceme->id) }}" method="post">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="emi_pay_method" class="form-label">EMI Pay Method</label>
                                <input class="form-control" id="emi_pay_method" type="text" name="emi_pay_method" value="{{ $sceme->emi_pay_method }}" placeholder="Enter EMI Pay Method" required>
                            </div>

                            <div class="mb-3">
                                <label for="is_active" class="form-label">Is Active</label>
                                <select class="form-select" id="is_active" name="is_active">
                                    <option value="1" {{ $sceme->is_active ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !$sceme->is_active ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn button-color text-white">Submit Form</button>
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
