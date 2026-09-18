<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\api\BaseController;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CustomerController extends BaseController
{
    public function getUserByMobile(Request $request): JsonResponse
    {
        $mobile = $request->input('mobile_number');

        $user = User::where('mobile', $mobile)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'This mobile number is not registered',
            ]);
        }

        // Check if user account is suspended
        if (isset($user->status) && $user->status == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Your account has been suspended by the administrator. Please contact support.',
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Customer found',
            'data' => [
                // 'id' => $user->id,
                'name' => $user->name,
                'mobile' => $user->mobile,
                // 'email' => $user->email,
                // 'address' => $user->address,
                // 'company_name' => $user->company_name,
                // 'gst_number' => $user->gst_number,
                // 'location' => $user->location,
                // 'signature' => $user->signature,
                // 'occupation' => $user->occupation,
                // 'date_of_birth' => $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : null,
                // 'gender' => $user->gender,
                // 'photo_url' => $user->photo_url,
                // 'created_at' => $user->created_at,
                // 'updated_at' => $user->updated_at,
            ]
        ]);
    }
    /**
     * V2 API to fetch user (customer) details using a mobile number.
     * Searches the users table instead of customers table.
     */
    public function getCustomerByMobile(Request $request): JsonResponse
    {
        $mobile = $request->input('mobile_number');
        $passedUserId = $request->input('user_id');

        // 1. Try finding in Users table first
        $user = User::where('mobile', $mobile)->first();

        if ($user) {
            // Check if user account is suspended
            if (isset($user->status) && $user->status == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has been suspended by the administrator. Please contact support.',
                ]);
            }

            // Same User Check (Only check if user_id is provided)
            if ($request->filled('user_id') && $passedUserId == $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Same User not allowed',
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Customer found',
                'data' => [
                    'id' => $user->id,
                    'user_type' => 'user',
                    'name' => $user->name,
                    'mobile' => $user->mobile,
                    'email' => $user->email,
                    'address' => $user->address,
                    'company_name' => $user->company_name,
                    'gst_number' => $user->gst_number,
                    'location' => $user->location,
                    'signature' => $user->signature,
                    'occupation' => $user->occupation,
                    'date_of_birth' => $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : null,
                    'gender' => $user->gender,
                    'photo_url' => $user->photo_url,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ]);
        }

        // 2. Fallback to Customers table if not found in Users
        $customer = Customer::where('mobile', $mobile)->first();

        if ($customer) {
            if ($customer->is_active == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has been suspended by the administrator. Please contact support.',
                ]);
            }

            // Same User Check (Only check if user_id is provided)
            if ($request->filled('user_id') && $passedUserId == $customer->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Same User not allowed',
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Customer found',
                'data' => [
                    'id' => $customer->id,
                    'user_type' => 'customer',
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
                    'photo_url' => $customer->photo ? asset('uploads/customers/' . $customer->photo) : null,
                    'created_at' => $customer->created_at,
                    'updated_at' => $customer->updated_at,
                ]
            ]);
        }

        // 3. Not found anywhere
        return response()->json([
            'status' => false,
            'message' => 'This mobile number is not registered',
        ]);
    }

    public function getCustomerByMobileOld(Request $request): JsonResponse
    {
        $mobile = $request->input('mobile_number');
        $passedUserId = $request->input('user_id');

        // 1. Try finding in Users table
        $user = User::where('mobile', $mobile)->first();

        if ($user) {
            // Check if user account is suspended
            if (isset($user->status) && $user->status == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has been suspended by the administrator. Please contact support.',
                ]);
            }

            // Same User Check
            if ($passedUserId && $passedUserId == $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Same User not allowed',
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Customer found',
                'data' => [
                    'id' => $user->id,
                    'user_type' => 'user',
                    'name' => $user->name,
                    'mobile' => $user->mobile,
                    'email' => $user->email,
                    'address' => $user->address,
                    'company_name' => $user->company_name,
                    'gst_number' => $user->gst_number,
                    'location' => $user->location,
                    'signature' => $user->signature,
                    'occupation' => $user->occupation,
                    'date_of_birth' => $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : null,
                    'gender' => $user->gender,
                    'photo_url' => $user->photo_url,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ]);
        }

        // 2. Fallback to Customers table
        $customer = Customer::where('mobile', $mobile)->first();

        if ($customer) {
            if ($customer->is_active == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has been suspended by the administrator. Please contact support.',
                ]);
            }

            // Same User Check
            if ($passedUserId && $passedUserId == $customer->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Same User not allowed',
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Customer found',
                'data' => [
                    'id' => $customer->id,
                    'user_type' => 'customer',
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
                    'photo_url' => $customer->photo ? asset('uploads/customers/' . $customer->photo) : null,
                    'created_at' => $customer->created_at,
                    'updated_at' => $customer->updated_at,
                ]
            ]);
        }

        // 3. Not found anywhere
        return response()->json([
            'status' => false,
            'message' => 'This mobile number is not registered',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error: ' . $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 400);
        }

        // Check if customer already exists
        $existing = Customer::where('mobile', $request->mobile)->first();
        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'Customer with this mobile number already exists.',
                'data' => $existing
            ], 400);
        }

        $customer = new Customer();
        $fields = [
            'name',
            'mobile',
            'email',
            'address',
            'company_name',
            'gst_number',
            'location',
            'occupation',
            'gender',
            'date_of_birth'
        ];

        foreach ($fields as $field) {
            if ($request->has($field) && $request->filled($field)) {
                $customer->$field = $request->$field;
            }
        }

        $customer->save();

        return response()->json([
            'status' => true,
            'message' => 'Customer created successfully',
            'data' => $customer
        ]);
    }

    /**
     * Update the specified resource in storage.
     * In V2, this gracefully handles if a 'User' ID is passed by skipping the update
     * but returning a success response so the frontend flow doesn't break.
     */
    public function update(Request $request)
    {
        try {
            // Check for the correct spelling, but fallback to the typo if the mobile app has it hardcoded
            $id = $request->input('customer_id') ?? $request->input('costomer_id') ?? $request->input('id');

            if (empty($id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer ID is required in the request payload.',
                ], 200);
            }

            $customer = Customer::find($id);

            if (!$customer) {
                // Check if it's actually a user ID to prevent 404 crash
                $user = User::find($id);
                if ($user) {
                    return response()->json([
                        'status' => true,
                        'message' => 'User profile update skipped.',
                        'data' => [
                            'id' => $user->id,
                            'user_type' => 'user',
                            'name' => $user->name,
                            'mobile' => $user->mobile,
                        ]
                    ], 200);
                }

                return response()->json([
                    'status' => false,
                    'message' => 'Customer not found.',
                    'error' => 'The specified customer ID does not exist.'
                ], 404);
            }

            // Process file uploads (Commented out per request)
            // $personImage = $request->file('photo') ? app('App\Traits\ImageResizer')->image_resize($request->file('photo'), 'person_images') : null;
            // $upiImage = $request->file('upi_image') ? app('App\Traits\ImageResizer')->image_resize($request->file('upi_image'), 'upi_images') : null;

            // Prepare data for updating the customer record
            $customerData = $request->only([
                'name',
                // 'mobile',
                // 'email',
                'address',
                'per_address',
                // 'aadhaar_card',
                // 'is_aadhaar_verify',
                // 'aadhaar_card_all_column',
                // 'is_active',
                // 'is_payment_details_configured',
                // 'bank_name',
                // 'account_number',
                // 'ifsc',
                // 'account_type',
                // 'upi_id',
                // 'last_lat',
                // 'last_lon',
                // 'signature',
                // 'date_of_birth',
                // 'gender',
                'occupation'
            ]);

            // Add IDs for city, state, and country if passed (Commented out)
            // if ($request->has('city_id'))
            //     $customerData['city_id'] = \App\Models\City::where('city', $request->city_id)->value('id') ?? $customer->city_id;
            // if ($request->has('state_id'))
            //     $customerData['state_id'] = \App\Models\State::where('name', $request->state_id)->value('id') ?? $customer->state_id;
            // if ($request->has('country_id'))
            //     $customerData['country_id'] = \App\Models\Country::where('name', $request->country_id)->value('id') ?? $customer->country_id;

            // if ($personImage)
            //     $customerData['person_image'] = $personImage;
            // if ($upiImage)
            //     $customerData['upi_image'] = $upiImage;

            // Update the customer record
            $customer->update($customerData);

            return response()->json([
                'status' => true,
                'message' => 'Customer Updated Successfully',
                'data' => [
                    'id' => $customer->id,
                    'user_type' => 'customer',
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
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update customer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
