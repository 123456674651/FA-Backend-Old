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
            $query = Admin::select(['id', 'name', 'email', 'image', 'role', 'status', 'created_at']);
            $defaultImage = asset('assets/img/logo/logo.jpeg');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('name', function ($row) use ($defaultImage) {
                    $src = $row->image ? asset($row->image) : $defaultImage;
                    $img = '<img src="' . $src . '" alt="' . e($row->name) . '" style="height:40px;width:40px;border-radius:50%;object-fit:cover;margin-right:10px;">';
                    $nameLink = '<a href="javascript:void(0);" class="text-primary text-decoration-none user-name-link" style="font-weight: 500; font-family: \'Inter\', sans-serif;" data-id="' . $row->id . '">' . e($row->name) . '</a>';
                    return '<div class="d-flex align-items-center">' . $img . $nameLink . '</div>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->timezone('Asia/Kolkata')->format('d M, Y h:i A') : 'N/A';
                })
                ->addColumn('role', function ($row) {
                    return $row->role === 'SUPER_ADMIN' ? 'Super Admin' : 'Admin';
                })
                ->addColumn('status', function ($row) {
                    $badge = $row->status == 1
                        ? '<span class="badge rounded-pill" style="background-color: rgba(10, 179, 156, 0.1); color: #0ab39c; font-weight: 500; font-size: 11px; padding: 4px 8px;">Active</span>'
                        : '<span class="badge rounded-pill" style="background-color: rgba(240, 101, 72, 0.1); color: #f06548; font-weight: 500; font-size: 11px; padding: 4px 8px;">Inactive</span>';

                    return '<a href="#" class="toggle-status" data-id="' . $row->id . '" data-status="' . $row->status . '" style="text-decoration:none;">' . $badge . '</a>';
                })
                ->addColumn('action', function ($row) {
                    return view('admin.users.action', compact('row'))->render();
                })
                ->rawColumns(['name', 'status', 'action'])
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

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Admin created successfully!',
                    'redirect' => route('users.index')
                ]);
            }

            return redirect()->route('users.index')->with('success', 'Admin created successfully!');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error creating admin: ' . $e->getMessage()
                ], 500);
            }
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
