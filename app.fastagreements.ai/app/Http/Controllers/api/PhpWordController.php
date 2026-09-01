<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Deal;
use Carbon\Carbon;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use App\Models\Aggriment;
use App\Models\CategoryLanguage;
use App\Models\AgreementAttribute;
use App\Models\Installment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ImageResizer;
use App\Models\Document;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;
use Blaspsoft\Doxswap\Facades\Doxswap;
//use Blaspsoft\Doxswap\Doxswap;
use Illuminate\Support\Facades\Storage;
use NumberFormatter;
use App\Models\DealCategory;
use App\Models\Language;
use App\Services\AgreementEntitlementService;
use App\Services\AgreementOtpModeService;
use App\Services\PartyVerificationException;
use App\Support\ApiResponse;
use Symfony\Component\Process\Process;
//use App\Models\UserSubscription;


class PhpWordController extends Controller
{

	use ImageResizer;

	public function test()
	{

		$pathOnDisk = 'word/filled_template1_dave_dipak_bharatbhai_20251119_180513.docx'; // relative to the public disk root
		$absolutePath = storage_path('app/public/' . $pathOnDisk);

		if (! file_exists($absolutePath)) {
			throw new \RuntimeException("Input DOCX missing at {$absolutePath}");
		}

		/** @var \Blaspsoft\Doxswap\ConversionResult $result */
		$result = Doxswap::convert($pathOnDisk, 'pdf');


		// Serve the converted file
		return Response::download(
			$result->outputFilePath,
			$result->outputFilename
		);
	}
	
       
  private function saveBase64Image(?string $base64, string $name, bool $isSignature = false): ?string
{
    if (!$base64) {
        return null;
    }

    $data = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
    $imageData = base64_decode($data);

    $path = storage_path('app/tmp/' . $name . '_' . uniqid() . '.png');
    file_put_contents($path, $imageData);

    // ADD EXTRA SPACE FOR SIGNATURE (PREVENT CUT)
    if ($isSignature) {
        $this->padSignature($path);
    }

    return $path;
}
  
  

private function numberToWords($number)
{
    $formatter = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    return ucfirst($formatter->format($number));
}


private function imageToBase64($relativePath)
{
    if (!$relativePath) {
        return null;
    }

    $fullPath = public_path($relativePath);

    if (!file_exists($fullPath)) {
        \Log::error("Image not found: " . $fullPath);
        return null;
    }

    return base64_encode(file_get_contents($fullPath));
}
function indianMoneyFormat($number)
{
    $number = (int) $number;
    $lastThree = substr($number, -3);
    $restUnits = substr($number, 0, -3);

    if ($restUnits != '') {
        $lastThree = ',' . $lastThree;
    }

    return preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits) . $lastThree;
}

