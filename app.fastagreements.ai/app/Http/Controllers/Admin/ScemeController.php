<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sceme;
use Yajra\DataTables\Facades\DataTables;

class ScemeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $scemes = Sceme::all();
            return Datatables::of($scemes)
                ->addIndexColumn()
                ->addColumn('action', function ($sceme) {
                    $csrfToken = csrf_token();

                    $btn = '<a href="' . route('scemes.edit', $sceme->id) . '" class="edit btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i></a>';

                    $btn .= ' <a data-bs-toggle="modal" href="#delete_modal_' . $sceme->id . '" class="btn btn-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></a>';
                    $btn .= '<div id="delete_modal_' . $sceme->id . '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Confirmation</h4>
                                            <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this item? This action cannot be undone and you will be unable to recover any data.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancel</button>
                                            <form action="' . route('scemes.destroy', $sceme->id) . '" method="POST">
                                               <input type="hidden" name="_token" value="' . $csrfToken . '">
                                               <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-danger">Yes, delete it!</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.scemes.index');
    }

    public function scemelist()
    {
        $sceme = Sceme::all();

        return response()->json([
            'success' => true,
            'message' => 'Scemes',
            'sceme' => $sceme
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.scemes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'emi_pay_method' => 'required|string|max:255',
        ]);

        Sceme::create($request->all());

        return redirect()->route('scemes.index')
            ->with('success', 'Sceme created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('admin.scemes.show', compact('sceme'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $sceme = Sceme::find($id);
        return view('admin.scemes.edit', compact('sceme'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'emi_pay_method' => 'required|string|max:255',
        ]);
        $sceme = Sceme::find($id);

        $sceme->update($request->all());

        return redirect()->route('scemes.index')
            ->with('success', 'Sceme updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sceme = Sceme::find($id);
        $sceme->delete();

        return redirect()->route('scemes.index')
            ->with('success', 'Sceme deleted successfully.');
    }
}
