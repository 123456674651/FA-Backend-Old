<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class UserController extends Controller
{
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $query = Admin::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('profile', function ($row) {
                    $src = $row->image ? asset($row->image) : asset('assets/img/logo/logo.jpeg');
                    return '<img src="' . $src . '" alt="' . e($row->name) . '" style="height:40px;width:40px;border-radius:50%;object-fit:cover;">';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('Y-m-d') : 'N/A';
                })
                ->addColumn('role', function ($row) {
                    return $row->role === 'SUPER_ADMIN' ? 'Super Admin' : 'Admin';
                })
               ->addColumn('status', function ($row) {
    $badge = $row->status == 1
        ? '<span class="badge bg-success">Active</span>'
        : '<span class="badge bg-danger">Inactive</span>';

    return '<a href="#" class="toggle-status"
                data-id="' . $row->id . '"
                data-status="' . $row->status . '"
                style="text-decoration:none;">' . $badge . '</a>';
})
                ->addColumn('action', function ($row) {
                    $edit = route('users.edit', $row->id);
                    $show = route('users.show', $row->id);
                    $del = route('users.destroy', $row->id);
                    $csrf = csrf_token();
                    return '<a href="' . $show . '" class="btn btn-info btn-sm me-1"><i class="bi bi-eye"></i></a>' .
                           '<a href="' . $edit . '" class="edit btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i></a>' .
                           '<a data-bs-toggle="modal" href="#delete_modal_' . $row->id . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>' .
                           '<div id="delete_modal_' . $row->id . '" class="modal fade" tabindex="-1">' .
                           '<div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 class="modal-title">Confirm</h4><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>' .
                           '<div class="modal-body"><p>Are you sure you want to delete this admin?</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>' .
                           '<form action="' . $del . '" method="POST" style="display:inline;"><input type="hidden" name="_token" value="' . $csrf . '"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="btn btn-danger">Yes, delete</button></form></div></div></div></div>';
                })
                ->rawColumns(['profile', 'status', 'action'])
                ->make(true);
        }

        return view('admin.users.index');
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request)
    {
        try {
            $data = $request->validated();

            $admin = new Admin();
            $admin->name = $data['name'];
            $admin->email = $data['email'];
            $admin->role = $data['role'];
            $admin->password = Hash::make($data['password']);
            $admin->status = $request->has('status') ? (bool) $data['status'] : true;
            $admin->save();

            return redirect()->route('users.index')->with('success', 'Admin created successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error creating admin: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $user = Admin::findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = Admin::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, $id)
    {
        try {
            $admin = Admin::findOrFail($id);
            $data = $request->validated();

            $admin->name = $data['name'];
            $admin->email = $data['email'];
            $admin->role = $data['role'];
            if (!empty($data['password'])) {
                $admin->password = Hash::make($data['password']);
            }
            $admin->status = $request->has('status') ? (bool) $data['status'] : $admin->status;
            $admin->save();

            return redirect()->route('users.index')->with('success', 'Admin updated successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error updating admin: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $admin = Admin::findOrFail($id);
            $admin->delete();
            return redirect()->route('users.index')->with('success', 'Admin deleted successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error deleting admin: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:0,1']);
        $admin = Admin::findOrFail($id);
        $admin->status = $request->input('status');
        $admin->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $admin->status]);
        }

        return redirect()->back()->with('success', 'Status updated successfully!');
    }
}