public function amountToWords($number)
{
    $ones = [
        0 => '',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen',
    ];

    $tens = [
        2 => 'Twenty',
        3 => 'Thirty',
        4 => 'Forty',
        5 => 'Fifty',
        6 => 'Sixty',
        7 => 'Seventy',
        8 => 'Eighty',
        9 => 'Ninety',
    ];

    if ($number == 0) {
        return 'Zero';
    }

    $result = '';

    if ($number >= 10000000) {
        $result .= $this->amountToWords(floor($number / 10000000)) . ' Crore ';
        $number %= 10000000;
    }

    if ($number >= 100000) {
        $result .= $this->amountToWords(floor($number / 100000)) . ' Lakh ';
        $number %= 100000;
    }

    if ($number >= 1000) {
        $result .= $this->amountToWords(floor($number / 1000)) . ' Thousand ';
        $number %= 1000;
    }

    if ($number >= 100) {
        $result .= $this->amountToWords(floor($number / 100)) . ' Hundred ';
        $number %= 100;
    }

    if ($number > 0) {

        if ($number < 20) {
            $result .= $ones[$number];
          
        } else {

            $result .= $tens[floor($number / 10)];

            if ($number % 10 > 0) {
                $result .= ' ' . $ones[$number % 10];
            }
        }
    }
  
    return trim($result);
}

  function format_inr($number) {
    $number = (string)$number;

    $lastThree = substr($number, -3);
    $restUnits = substr($number, 0, -3);

    if ($restUnits != '') {
        $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);
        return $restUnits . "," . $lastThree;
    } else {
        return $lastThree;
    }
}


	public function create_aggriment(Request $request)
	{
      
		try {
			$party_1 = $request->party_1_id;
			$party_2 = $request->party_2_id;

			// validation start
			$validator = Validator::make($request->all(), [
                'party_1_id' => 'required|integer|exists:customers,id',
                'party_2_id' => 'required|integer|different:party_1_id|exists:customers,id',
                'amount'     => 'nullable|numeric|min:1',
                'start_date' => 'nullable|date',
                //'end_date'   => 'nullable|date|after:start_date',

                // Which verification tier this agreement is being created under.
                // Nullable so an older caller still works; null is treated the
                // same as without_otp by the gate below.
                'otp_mode'   => 'nullable|in:' . implode(',', AgreementOtpModeService::modes()),
                'is_draft'   => 'nullable|boolean',

            ]);


			if ($validator->fails()) {
				return response()->json([
					'status' => 'true',
					'message' => 'Validation Fail : ' . $validator->errors(),
					'errors' => $validator->errors()
				], 400);
			}

			// validation end

			// fatch data from customers table  

			$persons = Customer::where('is_active', 1);
         

			$person1 = (clone $persons)->where('id', $party_1)->first();

			if (!$person1) {
				return response()->json([
					'status' => 'false',
					'message' => 'party one  not found'
				]);
			}

			$person2 = (clone $persons)->where('id', $party_2)->first();

			if (!$person2) {
				return response()->json([
					'status' => 'false',
					'message' => 'party  two not found'
				]);
			}

			// ---- Caller, OTP tier and plan entitlement -----------------------
			//
			// Everything below derives from the session token rather than the
			// request body. Before this, any caller could name any two customer
			// ids and create an agreement between strangers, with no plan.

			$caller = $request->user();
			$callerId = (int) $caller->id;

			if ($callerId !== (int) $party_1 && $callerId !== (int) $party_2) {
				throw new PartyVerificationException(
					403,
					'NOT_A_PARTY',
					'You can only create an agreement you are a party to.',
				);
			}

			$isDraft = $request->boolean('is_draft');
			$otpModeService = app(AgreementOtpModeService::class);
			$otpMode = $request->input('otp_mode');

			$requiredParties = $otpModeService->requiredForCreation(
				$person1,
				$person2,
				$request->input('guarantor'),
				$request->input('guarantor_number'),
			);

			// Under with_otp every party and guarantor must already have
			// confirmed their number through Firebase. Throws with the list of
			// who is still outstanding.
			if (!$isDraft) {
				$otpModeService->assertVerifiedForCreation($callerId, $otpMode, $requiredParties);
			}

			$entitlements = app(AgreementEntitlementService::class);
			$entitlement = $isDraft ? null : $entitlements->getEntitlement($callerId, $otpMode);

			if (!$isDraft && $entitlement === null) {
				throw new PartyVerificationException(
					402,
					'SUBSCRIPTION_REQUIRED',
					'Your plan does not cover another agreement. Please renew to continue.',
				);
			}

			// An invoice may only be attached if it belongs to the caller —
			// it used to be taken from the request body and trusted.
			$invoiceId = $request->input('invoice_id');

			if (!empty($invoiceId)) {
				$ownsInvoice = DB::table('subscription_invoices')
					->where('id', $invoiceId)
					->where('customer_id', $callerId)
					->exists();

				if (!$ownsInvoice) {
					throw new PartyVerificationException(
						403,
						'INVOICE_NOT_YOURS',
						'That invoice does not belong to this account.',
					);
				}
			}
			// ------------------------------------------------------------------

			// Add aggriment Details in aggriment table
			$aggriment = new Aggriment;
			$aggriment->otp_mode = $otpMode;
			$aggriment->is_draft = $isDraft;
			$aggriment->party_1_id = $party_1;
			$aggriment->party_1_signature = $request->party_1_signature;
			$aggriment->party_2_id = $party_2;
			$aggriment->party_2_signature = $request->party_2_signature;
			$aggriment->amount = $request->amount;
			$aggriment->start_date = $request->start_date;
			$aggriment->end_date = $request->end_date;
			$aggriment->agreement_date = $request->agreement_date;
			$aggriment->agreement_type = $request->agreement_type;
			$aggriment->is_interest = $request->is_interest;
			$aggriment->reference_no = $request->reference_no;
			$aggriment->reference_remark = $request->reference_remark;
			$aggriment->address = $request->address;
			$aggriment->note = $request->note;
			$aggriment->security = $request->security;
			$aggriment->guarantor = $request->guarantor;
			$aggriment->guarantor_number = $request->guarantor_number;
			$aggriment->agreement_status = $request->agreement_status;
			$aggriment->period = $request->period;
			$aggriment->documents = $request->documents;
			$aggriment->repayment_term = $request->repayment_term;
			$aggriment->aggriment_language_id = $request->aggriment_language_id;
			$aggriment->category_id = $request->category_id;
          	$aggriment->sub_category = $request->sub_cat_id;
          	$aggriment->location = $request->location;
			$aggriment->purpose = $request->purpose;
			$person_one = $request->file('party_one_image') ? $this->image_resize($request->file('party_one_image'), 'person_images') : null;
			$person_two = $request->file('party_two_image') ? $this->image_resize($request->file('party_two_image'), 'person_images') : null;
			$aggriment->party_1_image = $person_one;
			$aggriment->party_2_image = $person_two;
            $party_1_adhar_front = $request->file('party_1_adhar_front') ? $this->image_resize($request->file('party_1_adhar_front'), 'adhar_images') : null;
			$party_1_adhar_back = $request->file('party_1_adhar_back') ? $this->image_resize($request->file('party_1_adhar_back'), 'adhar_images') : null;
			$party_2_adhar_front = $request->file('party_2_adhar_front') ? $this->image_resize($request->file('party_2_adhar_front'), 'adhar_images') : null;
			$party_2_adhar_back = $request->file('party_2_adhar_back') ? $this->image_resize($request->file('party_2_adhar_back'), 'adhar_images') : null;
            $aggriment->party_1_adhar_front = $party_1_adhar_front;
			$aggriment->party_1_adhar_back = $party_1_adhar_back;
			$aggriment->party_2_adhar_front = $party_2_adhar_front;
			$aggriment->party_2_adhar_back = $party_2_adhar_back;
            $aggriment->invoice_id = $request->invoice_id; 
          
          // Vehicle Images Upload
            $vehicle_front_side = $request->file('vehicle_front_side')
                ? $this->image_resize($request->file('vehicle_front_side'), 'vehicle_images')
                : null;

            $vehicle_back_side = $request->file('vehicle_back_side')
                ? $this->image_resize($request->file('vehicle_back_side'), 'vehicle_images')
                : null;

            $vehicle_left_side = $request->file('vehicle_left_side')
                ? $this->image_resize($request->file('vehicle_left_side'), 'vehicle_images')
                : null;

            $vehicle_right_side = $request->file('vehicle_right_side')
                ? $this->image_resize($request->file('vehicle_right_side'), 'vehicle_images')
                : null;

            $aggriment->vehicle_front_side = $vehicle_front_side;
            $aggriment->vehicle_back_side = $vehicle_back_side;
            $aggriment->vehicle_left_side = $vehicle_left_side;
            $aggriment->vehicle_right_side = $vehicle_right_side;
            $aggriment->party_1_age = $request->party_1_age;
			$aggriment->party_2_age = $request->party_2_age;
			$aggriment->party_1_business = $request->party_1_business;
			$aggriment->party_2_business = $request->party_2_business;
			// The insert and the plan decrement share one transaction, so two
			// agreements racing on the last remaining credit cannot both
			// succeed — without it, both read "1 left" and both write "0".
			DB::transaction(function () use ($aggriment, $entitlement, $entitlements, $isDraft) {
				$aggriment->save();

				if (!$isDraft && !$entitlements->consume($entitlement['subscription_id'])) {
					throw new PartyVerificationException(
						402,
						'SUBSCRIPTION_EXHAUSTED',
						'Your plan ran out while this agreement was being created. Please renew and try again.',
					);
				}
			});

			// Records who confirmed, so the agreement carries its own proof
			// rather than depending on rows that expire.
			if (!$isDraft) {
				$otpModeService->snapshotForAgreement($aggriment, $callerId, $requiredParties);
			}
			// Add aggriment Details in aggriment table




			// Creating an agreement does not post to the feed. The app asks the
			// creator after payment and calls POST /api/feed/publish only if
			// they say yes.

			if ($request->filled('attribute')) {

    // Split by |
    $attributes = explode('|', $request->attribute);

    foreach ($attributes as $attribute) {

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

			if ($isDraft) {
				return response()->json([
					'data' => $aggriment->fresh(['party1', 'party2', 'category', 'attributes.categoryAttribute']),
					'status' => 'success',
					'message' => 'Draft saved successfully',
				]);
			}

			$aggriment = Aggriment::with('party1', 'party2', 'category', 'attributes.categoryAttribute')->where('id', $aggriment->id)->first();

			if (!is_null($aggriment->amount)) {

				if (
    !is_null($aggriment->amount) &&
    !empty($aggriment->start_date) &&
    !empty($aggriment->end_date) &&
    !empty($aggriment->period) &&
    $aggriment->period > 0
) {

    $start = Carbon::parse($aggriment->start_date);
    $end   = Carbon::parse($aggriment->end_date);

    $amount = $aggriment->amount / $aggriment->period;

    for ($i = 0; $i < $aggriment->period; $i++) {
        $installment = new Installment;
        $installment->agreement_id = $aggriment->id;
        $installment->emi_amount = $amount;
        $installment->emi_date = $start->copy()->addMonths($i + 1);
        $installment->save();
    }
}			}

			$attribute_list = AgreementAttribute::join('category_attributes', 'category_attributes.id', '=', 'agreement_attribute.attribute_id')->where('agreement_id', $aggriment->id)->get();
          	if (
                !is_null($aggriment->amount) &&
                !empty($aggriment->start_date) &&
                !empty($aggriment->end_date) &&
                !empty($aggriment->period) &&
                $aggriment->period > 0
            ) {
			$startDateTime = Carbon::createFromFormat('Y-m-d', $aggriment->start_date);

            }
			// Template values plus the four image paths generate() takes as its
			// own arguments. Extracted so update_aggriment re-renders an edited
			// agreement through exactly the same map.
			[
				'values' => $values,
				'party_1_image' => $party1ImagePath,
				'party_2_image' => $party2ImagePath,
				'party_1_signature' => $party1SignaturePath,
				'party_2_signature' => $party2SignaturePath,
			] = $this->buildTemplateValues($aggriment);

			$url = $this->generate($aggriment->id,
                                   $values,
                                   $request, 
                                   $aggriment->category_id, 
                                   $aggriment->sub_category, 
                                   $aggriment->aggriment_language_id, 
                                   $party2ImagePath, 
                                   $party1ImagePath, 
                                   $party1SignaturePath, 
                                   $party2SignaturePath);
          
			return response()->json([
				'data' => $url,
				'status' => 'success',
				'message' => 'Agreement created successfully',
			]);
		} catch (PartyVerificationException $e) {

			// A refusal about what the caller sent — the OTP tier is unsatisfied
			// or the plan is exhausted. Caught ahead of the generic handler so it
			// keeps its own status code instead of becoming a 500.
			return $e->toResponse();

		} catch (\Throwable $e) {

			// PHP TypeError/Error instances do not extend Exception. Catching
			// Throwable keeps an API failure JSON-shaped instead of letting
			// Laravel render its HTML debug page to the Flutter client.
			Log::error('Agreement creation failed.', [
				'party_1_id' => $party_1 ?? null,
				'party_2_id' => $party_2 ?? null,
				'exception' => $e,
			]);

			return ApiResponse::error(
				500,
				'AGREEMENT_GENERATION_FAILED',
				'Unable to create the agreement. Please try again.'
			);
		}
	}

	/**
	 * Everything the DOCX template needs, read from the saved agreement.
	 *
	 * This was inline in create_aggriment. update_aggriment re-renders the same
	 * document after an edit, so the map lives here and both callers share it —
	 * kept in two places, an edited agreement would slowly start rendering
	 * differently from a freshly created one.
	 *
	 * Dates come off the model rather than the request: on an edit the caller
	 * may not resend start_date, and the stored value is the one the document
	 * has to show. For a create the two are the same value anyway.
	 *
	 * @return array{
	 *     values: array<string, mixed>,
	 *     party_1_image: ?string,
	 *     party_2_image: ?string,
	 *     party_1_signature: ?string,
	 *     party_2_signature: ?string
	 * }
	 */
	private function buildTemplateValues(Aggriment $aggriment): array
	{
		$party1SignaturePath = $this->saveBase64Image($aggriment->party_1_signature, 'party1_signature');
		$party2SignaturePath = $this->saveBase64Image($aggriment->party_2_signature, 'party2_signature');

		$party1ImageRelativePath = $aggriment->party_1_image ? 'admin/images/person_images/' . $aggriment->party_1_image : null;
		$party2ImageRelativePath = $aggriment->party_2_image ? 'admin/images/person_images/' . $aggriment->party_2_image : null;

		$party1adharFrontRelativePath = $aggriment->party_1_adhar_front ? 'admin/images/adhar_images_thumb/' . $aggriment->party_1_adhar_front : null;
		$party1adharBackRelativePath = $aggriment->party_1_adhar_back ? 'admin/images/adhar_images_thumb/' . $aggriment->party_1_adhar_back : null;

		$party2adharFrontRelativePath = $aggriment->party_2_adhar_front ? 'admin/images/adhar_images_thumb/' . $aggriment->party_2_adhar_front : null;
		$party2adharBackRelativePath = $aggriment->party_2_adhar_back ? 'admin/images/adhar_images_thumb/' . $aggriment->party_2_adhar_back : null;

		$party1Base64 = $this->imageToBase64($party1ImageRelativePath);
		$party2Base64 = $this->imageToBase64($party2ImageRelativePath);

		$party1AdharFrontBase64 = $this->imageToBase64($party1adharFrontRelativePath);
		$party1AdharBackBase64 = $this->imageToBase64($party1adharBackRelativePath);

		$party2AdharFrontBase64 = $this->imageToBase64($party2adharFrontRelativePath);
		$party2AdharBackBase64 = $this->imageToBase64($party2adharBackRelativePath);

		$party1ImagePath = $this->saveBase64Image($party1Base64, 'party1_photo_' . $aggriment->id);
		$party2ImagePath = $this->saveBase64Image($party2Base64, 'party2_photo_' . $aggriment->id);

		$party1AdharFrontPath = $this->saveBase64Image($party1AdharFrontBase64, 'party1_adhar_front_' . $aggriment->id);
		$party1AdharBackPath = $this->saveBase64Image($party1AdharBackBase64, 'party1_adhar_back_' . $aggriment->id);

		$party2AdharFrontPath = $this->saveBase64Image($party2AdharFrontBase64, 'party2_adhar_front_' . $aggriment->id);
		$party2AdharBackPath = $this->saveBase64Image($party2AdharBackBase64, 'party2_adhar_back_' . $aggriment->id);

		$vehicleFrontPath = $this->getVehicleImagePath(
			$aggriment->vehicle_front_side,
			'vehicle_front',
			$aggriment->id
		);

		$vehicleBackPath = $this->getVehicleImagePath(
			$aggriment->vehicle_back_side,
			'vehicle_back',
			$aggriment->id
		);

		$vehicleLeftPath = $this->getVehicleImagePath(
			$aggriment->vehicle_left_side,
			'vehicle_left',
			$aggriment->id
		);

		$vehicleRightPath = $this->getVehicleImagePath(
			$aggriment->vehicle_right_side,
			'vehicle_right',
			$aggriment->id
		);

		// Extract guarantors
		$guarantors = explode(',', $aggriment->guarantor);
		$guarantors_mobile = explode(',', $aggriment->guarantor_number);

		$attributes = DB::table('agreement_attribute')
			->leftJoin('category_attributes', 'agreement_attribute.attribute_id', '=', 'category_attributes.id')
			->where('agreement_attribute.agreement_id', $aggriment->id)
			->select('agreement_attribute.id', 'agreement_attribute.attribute_id', 'category_attributes.attribute_name', 'category_attributes.attribute_code', 'agreement_attribute.attribute_value')
			->get();

		$duration = [];
		$duration_word = [];

		if (!empty($aggriment->start_date) && !empty($aggriment->end_date)) {
			$startDate = Carbon::parse($aggriment->start_date);
			$endDate   = Carbon::parse($aggriment->end_date)->addDay();

			$diff = $startDate->diff($endDate);

			$years  = $diff->y;
			$months = $diff->m;
			$days   = $diff->d;

			if ($years > 0) {
				$duration[] = $years . ' ' . ($years == 1 ? 'Year' : 'Years');
				$duration_word[] = $years;
			}

			if ($months > 0) {
				$duration[] = $months . ' ' . ($months == 1 ? 'Month' : 'Months');
				$duration_word[] = $months;
			}

			if ($days > 0) {
				$duration[] = $days . ' ' . ($days == 1 ? 'Day' : 'Days');
				$duration_word[] = $days;
			}
		}

		$remaining_balance = $aggriment->amount - $aggriment->security;

		$values = [
			"date" => \Carbon\Carbon::now()->format('d-m-Y'),
			"party_1" => $aggriment->party1->name,
			"party_no_1" => $aggriment->party1->mobile,
			"party_2" => $aggriment->party2->name,
			"party_no_2" => $aggriment->party2->mobile,
			"location" => $aggriment->location,
			"amount" => $this->indianMoneyFormat($aggriment->amount),
			"amount_word" => $this->amountToWords($aggriment->amount),
			"deposit_amount" => $this->format_inr((int) $aggriment->security),
			"deposit_word" => $this->amountToWords((int) $aggriment->security),
			"remaining_balance" => $this->format_inr($remaining_balance),
			"remaining_in_word" => $this->amountToWords($remaining_balance),
			"duration" => implode(' ', $duration),
			"duration_word" => $this->amountToWords((int) implode('', $duration_word)),
			"area" => $aggriment->address,
			"party_1_signature" => $party1SignaturePath,
			"party_2_signature" => $party2SignaturePath,
			"1image" => $party1ImagePath,
			"2image" => $party2ImagePath,
			"party_1_adhar_front" => $party1AdharFrontPath,
			"party_1_adhar_back" => $party1AdharBackPath,
			"party_2_adhar_front" => $party2AdharFrontPath,
			"party_2_adhar_back" => $party2AdharBackPath,
			"address_1" => $aggriment->party1->address,
			"address_2" => $aggriment->party2->address,
			"guarantor_1" => $guarantors[0] ?? '',
			"guarantor_2" => $guarantors[1] ?? '',
			"guarantor_1_no" => $guarantors_mobile[0] ?? '',
			"guarantor_2_no" => $guarantors_mobile[1] ?? '',
			"guarantor_3" => $guarantors[2] ?? '',
			"guarantor_4" => $guarantors[3] ?? '',
			"guarantor_3_no" => $guarantors_mobile[2] ?? '',
			"guarantor_4_no" => $guarantors_mobile[3] ?? '',
			"startat" => !empty($aggriment->start_date)
				? Carbon::parse($aggriment->start_date)->translatedFormat('jS M Y')
				: '',

			"endat" => !empty($aggriment->end_date)
				? Carbon::parse($aggriment->end_date)->translatedFormat('jS M Y')
				: '',

			"start_at" => !empty($aggriment->start_date)
				? Carbon::parse($aggriment->start_date)->format('d-m-Y')
				: '',

			"end_at" => !empty($aggriment->end_date)
				? Carbon::parse($aggriment->end_date)->format('d-m-Y')
				: '',
			"occupation_1" => $aggriment->party_1_business,
			"occupation_2" => $aggriment->party_2_business,
			"party1age" => $aggriment->party_1_age,
			"party2age" => $aggriment->party_2_age,
			"witnes1"    => $guarantors[0] ?? "No witness",
			"witnes2"    => $guarantors[1] ?? "No witness",
			"witnes1No"  => $guarantors_mobile[0] ?? "No witness",
			"witnes2No"  => $guarantors_mobile[1] ?? "No witness",
			"front_side" => $vehicleFrontPath,
			"back_side"  => $vehicleBackPath,
			"left_side"  => $vehicleLeftPath,
			"right_side" => $vehicleRightPath,
		];

		// Build payment details from attribute codes
		$attributeData = [];

		foreach ($attributes as $item) {
			if (!empty($item->attribute_code)) {
				$attributeData[$item->attribute_code] = $item->attribute_value;
			}
		}

		$paymentDetails = '';

		// Payment Method
		$paymentMethod = $attributeData['@money_payment_method'] ?? '';

		if (strtolower($paymentMethod) == 'cash') {

			$paymentDetails = 'Cash';

		} elseif (!empty($attributeData['@upi_transaction'])) {

			$paymentDetails =
				'Online Payment UPI Transaction ID: ' .
				$attributeData['@upi_transaction'];
		} elseif (
			!empty($attributeData['@cheque']) ||
			!empty($attributeData['@bank_name']) ||
			!empty($attributeData['@branch']) ||
			!empty($attributeData['@ac'])
		) {
			$paymentDetails = 'Cheque ' .
				($attributeData['@cheque'] ?? '') . ', ' .
				($attributeData['@bank_name'] ?? '') . ', ' .
				($attributeData['@branch'] ?? '') . ', ' .
				($attributeData['@ac'] ?? '');
		}

		// Add all attributes to values array
		foreach ($attributes as $item) {

			if (!empty($item->attribute_code)) {

				$cleanCode = ltrim($item->attribute_code, '@');
				$safeKey = Str::slug($cleanCode, '_');

				// Property Tax Paid By
				if ($item->attribute_code == '@taxpaidby') {

					$values[$safeKey] = ((int)$item->attribute_value === 0)
						? 'Tenant'
						: 'Owner';

				} else {

					$values[$safeKey] = $item->attribute_value;
				}
			}
		}

		// Template variable
		$values['money_payment_method'] = $paymentDetails;

		return [
			'values' => $values,
			'party_1_image' => $party1ImagePath,
			'party_2_image' => $party2ImagePath,
			'party_1_signature' => $party1SignaturePath,
			'party_2_signature' => $party2SignaturePath,
		];
	}

	/**
	 * Edit an existing agreement and re-render its document.
	 *
	 * Content only. The two parties, the category and the language are fixed at
	 * creation: the parties because their OTP confirmations were snapshotted
	 * against them, the category and language because together they select the
	 * DOCX template and the attribute set the form is built from. Sending any
	 * of them with a different value is refused rather than quietly ignored, so
	 * a caller never believes a change landed when it did not.
	 *
	 * POST rather than PATCH: PHP does not populate $_FILES for PATCH bodies,
	 * and an edit may re-upload party photos, Aadhaar or vehicle images.
	 *
	 * No subscription credit is consumed and no feed row is written — both of
	 * those belong to creation, and an edit is not a second agreement.
	 */
	public function update_aggriment(Request $request)
	{
		$agreementId = $request->agreement_id;

		try {
			$validator = Validator::make($request->all(), [
				'agreement_id'   => 'required|integer|exists:agreements,id',
				'amount'         => 'nullable|numeric|min:1',
				'start_date'     => 'nullable|date',
				'end_date'       => 'nullable|date',
				'agreement_date' => 'nullable|date',
				'period'         => 'nullable|integer|min:0',
				'security'       => 'nullable|numeric',
				'is_draft'       => 'nullable|boolean',
			]);

			if ($validator->fails()) {
				return response()->json([
					'status' => false,
					'message' => 'Validation Fail : ' . $validator->errors(),
					'errors' => $validator->errors()
				], 400);
			}

			$aggriment = Aggriment::with('party1', 'party2')->findOrFail($agreementId);
			$publishingDraft = (bool) $aggriment->is_draft
				&& $request->has('is_draft')
				&& !$request->boolean('is_draft');

			// Identity comes from the token, never from the body — the same rule
			// create_aggriment follows.
			$callerId = (int) $request->user()->id;

			if ($callerId !== (int) $aggriment->party_1_id && $callerId !== (int) $aggriment->party_2_id) {
				throw new PartyVerificationException(
					403,
					'NOT_A_PARTY',
					'You can only edit an agreement you are a party to.',
				);
			}

			// Refuse, rather than ignore, an attempt to move the agreement to
			// different parties, a different category or a different language.
			$immutable = [
				'party_1_id'            => $aggriment->party_1_id,
				'party_2_id'            => $aggriment->party_2_id,
				'category_id'           => $aggriment->category_id,
				'sub_cat_id'            => $aggriment->sub_category,
				'aggriment_language_id' => $aggriment->aggriment_language_id,
			];

			$rejected = [];

			foreach ($immutable as $field => $current) {
				if ($request->filled($field) && (string) $request->input($field) !== (string) $current) {
					$rejected[] = $field;
				}
			}

			if ($rejected !== []) {
				throw new PartyVerificationException(
					422,
					'FIELD_IMMUTABLE',
					'These cannot be changed on an existing agreement: ' . implode(', ', $rejected) . '. Create a new agreement instead.',
					['fields' => $rejected],
				);
			}

			// A guarantor added or swapped on a with_otp agreement has confirmed
			// nothing yet. Checked before anything is written, so a refusal
			// leaves the agreement exactly as it was.
			$otpModeService = app(AgreementOtpModeService::class);

			$guarantorChanged =
				($request->has('guarantor') && (string) $request->input('guarantor') !== (string) $aggriment->guarantor)
				|| ($request->has('guarantor_number') && (string) $request->input('guarantor_number') !== (string) $aggriment->guarantor_number);

			$reverify = !$aggriment->is_draft && $guarantorChanged && $aggriment->otp_mode === AgreementOtpModeService::WITH_OTP;
			$required = [];

			if ($reverify || $publishingDraft) {
				$required = $otpModeService->requiredForCreation(
					$aggriment->party1,
					$aggriment->party2,
					$request->input('guarantor', $aggriment->guarantor),
					$request->input('guarantor_number', $aggriment->guarantor_number),
				);

				$otpModeService->assertVerifiedForCreation($callerId, $aggriment->otp_mode, $required);
			}

			$entitlements = app(AgreementEntitlementService::class);
			$entitlement = $publishingDraft ? $entitlements->getEntitlement($callerId, $aggriment->otp_mode) : null;

			if ($publishingDraft && $entitlement === null) {
				throw new PartyVerificationException(402, 'SUBSCRIPTION_REQUIRED', 'Your plan does not cover another agreement. Please renew to continue.');
			}

			// Only fields the caller actually sent are touched, so the app can
			// send a subset without wiping the rest of the agreement.
			$editable = [
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
				'is_draft',
				'documents',
				'party_1_age',
				'party_2_age',
				'party_1_business',
				'party_2_business',
				'party_1_signature',
				'party_2_signature',
			];

			foreach ($editable as $field) {
				if ($request->has($field)) {
					$aggriment->{$field} = $request->input($field);
				}
			}

			// Images are replaced only when a new file arrives. The previous
			// file is left on disk: documents rendered before this edit still
			// point at it.
			$imageInputs = [
				'party_one_image'    => ['party_1_image', 'person_images'],
				'party_two_image'    => ['party_2_image', 'person_images'],
				'party_1_adhar_front' => ['party_1_adhar_front', 'adhar_images'],
				'party_1_adhar_back' => ['party_1_adhar_back', 'adhar_images'],
				'party_2_adhar_front' => ['party_2_adhar_front', 'adhar_images'],
				'party_2_adhar_back' => ['party_2_adhar_back', 'adhar_images'],
				'vehicle_front_side' => ['vehicle_front_side', 'vehicle_images'],
				'vehicle_back_side'  => ['vehicle_back_side', 'vehicle_images'],
				'vehicle_left_side'  => ['vehicle_left_side', 'vehicle_images'],
				'vehicle_right_side' => ['vehicle_right_side', 'vehicle_images'],
			];

			foreach ($imageInputs as $input => [$column, $folder]) {
				if ($request->file($input)) {
					$aggriment->{$column} = $this->image_resize($request->file($input), $folder);
				}
			}

			// Read before save(), which clears the dirty state.
			$scheduleChanged = $aggriment->isDirty(['amount', 'period', 'start_date', 'end_date']);

			DB::transaction(function () use ($aggriment, $request, $scheduleChanged, $publishingDraft, $entitlement, $entitlements) {
				$aggriment->save();

				$this->syncAgreementAttributes($aggriment, $request->input('attribute'));

				if (!$aggriment->is_draft && ($scheduleChanged || $publishingDraft)) {
					$this->rebuildInstallments($aggriment);
				}

				if ($publishingDraft && !$entitlements->consume($entitlement['subscription_id'])) {
					throw new PartyVerificationException(402, 'SUBSCRIPTION_EXHAUSTED', 'Your plan ran out while this agreement was being published. Please renew and try again.');
				}
			});

			// Re-record who confirmed, now that the guarantor list has changed.
			if ($reverify || $publishingDraft) {
				$otpModeService->snapshotForAgreement($aggriment, $callerId, $required);
			}

			if ($aggriment->is_draft) {
				return response()->json([
					'data' => $aggriment->fresh(['party1', 'party2', 'category', 'attributes.categoryAttribute']),
					'status' => 'success',
					'message' => 'Draft updated successfully',
				]);
			}

			$aggriment = Aggriment::with('party1', 'party2', 'category', 'attributes.categoryAttribute')
				->findOrFail($aggriment->id);

			[
				'values' => $values,
				'party_1_image' => $party1ImagePath,
				'party_2_image' => $party2ImagePath,
				'party_1_signature' => $party1SignaturePath,
				'party_2_signature' => $party2SignaturePath,
			] = $this->buildTemplateValues($aggriment);

			// generate() already returns { status, word_url, agreement_id,
			// message }; the caller feeds word_url's filename straight to
			// convert_Word_to_pdf/v1, same as after a create.
			return $this->generate(
				$aggriment->id,
				$values,
				$request,
				$aggriment->category_id,
				$aggriment->sub_category,
				$aggriment->aggriment_language_id,
				$party2ImagePath,
				$party1ImagePath,
				$party1SignaturePath,
				$party2SignaturePath
			);
		} catch (PartyVerificationException $e) {

			return $e->toResponse();

		} catch (\Throwable $e) {

			Log::error('Agreement update failed.', [
				'agreement_id' => $agreementId ?? null,
				'exception' => $e,
			]);

			return ApiResponse::error(
				500,
				'AGREEMENT_UPDATE_FAILED',
				'Unable to update the agreement. Please try again.'
			);
		}
	}

	/**
	 * Upsert the `id:value|id:value` attribute string onto an agreement.
	 *
	 * Codes the caller left out are kept, not deleted — a partial edit that
	 * resends three of a category's twenty fields must not empty the other
	 * seventeen out of the rendered document.
	 */
	private function syncAgreementAttributes(Aggriment $aggriment, ?string $attributeString): void
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

	/**
	 * Rebuild the EMI schedule after the amount, period or dates changed.
	 *
	 * The old rows are dropped rather than adjusted: the schedule is derived
	 * entirely from those four fields, so anything left over describes an
	 * agreement that no longer exists.
	 */
	private function rebuildInstallments(Aggriment $aggriment): void
	{
		Installment::where('agreement_id', $aggriment->id)->delete();

		if (
			is_null($aggriment->amount) ||
			empty($aggriment->start_date) ||
			empty($aggriment->end_date) ||
			empty($aggriment->period) ||
			$aggriment->period <= 0
		) {
			return;
		}

		$start = Carbon::parse($aggriment->start_date);
		$amount = $aggriment->amount / $aggriment->period;

		for ($i = 0; $i < $aggriment->period; $i++) {
			$installment = new Installment;
			$installment->agreement_id = $aggriment->id;
			$installment->emi_amount = $amount;
			$installment->emi_date = $start->copy()->addMonths($i + 1);
			$installment->save();
		}
	}
	/**
	 * Generate two DOCX files from two templates using submitted details.
	 */

	public function generate(
    $aggriment_id,
    $values,
    Request $request,
    $category_id,
    $sub_catgory,
    $language_id,
    $party2ImagePath,
    $party1ImagePath,
    $party1SignaturePath,
    $party2SignaturePath
): Response {

     
   $query = Document::where('language_id', $language_id);

if (!empty($sub_catgory)) {
    $query->where(function ($q) use ($sub_catgory) {
        $q->where('category_id', $sub_catgory)
          ->orWhere('sub_category', $sub_catgory);
    });
} else {
    $query->where('category_id', $category_id);
}
      $path = $query->firstOrFail();

    // Resolve the stored template path in a way that works with the app's
    // local storage layout and avoid passing a missing or corrupt file to
    // PhpWord's ZipArchive / TemplateProcessor.
    $templatePath = $this->resolveDocumentTemplatePath($path->file_path);
    $this->assertValidDocx($templatePath);

    // OUTPUT DIRECTORY → public/word
    $outputsDir = public_path('word');
    if (!is_dir($outputsDir)) {
        mkdir($outputsDir, 0777, true);
    }

    // File naming
    $timestamp = now()->format('Ymd_His');
     $document_name = DealCategory::where('id', $category_id)->value('category_name');
     $language   = Language::findOrFail($language_id);

      $safeName = Str::slug($document_name ?? 'agreement', '_');
      $safeLanguageName = Str::slug($language->language_name ?? 'english', '_'); // use correct column name

    $filenameDocx = "{$safeName}_{$safeLanguageName}_{$timestamp}.docx";
    $destDocx = $outputsDir . '/' . $filenameDocx;

    // Normalize template placeholders
    $normalizedTemplate = $this->normalizePlaceholdersToCurly($templatePath);

    // Fill template and save DOCX
    $this->fillTemplateAndSave(
        $normalizedTemplate,
        $values,
        $destDocx,
        $party2ImagePath,
        $party1ImagePath,
        $party1SignaturePath,
        $party2SignaturePath
    );

    // Verify file exists
    if (!file_exists($destDocx)) {
        throw new \RuntimeException("Generated DOCX missing at {$destDocx}");
    }

    // Public URL
    $urlDocx = asset("word/{$filenameDocx}");

    return response()->json([
        'status'   => 'success',
        'word_url' => $urlDocx,
        'agreement_id' => $aggriment_id,
        'message'  => 'Document generated successfully.'
    ]);
}

  /* ---------------- CHECK NEW */
  public function convertWordToPdf(Request $request)
{
    $validator = Validator::make($request->all(), [
        'filename' => ['required', 'string', 'max:255'],
        'agreement_id' => ['required', 'integer', 'exists:aggriments,id'],
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'message' => 'Invalid PDF conversion request.', 'errors' => $validator->errors(), 'output' => [], 'pdf_name' => null, 'pdf_url' => null], 422);
    }

    $filename = basename($request->string('filename')->toString());
    if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'docx') {
        return response()->json(['status' => false, 'message' => 'Only DOCX files can be converted to PDF.', 'output' => [], 'pdf_name' => null, 'pdf_url' => null], 422);
    }

    $pdfName = pathinfo($filename, PATHINFO_FILENAME) . '.pdf';

    // Input DOCX
    $inputPath = public_path('word/' . $filename);

    // Output PDF directory (storage/app/public/pdfs)
    //$outputDir = Storage::disk('public')->path('pdfs');
    
    $outputDir = public_path('agreement_pdfs');


    if (!file_exists($inputPath)) {
        return response()->json([
            'status' => false,
            'message' => 'DOCX not found.',
            'output' => [],
            'pdf_name' => $pdfName,
            'pdf_url' => null,
        ], 404);
    }

    try {
        File::ensureDirectoryExists($outputDir, 0775, true);

    // ✅ FORCE A4 PDF
        $process = new Process([
            config('services.libreoffice.binary'), '--headless', '--nologo', '--nofirststartwizard',
            '--convert-to', 'pdf:writer_pdf_Export:PaperSize=A4', '--outdir', $outputDir, $inputPath,
        ]);
        $process->setTimeout(120);
        $process->run();

        $output = array_values(array_filter(preg_split('/\r?\n/', trim($process->getOutput() . "\n" . $process->getErrorOutput()))));
        $pdfPath = $outputDir . DIRECTORY_SEPARATOR . $pdfName;

        if (!$process->isSuccessful() || !is_file($pdfPath)) {
            Log::warning('Agreement DOCX-to-PDF conversion failed.', ['agreement_id' => $request->agreement_id, 'filename' => $filename, 'exit_code' => $process->getExitCode(), 'output' => $output]);
            return response()->json(['status' => false, 'message' => 'Unable to convert the agreement to PDF.', 'output' => $output, 'pdf_name' => $pdfName, 'pdf_url' => null], 422);
        }

        Aggriment::whereKey($request->integer('agreement_id'))->update(['documents' => $pdfName]);

        return response()->json([
            'status' => true,
            'message' => 'PDF generated successfully.',
            'output' => $output,
            'pdf_name' => $pdfName,
            'pdf_url' => asset('agreement_pdfs/' . $pdfName),
        ]);
    } catch (\Throwable $exception) {
        Log::error('Agreement PDF conversion crashed.', ['agreement_id' => $request->agreement_id, 'filename' => $filename, 'exception' => $exception]);

        return response()->json([
            'status' => false,
            'message' => 'PDF generation failed on the server. Please try again.',
            'output' => [],
            'pdf_name' => $pdfName,
            'pdf_url' => null,
        ], 500);
    }
}


