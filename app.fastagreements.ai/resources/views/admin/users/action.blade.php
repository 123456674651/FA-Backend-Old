<a href="{{ route('users.show', $row->id) }}" class="btn btn-info btn-sm me-1"><i class="bi bi-eye"></i></a>
<a href="{{ route('users.edit', $row->id) }}" class="edit btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i></a>
<a data-bs-toggle="modal" href="#delete_modal_{{ $row->id }}" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>

<div id="delete_modal_{{ $row->id }}" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirm</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this admin?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('users.destroy', $row->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes, delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
