<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Auth\FirebaseIdTokenVerifier;
use App\Services\Auth\JwtService;
use App\Support\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Customer sign-in.
 *
 * Replaces the old `verify_mobile` / `verify_mobile_otp` / `customer_register`
 * trio, which generated its own six-digit code, returned that code in the
 * response body, and issued no session at all. Here the code is sent and
 * checked by Firebase on the handset, and what reaches this server is a token
 * signed by Google that the app cannot fabricate.
 */
class AuthApiController extends Controller
{
    public function __construct(
        private readonly FirebaseIdTokenVerifier $firebase,
        private readonly JwtService $jwt,
    ) {
    }

    /**
     * Trades a verified Firebase ID token for a session token.
     *
     * Auto-provisions the customer on first sign-in, so a new user is not
     * bounced through a separate registration call before they have a session.
     * The response says whether the profile still needs filling in.
     */
    public function firebaseExchange(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);
    
        $identity = $this->firebase->verify(
            $request->input('id_token')
        );
    
        $mobile = FirebaseIdTokenVerifier::toStoredMobile(
            $identity['phone_number']
        );
    
        if (strlen($mobile) !== 10) {
            return ApiResponse::error(
                422,
                'PHONE_UNSUPPORTED',
                'The verified number is not a 10-digit mobile.',
            );
        }
    
        $customer = Customer::where('mobile', $mobile)->first();
        $isNew = false;
    
        if ($customer === null) {
            $customer = Customer::create([
                'mobile' => $mobile,
                'name' => '',
                'address' => '',
                'is_active' => 1,
            ]);
    
            $isNew = true;
        }
    
        if (!$customer->is_active) {
            return ApiResponse::error(
                403,
                'ACCOUNT_DISABLED',
                'This account has been disabled.'
            );
        }
    
        if ($request->filled('fcm_token')) {
            $customer->fcm_token = $request->input('fcm_token');
            $customer->save();
        }
    
        return ApiResponse::ok([
            'token' => $this->jwt->issueForCustomer(
                (int) $customer->id
            ),
            'is_new_customer' => $isNew,
            'profile_complete' => $this->profileIsComplete($customer),
            'customer' => $this->publicCustomer($customer),
        ], 'Signed in successfully.');
    }

    /**
     * Whether a number already has an account.
     *
     * The login screen asks before triggering an SMS, so an unregistered
     * caller finds out immediately rather than after Firebase has spent a
     * message. Returns a bare boolean on purpose — nothing about the account
     * leaks to an unauthenticated caller.
     */
    public function exists(Request $request): JsonResponse
    {
        $request->validate(['mobile' => 'required|string']);

        $mobile = FirebaseIdTokenVerifier::toStoredMobile($request->query('mobile', ''));

        return ApiResponse::ok([
            'exists' => strlen($mobile) === 10 && Customer::where('mobile', $mobile)->exists(),
        ]);
    }

    /** The signed-in customer. */
    public function me(Request $request): JsonResponse
    {
        $customer = $request->user();

        return ApiResponse::ok([
            'profile_complete' => $this->profileIsComplete($customer),
            'customer' => $this->publicCustomer($customer),
        ]);
    }

    /**
     * Completes or edits the signed-in customer's own profile.
     *
     * The mobile number is deliberately not editable here: it is what the
     * Firebase verification was bound to, and letting it be changed by a plain
     * PATCH would undo the proof.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $customer = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|nullable|email|max:255',
            'address' => 'sometimes|nullable|string|max:500',
            'city_id' => 'sometimes|nullable|integer',
            'state_id' => 'sometimes|nullable|integer',
            'country_id' => 'sometimes|nullable|integer',
            'occupation' => 'sometimes|nullable|string|max:255',
            'date_of_birth' => 'sometimes|nullable|date',
            'gender' => 'sometimes|nullable|string|max:20',
            'fcm_token' => 'sometimes|nullable|string',
        ]);

        if ($data === []) {
            throw ValidationException::withMessages(['name' => 'Nothing to update.']);
        }

        $customer->fill($data)->save();

        return ApiResponse::ok([
            'profile_complete' => $this->profileIsComplete($customer),
            'customer' => $this->publicCustomer($customer),
        ], 'Profile updated.');
    }

    private function profileIsComplete(Customer $customer): bool
    {
        return trim((string) $customer->name) !== '';
    }

    /** @return array<string, mixed> */
    private function publicCustomer(Customer $customer): array
    {
        return [
            'id' => (int) $customer->id,
            'name' => $customer->name,
            'mobile' => $customer->mobile,
            'email' => $customer->email,
            'address' => $customer->address,
            'person_image_url' => $customer->person_image_url,
            'is_active' => (bool) $customer->is_active,
        ];
    }
}
