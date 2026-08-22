<a href="{{ route('videos.edit', $video->id) }}" class="btn btn-primary btn-sm">Edit</a>
<form action="{{ route('videos.destroy', $video->id) }}" method="post" style="display:inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
</form>
<a href="{{ route('videos.show', $video->id) }}" class="btn btn-info btn-sm">View</a>
