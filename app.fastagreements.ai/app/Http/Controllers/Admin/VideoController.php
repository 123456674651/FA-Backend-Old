<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Video;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\DataTables;

class VideoController extends Controller
{

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(Video::query()->orderBy('id', 'desc'))
                ->addIndexColumn()  // This will add the 'DT_RowIndex' column
                ->addColumn('action', function ($video) {
                    $editUrl = route('videos.edit', $video->id);
                    $deleteUrl = route('videos.destroy', $video->id);
                    $csrfToken = csrf_token();

                    return '<div class="text-center">
                    <a href="' . $editUrl . '" class="edit btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i></a>
                    <a data-bs-toggle="modal" href="#delete_modal_' . $video->id . '" class="btn btn-danger btn-sm" title="Delete">
                        <i class="bi bi-trash"></i>
                    </a>
                    <div id="delete_modal_' . $video->id . '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Confirmation</h4>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete this video? This action cannot be undone and you will be unable to recover any data.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancel</button>
                                    <form action="' . $deleteUrl . '" method="POST" style="display:inline;">
                                        <input type="hidden" name="_token" value="' . $csrfToken . '">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-danger">Yes, delete it!</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
                })
                ->make(true);
        }

        return view('admin.video.index');
    }

    public function create()
    {
        // Return the view for the video upload form
        return view('admin.video.create');
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'video_file' => 'required|file|mimes:mp4,avi,mkv|max:20480', // Validation rules
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tags' => 'nullable|string',
        ]);

        // Handle the file upload
        if ($request->hasFile('video_file')) {
            // Store the new video file
            $videoFile = $request->file('video_file');
            $fileName = time() . '.' . $videoFile->getClientOriginalExtension();
            $destinationPath = public_path('admin/video');
            $videoFile->move($destinationPath, $fileName);

            // Create a new video record
            $video = new Video();
            $video->file_name = $fileName;
            $video->title = $request->input('title');
            $video->description = $request->input('description');
            $video->tags = $request->input('tags');
            $video->save();

            // Redirect to the videos index with a success message
            return redirect()->route('videos.index')->with('success', 'Video uploaded successfully.');
        } else {
            // If no file was uploaded, redirect back with an error message
            return redirect()->back()->with('error', 'No video file was uploaded.');
        }
    }



    public function edit(Video $video)
    {
        // Return the view for the edit form with the video data
        return view('admin.video.edit', compact('video'));
    }

    public function update(Request $request, Video $video)
    {
        // Validate the incoming request
        $request->validate([
            'video_file' => 'nullable|file|mimes:mp4,avi,mkv|max:20480', // Validation rules
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tags' => 'nullable|string',
        ]);

        // Handle the file upload
        if ($request->hasFile('video_file')) {
            // Delete the old video file if exists
            $oldFilePath = public_path('admin/video/' . $video->file_name);
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            // Store the new video file
            $videoFile = $request->file('video_file');
            $fileName = time() . '.' . $videoFile->getClientOriginalExtension();
            $destinationPath = public_path('admin/video');
            $videoFile->move($destinationPath, $fileName);

            // Update the file name in the video model
            $video->file_name = $fileName;
        }

        // Update the video record
        $video->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'tags' => $request->input('tags'),
        ]);

        // Redirect to the videos index with a success message
        return redirect()->route('videos.index')->with('success', 'Video updated successfully.');
    }

    public function show($id)
    {
        // Fetch the video by its ID
        $video = Video::findOrFail($id);

        // Return the 'show' view with the video data
        return view('admin.video.show', compact('video'));
    }

    public function destroy($id)
    {
        // Fetch the video by its ID
        $video = Video::findOrFail($id);

        // Determine the path to the video file
        $filePath = public_path('admin/video/' . $video->file_name);

        // Check if the file exists and delete it
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // Delete the video record from the database
        $video->delete();

        // Redirect to the videos index with a success message
        return redirect()->route('videos.index')->with('success', 'Video deleted successfully.');
    }
}
