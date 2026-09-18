<?php

namespace App\Http\Controllers\api\V2;

use App\Http\Controllers\api\V2\PhpWordController;
use Illuminate\Http\Request;
use App\Models\Aggriment;
use App\Models\Customer;
use App\Models\AgreementAttribute;
use App\Models\User;
use App\Models\PaymentHistory;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Services\AgreementEntitlementService;
use App\Services\AgreementOtpModeService;
use App\Services\PartyVerificationException;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Log;

class AgreementController extends PhpWordController
{
    public function save_agreement_step(Request $request)
    {
        $logData = $request->all();
        // If an uploaded file (e.g. Image) is present, extract its details (name, size, type) for logging.
        foreach ($logData as $key => $value) {
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                $logData[$key] = [
                    'is_file' => true,
                    'original_name' => $value->getClientOriginalName(),
                    'mime_type' => $value->getMimeType(),
                    'size_in_bytes' => $value->getSize(),
                ];
            }
        }

        try {
            // 1. Validation (Check if the input data is valid)
            $validator = Validator::make($request->all(), [
                'agreement_id' => 'nullable|exists:agreements,id',
                'party_1_id' => 'nullable',
                'party_2_id' => 'nullable|different:party_1_id',
                'party_1_user_type' => 'nullable|in:user,customer',
                'party_2_user_type' => 'nullable|in:user,customer',
                'progress_status' => 'nullable|in:DRAFT,COMPLETED',
                'step' => 'nullable|integer',
            ]);

            if ($validator->fails()) {

                $response = response()->json([
                    'status' => false,
                    'message' => 'Validation Fail: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 400);

                return $response;
            }

            // 2. Fetch or Create Agreement (Get from DB if exists, otherwise create new)
            $agreementId = $request->agreement_id;
            $agreement = $agreementId ? Aggriment::findOrFail($agreementId) : new Aggriment;

            // 3. Set Default Draft Status for New Agreements
            if (!$agreementId) {
                $agreement->progress_status = 'DRAFT';
                if ($request->user()) {
                    $agreement->user_id = $request->user()->id;
                }
            }

            // 4. Update Text Fields (Only update fields if a non-empty value is provided)
            // The names in this list match the database column names.
            $textFields = [
                'party_1_id',
                'party_2_id',
                'party_1_user_type',
                'party_2_user_type',
                'amount',
                'start_date',
                'end_date',
                'agreement_date',
                'agreement_type',
                'is_interest',
                'period',
                'repayment_term',
                'purpose',
                'note',
                'security',
                'address',
                'location',
                'reference_no',
                'reference_remark',
                'guarantor',
                'guarantor_number',
                'agreement_status',
                'party_1_age',
                'party_2_age',
                'party_1_business',
                'party_2_business',
                'party_1_signature',
                'party_2_signature',
                'aggriment_language_id',
                'category_id',
                'invoice_id',
                'otp_mode',
                'progress_status',
                'step'
            ];

            foreach ($textFields as $field) {
                // $request->filled() checks if the field exists and is not empty.
                // This prevents overwriting existing database values with empty strings.
                if ($request->filled($field)) {
                    $agreement->$field = $request->$field;
                }
            }

            if ($request->filled('sub_cat_id')) {
                $agreement->sub_category = $request->sub_cat_id;
            }

            // 5. Update Images (Only save the image if a new file is uploaded)
            $imageFields = [
                'party_one_image' => ['party_1_image', 'person_images'],
                'party_two_image' => ['party_2_image', 'person_images'],
                'party_1_adhar_front' => ['party_1_adhar_front', 'adhar_images'],
                'party_1_adhar_back' => ['party_1_adhar_back', 'adhar_images'],
                'party_2_adhar_front' => ['party_2_adhar_front', 'adhar_images'],
                'party_2_adhar_back' => ['party_2_adhar_back', 'adhar_images'],
                'vehicle_front_side' => ['vehicle_front_side', 'vehicle_images'],
                'vehicle_back_side' => ['vehicle_back_side', 'vehicle_images'],
                'vehicle_left_side' => ['vehicle_left_side', 'vehicle_images'],
                'vehicle_right_side' => ['vehicle_right_side', 'vehicle_images'],
            ];

            foreach ($imageFields as $reqKey => [$dbKey, $folder]) {
                if ($request->file($reqKey)) {
                    $agreement->$dbKey = $this->image_resize($request->file($reqKey), $folder);
                }
            }

            // 6. Save Agreement Data
            $agreement->save();

            // Update Customer (Party 1 & 2) Profiles
            if ($agreement->party_1_id) {
                $party1 = User::find($agreement->party_1_id) ?? Customer::find($agreement->party_1_id);
                if ($party1) {
                    $updated = false;
                    if ($request->filled('address')) {
                        $party1->address = $request->address;
                        $updated = true;
                    }
                    if ($request->filled('location')) {
                        $party1->location = $request->location;
                        $updated = true;
                    }
                    if ($request->filled('party_1_business')) {
                        $party1->occupation = $request->party_1_business;
                        $updated = true;
                    }
                    if ($updated) {
                        $party1->save();
                    }
                }
            }

            if ($agreement->party_2_id) {
                $party2 = User::find($agreement->party_2_id) ?? Customer::find($agreement->party_2_id);
                if ($party2) {
                    if ($request->filled('party_2_business')) {
                        $party2->occupation = $request->party_2_business;
                        $party2->save();
                    }
                }
            }

            // 7. Save Attributes if provided (e.g., extra dynamic fields)
            if ($request->filled('attribute')) {
                $this->syncAgreementAttributes($agreement, $request->attribute);
            }

            // 8. Handle Final Step (This runs when the final form is submitted with status COMPLETED)
            if ($agreement->progress_status === 'COMPLETED') {
                $agreement->save();

                $callerId = $request->user() ? $request->user()->id : null;

                $party1 = User::find($agreement->party_1_id) ?? Customer::find($agreement->party_1_id);
                $party2 = User::find($agreement->party_2_id) ?? Customer::find($agreement->party_2_id);

                // 8.1 - Security & Payment Checks (Verify OTP and check payment history)
                if ($callerId) {
                    $otpService = app(AgreementOtpModeService::class);

                    // Step A: Check if OTP is verified for all required parties
                    $requiredParties = $otpService->requiredForCreation($party1, $party2, $agreement->guarantor, $agreement->guarantor_number);
                    $otpService->assertVerifiedForCreation($callerId, $agreement->otp_mode, $requiredParties);

                    // Step B: Check if User has successfully paid for this agreement
                    $hasPaid = PaymentHistory::where('user_id', $callerId)
                        ->where('agreement_id', $agreement->id)
                        ->where('status', 'paid')
                        ->exists();

                    if (!$hasPaid) {
                        throw new PartyVerificationException(402, 'PAYMENT_REQUIRED', 'Payment for this agreement is not successful or not found.');
                    }

                    // Step C: Save proof of OTP verification
                    $otpService->snapshotForAgreement($agreement, $callerId, $requiredParties);
                }

                // 8.2 - Calculate EMI Installments (Generate EMI schedule and save to database)
                $this->rebuildInstallments($agreement);

                // 8.3 - Prepare Data for Document (Load related data to print on the agreement document)
                $agreement->load(['category', 'attributes.categoryAttribute']);
                $agreement->setRelation('party1', $party1);
                $agreement->setRelation('party2', $party2);
                // Note: The buildTemplateValues function is defined in PhpWordController
                $templateData = $this->buildTemplateValues($agreement);

                // 8.4 - Generate the Final Document (Create the PDF/Word file and return it)
                $url = $this->generate(
                    $agreement->id,
                    $templateData['values'],
                    $request,
                    $agreement->category_id,
                    $agreement->sub_category,
                    $agreement->aggriment_language_id,
                    $templateData['party_2_image'],
                    $templateData['party_1_image'],
                    $templateData['party_1_signature'],
                    $templateData['party_2_signature']
                );

                $response = response()->json([
                    'data' => $url,
                    'status' => 'success',
                    'message' => 'Agreement created successfully',
                ]);

                return $response;
            }

            // 9. Return Response for Draft Steps (Just return success for draft saves)
            $response = response()->json([
                'data' => $agreement->fresh(['party1', 'party2', 'category', 'attributes.categoryAttribute']),
                'status' => 'success',
                'message' => 'Draft saved successfully',
            ]);

            return $response;

        } catch (PartyVerificationException $e) {
            $response = $e->toResponse();
            return $response;
        } catch (\Throwable $e) {
            $response = ApiResponse::error(500, 'AGREEMENT_STEP_SAVE_FAILED', 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return $response;
        }
    }

    /**
     * Upsert the `id:value|id:value` attribute string onto an agreement.
     */
    protected function syncAgreementAttributes(Aggriment $aggriment, ?string $attributeString): void
    {
        if ($attributeString === null || trim($attributeString) === '') {
            return;
        }

        foreach (explode('|', $attributeString) as $attribute) {

            $attribute = trim($attribute);

            if ($attribute === '') {
                continue;
            }

            // Split only on FIRST colon
            [$attribute_id, $attribute_value] = array_pad(
                explode(':', $attribute, 2),
                2,
                null
            );

            $attribute_id = (int) trim($attribute_id);
            $attribute_value = trim($attribute_value ?? '');

            // Skip invalid attribute id
            if ($attribute_id <= 0) {
                continue;
            }

            AgreementAttribute::updateOrCreate(
                [
                    'agreement_id' => $aggriment->id,
                    'attribute_id' => $attribute_id,
                ],
                [
                    'attribute_value' => $attribute_value,
                ]
            );
        }
    }

    public function party_wise_agreements(Request $request)
    {
        try {
            $user = $request->user();
            $userId = $user->id;

            // Pagination values
            $perPage = min(max((int) $request->input('per_page', 10), 1), 100);
            $page = $request->page ?? 1;

            // In V2 we use progress_status instead of is_draft
            $progressStatus = $request->input('progress_status');

            // Fallback for V1 compatibility if they pass is_draft
            if ($request->has('is_draft')) {
                $progressStatus = $request->boolean('is_draft') ? 'DRAFT' : 'COMPLETED';
            }

            // Find if the same user exists as a customer based on mobile number
            $customer = Customer::where('mobile', $user->mobile)->first();
            $customerId = $customer ? $customer->id : null;

            // Fetch paginated agreements without party1 and party2 relations initially
            $query = Aggriment::with(['category'])
                ->where(function ($q) use ($userId, $customerId) {
                    // Match agreements where this person is involved as a 'user'
                    $q->where(function ($subQ) use ($userId) {
                        $subQ->where('party_1_id', $userId)
                            ->where(function ($typeQ) {
                                $typeQ->where('party_1_user_type', 'user')->orWhereNull('party_1_user_type');
                            });
                    })
                        ->orWhere(function ($subQ) use ($userId) {
                        $subQ->where('party_2_id', $userId)
                            ->where(function ($typeQ) {
                                $typeQ->where('party_2_user_type', 'user')->orWhereNull('party_2_user_type');
                            });
                    });

                    // Match agreements where this person is involved as a 'customer'
                    if ($customerId) {
                        $q->orWhere(function ($subQ) use ($customerId) {
                            $subQ->where('party_1_id', $customerId)
                                ->where('party_1_user_type', 'customer');
                        })
                            ->orWhere(function ($subQ) use ($customerId) {
                                $subQ->where('party_2_id', $customerId)
                                    ->where('party_2_user_type', 'customer');
                            });
                    }
                });

            if ($progressStatus && strtoupper($progressStatus) !== 'ALL') {
                $query->where('progress_status', $progressStatus);
            }

            $agreements = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

            if ($agreements->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No agreements found for this party.',
                    'data' => []
                ], 404);
            }

            // Optimized Loading of Party 1 and Party 2 based on user_type
            $userIds = [];
            $customerIds = [];

            foreach ($agreements as $agreement) {
                if ($agreement->party_1_id) {
                    if ($agreement->party_1_user_type === 'user') {
                        $userIds[] = $agreement->party_1_id;
                    } else {
                        $customerIds[] = $agreement->party_1_id;
                    }
                }

                if ($agreement->party_2_id) {
                    if ($agreement->party_2_user_type === 'user') {
                        $userIds[] = $agreement->party_2_id;
                    } else {
                        $customerIds[] = $agreement->party_2_id;
                    }
                }
            }

            $userIds = array_unique($userIds);
            $customerIds = array_unique($customerIds);

            // Fetch all required users and customers efficiently
            $users = empty($userIds) ? collect() : User::whereIn('id', $userIds)->get()->keyBy('id');
            $customers = empty($customerIds) ? collect() : Customer::whereIn('id', $customerIds)->get()->keyBy('id');

            // Attach relations to the items
            $agreements->getCollection()->transform(function ($agreement) use ($users, $customers) {
                if ($agreement->party_1_id) {
                    $party1 = $agreement->party_1_user_type === 'user'
                        ? $users->get($agreement->party_1_id)
                        : $customers->get($agreement->party_1_id);
                    $agreement->setRelation('party1', $party1);
                }

                if ($agreement->party_2_id) {
                    $party2 = $agreement->party_2_user_type === 'user'
                        ? $users->get($agreement->party_2_id)
                        : $customers->get($agreement->party_2_id);
                    $agreement->setRelation('party2', $party2);
                }

                return $agreement;
            });

            // Success response with pagination meta
            return response()->json([
                'status' => true,
                'message' => 'Party-wise agreements fetched successfully.',
                'data' => $agreements->items(),
                'pagination' => [
                    'current_page' => $agreements->currentPage(),
                    'per_page' => $agreements->perPage(),
                    'total' => $agreements->total(),
                    'last_page' => $agreements->lastPage(),
                    'from' => $agreements->firstItem(),
                    'to' => $agreements->lastItem(),
                ]
            ], 200);

        } catch (\Throwable $e) {
            Log::error('PartyWiseAgreements API V2 Error', [
                'party_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Internal server error.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agreement_id' => 'required|exists:agreements,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ]);
        }

        $agreement_id = $request->agreement_id;

        $aggriment_details = Aggriment::with([
            'category',
            'installments',
            'histories.cutomers',
            'invoice'
        ])->where('id', $agreement_id)->first();

        // Dynamically resolve party1
        if (strtolower($aggriment_details->party_1_user_type) === 'user') {
            $aggriment_details->load('user1');
            $aggriment_details->setRelation('party1', $aggriment_details->user1);
            $aggriment_details->unsetRelation('user1');
        } else {
            $aggriment_details->load('party1');
        }

        // Dynamically resolve party2
        if (strtolower($aggriment_details->party_2_user_type) === 'user') {
            $aggriment_details->load('user2');
            $aggriment_details->setRelation('party2', $aggriment_details->user2);
            $aggriment_details->unsetRelation('user2');
        } else {
            $aggriment_details->load('party2');
        }

        $aggriment_attribute = AgreementAttribute::select('agreement_attribute.id', 'category_attributes.attribute_name', 'agreement_attribute.attribute_value')
            ->join('category_attributes', 'agreement_attribute.attribute_id', '=', 'category_attributes.id')
            ->where('agreement_id', $aggriment_details->id)
            ->get();

        $aggriment_details->attributes = $aggriment_attribute;

        return response()->json([
            'status' => true,
            'message' => 'Agreement Details',
            'date' => $aggriment_details
        ]);
    }
}
