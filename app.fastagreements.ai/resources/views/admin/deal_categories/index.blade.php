@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            @if(isset($parentCategory))
                <h1>Sub Agreements</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('deal_categories.index') }}">Agreement Warnings</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('deal_categories.index') }}">{{ $parentCategory->category_name }}</a></li>
                        <li class="breadcrumb-item active">Sub Agreements</li>
                    </ol>
                </nav>
            @else
                <h1>Agreement Category List</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item">Agreement Category List</li>
                    </ol>
                </nav>
            @endif
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2">
            @if(isset($parentCategory))
                <a href="{{ route('deal_categories.index') }}" class="btn btn-secondary text-white me-2">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            @endif
            <a href="{{ route('deal_categories.create') }}">
                <button type="button" class="btn btn-dark text-white me-2">
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
                                    <th class="text-start align-middle">Name</th>
                                    <th class="text-center align-middle" style="width: 15%;">Sub Category</th>
                                    <th class="text-center align-middle">Status</th>
                                    <th class="text-center align-middle" style="width: 25%;">Action</th>
                                    <!-- <th class="text-center align-middle">Template</th> -->

                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will populate this -->
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


@section('js')
<script>
    $(document).ready(function () {
        $('.data-table').DataTable({
            processing: true,
            columnDefs: [{
                "defaultContent": "-",
                "targets": "_all",
                "className": "text-center align-middle"
            }],
            serverSide: true,
          pageLength: 50,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            ajax: {
                url: "{{ route('deal_categories.index') }}",
                data: function (d) {
                    d.parent_id = "{{ $parentId ?? '' }}";
                }
            },
            columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {
                data: 'category_name',
                className: 'text-start'
            },
            {
                data: 'sub_category',
                name: 'sub_category',
                orderable: false,
                searchable: false
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'action',
                orderable: false,
                searchable: false
            },
                // {
                //   data: 'template',
                //      orderable: false,
                //    searchable: false
                //}
            ],
        });
    });
</script>
@stop