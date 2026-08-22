<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\State;
use App\Traits\ImageResizer;
use App\Models\Country;
use App\Models\City;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerController extends Controller
{
    use ImageResizer;

    /**
     * Display a listing of all customers.
     */
    // public function index()
    // {
    //     if (request()->ajax()) {
    //         $customers = DB::table('customers')
    //             ->leftJoin('cities', 'customers.city_id', '=', 'cities.id')
    //             ->leftJoin('states', 'customers.state_id', '=', 'states.id')
    //             ->leftJoin('countries', 'customers.country_id', '=', 'countries.id')
    //             ->select([
    //                 'customers.id',
    //                 'customers.name',
    //                 'customers.mobile',
    //                 'customers.email',
    //                 'cities.city as city',
    //                 'states.name as state',
    //                 'countries.name as country',
    //                 'customers.is_active'
    //             ])
    //             ->orderBy('customers.id', 'desc');

    //         return DataTables::of($customers)
    //             ->addIndexColumn()
    //             ->addColumn('is_active', function ($row) {
    //                 return $row->is_active ? 'Active' : 'Inactive';
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $editUrl = route('customers.edit', $row->id);
    //                 $deleteUrl = route('customers.destroy', $row->id);
    //                 $csrfToken = csrf_token();

    //                 return '<div class="text-center">
    //                     <a href="' . $editUrl . '" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i></a>
    //                     <a data-bs-toggle="modal" href="#delete_modal_' . $row->id . '" class="btn btn-danger btn-sm" title="Delete">
    //                         <i class="bi bi-trash"></i>
    //                     </a>
    //                     <div id="delete_modal_' . $row->id . '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    //                         <div class="modal-dialog">
    //                             <div class="modal-content">
    //                                 <div class="modal-header">
    //                                     <h4 class="modal-title">Confirmation</h4>
    //                                     <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
    //                                 </div>
    //                                 <div class="modal-body">
    //                                     <p>Are you sure you want to delete this item? This action cannot be undone and you will be unable to recover any data.</p>
    //                                 </div>
    //                                 <div class="modal-footer">
    //                                     <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancel</button>
    //                                     <form action="' . $deleteUrl . '" method="POST">
    //                                         <input type="hidden" name="_token" value="' . $csrfToken . '">
    //                                         <input type="hidden" name="_method" value="DELETE">
    //                                         <button type="submit" class="btn btn-danger">Yes, delete it!</button>
    //                                     </form>
    //                                 </div>
    //                             </div>
    //                         </div>
    //                     </div>
    //                 </div>';
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     return view('admin.customers.index');
    // }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $customers = DB::table('customers')
                ->leftJoin('cities', 'customers.city_id', '=', 'cities.id')
                ->leftJoin('states', 'customers.state_id', '=', 'states.id')
                ->leftJoin('countries', 'customers.country_id', '=', 'countries.id')
                ->select([
                    'customers.id',
                    'customers.name',
                    'customers.mobile',
                    'customers.email',
                    'cities.city as city',
                    'states.name as state',
                    'countries.name as country',
                    'customers.is_active'
                ])
                ->orderBy('customers.id', 'desc');

            return DataTables::of($customers)
                ->filter(function ($query) use ($request) {
                    // Custom Name & Mobile Filters
                    if ($request->filled('name')) {
                        $query->where('customers.name', 'like', '%' . $request->name . '%');
                    }
                    if ($request->filled('mobile')) {
                        $query->where('customers.mobile', 'like', '%' . $request->mobile . '%');
                    }

                    // Custom Status Filters
                    if ($request->filled('status')) {
                        $query->where('customers.is_active', $request->status);
                    }

                    // Custom Plan & Plan Status Filters
                    if ($request->filled('plan_id') || $request->filled('plan_status')) {
                        $latestSubQuery = DB::table('user_subscriptions')
                            ->select('id')
                            ->whereRaw('customer_id = customers.id')
                            ->orderBy('id', 'desc')
                            ->limit(1);

                        if ($request->filled('plan_id')) {
                            $query->whereExists(function ($q) use ($request, $latestSubQuery) {
                                $q->select(DB::raw(1))
                                    ->from('user_subscriptions')
                                    ->whereRaw('user_subscriptions.customer_id = customers.id')
                                    ->where('user_subscriptions.id', '=', $latestSubQuery)
                                    ->where('user_subscriptions.subscription_plan_id', $request->plan_id);
                            });
                        }

                        if ($request->filled('plan_status')) {
                            $status = $request->plan_status;
                            if ($status === 'No Plan') {
                                $query->where(function ($noPlanQ) use ($latestSubQuery) {
                                    $noPlanQ->whereNotExists(function ($q) {
                                        $q->select(DB::raw(1))
                                            ->from('user_subscriptions')
                                            ->whereRaw('user_subscriptions.customer_id = customers.id');
                                    })->orWhereExists(function ($q) use ($latestSubQuery) {
                                        $q->select(DB::raw(1))
                                            ->from('user_subscriptions')
                                            ->join('subscription_plans', 'user_subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
                                            ->whereRaw('user_subscriptions.customer_id = customers.id')
                                            ->where('user_subscriptions.id', '=', $latestSubQuery)
                                            ->where('subscription_plans.duration_type', 'per_agreement');
                                    });
                                });
                            } else {
                                $query->whereExists(function ($q) use ($status, $latestSubQuery) {
                                    $q->select(DB::raw(1))
                                        ->from('user_subscriptions')
                                        ->join('subscription_plans', 'user_subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
                                        ->whereRaw('user_subscriptions.customer_id = customers.id')
                                        ->where('user_subscriptions.id', '=', $latestSubQuery)
                                        ->where('subscription_plans.duration_type', '!=', 'per_agreement')
                                        ->where(function ($subQ) use ($status) {
                                            $today = \Carbon\Carbon::today()->toDateString();
                                            if ($status === 'Lifetime') {
                                                $subQ->where('subscription_plans.duration_type', 'lifetime');
                                            } elseif ($status === 'Active') {
                                                $subQ->where('subscription_plans.duration_type', '!=', 'lifetime')
                                                    ->where(function ($ex) use ($today) {
                                                        $ex->where(function ($hasEnd) use ($today) {
                                                            $hasEnd->whereNotNull('user_subscriptions.end_date')
                                                                ->where('user_subscriptions.end_date', '>=', $today);
                                                        })->orWhere(function ($noEnd) {
                                                            $noEnd->whereNull('user_subscriptions.end_date');
                                                        });
                                                    });
                                            } elseif ($status === 'Expired') {
                                                $subQ->where('subscription_plans.duration_type', '!=', 'lifetime')
                                                    ->whereNotNull('user_subscriptions.end_date')
                                                    ->where('user_subscriptions.end_date', '<', $today);
                                            }
                                        });
                                });
                            }
                        }
                    }

                    // Global Search
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $searchValue = $request->input('search.value');
                        $query->where(function ($q) use ($searchValue) {
                            $q->where('customers.name', 'like', "%{$searchValue}%")
                                ->orWhere('customers.mobile', 'like', "%{$searchValue}%");
                        });
                    }
                })
                ->orderColumn('city', function ($query, $order) {
                    $query->orderBy('cities.city', $order);
                })
                ->orderColumn('state', function ($query, $order) {
                    $query->orderBy('states.name', $order);
                })
                ->orderColumn('country', function ($query, $order) {
                    $query->orderBy('countries.name', $order);
                })                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    return '<a href="#" class="view-customer-trigger fw-bold text-primary text-decoration-none" data-id="' . $row->id . '">' . e($row->name) . '</a>';
                })
                ->addColumn('plan', function ($row) {
                    $subscription = \App\Models\CustomerSubscription::with('plan')
                        ->where('customer_id', $row->id)
                        ->orderBy('id', 'desc')
                        ->first();

                    if (!$subscription || !$subscription->plan || $subscription->plan->duration_type === 'per_agreement') {
                        return 'No Plan';
                    }

                    return '<a href="#" class="view-plan-trigger fw-bold text-primary text-decoration-none" data-id="' . $subscription->id . '">' . e($subscription->plan->name) . '</a>';
                })
                ->addColumn('plan_status', function ($row) {
                    $subscription = \App\Models\CustomerSubscription::with('plan')
                        ->where('customer_id', $row->id)
                        ->orderBy('id', 'desc')
                        ->first();

                    if (!$subscription || !$subscription->plan || $subscription->plan->duration_type === 'per_agreement') {
                        return '<span class="badge bg-secondary">No Plan</span>';
                    }

                    $status = 'No Plan';
                    if ($subscription->plan->duration_type === 'lifetime') {
                        $status = 'Lifetime';
                    } elseif ($subscription->end_date) {
                        $expiry = \Carbon\Carbon::parse($subscription->end_date)->startOfDay();
                        if ($expiry->gte(\Carbon\Carbon::today())) {
                            $status = 'Active';
                        } else {
                            $status = 'Expired';
                        }
                    } else {
                        $status = 'Active';
                    }

                    if ($status === 'Active') {
                        return '<span class="badge bg-success">Active</span>';
                    } elseif ($status === 'Expired') {
                        return '<span class="badge bg-danger">Expired</span>';
                    } elseif ($status === 'Lifetime') {
                        return '<span class="badge" style="background-color: #6f42c1; color: white;">Lifetime</span>';
                    }

                    return '<span class="badge bg-secondary">No Plan</span>';
                })
                ->addColumn('is_active', function ($row) {
                    $csrfToken = csrf_token();
                    $route = route('customers.status_changes', $row->id);
                    $buttonClass = $row->is_active ? 'danger' : 'success';
                    $buttonText = $row->is_active ? 'Suspend' : 'Activate';
                    $newStatus = $row->is_active ? 0 : 1;
 
                    return '<div class="text-center">
                        <form action="' . $route . '" method="POST" style="display:inline;">
                            <input type="hidden" name="_token" value="' . $csrfToken . '">
                            <input type="hidden" name="_method" value="PATCH">
                            <button type="submit" class="btn btn-' . $buttonClass . ' btn-sm">
                                ' . $buttonText . '
                            </button>
                            <input type="hidden" name="status" value="' . $newStatus . '">
                        </form>
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('customers.edit', $row->id);
                    $deleteUrl = route('customers.destroy', $row->id);
                    $csrfToken = csrf_token();
 
                    return '<div class="text-center">
                        <a href="' . $editUrl . '" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i></a>
                        <a data-bs-toggle="modal" href="#delete_modal_' . $row->id . '" class="btn btn-danger btn-sm" title="Delete">
                            <i class="bi bi-trash"></i>
                        </a>
                        <div id="delete_modal_' . $row->id . '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Confirmation</h4>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete this item? This action cannot be undone.</p>
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
                ->rawColumns(['name', 'plan', 'plan_status', 'is_active', 'action'])
                ->make(true);
        }

        $plans = \App\Models\SubscriptionPlan::where('is_active', true)
            ->where('duration_type', '!=', 'per_agreement')
            ->get();
        return view('admin.customers.index', compact('plans'));
    }



    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'city_id' => 'nullable|string|max:255',
            'state_id' => 'nullable|string|max:255',
            'country_id' => 'nullable|string|max:255',
            'per_city_id' => 'nullable|string|max:255',
            'per_state_id' => 'nullable|string|max:255',
            'per_country_id' => 'nullable|string|max:255',
            'person_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aadhaar_card' => 'nullable|string|max:255',
            'is_aadhaar_verify' => 'nullable|boolean',
            'aadhaar_card_all_column' => 'nullable|string',
            'is_active' => 'required|boolean',
            'is_payment_details_configured' => 'nullable|boolean',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'ifsc' => 'nullable|string|max:11',
            'account_type' => 'nullable|string|max:50',
            'upi_id' => 'nullable|string|max:255',
            'upi_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'last_lat' => 'nullable|numeric',
            'last_lon' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $cityID = $request->has('city_id') ? City::where('city', $request->city_id)->value('id') : null;
            $stateID = $request->has('state_id') ? State::where('name', $request->state_id)->value('id') : null;
            $countryID = $request->has('country_id') ? Country::where('name', $request->country_id)->value('id') : null;
            $permanentCityID = $request->has('per_city_id') ? City::where('city', $request->per_city_id)->value('id') : null;
            $permanentStateID = $request->has('per_state_id') ? State::where('name', $request->per_state_id)->value('id') : null;
            $permanentCountryID = $request->has('per_country_id') ? Country::where('name', $request->per_country_id)->value('id') : null;

            $personImage = $request->file('person_image') ? $this->image_resize($request->file('person_image'), 'person_images') : null;
            $upiImage = $request->file('upi_image') ? $this->image_resize($request->file('upi_image'), 'upi_images') : null;

            $customerData = $request->only([
                'name',
                'mobile',
                'email',
                'address',
                'per_address',
                'aadhaar_card',
                'is_aadhaar_verify',
                'aadhaar_card_all_column',
                'is_active',
                'is_payment_details_configured',
                'bank_name',
                'account_number',
                'ifsc',
                'account_type',
                'upi_id',
                'last_lat',
                'last_lon'
            ]);

            $customerData['city_id'] = $cityID;
            $customerData['state_id'] = $stateID;
            $customerData['country_id'] = $countryID;
            $customerData['per_city_id'] = $permanentCityID;
            $customerData['per_state_id'] = $permanentStateID;
            $customerData['per_country_id'] = $permanentCountryID;
            $customerData['person_image'] = $personImage;
            $customerData['upi_image'] = $upiImage;

            Customer::create($customerData);

            return redirect()->route('customers.index')->with('success', 'Customer created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create customer.')->withInput();
        }
    }

    /**
     * Display the specified customer resource.
     */
    public function show(string $id)
    {
        try {
            $customer = Customer::findOrFail($id);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $customer
                ]);
            }

            return view('admin.customers.show', [
                'customer' => $customer
            ]);
        } catch (ModelNotFoundException $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
            }
            return redirect()->route('customers.index')->with('error', 'Customer not found.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to retrieve customer.'], 500);
            }
            return redirect()->route('customers.index')->with('error', 'Failed to retrieve customer.');
        }
    }

    /**
     * Show the form for editing the specified customer.
     */
    // public function edit(string $id)
    // {
    //     try {
    //         $customer = Customer::findOrFail($id);

    //         return view('admin.customers.edit', [
    //             'customer' => $customer
    //         ]);
    //     } catch (ModelNotFoundException $e) {
    //         return redirect()->route('customers.index')->with('error', 'Customer not found.');
    //     } catch (\Exception $e) {
    //         return redirect()->route('customers.index')->with('error', 'Failed to retrieve customer.');
    //     }
    // }

    public function edit(string $id)
    {
        try {
            $customer = Customer::findOrFail($id);

            // dd($customer);
            // Assuming you have City, State, and Country models with a relationship defined
            $cities = City::pluck('city', 'id');
            $states = State::pluck('name', 'id');
            $countries = Country::pluck('name', 'id');

            return view('admin.customers.edit', [
                'customer' => $customer,
                'cities' => $cities,
                'states' => $states,
                'countries' => $countries,
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('customers.index')->with('error', 'Customer not found.');
        } catch (\Exception $e) {
            return redirect()->route('customers.index')->with('error', 'Failed to retrieve customer.');
        }
    }


    /**
     * Update the specified customer resource.
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'city_id' => 'nullable|string|max:255',
            'state_id' => 'nullable|string|max:255',
            'country_id' => 'nullable|string|max:255',
            'per_city_id' => 'nullable|string|max:255',
            'per_state_id' => 'nullable|string|max:255',
            'per_country_id' => 'nullable|string|max:255',
            'person_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aadhaar_card' => 'nullable|string|max:255',
            'is_aadhaar_verify' => 'nullable|boolean',
            'aadhaar_card_all_column' => 'nullable|string',
            'is_active' => 'required|boolean',
            'is_payment_details_configured' => 'nullable|boolean',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'ifsc' => 'nullable|string|max:11',
            'account_type' => 'nullable|string|max:50',
            'upi_id' => 'nullable|string|max:255',
            'upi_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'last_lat' => 'nullable|numeric',
            'last_lon' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $customer = Customer::findOrFail($id);

            // $cityID = $request->has('city_id') ? City::where('city', $request->city_id)->value('id') : $customer->city_id;
            // $stateID = $request->has('state_id') ? State::where('name', $request->state_id)->value('id') : $customer->state_id;
            // $countryID = $request->has('country_id') ? Country::where('name', $request->country_id)->value('id') : $customer->country_id;
            // $permanentCityID = $request->has('per_city_id') ? City::where('city', $request->per_city_id)->value('id') : $customer->per_city_id;
            // $permanentStateID = $request->has('per_state_id') ? State::where('name', $request->per_state_id)->value('id') : $customer->per_state_id;
            // $permanentCountryID = $request->has('per_country_id') ? Country::where('name', $request->per_country_id)->value('id') : $customer->per_country_id;

            $personImage = $request->file('person_image') ? $this->image_resize($request->file('person_image'), 'person_images') : $customer->person_image;
            $upiImage = $request->file('upi_image') ? $this->image_resize($request->file('upi_image'), 'upi_images') : $customer->upi_image;

            $customerData = $request->only([
                'name',
                'mobile',
                'email',
                'address',
                'city_id',
                'state_id',
                'country_id',
                'per_address',
                'per_city_id',
                'per_state_id',
                'per_country_id',
                'aadhaar_card',
                'is_aadhaar_verify',
                'aadhaar_card_all_column',
                'is_active',
                'is_payment_details_configured',
                'bank_name',
                'account_number',
                'ifsc',
                'account_type',
                'upi_id',
                'last_lat',
                'last_lon'
            ]);

            // $customerData['city_id'] = $cityID;
            // $customerData['state_id'] = $stateID;
            // $customerData['country_id'] = $countryID;
            // $customerData['per_city_id'] = $permanentCityID;
            // $customerData['per_state_id'] = $permanentStateID;
            // $customerData['per_country_id'] = $permanentCountryID;
            $customerData['person_image'] = $personImage;
            $customerData['upi_image'] = $upiImage;

            $customer->update($customerData);

            return redirect()->route('customers.index')->with('success', 'Customer updated successfully!');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('customers.index')->with('error', 'Customer not found.');
        } catch (\Exception $e) {
            return redirect()->route('customers.index')->with('error', 'Failed to update customer.');
        }
    }

    /**
     * Remove the specified customer resource.
     */
    public function destroy(string $id)
    {
        try {
            $customer = Customer::findOrFail($id);

            $customer->delete();

            return redirect()->route('customers.index')->with('success', 'Customer deleted successfully!');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('customers.index')->with('error', 'Customer not found.');
        } catch (\Exception $e) {
            return redirect()->route('customers.index')->with('error', 'Failed to delete customer.');
        }
    }

    /**
     * Display a listing of all active customers.
     */
    // public function activeCustomers()
    // {
    //     try {
    //         $customers = Customer::where('is_active', true)->get();

    //         return view('admin.customers.active', [
    //             'customers' => $customers
    //         ]);
    //     } catch (\Exception $e) {
    //         return redirect()->route('customers.index')->with('error', 'Failed to retrieve active customers.');
    //     }
    // }

    public function status_changes(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1', // Ensure the status is either 0 or 1
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Find the customer by ID
        $customer = Customer::findOrFail($id);

        // Update the status based on the provided value
        $newStatus = (int) $request->input('status');
        $customer->is_active = $newStatus;
        $customer->save();  // Save the model to persist the changes in the database

        // If customer is suspended (status = 0), send email and push notification
        if ($newStatus === 0) {
            // Send Email notification
            if ($customer->email) {
                try {
                    $mailDriver = setting('mail_driver');
                    // dd(setting('mail_password'));
                    if ($mailDriver) {
                        config([
                            'mail.default' => $mailDriver,
                            'mail.mailers.' . $mailDriver . '.transport' => $mailDriver,
                            'mail.mailers.' . $mailDriver . '.host' => setting('mail_host'),
                            'mail.mailers.' . $mailDriver . '.port' => setting('mail_port'),
                            'mail.mailers.' . $mailDriver . '.username' => setting('mail_username'),
                            'mail.mailers.' . $mailDriver . '.password' => setting('mail_password'),
                            'mail.mailers.' . $mailDriver . '.encryption' => setting('mail_encryption'),
                            'mail.from.address' => setting('mail_from_email'),
                            'mail.from.name' => setting('mail_from_name'),
                        ]);
                    }

                    \Illuminate\Support\Facades\Mail::raw("Dear {$customer->name},\n\nYour account on Fast Agreements has been suspended. Please contact admin for support.", function ($message) use ($customer) {
                        $message->to($customer->email)
                            ->subject('Account Suspended - Fast Agreements');
                    });
                } catch (\Exception $e) {

                    \Illuminate\Support\Facades\Log::error('Failed to send suspension email to ' . $customer->email . ': ' . $e->getMessage());
                }
            }

            // Send Push notification
            if ($customer->fcm_token) {
                send_push_notification(
                    $customer->fcm_token,
                    'Account Suspended',
                    'Your account has been suspended by the administrator.'
                );
            }
        }

        $msg = $newStatus === 0 ? 'Customer has been suspended successfully!' : 'Customer has been activated successfully!';
        return redirect()->back()->with('success', $msg);
    }

    public function getSubscriptionDetails($id)
    {
        try {
            $subscription = \App\Models\CustomerSubscription::with(['plan', 'invoice'])->findOrFail($id);
            $customer = \App\Models\Customer::find($subscription->customer_id);

            // Determine status
            $status = 'No Plan';
            if ($subscription->plan && $subscription->plan->duration_type === 'lifetime') {
                $status = 'Lifetime';
            } elseif ($subscription->end_date) {
                $expiry = \Carbon\Carbon::parse($subscription->end_date)->startOfDay();
                if ($expiry->gte(\Carbon\Carbon::today())) {
                    $status = 'Active';
                } else {
                    $status = 'Expired';
                }
            } else {
                if ($subscription->plan && $subscription->plan->duration_type === 'per_agreement') {
                    if ($subscription->remaining_agreements > 0) {
                        $status = 'Active';
                    } else {
                        $status = 'Expired';
                    }
                } else {
                    $status = 'Active';
                }
            }

            // Calculate remaining days
            $remainingDays = 'N/A';
            if ($subscription->plan && $subscription->plan->duration_type !== 'lifetime' && $subscription->end_date) {
                $today = \Carbon\Carbon::today();
                $expiry = \Carbon\Carbon::parse($subscription->end_date)->startOfDay();
                if ($expiry->gte($today)) {
                    $remainingDays = $today->diffInDays($expiry) . ' Days';
                } else {
                    $remainingDays = 'Expired';
                }
            }

            // Calculate duration and validity
            $durationType = $subscription->plan->duration_type ?? 'N/A';
            $durationValue = $subscription->plan->duration_value ?? '';
            $duration = trim($durationValue . ' ' . ucfirst($durationType));
            
            $validity = 'N/A';
            if ($subscription->plan) {
                if ($subscription->plan->duration_type === 'lifetime') {
                    $validity = 'Lifetime';
                } elseif ($subscription->plan->validity_days) {
                    $validity = $subscription->plan->validity_days . ' Days';
                } elseif ($subscription->end_date && $subscription->start_date) {
                    $start = \Carbon\Carbon::parse($subscription->start_date);
                    $end = \Carbon\Carbon::parse($subscription->end_date);
                    $validity = $start->diffInDays($end) . ' Days';
                }
            }

            $data = [
                'customer_name' => $customer->name ?? 'N/A',
                'plan_name' => $subscription->plan->name ?? 'N/A',
                'plan_type' => $subscription->plan->duration_type ?? 'N/A',
                'plan_price' => $subscription->invoice->amount ?? $subscription->plan->price ?? 'N/A',
                'plan_duration' => $duration,
                'purchase_date' => ($subscription->invoice && $subscription->invoice->invoice_date) ? $subscription->invoice->invoice_date->format('Y-m-d') : ($subscription->created_at ? $subscription->created_at->format('Y-m-d') : 'N/A'),
                'activation_date' => $subscription->start_date ? \Carbon\Carbon::parse($subscription->start_date)->format('Y-m-d') : 'N/A',
                'expiry_date' => ($subscription->plan && $subscription->plan->duration_type === 'lifetime') ? 'N/A' : ($subscription->end_date ? \Carbon\Carbon::parse($subscription->end_date)->format('Y-m-d') : 'N/A'),
                'validity' => $validity,
                'remaining_days' => $remainingDays,
                'status' => $status,
                'payment_status' => $subscription->invoice->payment_status ?? 'Paid',
                'transaction_id' => $subscription->invoice->invoice_number ?? 'N/A',
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
