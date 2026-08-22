@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Attribute List</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active">Attribute List</li>
                </ol>
            </nav>
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('attribute.create') }}">
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
                                    <th class="text-center align-middle">Aggrimnent</th>
                                    <th class="text-center align-middle">Name</th>
                                    <th class="text-center align-middle">Input Type</th>
                                    <th class="text-center align-middle">Required</th>
                                    <th class="text-center align-middle" style="width: 15%;">Actions</th>
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

<!-- DataTables CSS and JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('category_attribute', ['id' => $id]) }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'category_name', name: 'category_name' }, 
            { data: 'attribute_name', name: 'attribute_name' },
            { data: 'input_type_name', name: 'input_type_name' },
            { data: 'is_required_name', name: 'is_required_name'},
            { data: 'action', name: 'action', orderable: false, searchable: false },

        ]
    });
});


// $(document).on('click', '.duplicate-btn', function () {
//     const entryId = $(this).data('id');
//     $.ajax({
//         url: `/duplicate-entry/${entryId}`,
//         type: 'GET',
//         data: {
//             _token: '{{ csrf_token() }}'
//         },
//         success: function (response) {
//             alert(response.message);
//             // Optional: Refresh the page or update the UI
//         },
//         error: function (xhr) {
//             alert('Error: ' + xhr.responseJSON.message);
//         }
//     });
// });

</script>
@endsection