public function preview($file)
{
    $path = $this->resolveAgreementPdfPath($file);

    if ($path === null) {
        abort(404);
    }

    return response()->file($path, [
        'Content-Type' => 'application/pdf'
    ]);
}


  public function download($file)
{
    $path = $this->resolveAgreementPdfPath($file);

    if ($path === null) {
        abort(404);
    }

    return response()->download($path);
}

/**
 * Resolves a caller-supplied filename to a file inside the agreement PDF
 * directory, or null.
 *
 * The route matches `.*`, so the parameter can contain slashes and dot
 * segments. Concatenating it onto a base path let anyone read arbitrary files
 * off the server — `../../.env` returned the database credentials. Two checks
 * close that: basename() throws away any directory portion, and realpath()
 * confirms the result really sits under the intended directory even if the
 * name resolves through a symlink.
 */
private function resolveAgreementPdfPath($file): ?string
{
    if (!is_string($file) || $file === '') {
        return null;
    }

    $name = basename($file);

    if ($name === '' || $name === '.' || $name === '..') {
        return null;
    }

    if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'pdf') {
        return null;
    }

    $baseDir = realpath(public_path('agreement_pdfs'));
    $path = realpath($baseDir . DIRECTORY_SEPARATOR . $name);

    if ($baseDir === false || $path === false) {
        return null;
    }

    if (!str_starts_with($path, $baseDir . DIRECTORY_SEPARATOR)) {
        return null;
    }

    return is_file($path) ? $path : null;
}

  
  	
  
 // ----------------- Vehicle Photo ----------------------------------
  
  public function getVehicleImagePath($imageName, $type, $agreementId)
{
    if (!$imageName) {
        return null;
    }

    $relativePath = 'admin/images/vehicle_images/' . $imageName;

    $base64 = $this->imageToBase64($relativePath);

    return $this->saveBase64Image($base64, $type . '_' . $agreementId);
}
  
   // ----------------- Vehicle Photo ----------------------------------

  
	


	private function createDefaultTemplate(string $path, string $title): void
	{
		$phpWord = new PhpWord();
		$section = $phpWord->addSection();
		$section->addText($title, ['bold' => true, 'size' => 16]);
		$section->addTextBreak(1);
		$section->addText('Name: ${name}');
		$section->addText('Phone: ${phone}');
		$section->addText('Age: ${age}');

		$writer = IOFactory::createWriter($phpWord, 'Word2007');
		$writer->save($path);
	}

	private function normalizePlaceholdersToCurly(string $srcPath): string
	{
		$this->assertValidDocx($srcPath);

		$tmpDir = storage_path('app/tmp');
		if (! is_dir($tmpDir)) mkdir($tmpDir, 0777, true);
		$destPath = $tmpDir . DIRECTORY_SEPARATOR . 'norm_' . Str::uuid() . '.docx';

		$zip = new \ZipArchive();
		$zipOut = new \ZipArchive();
		if ($zip->open($srcPath) !== true) {
			throw new \RuntimeException('DOCX template is invalid or unreadable: ' . $srcPath);
		}
		if ($zipOut->open($destPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
			$zip->close();
			throw new \RuntimeException('Unable to create normalized DOCX copy for: ' . $srcPath);
		}
		for ($i = 0; $i < $zip->numFiles; $i++) {
			$stat = $zip->statIndex($i);
			$name = $stat['name'] ?? '';
			$content = $zip->getFromIndex($i);
			if ($content === false) $content = '';

			if (preg_match('#^word/(document|header\\d*|footer\\d*)\\.xml$#', $name)) {
				// Replace $var$ or $ var $ or $ first name $ with ${var} or ${first_name}
				$content = preg_replace_callback('/\\$[\\p{Z}\\h]*([\\p{L}\\p{N}_]+(?:[\\p{Z}\\h]+[\\p{L}\\p{N}_]+)*)[\\p{Z}\\h]*\\$/u', function ($m) {
					$key = trim(preg_replace('/[\\p{Z}\\h]+/u', '_', $m[1]), '_');
					return '${' . $key . '}';
				}, $content);

				// Cross-run normalization: $ ... $ where inner may include tags
				$content = preg_replace_callback('/\\$([\\s\\S]{0,300}?)\\$/u', function ($m) {
					$textOnly = trim(strip_tags($m[1]));
					$textOnly = preg_replace('/[\\p{Z}\\h]+/u', ' ', $textOnly);
					if ($textOnly !== '' && preg_match('/^[\\p{L}\\p{N}_]+(?:\\s+[\\p{L}\\p{N}_]+)*$/u', $textOnly)) {
						$key = preg_replace('/\\s+/u', '_', $textOnly);
						return '${' . $key . '}';
					}
					return $m[0];
				}, $content);
			}
			$zipOut->addFromString($name, $content);
		}
		$zip->close();
		$zipOut->close();
		return $destPath;
	}

  private function resolveDocumentTemplatePath(?string $storedPath): string
  {
      if (empty($storedPath)) {
          throw new \RuntimeException('Document template path is empty.');
      }

      $candidates = [
          $storedPath,
          storage_path('app/' . ltrim($storedPath, '/')),
          storage_path(ltrim($storedPath, '/')),
          base_path(ltrim($storedPath, '/')),
          public_path(ltrim($storedPath, '/')),
      ];

      foreach ($candidates as $candidate) {
          if (is_string($candidate) && $candidate !== '' && is_file($candidate)) {
              return $candidate;
          }
      }

      $localPath = Storage::disk('local')->path(ltrim($storedPath, '/'));
      if (is_file($localPath)) {
          return $localPath;
      }

      throw new \RuntimeException('Document template not found: ' . $storedPath);
  }

  private function assertValidDocx(string $templatePath): void
  {
      if (!is_file($templatePath)) {
          throw new \RuntimeException('DOCX template not found: ' . $templatePath);
      }

      $zip = new \ZipArchive();
      $openResult = $zip->open($templatePath);

      if ($openResult !== true) {
          $zip->close();
          throw new \RuntimeException('DOCX template is invalid or unreadable: ' . $templatePath . ' (ZipArchive error: ' . $openResult . ')');
      }

      $hasDocumentXml = $zip->locateName('word/document.xml') !== false;
      $zip->close();

      if (!$hasDocumentXml) {
          throw new \RuntimeException('DOCX template is missing word/document.xml: ' . $templatePath);
      }
  }

  private function fillTemplateAndSave(string $templatePath, array $values, string $destinationPath): void
{
    $this->assertValidDocx($templatePath);

    $processor = new TemplateProcessor($templatePath);
    foreach ($values as $key => $val) {

        // CHECK: is it an image?
        if ($this->isImagePath($val)) {

            // Remove $ sign if key has $something$
            $cleanKey = trim($key, '$');

            // Default size for normal images
            $width  = 500;
            $height = 110;

            // Aadhaar images Size
            if (in_array($cleanKey, [
                'party_2_adhar_front',
                'party_1_adhar_back',
                'party_2_adhar_back',
                'party_1_adhar_front',
            ], true)) {
                $width  = 600;  // change as needed
                $height = 280;  // change as needed
            }
          
          	// Vehicle images Size
            if (in_array($cleanKey, [
                'front_side',
                'back_side',
                'left_side',
                'right_side',
            ], true)) {
                $width  = 275;  // change as needed
                $height = 400;  // change as needed
            }

           // Signature Size
            if (in_array($cleanKey, [
                'party_1_signature',
                'party_2_signature',
            ], true)) {
                $width  = 100;  // change as needed
                $height = 50;  // change as needed
            }

            $processor->setImageValue($cleanKey, [
                'path'   => $val,
                'width'  => $width,
                'height' => $height,
                'ratio'  => true,
            ]);
        } else {

            // Normal text — clean placeholder
            $cleanKey = trim($key, '$');
            $processor->setValue($cleanKey, (string) $val);
        }
    }

    // Clear unused variables (your existing logic)
    if (method_exists($processor, 'getVariables')) {

        $vars = $processor->getVariables();

        if (is_array($vars)) {
            foreach ($vars as $var) {
                if (!array_key_exists($var, $values)) {
                    $processor->setValue($var, '');
                }
            }
        }
    }

    // Save final DOCX
    $processor->saveAs($destinationPath);

    // Your post-processing step (keep it)
    $this->finalizeDollarPlaceholders($destinationPath, $values);
}
	private function fillTemplateAndSav(string $templatePath, array $values, string $destinationPath): void
	{
		$processor = new TemplateProcessor($templatePath);

		foreach ($values as $key => $val) {

			// CHECK: is it an image?
			if ($this->isImagePath($val)) {

				// Remove $ sign if key has $something$
				$cleanKey = trim($key, '$');

				$processor->setImageValue($cleanKey, [
					'path'   => $val,
					'width'  => 200,
					'height' => 80,
					'ratio'  => true,
				]);
              
              
			} else {

				// Normal text — clean placeholder
				$cleanKey = trim($key, '$');
				$processor->setValue($cleanKey, (string) $val);
			}
		}

		// Clear unused variables (your existing logic)
		if (method_exists($processor, 'getVariables')) {

			$vars = $processor->getVariables();

			if (is_array($vars)) {
				foreach ($vars as $var) {
					if (!array_key_exists($var, $values)) {
						$processor->setValue($var, '');
					}
				}
			}
		}

		// Save final DOCX
		$processor->saveAs($destinationPath);

		// Your post-processing step (keep it)
		$this->finalizeDollarPlaceholders($destinationPath, $values);
	}

	private function isImagePath($path)
	{
		if (!is_string($path)) return false;

		if (!file_exists($path)) return false;

		$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

		return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
	}

	private function finalizeDollarPlaceholders(string $docxPath, array $values): void
	{
		// dd('finalizeDollarPlaceholders', $docxPath, $values);
		$zip = new \ZipArchive();
		if ($zip->open($docxPath) !== true) {
			return;
		}
		$parts = [];
		for ($i = 0; $i < $zip->numFiles; $i++) {
			$stat = $zip->statIndex($i);
			$name = $stat['name'] ?? '';
			if (preg_match('#^word/(document|header\\d*|footer\\d*)\\.xml$#', $name)) {
				$parts[] = $name;
			}
		}

		foreach ($parts as $name) {
			$xml = $zip->getFromName($name);
			if ($xml === false) continue;

			// Replace matches we can resolve to provided values
			$xml = preg_replace_callback('/\\$([\\s\\S]{1,5000}?)\\$/u', function ($m) use ($values) {
				$textOnly = trim(strip_tags($m[1]));
				$textOnly = preg_replace('/[^\\p{L}\\p{N}_]+/u', ' ', $textOnly);
				$key = trim(preg_replace('/\\s+/u', '_', $textOnly), '_');
				if ($key !== '' && array_key_exists($key, $values)) {
					return htmlspecialchars((string) $values[$key], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
				}
				// Leave for a second pass
				return $m[0];
			}, $xml);

			// Remove any remaining $...$ that couldn't be mapped
			$xml = preg_replace('/\\$[\\s\\S]{1,5000}?\\$/u', '', $xml);

			$zip->addFromString($name, $xml);
		}

		$zip->close();
	}





	private function selectFontByLanguage(string $language, array $allMatches): array
	{
		$scriptConfigs = [
			'gujarati' => [
				'fonts' => [
					[
						'family' => 'Noto Sans Gujarati',
						'paths' => [
							storage_path('app/fonts/NotoSansGujarati-Regular.ttf'),
							base_path('vendor/dompdf/dompdf/lib/fonts/NotoSansGujarati-Regular.ttf'),
						],
					],
					[
						'family' => 'LMG Arun',
						'paths' => [
							storage_path('app/fonts/LMG-Arun.ttf'),
							base_path('vendor/dompdf/dompdf/lib/fonts/LMG-Arun.ttf'),
						],
					],
				],
				'fallback' => "'Noto Sans Gujarati', 'Shruti', sans-serif",
			],
			'hindi' => [
				'fonts' => [
					[
						'family' => 'Noto Sans Devanagari',
						'paths' => [
							storage_path('app/fonts/NotoSansDevanagari-Regular.ttf'),
							storage_path('fonts/NotoSansDevanagari-Regular.ttf'),
							base_path('storage/app/fonts/NotoSansDevanagari-Regular.ttf'),
							base_path('public/fonts/NotoSansDevanagari-Regular.ttf'),
							base_path('vendor/dompdf/dompdf/lib/fonts/NotoSansDevanagari-Regular.ttf'),
							'C:\\Windows\\Fonts\\NotoSansDevanagari-Regular.ttf',
						],
					],
					[
						'family' => 'Mangal',
						'paths' => [
							storage_path('app/fonts/Mangal.ttf'),
							$this->windowsFontPath('mangal.ttf'),
							$this->windowsFontPath('Mangal.ttf'),
							'C:\\Windows\\Fonts\\mangal.ttf',
						],
					],
					[
						'family' => 'Nirmala UI',
						'paths' => [
							$this->windowsFontPath('Nirmala.ttf'),
							$this->windowsFontPath('NirmalaB.ttf'),
							'C:\\Windows\\Fonts\\Nirmala.ttf',
							'C:\\Windows\\Fonts\\nirmala.ttf',
						],
					],
				],
				'fallback' => "'Noto Sans Devanagari', 'Mangal', 'Nirmala UI', sans-serif",
			],
			'marathi' => [
				'fonts' => [
					[
						'family' => 'Noto Sans Devanagari',
						'paths' => [
							storage_path('app/fonts/NotoSansDevanagari-Regular.ttf'),
							base_path('vendor/dompdf/dompdf/lib/fonts/NotoSansDevanagari-Regular.ttf'),
						],
					],
				],
				'fallback' => "'Noto Sans Devanagari', 'Mangal', sans-serif",
			],
			'tamil' => [
				'fonts' => [
					[
						'family' => 'Noto Sans Tamil',
						'paths' => [
							storage_path('app/fonts/NotoSansTamil-Regular.ttf'),
							base_path('vendor/dompdf/dompdf/lib/fonts/NotoSansTamil-Regular.ttf'),
						],
					],
					[
						'family' => 'Latha',
						'paths' => [
							storage_path('app/fonts/Latha.ttf'),
							$this->windowsFontPath('Latha.ttf'),
						],
					],
				],
				'fallback' => "'Noto Sans Tamil', 'Latha', sans-serif",
			],
			'default' => [
				'fonts' => [
					[
						'family' => 'DejaVu Sans',
						'paths' => [
							storage_path('app/fonts/DejaVuSans.ttf'),
							base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf'),
						],
					],
				],
				'fallback' => "'DejaVu Sans', sans-serif",
			],
		];

		$config = $scriptConfigs[$language] ?? $scriptConfigs['default'];

		$selectedFont = $this->selectFontResource($config['fonts']);
		if (!$selectedFont) {
			$selectedFont = $this->selectFontResource($scriptConfigs['default']['fonts']);
		}

		$family = $selectedFont['family'] ?? ($config['fonts'][0]['family'] ?? 'DejaVu Sans');
		$path = $selectedFont['path'] ?? null;
		$fallbackStack = $config['fallback'] ?? $scriptConfigs['default']['fallback'];
		$defaultFamily = $path ? $family : 'DejaVu Sans';

		return [
			'family' => $family,
			'path' => $path,
			'stack' => "'{$family}', {$fallbackStack}",
			'default' => $defaultFamily,
		];
	}
	private function selectFontResource(array $fontOptions): ?array
	{
		foreach ($fontOptions as $option) {
			if (!isset($option['family'], $option['paths'])) {
				continue;
			}
			$path = $this->resolveExistingFontPath($option['paths']);
			if ($path) {
				return [
					'family' => $option['family'],
					'path' => $path,
				];
			}
		}
		return null;
	}
	private function resolveExistingFontPath(array $paths): ?string
	{
		foreach ($paths as $path) {
			if (is_string($path) && $path !== '' && file_exists($path)) {
				return $path;
			}
		}
		return null;
	}
	private function windowsFontPath(string $filename): ?string
	{
		$root = getenv('WINDIR') ?: ($_SERVER['SystemRoot'] ?? null);
		if (!$root) {
			return null;
		}
		$path = $root . DIRECTORY_SEPARATOR . 'Fonts' . DIRECTORY_SEPARATOR . $filename;
		return file_exists($path) ? $path : null;
	}
	private function extractTextFromDocxFile(string $docxPath): string
	{
		$text = '';
		$zip = new \ZipArchive();

		if ($zip->open($docxPath) === true) {
			// Extract text from main document and headers/footers
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$stat = $zip->statIndex($i);
				$name = $stat['name'] ?? '';
				if (preg_match('#^word/(document|header\\d*|footer\\d*)\\.xml$#', $name)) {
					$xml = $zip->getFromName($name);
					if ($xml !== false) {
						// Extract text from w:t (text) elements, preserving Unicode
						if (preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/is', $xml, $matches)) {
							foreach ($matches[1] as $match) {
								$decoded = html_entity_decode($match, ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8');
								$text .= $decoded . ' ';
							}
						}
					}
				}
			}
			$zip->close();
		}

		return trim($text);
	}
	private function detectLanguageFromText(string $text): array
	{
		$text = mb_substr($text, 0, 10000);

		$scriptPatterns = [
			'gujarati' => '/[\x{0A81}-\x{0AFF}]/u',
			'hindi' => '/[\x{0900}-\x{097F}]/u',
			'tamil' => '/[\x{0B80}-\x{0BFF}]/u',
			'bengali' => '/[\x{0980}-\x{09FF}]/u',
			'telugu' => '/[\x{0C00}-\x{0C7F}]/u',
			'marathi' => '/[\x{0900}-\x{097F}]/u', // Uses Devanagari like Hindi
		];

		$selectedKey = 'default';
		$highestCount = 0;
		$languageMatches = [];

		foreach ($scriptPatterns as $key => $pattern) {
			if (preg_match_all($pattern, $text, $matches)) {
				$count = count($matches[0]);
				$languageMatches[$key] = $count;
				if ($count > $highestCount) {
					$highestCount = $count;
					$selectedKey = $key;
				}
			} else {
				$languageMatches[$key] = 0;
			}
		}

		return [
			'selected_language' => $selectedKey,
			'all_matches' => $languageMatches,
		];
	}
}
