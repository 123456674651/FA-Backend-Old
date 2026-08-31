<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\DealCategory;
use App\Models\Purpose;
use App\Models\Language;
use App\Models\Sceme;
use Yajra\DataTables\DataTables;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class DealController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $deals = Deal::query()->select('deals.*')->orderBy('id', 'desc');
            ; // Adjust query as needed

            return DataTables::of($deals)
                ->addIndexColumn() // Automatically handle row index
                ->addColumn('person_1', function ($deal) {
                    // Fetch person_1 name based on ID
                    $person = Customer::find($deal->person_1); // Adjust field name as needed
                    return $person ? $person->name : 'N/A';
                })
                ->addColumn('person_2', function ($deal) {
                    // Fetch person_2 name based on ID
                    $person = Customer::find($deal->person_2); // Adjust field name as needed
                    return $person ? $person->name : 'N/A';
                })
                ->addColumn('purpose', function ($deal) {
                    // Fetch purpose description based on ID
                    $purpose = Purpose::find($deal->purpose); // Adjust field name as needed
                    return $purpose ? $purpose->purpose_name : 'N/A';
                })
                ->addColumn('actions', function ($deal) {
                    return '
                        <a href="' . route('deals.edit', $deal->id) . '" class="btn btn-primary btn-sm">Edit</a>
                        <a href="' . route('deals.history', $deal->id) . '" class="btn btn-info btn-sm">History</a>
                        <form action="' . route('deals.destroy', $deal->id) . '" method="POST" style="display:inline;">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this deal?\')">Delete</button>
                        </form>
                    ';
                })
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('admin.deals.index');
    }

    public function create()
    {
        $purposes = Purpose::all(); // Fetch all purposes
        $dealCategories = DealCategory::all(); // Fetch all deal categories
        $languages = Language::where('is_active', 1)->orderBy('language_name')->get(); // Fetch active languages
        $scemes = Sceme::all(); // Fetch all schemes
        $customers = Customer::all(); // Fetch all customers

        return view('admin.deals.create', compact('purposes', 'dealCategories', 'languages', 'scemes', 'customers'));
    }

    public function getMediatorBySakshi($sakshiId)
    {
        // Fetch the customer based on sakshi_id
        $customer = Customer::find($sakshiId);

        // Return the mediator_id as JSON
        return response()->json([
            'mediator_id' => $customer ? $customer->id : null
        ]);
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'person_1' => 'required|string',
            'person_2' => 'required|string',
            'amount' => 'required|numeric',
            'purpose' => 'required|string',
            'category_id' => 'required|numeric',
            'language_id' => 'required|numeric',
            'contract_template_file_name_id' => 'required|numeric',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'contract_date' => 'required|date',
            'status' => 'required|string',
            'currency' => 'required|string',
            'lat' => 'nullable|string',
            'lon' => 'nullable|string',
            'allow_live_location' => 'required|string|in:yes,no',
            'interest_rate' => 'nullable|numeric',
            'payable_amount' => 'nullable|numeric',
            'interest_term_in_month' => 'nullable|numeric',
            'sakshi' => 'required|string',
            'mediator_id' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'video_recording' => 'nullable|file|mimes:mp4,avi,mov',
            'audio_recording' => 'nullable|file|mimes:mp3,wav',
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle the validated data except files
        $dealData = $request->except(['video_recording', 'audio_recording', 'image']);
        $dealData['allow_live_location'] = $dealData['allow_live_location'] === 'yes' ? 1 : 0;
        // $dealData->mediator_id = $request->input('mediator_id');

        // Create the deal record
        $deal = Deal::create($dealData);

        // Handle video recording upload
        if ($request->hasFile('video_recording')) {
            $video = $request->file('video_recording');
            $videoPath = 'admin/dealVideo/' . $video->getClientOriginalName();
            $video->move(public_path('admin/dealVideo'), $videoPath);
            $deal->video_recording = $videoPath;
        }

        // Handle audio recording upload
        if ($request->hasFile('audio_recording')) {
            $audio = $request->file('audio_recording');
            $audioPath = 'admin/dealAudio/' . $audio->getClientOriginalName();
            $audio->move(public_path('admin/dealAudio'), $audioPath);
            $deal->audio_recording = $audioPath;
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = 'admin/dealImage/' . $image->getClientOriginalName();
            $image->move(public_path('admin/dealImage'), $imagePath);
            $deal->images = $imagePath;
        }

        // Save any changes to the deal
        $deal->save();

        // Redirect with success message
        return redirect()->route('deals.index')->with('success', 'Agreement created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        // Find the deal by its ID
        $deal = Deal::findOrFail($id);

        // Retrieve options for dropdowns and other form fields if needed
        $customers = Customer::all();
        $purposes = Purpose::all();
        $dealCategories = DealCategory::all();
        $languages = Language::where('is_active', 1)->orderBy('language_name')->get();
        $scemes = Sceme::all();
        $existingFileVideo = $deal->video_recording;
        $existingFileAudio = $deal->audio_recording;
        $existingFileImage = $deal->images;


        // Return the edit view with the deal data
        return view('admin.deals.edit', compact('deal', 'customers', 'purposes', 'dealCategories', 'languages', 'scemes', 'existingFileVideo', 'existingFileAudio', 'existingFileImage'));
    }

    public function update(Request $request, $id)
    {
        // Find the deal by its ID
        $deal = Deal::findOrFail($id);

        // Validate the incoming request
        $validated = $request->validate([
            'person_1' => 'required|string',
            'person_2' => 'required|string',
            'amount' => 'required|numeric',
            'purpose' => 'required|string',
            'category_id' => 'required|numeric',
            'language_id' => 'required|numeric',
            'contract_template_file_name_id' => 'required|numeric',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'contract_date' => 'required|date',
            'status' => 'required|string',
            'currency' => 'required|string',
            'lat' => 'nullable|string',
            'lon' => 'nullable|string',
            'allow_live_location' => 'required|string|in:yes,no',
            'interest_rate' => 'nullable|numeric',
            'payable_amount' => 'nullable|numeric',
            'interest_term_in_month' => 'nullable|numeric',
            'sakshi' => 'nullable|string',
            'mediator_id' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'video_recording' => 'nullable|file|mimes:mp4,avi,mov',
            'audio_recording' => 'nullable|file|mimes:mp3,wav',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle the validated data except files
        $dealData = $request->except(['video_recording', 'audio_recording', 'image']);

        // Ensure the 'allow_live_location' field is correctly converted to an integer
        $dealData['allow_live_location'] = ($request->input('allow_live_location') === 'yes') ? 1 : 0;


        // Update the deal record
        $deal->update($dealData);

        // Handle video recording upload
        if ($request->hasFile('video_recording')) {
            // Delete old file if needed
            if ($deal->video_recording && file_exists(public_path($deal->video_recording))) {
                unlink(public_path($deal->video_recording));
            }
            $video = $request->file('video_recording');
            $videoPath = 'admin/dealVideo/' . $video->getClientOriginalName();
            $video->move(public_path('admin/dealVideo'), $videoPath);
            $deal->video_recording = $videoPath;
        }

        // Handle audio recording upload
        if ($request->hasFile('audio_recording')) {
            // Delete old file if needed
            if ($deal->audio_recording && file_exists(public_path($deal->audio_recording))) {
                unlink(public_path($deal->audio_recording));
            }
            $audio = $request->file('audio_recording');
            $audioPath = 'admin/dealAudio/' . $audio->getClientOriginalName();
            $audio->move(public_path('admin/dealAudio'), $audioPath);
            $deal->audio_recording = $audioPath;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old file if needed
            if ($deal->images && file_exists(public_path($deal->images))) {
                unlink(public_path($deal->images));
            }
            $image = $request->file('image');
            $imagePath = 'admin/dealImage/' . $image->getClientOriginalName();
            $image->move(public_path('admin/dealImage'), $imagePath);
            $deal->images = $imagePath;
        }

        // Save any changes to the deal
        $deal->save();

        // Redirect with success message
        return redirect()->route('deals.index')->with('success', 'Deal updated successfully.');
    }

    public function destroy($id)
    {
        // Find the deal by its ID
        $deal = Deal::findOrFail($id);

        // Delete associated files if they exist
        if ($deal->video_recording && file_exists(public_path($deal->video_recording))) {
            unlink(public_path($deal->video_recording));
        }

        if ($deal->audio_recording && file_exists(public_path($deal->audio_recording))) {
            unlink(public_path($deal->audio_recording));
        }

        if ($deal->image && file_exists(public_path($deal->image))) {
            unlink(public_path($deal->image));
        }

        // Delete the deal record
        $deal->delete();

        // Redirect with success message
        return redirect()->route('deals.index')->with('success', 'Deal deleted successfully.');
    }

    // public function showDealHistory(string $id)
    // {
    //     try {
    //         $deal = Deal::findOrFail($id);

    //         $payableAmount = $deal->payable_amount;
    //         $termInMonths = $deal->interest_term_in_month;
    //         $startDate = new Carbon($deal->created_at);
    //         $dueDates = [];
    //         $currentDate = Carbon::now();

    //         for ($i = 1; $i <= $termInMonths; $i++) {
    //             $dueDate = $startDate->copy()->addMonths($i);
    //             $amountDue = round($payableAmount / $termInMonths, 2);

    //             $isOverdue = $currentDate->greaterThan($dueDate);
    //             $actualAmountDue = $isOverdue ? $amountDue : $amountDue;

    //             $dueDates[] = [
    //                 'month' => $i,
    //                 'due_date' => $dueDate->format('Y-m-d'),
    //                 'amount_due' => $actualAmountDue,
    //                 'is_overdue' => $isOverdue ? 1 : 0,
    //             ];
    //         }

    //         return view('admin.deal-history.index', [
    //             'status' => $deal->status,
    //             'message' => 'Deal history retrieved successfully.',
    //             'deal' => [
    //                 'deal_id' => $deal->id,
    //                 'payable_amount' => $payableAmount,
    //                 'interest_term_in_month' => $termInMonths,
    //                 'start_date' => $startDate->format('Y-m-d'),
    //                 'due_dates' => $dueDates,
    //             ],
    //         ]);
    //     } catch (ModelNotFoundException $e) {
    //         return view('admin.deal-history.index', [
    //             'status' => 0,
    //             'message' => 'Deal not found',
    //         ])->with('error', 'Deal not found');
    //     } catch (Exception $e) {
    //         return view('admin.deal-history.index', [
    //             'status' => 0,
    //             'message' => 'An error occurred while retrieving the deal history.',
    //             'error' => $e->getMessage()
    //         ]);
    //     }
    // }

    public function showDealHistory(string $id)
    {
        try {
            $deal = Deal::findOrFail($id);

            // Fetch names from the customers and purposes tables
            $person1 = Customer::find($deal->person_1);
            $person2 = Customer::find($deal->person_2);
            $sakshi = Customer::find($deal->sakshi);
            $purpose = Purpose::find($deal->purpose);

            $payableAmount = $deal->payable_amount ?? 0;
            $termInMonths = $deal->interest_term_in_month ?? 0;
            $startDate = new Carbon($deal->created_at);
            $dueDates = [];
            $currentDate = Carbon::now();

            for ($i = 1; $i <= $termInMonths; $i++) {
                $dueDate = $startDate->copy()->addMonths($i);
                $amountDue = round($payableAmount / $termInMonths, 2);

                $isOverdue = $currentDate->greaterThan($dueDate);
                $actualAmountDue = $isOverdue ? $amountDue : $amountDue;

                $dueDates[] = [
                    'month' => $i,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'amount_due' => $actualAmountDue,
                    'is_overdue' => $isOverdue ? 1 : 0,
                ];
            }

            return view('admin.deal-history.index', [
                'status' => $deal->status,
                'message' => 'Deal history retrieved successfully.',
                'deal' => [
                    'deal_id' => $deal->id,
                    'person_1' => $person1 ? $person1->name : 'N/A',
                    'person_2' => $person2 ? $person2->name : 'N/A',
                    'sakshi' => $sakshi ? $sakshi->name : 'N/A',
                    'purpose' => $purpose ? $purpose->purpose_name : 'N/A',
                    'amount' => $deal->amount,
                    'currency' => $deal->currency,
                    'payable_amount' => $payableAmount,
                    'interest_rate' => $deal->interest_rate,
                    'interest_term_in_month' => $termInMonths,
                    'start_date' => $startDate->format('Y-m-d'),
                    'due_dates' => $dueDates,
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return view('admin.deal-history.index', [
                'status' => 0,
                'message' => 'Deal not found',
            ])->with('error', 'Deal not found');
        } catch (Exception $e) {
            return view('admin.deal-history.index', [
                'status' => 0,
                'message' => 'An error occurred while retrieving the deal history.',
                'error' => $e->getMessage()
            ]);
        }
    }

}
