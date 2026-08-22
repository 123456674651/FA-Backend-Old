@extends('admin.layout.admin')

@section('content')
    <main id="main" class="main">
        <div class="row">
            <div class="pagetitle col-lg-6 pt-2">
                <h1>Language List</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active">Language List</li>
                    </ol>
                </nav>
            </div>

            <div class="pagetitle col-lg-6 text-end pt-2">
                <!-- Add button hidden -->
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
                                        <th class="text-center align-middle">Language Name</th>
                                        <th class="text-center align-middle">Language Code</th>
                                        <th class="text-center align-middle">Language in GUJ</th> <!-- Add this line -->
                                        <th class="text-center align-middle">Active</th>
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
        $(document).ready(function () {
            $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('languages.index') }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'language_name', name: 'language_name' },
                    { data: 'language_code', name: 'language_code' },
                    { data: 'language_in_guj', name: 'language_in_guj' }, // Add this line
                    { data: 'is_active', name: 'is_active', orderable: false, searchable: false }
                ]
            });
        });
    </script>

    <script>
        // Delegate event since DataTables re-renders rows
        $(document).on('click', '.language-status-btn', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var url = $btn.data('url');

            $.ajax({
                url: url,
                type: 'PATCH',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response && response.status) {
                        var active = response.data.is_active;
                        if (active == 1) {
                            $btn.removeClass('btn-outline-danger').addClass('btn-outline-success').text('Active');
                        } else {
                            $btn.removeClass('btn-outline-success').addClass('btn-outline-danger').text('Inactive');
                        }
                    }
                },
                error: function (xhr) {
                    console.error('Failed to update language status');
                }
            });
        });
    </script>
@endsection