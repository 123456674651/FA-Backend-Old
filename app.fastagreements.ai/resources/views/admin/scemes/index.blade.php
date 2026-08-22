@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Sceme List</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('') }}">Home</a></li>
                    <li class="breadcrumb-item">Sceme List</li>
                </ol>
            </nav>
        </div>

        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('scemes.create') }}">
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
                                    <th class="text-center align-middle">EMI/Pay Method</th>
                                    <th class="text-center align-middle" colspan="3" style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- The content will be dynamically loaded by DataTables -->
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

<script type="text/javascript">
    $(function() {
        var table = $(".data-table").DataTable({
            columnDefs: [{
                "defaultContent": "-",
                "targets": "_all",
                "className": "text-center align-middle"
            }],
            serverSide: true,
            ajax: "{{ route('scemes.index') }}",
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'emi_pay_method',
                    name: 'emi_pay_method'
                },
                {
                    data: 'action',
                    name: 'action'
                }
            ],
        });
    });
</script>
@stop
