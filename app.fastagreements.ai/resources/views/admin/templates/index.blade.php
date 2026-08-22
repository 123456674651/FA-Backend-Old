@extends('admin.layout.admin')

@section('content')

    <style>
  @supports (-webkit-appearance: none) or (-moz-appearance: none) {
    .checkbox-wrapper-14 input[type=checkbox] {
      --active: #275EFE;
      --active-inner: #fff;
      --focus: 2px rgba(39, 94, 254, .3);
      --border: #BBC1E1;
      --border-hover: #275EFE;
      --background: #fff;
      --disabled: #F6F8FF;
      --disabled-inner: #E1E6F9;
      -webkit-appearance: none;
      -moz-appearance: none;
      height: 21px;
      outline: none;
      display: inline-block;
      vertical-align: top;
      position: relative;
      margin: 0;
      cursor: pointer;
      border: 1px solid var(--bc, var(--border));
      background: var(--b, var(--background));
      transition: background 0.3s, border-color 0.3s, box-shadow 0.2s;
    }
    .checkbox-wrapper-14 input[type=checkbox]:after {
      content: "";
      display: block;
      left: 0;
      top: 0;
      position: absolute;
      transition: transform var(--d-t, 0.3s) var(--d-t-e, ease), opacity var(--d-o, 0.2s);
    }
    .checkbox-wrapper-14 input[type=checkbox]:checked {
      --b: var(--active);
      --bc: var(--active);
      --d-o: .3s;
      --d-t: .6s;
      --d-t-e: cubic-bezier(.2, .85, .32, 1.2);
    }
    .checkbox-wrapper-14 input[type=checkbox]:disabled {
      --b: var(--disabled);
      cursor: not-allowed;
      opacity: 0.9;
    }
    .checkbox-wrapper-14 input[type=checkbox]:disabled:checked {
      --b: var(--disabled-inner);
      --bc: var(--border);
    }
    .checkbox-wrapper-14 input[type=checkbox]:disabled + label {
      cursor: not-allowed;
    }
    .checkbox-wrapper-14 input[type=checkbox]:hover:not(:checked):not(:disabled) {
      --bc: var(--border-hover);
    }
    .checkbox-wrapper-14 input[type=checkbox]:focus {
      box-shadow: 0 0 0 var(--focus);
    }
    .checkbox-wrapper-14 input[type=checkbox]:not(.switch) {
      width: 21px;
    }
    .checkbox-wrapper-14 input[type=checkbox]:not(.switch):after {
      opacity: var(--o, 0);
    }
    .checkbox-wrapper-14 input[type=checkbox]:not(.switch):checked {
      --o: 1;
    }
    .checkbox-wrapper-14 input[type=checkbox] + label {
      display: inline-block;
      vertical-align: middle;
      cursor: pointer;
      margin-left: 4px;
    }

    .checkbox-wrapper-14 input[type=checkbox]:not(.switch) {
      border-radius: 7px;
    }
    .checkbox-wrapper-14 input[type=checkbox]:not(.switch):after {
      width: 5px;
      height: 9px;
      border: 2px solid var(--active-inner);
      border-top: 0;
      border-left: 0;
      left: 7px;
      top: 4px;
      transform: rotate(var(--r, 20deg));
    }
    .checkbox-wrapper-14 input[type=checkbox]:not(.switch):checked {
      --r: 43deg;
    }
    .checkbox-wrapper-14 input[type=checkbox].switch {
      width: 38px;
      border-radius: 11px;
    }
    .checkbox-wrapper-14 input[type=checkbox].switch:after {
      left: 2px;
      top: 2px;
      border-radius: 50%;
      width: 17px;
      height: 17px;
      background: var(--ab, var(--border));
      transform: translateX(var(--x, 0));
    }
    .checkbox-wrapper-14 input[type=checkbox].switch:checked {
      --ab: var(--active-inner);
      --x: 17px;
    }
    .checkbox-wrapper-14 input[type=checkbox].switch:disabled:not(:checked):after {
      opacity: 0.6;
    }
  }

  .checkbox-wrapper-14 * {
    box-sizing: inherit;
  }
  .checkbox-wrapper-14 *:before,
  .checkbox-wrapper-14 *:after {
    box-sizing: inherit;
  }
</style>

</style>
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
            <a href="{{ route('template.create') }}">
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
                                    <th class="text-center align-middle">Aggrimnent Name</th>
                                    <th class="text-center align-middle">Language Name</th>
                                    <th class="text-center align-middle">Actions</th>
                                    <th class="text-center align-middle">Template</th>
                                    <th class="text-center align-middle" style="width: 5%;">background</th>

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
        ajax: '{{ route('template.index') }}',

        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'category_name', name: 'category_name' }, 
            { data: 'language_name', name: 'language_name' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'template', name: 'template'},
            { data: 'background', name: 'background',}


        ]
    });
});
</script>
@endsection