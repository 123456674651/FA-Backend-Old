<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Language;
use Yajra\DataTables\DataTables;

class LanguageController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(Language::query())
                ->addIndexColumn()
                ->addColumn('is_active', function ($language) {
                    $url = route('languages.status_changes', $language->id);
                    $btnClass = $language->is_active ? 'outline-success' : 'outline-danger';
                    $btnText = $language->is_active ? 'Active' : 'Inactive';

                    return '<div class="text-center"><button type="button" class="btn btn-' . $btnClass . ' btn-sm language-status-btn" data-url="' . $url . '" data-id="' . $language->id . '">' . $btnText . '</button></div>';
                })
                ->addColumn('action', function ($language) {
                    return '';
                })
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        return view('admin.languages.index');
    }

    public function status_changes(Request $request, $id)
    {
        $language = Language::findOrFail($id);

        // Accept explicit value (0/1) or toggle if not provided
        if ($request->has('is_active')) {
            $language->is_active = (int) $request->input('is_active');
        } else {
            $language->is_active = $language->is_active ? 0 : 1;
        }

        $language->save();

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Language status updated successfully.',
                'data' => [
                    'id' => $language->id,
                    'is_active' => (int) $language->is_active
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Language status updated successfully.');
    }

    public function create()
    {
        return view('admin.languages.create');
    }

    public function store(Request $request)
    {
        // Validate the input data
        $validatedData = $request->validate([
            'language_name' => 'required|string|max:255',
            'language_code' => 'required|string|max:10',
            'language_in_guj' => 'required|string|max:255', // Validate the new field
        ]);

        // Create a new Language entry
        Language::create($validatedData);

        // Redirect or respond as needed
        return redirect()->route('languages.index')->with('success', 'Language added successfully!');
    }

    public function edit($id)
    {
        $language = Language::findOrFail($id);
        return view('admin.languages.edit', compact('language'));
    }

    public function update(Request $request, $id)
    {
        // Validate the input data
        $validatedData = $request->validate([
            'language_name' => 'required|string|max:255',
            'language_code' => 'required|string|max:10',
            'language_in_guj' => 'required|string|max:255', // Validate the new field
        ]);

        // Find the language record
        $language = Language::findOrFail($id);

        // Update the record
        $language->update($validatedData);

        // Redirect or respond as needed
        return redirect()->route('languages.index')->with('success', 'Language updated successfully!');
    }

    public function destroy($id)
    {
        $language = Language::findOrFail($id);
        $language->delete();

        return redirect()->route('languages.index')->with('success', 'Language deleted successfully.');
    }
}
