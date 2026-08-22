<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Calculation\Category;
use App\Models\DealCategory;
use App\Models\Language;
use App\Models\CategoryLanguage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use App\Models\CategoryAttribute;


class TemplateController extends Controller
{
    public function index(){
        if (request()->ajax()) {
            $data = CategoryLanguage::with('dealCategory', 'languages')->get(); // Eager load relationship
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('category_name', function ($row) {
                    return $row->dealCategory ? $row->dealCategory->category_name : 'N/A';
                })
                ->addColumn('language_name', function ($row){
                    return $row->languages ? $row->languages->language_name : 'N/A';
                })
                ->addColumn('action', function ($template) {
                    $editUrl = route('template.edit', $template->id);
                    $deleteUrl = route('template.delete', $template->id);
                    $csrfToken = csrf_token();

                    return '<div class="text-center">
                        <a href="' . $editUrl . '" class="edit btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i></a>
                        <a data-bs-toggle="modal" href="#delete_modal_' . $template->id . '" class="btn btn-danger btn-sm" title="Delete">
                            <i class="bi bi-trash"></i>
                        </a>
                        <div id="delete_modal_' . $template->id . '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                                        <form action="' . $deleteUrl . '" method="POST">
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
               ->addColumn('template', function ($dealCategory) {
                    $duplicateUrl = route('emplate.duplicate.entry', $dealCategory->id);

                    return '<div class="text-center">
                        <a data-bs-toggle="modalx" href="https://gnhub.net/fast-agreement/public/api/agreement?persone_1_id=38&persone_2_id=38&rent=5000&deposite=15000&start_date=16-11-2024&duration=11&view=1&lang='. $dealCategory->language_code.'&category_id='. $dealCategory->category_id.'&language_id='. $dealCategory->language_id.'" class="btn btn-danger btn-sm" title="Delete">
                            <i class="bi bi-eye"></i>
                        </a>

                         <a href="' . $duplicateUrl . '" data-bs-toggle="modalx"   class="btn btn-warning btn-sm" title="Delete">
                            <i class="bi bi-copy"></i>
                        </a>

                        

                    </div>';
                })
                ->addColumn('background', function ($dealCategory) {
    $toggleUrl = route('news_toggleStatus', $dealCategory->id);

    return '<form action="' . $toggleUrl . '" method="POST">
                ' . csrf_field() . '
                ' . method_field('PATCH') . '

                <label class="checkbox-wrapper-14 d-flex justify-content-center align-items-center">
                    <input type="checkbox" name="is_active"
                        onchange="this.form.submit()" ' . ($dealCategory->is_active == 1 ? 'checked' : '') . '>
                    <span class="slider round"></span>
                </label>
            </form>';
})

                
                ->rawColumns(['action', 'template', 'background'])
                ->make(true);
        }
        return view('admin.templates.index');
    }

    public function create(){
      
        $dealCategories = DealCategory::all(); 
        $languages = Language::where('is_active', 1)->orderBy('language_name')->get();
         $attributes = [
            '@party_one_name',
            '@party_one_address',
            '@party_one_number',
            '@person_1_image',
            '@party_two_name',
            '@party_two_address',
            '@party_two_number',
            '@person_2_image',
            '@deposite',
            '@agreement_amount',
            '@start_date',
            '@end_date',
            '@rent',
            '@duration',
            '@guarantor',
            '@agreement_date',
            '@reference_no',
            '@address',
            '@note',
        ];



        return view('admin.templates.create', compact( 'dealCategories', 'languages','attributes'));
    }

    public function store(Request $request){
       
          // Validate the incoming request data
          $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'language_id' => 'required',
            'description' => 'required',

        ]);

        // If validation fails, redirect back with errors
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $dealCategories = new CategoryLanguage;
        $dealCategories->category_id = $request->category_id;
        $dealCategories->language_id = $request->language_id;
        $dealCategories->agreement_text = $request->description;
        $dealCategories->save();

        


        return redirect()->back()->with('success', 'Tamplate added successfully!');

    }

    public function edit($id){
        $template = CategoryLanguage::find($id);
        $dealCategories = DealCategory::all(); 
        $languages = Language::where('is_active', 1)->orderBy('language_name')->get();
         $attributes = [
            '@party_one_name',
            '@party_one_address',
            '@party_one_number',
            '@person_1_image',
            '@party_two_name',
            '@party_two_address',
            '@party_two_number',
            '@person_2_image',
            '@deposite',
            '@agreement_amount',
            '@start_date',
            '@end_date',
            '@rent',
            '@duration',
            '@guarantor',
            '@agreement_date',
            '@reference_no',
            '@address',
            '@note',
        ];
        
        $category_attributes = CategoryAttribute::where('category_id', $template->category_id)->where('is_active', 1)->pluck('attribute_code');

        
        return view('admin.templates.edit', compact('template','dealCategories','languages', 'attributes', 'category_attributes'));
    }

    public function update(Request $request)
{
    $validator = Validator::make($request->all(), [
        'category_id' => 'required',
        'language_id' => 'required',
        'description' => 'required',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $dealCategories = CategoryLanguage::findOrFail($request->id);

    $dealCategories->category_id = $request->category_id;
    $dealCategories->language_id = $request->language_id;
    $dealCategories->agreement_text = $request->description;

    $dealCategories->save();

    return redirect()->back()->with('success', 'Template updated successfully!');
}

public function duplicateEntry($id)
{

    $originalEntry = CategoryLanguage::find($id);

    if (!$originalEntry) {
        return redirect()->back()->with('error', 'Entry not found.');
    }

    // Create a duplicate
    $newEntry = $originalEntry->replicate();
    $newEntry->save();

    return view('admin.templates.index');
}

public function show($id){

    $show = CategoryLanguage::find($id);

    $show->agreement_text = str_replace('@name', $show->language_name, $show->agreement_text);


    return view('admin.templates.show', compact('show'));
}

public function delete($id)
{
    $originalEntry = CategoryLanguage::find($id);
    if (!$originalEntry) {
        return redirect()->back()->with('error', 'Entry not found.');
    }

    $originalEntry->delete();

    return redirect()->back()->with('success', 'Entry deleted successfully!');
}

public function toggleStatus($id)
    {
        $NewsAndNotification =CategoryLanguage::findOrFail($id);

        $NewsAndNotification->is_active = !$NewsAndNotification->is_active;
        $NewsAndNotification->save();

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

}
