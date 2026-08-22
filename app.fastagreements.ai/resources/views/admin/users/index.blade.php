@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="pagetitle mb-4">
        <div class="row align-items-center">
            <div class="col-md-6 pt-2">
                <h1 class="fw-bold text-dark mb-1" style="font-size: 28px;">Users</h1>
                <p class="text-muted mb-0" style="font-size: 14px;">Manage application users.</p>
            </div>
            <div class="col-md-6 text-md-end pt-2">
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle-fill me-2"></i>Add User
                </a>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card border-0 shadow-sm" style="border-radius: 10px; overflow: hidden;">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="usersTable" class="table table-hover w-100 align-middle">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 80px;">#</th>
                                <th>Profile</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th class="text-center" style="width: 150px;">Status</th>
                                <th>Created Date</th>
                                <th class="text-center" style="width: 150px;">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('users.index') }}',
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle fw-semibold text-secondary' },
            { data: 'profile', name: 'profile', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'mobile', name: 'mobile' },
            { data: 'status', name: 'status', className: 'text-center' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[6, 'desc']]
    });
});
</script>
<script>
// Delegate click for status toggle
$(document).on('click', '.toggle-status', function(e) {
    e.preventDefault();
    var el = $(this);
    var id = el.data('id');
    var status = parseInt(el.data('status'));
    var newStatus = status === 1 ? 0 : 1;
    var url = '{{ url('users') }}' + '/' + id + '/status';

    fetch(url, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(function(res) { return res.json(); })
    .then(function(json) {
        if (json.success) {
            // reload the table row to reflect change
            $('#usersTable').DataTable().ajax.reload(null, false);
        } else {
            alert('Could not update status');
        }
    }).catch(function() {
        alert('Error updating status');
    });
});
</script>
@endsection
