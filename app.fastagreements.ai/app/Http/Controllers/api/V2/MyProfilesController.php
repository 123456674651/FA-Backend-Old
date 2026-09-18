<?php

namespace App\Http\Controllers\api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use App\Models\SubscriptionInvoice;
use App\Models\CustomerSubscription;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\ImageResizer;
use App\Models\LegalNotice;
use App\Http\Resources\LegalNoticeResource;

class MyProfilesController extends Controller
{
    use ImageResizer;

    public function show(Request $request)
    {
        $id = $request->user()->id;
        // $subscriptionStatus = $this->status($id);

        try {
            // Attempt to find the user by ID
            $user = User::findOrFail($id);

            // Fetch related city, state, and country details
            // $currentCity = $user->city_id ? City::find($user->city_id) : null;
            // $currentState = $user->state_id ? State::find($user->state_id) : null;
            // $currentCountry = $user->country_id ? Country::find($user->country_id) : null;

            // Fetch permanent city, state, and country details
            // $permanentCity = $user->per_city_id ? City::find($user->per_city_id) : null;
            // $permanentState = $user->per_state_id ? State::find($user->per_state_id) : null;
            // $permanentCountry = $user->per_country_id ? Country::find($user->per_country_id) : null;
            // $customer_invoice = SubscriptionInvoice::with('agreement.party2')->where('customer_id', $id)->get();

            // Prepare the response data
            $response = [
                'status' => true,
                'message' => 'Profile Page Successfully.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'mobile' => $user->mobile,
                    'email' => $user->email,
                    'address' => $user->address,
                    // 'company_name' => $user->company_name,
                    // 'gst_number' => $user->gst_number,
                    'location' => $user->location,
                    'signature' => $user->signature,
                    'occupation' => $user->occupation,
                    'date_of_birth' => $user->date_of_birth,
                    'gender' => $user->gender,
                    'photo_url' => $user->photo_url,
                    // 'created_at' => $user->created_at,
                    // 'updated_at' => $user->updated_at,
                    // 'activeSubscription' => $user->activeSubscription,
                    // 'subscription_status' => $subscriptionStatus->original ?? $subscriptionStatus,
                    // 'customer_invoice' => $customer_invoice ?? [],
                    'allow_prompt' => $user->allow_prompt,
                ]
            ];

            // Return a JSON response with the user data and related details
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

    public function update(Request $request)
    {
        try {
            $user = $request->user();

            // Process file upload for profile photo
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $photoName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/profiles'), $photoName);
                $user->profile_picture = $photoName;
            }

            // Strictly update ONLY the fields shown in the UI
            // Mobile number is explicitly excluded
            $userData = $request->only([
                'name',
                'address',
                'occupation',
                'gender',
                'date_of_birth',
                'signature'
            ]);

            // Update the user record
            $user->update($userData);

            return response()->json([
                'status' => true,
                'message' => 'Profile Updated Successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'mobile' => $user->mobile,
                    'address' => $user->address,
                    'gender' => $user->gender,
                    'occupation' => $user->occupation,
                    'date_of_birth' => $user->date_of_birth,
                    'signature' => $user->signature,
                    'photo_url' => $user->photo ? asset($user->photo) : null,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update profile.',
                'error' => $e->getMessage()
            ], 500);
        }
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
        $end = Carbon::parse($subscription->end_date);

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

    public function legalNotices(Request $request)
    {
        try {
            $id = $request->user()->id;
            $notices = LegalNotice::where('user_id', $id)->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => true,
                'message' => 'Legal Notices fetched successfully.',
                'data' => LegalNoticeResource::collection($notices)
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Legal Notices not found.',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function updateAllowPrompt(Request $request)
    {
        $request->validate([
            'allow_prompt' => 'required|boolean',
        ]);

        $user = $request->user();
        $user->allow_prompt = $request->boolean('allow_prompt');
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Allow prompt updated successfully.',
            'data' => [
                'customer_id' => $user->id,
                'allow_prompt' => $user->allow_prompt,
            ]
        ], 200);
    }
}
