@extends('admin.layout.admin')
<!-- Database CSS link ( includes Bootstrap 5 )  -->
<link href="https://cdn.datatables.net/1.13.2/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
@section('content')

<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>{{ $category->category_name ?? 'Agreement' }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('deal_categories.index') }}">Deal Categories</a></li>
                    <li class="breadcrumb-item active">Attribute</li>
                </ol>
            </nav>
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('deal_categories.index') }}" class="btn btn-secondary text-white me-2">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <a href="{{ route('attribute.create') }}">
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
                                    <th class="text-center align-middle">Name</th>
                                    <th class="text-center align-middle">Input Type</th>
                                    <th class="text-center align-middle">Required</th>
                                    <th class="text-center align-middle">Actions</th>
                                </tr>
                            </thead>

                            <?php
$i = 1;
                            ?>
                            <tbody id="tableBodyContents">
                                @foreach($data as $dat)
                                    <tr class="tableRow" data-id="{{ $dat->id }}">
                                        <td class="pl-3">
                                            {{$i++}}
                                        </td>
                                        <td>{{ $dat->attribute_name }}</td>
                                        <td>{{ $dat->input_type_name }}</td>
                                        <td>{{ $dat->is_required_name }}</td>

                                        <td>
                                            <div class="text-center">
                                                <a href="{{ route('attribute.edit', $dat->id)}}"
                                                    class="edit btn btn-primary btn-sm"><i
                                                        class="bi bi-pencil-square"></i></a>
                                                <a data-bs-toggle="modal" href="#delete_modal_{{$dat->id }}"
                                                    class="btn btn-danger btn-sm" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                                <div id="delete_modal_{{$dat->id }}" class="modal fade" tabindex="-1"
                                                    role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title">Confirmation</h4>
                                                                <button type="button" class="close" data-bs-dismiss="modal"
                                                                    aria-hidden="true">×</button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to delete this item? This action
                                                                    cannot be undone and you will be unable to recover any
                                                                    data.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default"
                                                                    data-bs-dismiss="modal">Cancel</button>
                                                                <form action="{{ route('attribute.delete', $dat->id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">Yes, delete
                                                                        it!</button>
                                                                </form>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
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


@section('js')
    <!-- jQuery CDN Link -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>

    <!-- Bootstrap 5 Bundle CDN Link -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery UI CDN Link -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>

    <!-- DataTables JS CDN Link -->
    <script src="https://cdn.datatables.net/1.13.2/js/jquery.dataTables.min.js"></script>

    <!-- DataTables JS ( includes Bootstrap 5 for design [UI] ) CDN Link -->
    <script src="https://cdn.datatables.net/1.13.2/js/dataTables.bootstrap5.min.js"></script>


    <script type="text/javascript">
        $(function () {

            $("#table").DataTable();

            $("#tableBodyContents").sortable({
                items: "tr",
                cursor: 'move',
                opacity: 0.6,
                update: function () {
                    sendOrderToServer();
                }
            });

            function sendOrderToServer() {
                var order = [];
                $("#tableBodyContents tr").each(function (index, element) {
                    order.push({
                        id: $(element).attr("data-id"),
                        sort_order: index + 1
                    });
                });

                $.ajax({
                    type: "POST",
                    url: "{{ route('post-reorder') }}", // Adjust route accordingly
                    data: {
                        sort_order: order,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        console.log("Order updated successfully");
                    },
                    error: function (error) {
                        console.log("Error updating order", error);
                    }
                });
            }

        });
    </script>

@endsection