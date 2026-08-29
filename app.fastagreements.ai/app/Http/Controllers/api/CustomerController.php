<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\State;
use App\Traits\ImageResizer;
use App\Models\Country;
use App\Models\City;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\SubscriptionInvoice;
use Carbon\Carbon;
use App\Models\CustomerSubscription;
use App\Models\Feed;
use App\Services\Auth\FirebaseIdTokenVerifier;
use App\Services\Auth\JwtService;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Log;



class CustomerController extends Controller
{
    use ImageResizer;

    public function __construct(
        private readonly FirebaseIdTokenVerifier $firebase,
        private readonly JwtService $jwt,
    ) {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Retrieve all active customers from the database
            $customers = Customer::where('is_active', true)->get();

            // Check if any customers are found
            if ($customers->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No active customers found.',
                    'data' => null
                ], 404); // 404 Not Found
            }

            // Transform the customers data
            $customersWithDetails = $customers->map(function ($customer) {
                // Fetch related city, state, and country details
                $currentCity = $customer->city_id ? City::find($customer->city_id) : null;
                $currentState = $customer->state_id ? State::find($customer->state_id) : null;
                $currentCountry = $customer->country_id ? Country::find($customer->country_id) : null;

                // Fetch permanent city, state, and country details
                $permanentCity = $customer->per_city_id ? City::find($customer->per_city_id) : null;
                $permanentState = $customer->per_state_id ? State::find($customer->per_state_id) : null;
                $permanentCountry = $customer->per_country_id ? Country::find($customer->per_country_id) : null;

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'mobile' => $customer->mobile,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'city' => $currentCity ? $currentCity->city : 'City not found',
                    'state' => $currentState ? $currentState->name : 'State not found',
                    'country' => $currentCountry ? $currentCountry->name : 'Country not found',
                    'per_address' => $customer->per_address,
                    'per_city' => $permanentCity ? $permanentCity->city : 'Permanent city not found',
                    'per_state' => $permanentState ? $permanentState->name : 'Permanent state not found',
                    'per_country' => $permanentCountry ? $permanentCountry->name : 'Permanent country not found',
                    // 'person_image' => $customer->person_image,
                    'person_image' => $customer->person_image_url,
                    'aadhaar_card' => $customer->aadhaar_card,
                    'is_aadhaar_verify' => (int) $customer->is_aadhaar_verify,
                    'aadhaar_card_all_column' => $customer->aadhaar_card_all_column,
                    'is_active' => (int) $customer->is_active,
                    // 'is_payment_details_configured' => $customer->is_payment_details_configured,
                    'is_payment_details_configured' => isset($customer->is_payment_details_configured) ? (int) $customer->is_payment_details_configured : 0,
                    'bank_name' => $customer->bank_name,
                    'account_number' => $customer->account_number,
                    'ifsc' => $customer->ifsc,
                    'account_type' => $customer->account_type,
                    'upi_id' => $customer->upi_id,
                    // 'upi_image' => $customer->upi_image,
                    'upi_image' => $customer->upi_image_url,
                    'last_lat' => $customer->last_lat,
                    'last_lon' => $customer->last_lon,
                    'signature' => $customer->signature,
                    'photo' => $customer->photo,
                    'created_at' => $customer->created_at,
                    'updated_at' => $customer->updated_at,
                ];
            });

            // Return a JSON response with the customers data
            return response()->json([
                'status' => true,
                'message' => 'Customer list retrieved successfully.',
                'data' => $customersWithDetails
            ], 200); // 200 OK

        } catch (Exception $e) {

            // Return a JSON response with a generic error message
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving customers.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Store a newly created resource in storage.
     */

    public function registertion(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'mobile' => 'required|numeric|min:11|unique:customers',
            'email' => 'email|unique:customers',
            'address' => 'required|regex:/(^[-0-9A-Za-z.,\/ ]+$)/',
            'is_company' => 'required|boolean',
        ])->sometimes(
            ['company_name', 'gst_number'],
            'required|string|max:255',
            function ($input) {
                return $input->is_company == 1;
            }
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'error' => $validator->errors()
            ]);
        }
 

        $customer = new Customer();
        $customer->name = $request->name;
        $customer->mobile = $request->mobile;
        $customer->email = $request->email;
        $customer->address = $request->address;
        $customer->company_name = $request->company_name ?? null;
        $customer->gst_number = $request->gst_number ?? null;
        $customer->location = $request->location;
        $customer->signature = $request->signature;
        $customer->occupation = $request->occupation;
        $customer->date_of_birth = $request->date_of_birth;
        $customer->gender = $request->gender;
        $photoPath = null;

  if ($request->hasFile('photo')) {
        $file = $request->file('photo');
        $filename = 'customer_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('uploads/customers');
        $file->move($destinationPath, $filename);

        // Save path in DB (optional: prepend 'uploads/customers/' to make it accessible via URL)
        $customer->photo = 'uploads/customers/' . $filename;
    }
    $customer->save();
      
      
      Feed::create([
            'type'=> 'customer_joined',
            'customer_id'=> $customer->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Registered Successfully',
            'data' => $customer
        ]);
    }

    public function updateCustomer(Request $request, $id)
    {
       
       $customer = Customer::find($id);

    if (!$customer) {
        return response()->json([
            'status' => false,
            'message' => 'Customer not found'
        ]);
    }

    $customer->name = $request->name;
    $customer->mobile = $request->mobile;
    $customer->email = $request->email;
    $customer->address = $request->address;
    $customer->company_name = $request->company_name ?? null;
    $customer->gst_number = $request->gst_number ?? null;
    $customer->is_company = $request->is_company;
    $customer->occupation = $request->occupation;
    $customer->date_of_birth = $request->date_of_birth;
    $customer->gender = $request->gender;

    if ($request->hasFile('photo')) {

        // ensure directory exists
        $destinationPath = public_path('uploads/customers');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // delete old photo (if exists)
        if ($customer->photo && file_exists(public_path($customer->photo))) {
            unlink(public_path($customer->photo));
        }

        $file = $request->file('photo');
        $filename = 'customer_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($destinationPath, $filename);

        $customer->photo = 'uploads/customers/' . $filename;
    }

    $customer->save();

    return response()->json([
        'status' => true,
        'message' => 'Customer updated successfully',
        'data' => $customer
    ]);
    }


 public function store(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:15|unique:customers,mobile',
            'email' => 'nullable|email|max:255|unique:customers,email',
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
            'is_active' => 'nullable|boolean',
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

        // Return validation errors if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error.',
                'errors' => $validator->errors()
            ], 400); // 400 Bad Request
        }

        try {
            // Fetch IDs for city, state, and country if names are provided
            $cityID = $request->has('city_id') ? City::where('city', $request->city_id)->value('id') : null;
            $stateID = $request->has('state_id') ? State::where('name', $request->state_id)->value('id') : null;
            $countryID = $request->has('country_id') ? Country::where('name', $request->country_id)->value('id') : null;
            $permanentCityID = $request->has('per_city_id') ? City::where('city', $request->per_city_id)->value('id') : null;
            $permanentStateID = $request->has('per_state_id') ? State::where('name', $request->per_state_id)->value('id') : null;
            $permanentCountryID = $request->has('per_country_id') ? Country::where('name', $request->per_country_id)->value('id') : null;

            // Process file uploads and resizing



            $personImage = $request->hasFile('person_image')
                ? $this->image_resize($request->file('person_image'), 'person_images')
                :  'default.webp';


            $upiImage = $request->hasFile('upi_image')
                ? $this->image_resize($request->file('upi_image'), 'upi_image')
                : 'default.webp';


            // Prepare data for creating a new customer record
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
                'last_lon',
                'signature',
                'date_of_birth',
                'gender',
                'occupation',
            ]);

            // Add IDs for city, state, and country
            $customerData['city_id'] = $cityID;
            $customerData['state_id'] = $stateID;
            $customerData['country_id'] = $countryID;
            $customerData['per_city_id'] = $permanentCityID;
            $customerData['per_state_id'] = $permanentStateID;
            $customerData['per_country_id'] = $permanentCountryID;

            // Add file paths if available
            $customerData['person_image'] = $personImage;
            $customerData['upi_image'] = $upiImage;

            // Create a new customer record
            $customer = Customer::create($customerData);

            return response()->json([
                'status' => true,
                'message' => 'Customer Created Successfully',
                'data' => [
                   'id' => $customer->id,
                        'name' => $customer->name,
                        'mobile' => $customer->mobile,
                        'email' => $customer->email,
                        'address' => $customer->address,
                        'signature' => $customer->signature,
                        'gender' => $customer->gender,
                        'occupation' => $customer->occupation,
                        'date_of_birth' => $customer->date_of_birth,
                        'person_image' => $customer->person_image_url,

                ]
            ], 201); // 201 Created

        } catch (\Exception $e) {
            // Return a JSON response with a generic error message
            return response()->json([
                'status' => false,
                'message' => 'Failed to create customer.',
                'error' => 'An unexpected error occurred.'
            ], 500); // 500 Internal Server Error
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
                $subscriptionStatus = $this->status($id);
        try {
            // Attempt to find the customer by ID
            $customer = Customer::findOrFail($id);

            // Fetch related city, state, and country details
            $currentCity = $customer->city_id ? City::find($customer->city_id) : null;
            $currentState = $customer->state_id ? State::find($customer->state_id) : null;
            $currentCountry = $customer->country_id ? Country::find($customer->country_id) : null;

            // Fetch permanent city, state, and country details
            $permanentCity = $customer->per_city_id ? City::find($customer->per_city_id) : null;
            $permanentState = $customer->per_state_id ? State::find($customer->per_state_id) : null;
            $permanentCountry = $customer->per_country_id ? Country::find($customer->per_country_id) : null;
            $customer_invoice = SubscriptionInvoice::with('agreement.party2')->where('customer_id', $id)->get();
          
            // Prepare the response data
            $response = [
                'status' => true,
                'message' => 'Profile Page Successfully',
                 'data' => [
                      'id'             => $customer->id,
                      'name'           => $customer->name,
                      'mobile'         => $customer->mobile,
                      'email'          => $customer->email,
                      'address'        => $customer->address,
                      'company_name'   => $customer->company_name,
                      'gst_number'     => $customer->gst_number,
                      'location'       => $customer->location,
                      'signature'      => $customer->signature,
                      'occupation'     => $customer->occupation,
                      'date_of_birth'  => $customer->date_of_birth,
                      'gender'         => $customer->gender,
                      'photo_url'          => $customer->photo ? asset($customer->photo) : null,
                      'created_at' => $customer->created_at,
                      'updated_at' => $customer->updated_at,
                      'activeSubscription' => $customer->activeSubscription,
                      'subscription_status' => $subscriptionStatus,
                      'customer_invoice' => $customer_invoice,
                      'allow_prompt' => $customer->allow_prompt,
                   
                  ]
            ];

            // Return a JSON response with the customer data and related details
            return response()->json($response, 200); // 200 OK

        } catch (ModelNotFoundException $e) {
            // Return a JSON response if the customer is not found
            return response()->json([
                'status' => false,
                'message' => 'Customer not found.',
                'error' => $e->getMessage()
            ], 404); // 404 Not Found

        } catch (Exception $e) {

            // Return a JSON response with a generic error message
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving the customer.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request
      
        try {
            // Fetch the existing customer record
            $customer = Customer::findOrFail($id);

            // Fetch IDs for city, state, and country if names are provided
            $cityID = $request->has('city_id') ? City::where('city', $request->city_id)->value('id') : $customer->city_id;
            $stateID = $request->has('state_id') ? State::where('name', $request->state_id)->value('id') : $customer->state_id;
            $countryID = $request->has('country_id') ? Country::where('name', $request->country_id)->value('id') : $customer->country_id;
            $permanentCityID = $request->has('per_city_id') ? City::where('city', $request->per_city_id)->value('id') : $customer->per_city_id;
            $permanentStateID = $request->has('per_state_id') ? State::where('name', $request->per_state_id)->value('id') : $customer->per_state_id;
            $permanentCountryID = $request->has('per_country_id') ? Country::where('name', $request->per_country_id)->value('id') : $customer->per_country_id;

            // Process file uploads

            $personImage = $request->file('photo') ? $this->image_resize($request->file('photo'), 'person_images') : 'default.webp';

            $upiImage = $request->file('upi_image') ? $this->image_resize($request->file('upi_image'), 'upi_images') : 'default.webp';


            // Prepare data for updating the customer record
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
                'last_lon',
                'signature',
                'date_of_birth',
                'gender',
                'occupation'
            ]);

            // Add IDs for city, state, and country
            $customerData['city_id'] = $cityID;
            $customerData['state_id'] = $stateID;
            $customerData['country_id'] = $countryID;
            $customerData['per_city_id'] = $permanentCityID;
            $customerData['per_state_id'] = $permanentStateID;
            $customerData['per_country_id'] = $permanentCountryID;

            // Add file paths if available
            $customerData['person_image'] = $personImage;
            $customerData['upi_image'] = $upiImage;

            // Update the customer record
            $customer->update($customerData);

            return response()->json([
                'status' => true,
                'message' => 'Customer Updated Successfully',
                 'data' => [
                         'id' => $customer->id,
                        'name' => $customer->name,
                        'mobile' => $customer->mobile,
                        'email' => $customer->email,
                        'address' => $customer->address,
                        'signature' => $customer->signature,
                        'gender' => $customer->gender,
                        'occupation' => $customer->occupation,
                        'date_of_birth' => $customer->date_of_birth,
                        'person_image' => $customer->person_image_url,

                ]
            ], 200); // 200 OK

        } catch (ModelNotFoundException $e) {
            // Return a JSON response if the customer is not found
            return response()->json([
                'status' => false,
                'message' => 'Customer not found.',
                'error' => 'The specified customer ID does not exist.'
            ], 404); // 404 Not Found
        } catch (Exception $e) {
            // Return a JSON response with a generic error message
            return response()->json([
                'status' => false,
                'message' => 'Failed to update customer.',
                'error' => 'An unexpected error occurred.'
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $customer = Customer::findOrFail($id);

            // Soft delete the customer record
            $customer->delete();

            return response()->json([
                'status' => true,
                'message' => 'Customer Deleted Successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Customer not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete customer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Customer sign-in by mobile number, for an already-registered customer.
     *
     * This runs *before* the app has any session — the old version required
     * `$request->user()` and compared the looked-up customer against "the
     * current user", which cannot be right for a pre-login step; there is no
     * current user yet. It also trusted a plain `mobile_number` field, so
     * anyone could query anyone else's profile just by guessing a number.
     *
     * Now: Firebase runs phone verification (OTP) on the handset, the app
     * posts the resulting `id_token` here, and the mobile number used for the
     * lookup is the one inside that Google-signed token — never anything the
     * client typed. A rejected token is rendered as 401 by the global
     * exception handler (see bootstrap/app.php), so no try/catch is needed
     * here.
     *
     * Unlike `auth/firebase-exchange`, this does NOT auto-register a new
     * customer — it answers "log this existing customer in", and a number
     * with no account gets a 404 so the app can route to registration
     * instead. If you also want first-time numbers to be provisioned here,
     * say so and this can call the same auto-create path firebaseExchange
     * uses.
     *
     * TESTING ONLY — id_token bypass:
     * When FIREBASE_OTP_BYPASS=true in .env (default: false/absent), a
     * caller can send `test_mobile` instead of `id_token` and skip Firebase
     * verification entirely. This must NEVER be true on a production or
     * staging box reachable by real users — it lets anyone log in as any
     * customer just by knowing their number. If the flag is off (the
     * default), `test_mobile` is silently ignored and `id_token` is
     * required as normal, so this is safe to leave in the code as long as
     * the env var itself is never set to true outside local dev.
     */
    public function getCustomerByMobile(Request $request)
    {
        $bypass = config('apiauth.firebase.otp_bypass') && $request->filled('mobile_number');

        $request->validate([
            'id_token' => $bypass ? 'nullable|string' : 'required|string',
            'mobile_number' => 'nullable|string',
            'fcm_token' => 'nullable|string',
        ]);
        

        if ($bypass) {
            $mobile = FirebaseIdTokenVerifier::toStoredMobile($request->input('mobile_number'));
        } else {
            $identity = $this->firebase->verify($request->input('id_token'));
            $mobile = FirebaseIdTokenVerifier::toStoredMobile($identity['phone_number']);
        }

        $customer = Customer::where('mobile', $mobile)->first();

        if (!$customer) {
            return ApiResponse::error(404, 'CUSTOMER_NOT_FOUND', 'This mobile number is not registered');
        }

        if ((int) $customer->is_active === 0) {
            return ApiResponse::error(
                403,
                'ACCOUNT_SUSPENDED',
                'Your account has been suspended by the administrator. Please contact support.',
            );
        }

        if ($request->filled('fcm_token')) {
            $customer->update([
                'fcm_token' => $request->fcm_token,
            ]);
        }

        return ApiResponse::ok([
            'token' => $this->jwt->issueForCustomer((int) $customer->id),
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'mobile' => $customer->mobile,
                'email' => $customer->email,
                'address' => $customer->address,
                'company_name' => $customer->company_name,
                'gst_number' => $customer->gst_number,
                'location' => $customer->location,
                'signature' => $customer->signature,
                'occupation' => $customer->occupation,
                'date_of_birth' => $customer->date_of_birth,
                'gender' => $customer->gender,
                'photo_url' => $customer->photo
                    ? asset($customer->photo)
                    : null,
                'created_at' => $customer->created_at,
                'updated_at' => $customer->updated_at,
            ],
        ], 'Customer found');
    }

    public function upload_image(Request $request)
{
    $validator = Validator::make($request->all(), [
        'customer_id' => 'required|exists:customers,id',
        'photo' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation Error',
            'errors' => $validator->errors()
        ], 400);
    }

    $customer = Customer::findOrFail($request->customer_id);

    if ($request->hasFile('photo')) {
        $file = $request->file('photo');
        $filename = 'customer_' . uniqid() . '.' . $file->getClientOriginalExtension();

        $destinationPath = public_path('uploads/customers');

        // Create folder if it doesn't exist
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0775, true);
        }

        $file->move($destinationPath, $filename);

        // Save relative path
        $customer->photo = 'uploads/customers/' . $filename;
    }

    $customer->save();

    return response()->json([
        'status' => true,
        'message' => 'Customer Updated Successfully',
        'data' => $customer
    ], 200);
}
  
  
 public function status($customer_id)
    {
        $today = Carbon::today();


        $subscription = CustomerSubscription::where('customer_id', $customer_id)
            ->where('is_active', 1)
            ->orderBy('end_date', 'desc')
            ->first();

   /* ===============================
       CASE 1: NO SUBSCRIPTION FOUND
    ================================ */
    if (!$subscription) {
       return response()->json([
                'status' => 'inactive',
                'days_remaining' => null,
                'message' => 'You do not have a subscription',
                'start_date' => null,
                'end_date' => null,
                'is_expiring_soon' => null
            ]);
    }
        if ($subscription->remaining_agreements !== null) {

            if ($subscription->remaining_agreements <= 0) {
                return response()->json([
                    'status' => 'expired',
                    'message' => 'Agreement limit exhausted',
                    'remaining_agreements' => 0,
                ]);
            }

            return response()->json([
                'status' => 'active',
                'message' => 'Per agreement plan active',
                'remaining_agreements' => $subscription->remaining_agreements,
            ]);
        }


        if (!$subscription) {
            return response()->json([
                'status' => 'inactive',
                'message' => 'No active subscription found'
            ]);
        }

        $start = Carbon::parse($subscription->start_date);
        $end   = Carbon::parse($subscription->end_date);

        // Lifetime plan
        if (is_null($end)) {
            return response()->json([
                'status' => 'active',
                'days_remaining' => null,
                'message' => 'Lifetime subscription active',
                'start_date' => $start->toDateString(),
                'end_date' => null,
                'is_expiring_soon' => false,
            ]);
        }

        // Expired
        if ($today->gt($end)) {
            return response()->json([
                'status' => 'expired',
                'days_remaining' => 0,
                'message' => 'Your subscription has expired',
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'is_expiring_soon' => false
            ]);
        }

        // Not started yet
        if ($today->lt($start)) {
            return response()->json([
                'status' => 'inactive',
                'days_remaining' => $start->diffInDays($today),
                'message' => 'Subscription not started yet',
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'is_expiring_soon' => false
            ]);
        }

        // Active
        $daysRemaining = $today->diffInDays($end);
        $isExpiringSoon = $daysRemaining <= 7;

        return response()->json([
            'status' => 'active',
            'days_remaining' => $daysRemaining,
            'message' => $isExpiringSoon
                ? "Your subscription will expire in {$daysRemaining} days"
                : 'Your subscription is active',
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'is_expiring_soon' => $isExpiringSoon
        ]);
    }
  
  public function updateAllowPrompt(Request $request)
{
    $request->validate([
        'allow_prompt' => 'required|boolean',
    ]);

    // Taken from the verified session, never from the body: accepting a
    // customer_id here let any caller flip the flag on any account.
    $customer = $request->user();

    $customer->update([
        'allow_prompt' => $request->allow_prompt,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Allow prompt updated successfully.',
        'data' => [
            'customer_id' => $customer->id,
            'allow_prompt' => $customer->allow_prompt,
        ]
    ], 200);
}
}