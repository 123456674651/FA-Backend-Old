@extends('admin.layout.admin')
@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Deal Category List</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item">Deal Category List</li>
                </ol>
            </nav>
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('deal_categories.create') }}">
                <button type="button" class="btn button-color text-white">
                    <i class="bi bi-plus-square"></i> Add
                </button>
            </a>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card p-2 pt-4">
                    <div class="card-body">
                        <table class="table data-table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle" style="width: 5%;">Sr#</th>
                                    <th class="text-center align-middle">Logo</th>
                                    <th class="text-center align-middle">Name</th>
                                    <th class="text-center align-middle">Deal Price</th>
                                    <th class="text-center align-middle">Interest</th>
                                    <th class="text-center align-middle">Description</th>
                                    <th class="text-center align-middle">Status</th>
                                    <th class="text-center align-middle" colspan="3" style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dealCategories as $dealCategory)
                                    <tr>
                                        <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                        <td class="text-center align-middle">
                                        <img src="{{ asset('admin/images/category_image_thumb/'.$dealCategory->category_image) }}" alt="{{ $dealCategory->category_name }} Logo" style="width: 50px; height: auto;">
                                        </td>                                        
                                        <td class="text-center align-middle">{{ $dealCategory->category_name }}</td>
                                        <td class="text-center align-middle">{{ $dealCategory->deal_price }}</td>
                                        <td class="text-center align-middle">{{ $dealCategory->is_on_interest }}</td>
                                        <td class="text-center align-middle">{{ $dealCategory->description }}</td>
                                        <td class="text-center align-middle">
                                            <form action="{{ route('status_changes', $dealCategory->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-{{ $dealCategory->is_active ? 'outline-success' : 'outline-danger' }} btn-sm">
                                                    {{ $dealCategory->is_active ? 'Active' : 'Inactive' }}
                                                </button>
                                                <input type="hidden" name="status" value="{{ $dealCategory->is_active ? 0 : 1 }}">
                                            </form>
                                        </td>
                                        <td class="text-center align-middle">
                                            <a href="{{ route('deal_categories.edit', $dealCategory->id) }}" class="edit btn btn-primary btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        </td>
                                        <td class="text-center align-middle">
    <a data-bs-toggle="modal" href="#delete_modal_{{ $dealCategory->id }}" class="btn btn-danger btn-sm" title="Delete">
        <i class="bi bi-trash"></i>
    </a>
    <div id="delete_modal_{{ $dealCategory->id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Confirmation</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this item? This action cannot be undone and you will be unable to recover any data.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('deal_categories.destroy', $dealCategory->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Yes, delete it!</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</td>

                                        <!-- <td class="text-center align-middle">
                                            <button data-toggle="tooltip" data-placement="left" title="View Details" type="button" class="show_details btn btn-success btn-sm" data-product-id="{{ $dealCategory->id }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td> -->
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <!-- End Table with striped rows -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->
@stop
