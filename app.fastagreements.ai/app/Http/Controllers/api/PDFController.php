<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Aggriment;
use App\Models\AgreementAttribute;
use App\Models\CategoryAttribute;
use App\Models\Installment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Mpdf\Mpdf;
use Illuminate\Support\Carbon;
use Dompdf\Options;
use Dompdf\Dompdf;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Models\CategoryLanguage;
use App\Models\History;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Round;
use App\Traits\ImageResizer;
use Exception;
use Illuminate\Support\Facades\DB;


class PDFController extends Controller
{
    use ImageResizer;

    public function generatePDFx()
    {

        $data = [
            'title' => 'Money Aggrimnent',
            'date' => date('d/m/y'),
        ];



        return view('aggriments.money_aggriment_gu', compact('data'));
    }
    public function generatePDF2(Request $request)
    {

        $persone_1_details = Customer::where('mobile', $request->persone_1_mobile_number)->first();
        $persone_2_details = Customer::where('mobile', $request->persone_2_mobile_number)->first();

        $formattedDate = Carbon::now()->format('d F Y, g:i a');

        $data = [
            'title' => 'એફીડેવીટ - પાહેંવરી',
            // Add any other data you need to pass to the view
        ];

        return view('aggriments.money_aggriment_gu', $data); // Renders HTML view

        $pdf = Pdf::loadView('your_blade_view', $data);


        $pdf = Pdf::loadView('aggriments.money_aggriment_gu', $data);

        $pdf->getDomPDF()->set_option("isFontSubsettingEnabled", true);


        $filePath = 'assets/pdfs/money_agreement_' . time() . '.pdf';

        File::put(public_path($filePath), $pdf->output());

        $fileUrl = asset($filePath);

        return $pdf->download('document.pdf');

        return response()->json([
            'status' => 'success',
            'url' => $fileUrl,
            'filename' => 'money_agreement.pdf',
        ]);
        $data = [
            'title' => 'એફીડેવીટ - પાહેંવરી',
        ];

    }

    public function generatePDF(Request $request)
    {
        // Fetch details from the database
        $persone_1_details = Customer::where('mobile', $request->persone_1_mobile_number)->first();
        $persone_2_details = Customer::where('mobile', $request->persone_2_mobile_number)->first();

        // Format date
        $formattedDate = Carbon::now()->format('d F Y, g:i a');

        $data = [
            'title' => 'એફીડેવીટ - પાહેંવરી',
            'date' => $formattedDate,
            'persone_1_details' => $persone_1_details,
            'persone_2_details' => $persone_2_details,
        ];

        $pdf = Pdf::loadView('aggriments.money_aggriment', $data);
        $pdf->getDomPDF()->set_option("isFontSubsettingEnabled", true);

        $filePath = 'assets/pdfs/money_agreement_' . time() . '.pdf';

        File::put(public_path($filePath), $pdf->output());

        $fileUrl = asset($filePath);


        return response()->json([
            'status' => 'success',
            'url' => $fileUrl,
            'filename' => 'money_agreement.pdf',
        ]);
    }

// working  function for pdf
    public function printAffidavit(Request $request)
    {

$fmt = new \NumberFormatter(app()->getLocale(), \NumberFormatter::SPELLOUT);
$amount_in_word = ucfirst($fmt->format(1000));
// dd($amount_in_word);
        // dd($request->person1_signature);
        $persone_1 = $request->persone_1_id;
        $persone_2 = $request->persone_2_id;

        $rent = $request->rent;
        $deposite = $request->deposite;
        $duration = (int)$request->duration;
        $startDate = $request->start_date;
        $language =  $request->lang;
        $category_id = $request->category_id;
        // dd($category_id);
        $current_date = Carbon::now();
        $startDateTime = Carbon::createFromFormat('d-m-Y', $startDate);

        $endDateTime = $startDateTime->copy()->addMonths($duration);

        $persone_1_details = Customer::where('id', $persone_1)->first();
        $persone_2_details = Customer::where('id', $persone_2)->first();

        $path = public_path('assets/img/pista_color_paper.jpg');
        $base64 = base64_encode(file_get_contents($path));
        $type = pathinfo($path, PATHINFO_EXTENSION);
        // $path = public_path('assets/img/pista_color_paper.jpg');

        // dd($persone_1_details->person_image_url);
        // / Check if person 1 image exists and is valid
        $person_1_image_base64 = null;
        if (!empty($persone_1_details->person_image)) {
            $person_1_image_path = public_path(parse_url($persone_1_details->person_image_url, PHP_URL_PATH));
            if (file_exists($person_1_image_path)) {
                $person_1_image_base64 = base64_encode(file_get_contents($person_1_image_path));
            }
        }

        // Check if person 2 image exists and is valid
        $person_2_image_base64 = null;
        if (!empty($persone_2_details->person_image)) {
            $person_2_image_path = public_path(parse_url($persone_2_details->person_image_url, PHP_URL_PATH));
            if (file_exists($person_2_image_path)) {
                $person_2_image_base64 = base64_encode(file_get_contents($person_2_image_path));
            }
        }




        $show = CategoryLanguage::where('category_id', $category_id)->where('language_id', $request->language_id)->first();

        if (!$show) {
            return response()->json([
                'status' => false,
                'message' => 'No record found for the given category and language.',
            ], 404);
        }

        $show->agreement_text = str_replace('@party_one_name', $persone_1_details->name, $show->agreement_text);
        $show->agreement_text = str_replace('@party_one_address', $persone_1_details->address, $show->agreement_text);
        $show->agreement_text = str_replace('@party_two_name', $persone_2_details->name, $show->agreement_text);
        $show->agreement_text = str_replace('@party_two_address', $persone_2_details->address, $show->agreement_text);
        $show->agreement_text = str_replace('@deposite', $deposite, $show->agreement_text);
        $show->agreement_text = str_replace('@start_date', $startDateTime->format('d-m-Y'), $show->agreement_text);
        $show->agreement_text = str_replace('@end_date', $endDateTime->format('d-m-Y'), $show->agreement_text);
        $show->agreement_text = str_replace('@rent', $rent, $show->agreement_text);
        $show->agreement_text = str_replace('@duration', $duration, $show->agreement_text);
        $show->agreement_text = str_replace('@person_2_image', $person_2_image_base64, $show->agreement_text);
        $show->agreement_text = str_replace('@person_1_image', $person_1_image_base64, $show->agreement_text);
        if ($persone_2_details->signature) {
            $show->agreement_text = str_replace(
                '@person2_signature',
                '<img src="data:image/png;base64,' . $persone_2_details->signature . '" alt="Party 2 Signature" style="width: 200px; height: auto; border: 0px solid #000;">',
                $show->agreement_text
            );
        }
        $show->agreement_text = str_replace(
            '@person1_signature',
            '<img src="data:image/png;base64,' . $persone_1_details->signature . '" alt="Party 1 Signature" style="width: 200px; height: auto; border: 0px solid #000;">',
            $show->agreement_text
        );
        // $show->agreement_text = str_replace('@person1_signature', $person1_signature_base64, $show->agreement_text);
        $data = [
            'persone_1_details' => $persone_1_details,
            'persone_2_details' => $persone_2_details,
            'start_date' => $startDateTime->format('d-m-Y'),
            'end_date' => $endDateTime->format('d-m-Y'),
            'rent' => $rent,
            'deposite' => $deposite,
            'duration' => $duration,
            'bg_image' => $path,
            'person_2_image_url' => $person_2_image_base64,
            'person_1_image_url' => $person_1_image_base64,
            'show' => $show,
        ];


        // dd($data);
        // Load the view with the data and generate the PDF
        if ($request->language_id == 2) {
            $pdf = Pdf::loadView('aggriments.money_aggriment_gu', $data)
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isPhpEnabled', true);

            if ($request->view == 1) {
                return $pdf->stream('affidavit_gu.pdf');
            }
        } elseif ($request->language_id == 1) {
            $pdf = Pdf::loadView('aggriments.money_aggriment_gu', $data)
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isPhpEnabled', true);

            if ($request->view == 1) {
                return $pdf->stream('affidavit_en.pdf');
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => "Invalid language or language ID. Please select a valid option.",
            ], 400);
        }

        $pdf->getDomPDF()->set_option("isFontSubsettingEnabled", true);

        $filePath = 'assets/pdfs/rent_agreement_' . time() . '.pdf';

        File::put(public_path($filePath), $pdf->output());

        $fileUrl = asset($filePath);


        return response()->json([
            'status' => 'success',
            'url' => $fileUrl,
            'filename' => 'money_agreement.pdf',
        ]);
    }

    public function printAffidavit_new(Request $request)
    {

        // dd($request->person1_signature);

        $persone_1 = $request->persone_1_id;
        $persone_2 = $request->persone_2_id;

        $rent = $request->rent;
        $deposite = $request->deposite;
        $duration = (int)$request->duration;
        $startDate = $request->start_date;
        $language =  $request->lang;
        $category_id = $request->category_id;
        // dd($category_id);
        $current_date = Carbon::now();
        $startDateTime = Carbon::createFromFormat('d-m-Y', $startDate);

        $endDateTime = $startDateTime->copy()->addMonths($duration);

        $persone_1_details = Customer::where('id', $persone_1)->first();
        $persone_2_details = Customer::where('id', $persone_2)->first();

        $path = public_path('assets/img/dfer.jpg');
        $base64 = base64_encode(file_get_contents($path));
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $path = public_path('assets/img/dfer.jpg');

        // dd($persone_1_details->person_image_url);
        // / Check if person 1 image exists and is valid
        $person_1_image_base64 = null;
        if (!empty($persone_1_details->person_image)) {
            $person_1_image_path = public_path(parse_url($persone_1_details->person_image_url, PHP_URL_PATH));
            if (file_exists($person_1_image_path)) {
                $person_1_image_base64 = base64_encode(file_get_contents($person_1_image_path));
            }
        }

        // Check if person 2 image exists and is valid
        $person_2_image_base64 = null;
        if (!empty($persone_2_details->person_image)) {
            $person_2_image_path = public_path(parse_url($persone_2_details->person_image_url, PHP_URL_PATH));
            if (file_exists($person_2_image_path)) {
                $person_2_image_base64 = base64_encode(file_get_contents($person_2_image_path));
            }
        }

        $show = CategoryLanguage::where('category_id', $category_id)->where('language_id', $request->language_id)->first();

        if (!$show) {
            return response()->json([
                'status' => false,
                'message' => 'No record found for the given category and language.',
            ], 404);
        }

        $show->agreement_text = str_replace('@party_one_name', $persone_1_details->name, $show->agreement_text);
        $show->agreement_text = str_replace('@party_one_address', $persone_1_details->address, $show->agreement_text);
        $show->agreement_text = str_replace('@party_two_name', $persone_2_details->name, $show->agreement_text);
        $show->agreement_text = str_replace('@party_two_address', $persone_2_details->address, $show->agreement_text);
        $show->agreement_text = str_replace('@deposite', $deposite, $show->agreement_text);
        $show->agreement_text = str_replace('@start_date', $startDateTime->format('d-m-Y'), $show->agreement_text);
        $show->agreement_text = str_replace('@end_date', $endDateTime->format('d-m-Y'), $show->agreement_text);
        $show->agreement_text = str_replace('@rent', $rent, $show->agreement_text);
        $show->agreement_text = str_replace('@duration', $duration, $show->agreement_text);
        $show->agreement_text = str_replace('@person_2_image', $person_2_image_base64, $show->agreement_text);
        $show->agreement_text = str_replace('@person_1_image', $person_1_image_base64, $show->agreement_text);
        $show->agreement_text = str_replace('@person1_signature', $person_2_image_base64, $request->person1_signature);
        $show->agreement_text = str_replace('@person2_signature', $person_1_image_base64, $request->person2_signature);

        $data = [
            'persone_1_details' => $persone_1_details,
            'persone_2_details' => $persone_2_details,
            'start_date' => $startDateTime->format('d-m-Y'),
            'end_date' => $endDateTime->format('d-m-Y'),
            'rent' => $rent,
            'deposite' => $deposite,
            'duration' => $duration,
            'bg_image' => $path,
            'person_2_image_url' => $person_2_image_base64,
            'person_1_image_url' => $person_1_image_base64,
            'show' => $show,
        ];


        // Load the view with the data and generate the PDF
        if ($language == "gu" && $request->language_id == 3) {
            $pdf = Pdf::loadView('aggriments.money_aggriment_gu', $data)
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isPhpEnabled', true);
        }

        if ($language == "en" && $request->language_id == 1) {


            $pdf = Pdf::loadView('aggriments.money_aggriment_gu', $data)
                ->setOption('isHtml5ParserEnabled', true)  // Enable HTML5 parser
                ->setOption('isPhpEnabled', true);
        }

        if ($request->view == 1) {
            return $pdf->stream('affidavit.pdf');
        }

        $pdf->getDomPDF()->set_option("isFontSubsettingEnabled", true);

        $filePath = 'assets/pdfs/rent_agreement_' . time() . '.pdf';

        File::put(public_path($filePath), $pdf->output());

        $fileUrl = asset($filePath);

        $deal = new Deal;
        $deal->person_1 = $persone_1;
        $deal->person_2 = $persone_2;
        $deal->amount = $deposite;
        $deal->category_id = $category_id;
        $deal->language_id = $request->language_id;
        $deal->from_date = $startDateTime;
        $deal->to_date = $endDateTime;
        $deal->contract_date = $current_date;
        $deal->status = 1;
        $deal->lat = $request->lat;
        $deal->lon = $request->lon;
        $deal->allow_live_location = 1;
        $deal->interest_rate = 0;
        $deal->payable_amount = $rent;
        $deal->interest_term_in_month = 1;
        $deal->witness = $request->witness;
        $deal->notes = $request->notes;
        $deal->currency = "INR";
        $deal->save();




        return response()->json([
            'status' => 'success',
            'url' => $fileUrl,
            'filename' => 'money_agreement.pdf',
        ]);
    }


    public function create_aggriment(Request $request)
    {
       
       try {
        $party_1 = $request->party_1_id;
        $party_2 = $request->party_2_id;
        $path = public_path('assets/img/dfer.jpg');

        // validation start
        $validator = validator::make($request->all(), [
            'party_1_id' => 'required',
            'party_2_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => true,
                'message' => 'Validation Fail',
                'errors' => $validator->errors()
            ], 400);
        }

        // validation end

        // fatch data from customers table  

        $persons = Customer::where('is_active', 1);

        $person1 = (clone $persons)->where('id', $party_1)->first();

        if (!$person1) {
            return response()->json([
                'status' => false,
                'message' => 'party one  not found'
            ]);
        }

        $person2 = (clone $persons)->where('id', $party_2)->first();
        // dd($person2);
        if (!$person2) {
            return response()->json([
                'status' => false,
                'message' => 'party  two not found'
            ]);
        }
         

        // Add aggriment Details in aggriment table 
        $aggriment = new Aggriment;
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
        $aggriment->purpose = $request->purpose;
         $person_one = $request->file('party_one_image') ? $this->image_resize($request->file('party_one_image'), 'person_images') : null;
        $person_two = $request->file('party_two_image') ? $this->image_resize($request->file('party_two_image'), 'person_images') : null;

        $aggriment->party_1_image = $person_one;
        $aggriment->party_2_image = $person_two;
        $aggriment->save();
        // Add aggriment Details in aggriment table 



        if($request->attribute){
              $attributes = explode('|', $request->attribute);
            
        foreach ($attributes as $attribute) {
            $attribute_parts = explode(': ', $attribute);

            $attribute_id = (int) trim($attribute_parts[0]);
            $attribute_value = trim($attribute_parts[1], '"');

            $agreement_attribute = new AgreementAttribute;
            $agreement_attribute->agreement_id = $aggriment->id;
            $agreement_attribute->attribute_id = $attribute_id;
            $agreement_attribute->attribute_value = $attribute_value;
            $agreement_attribute->save();
        }
        }

        // insert create_aggriment 


        $aggriment = Aggriment::with('party1', 'party2', 'category', 'attributes.categoryAttribute')->where('id', $aggriment->id)->first();

        if (!is_null($aggriment->amount)) {

            $start_date = $aggriment->start_date;
            $end_date  = $aggriment->end_date;

            $start = Carbon::parse($start_date);
            $end =  Carbon::parse($end_date);
            $perid = $start->diffInMonths($end);

            $amount = $aggriment->amount / $aggriment->period;

            for ($i = 0; $i < $aggriment->period; $i++) {
                $installment  = new Installment;
                $installment->agreement_id = $aggriment->id;
                $installment->emi_amount = $amount;
                $installment->emi_date = $start->copy()->addMonths($i + 1);
                $installment->save();
            }
        }
        $attribute_list = AgreementAttribute::join('category_attributes', 'category_attributes.id', '=', 'agreement_attribute.attribute_id')->where('agreement_id', $aggriment->id)->get();
        $startDateTime = Carbon::createFromFormat('Y-m-d', $aggriment->start_date);
        $endDateTime = Carbon::createFromFormat('Y-m-d', $aggriment->end_date);


         $person_1_image_base64 = null;
        if (!empty($aggriment->party_1_image)) {
            $person_1_image_path = base_path('public/admin/images/person_images_thumb/' . basename($aggriment->party_one_image_url));
            if (file_exists($person_1_image_path)) {
                $person_1_image_base64 = base64_encode(file_get_contents($person_1_image_path));
            }
        }

        $person_2_image_base64 = null;
        if (!empty($aggriment->party_2_image)) {
            $person_2_image_path = base_path('public/admin/images/person_images_thumb/' . basename($aggriment->party_two_image_url));
            if (file_exists($person_2_image_path)) {
                $person_2_image_base64 = base64_encode(file_get_contents($person_2_image_path));
            }
        }


        $show = CategoryLanguage::where('category_id', $aggriment->category_id)->where('language_id', $aggriment->aggriment_language_id)->first();

        // dd($show->agreement_text);
        if (!$show) {
            return response()->json([
                'status' => false,
                'message' => 'No record found for the given category and language.',
            ], 404);
        }
      $fmt = new \NumberFormatter(app()->getLocale(), \NumberFormatter::SPELLOUT);
$fmt->setTextAttribute(\NumberFormatter::DEFAULT_RULESET, "%spellout-cardinal"); // ICU rule
$depositValue = $aggriment->security;
$rentValue = $aggriment->amount;
$dp_word = $fmt->format($depositValue);
$rant_word = $fmt->format($rentValue);


$deposite_in_word = ucfirst($dp_word) . ' Only';
$rent_in_word = ucfirst($rant_word) . ' Only';


$guarantor_name = $aggriment->guarantor;   
$guarantor_number = $aggriment->guarantor_number;    
// dd($guarantor_number);

$guarantor_name_array = explode(',',$guarantor_name);
$guarantor_number_array = explode(',',$guarantor_number);


        $show->agreement_text = str_replace('@party_one_name', $aggriment->party1->name, $show->agreement_text);
        $show->agreement_text = str_replace('@party_one_address', $aggriment->party1->address, $show->agreement_text);
        $show->agreement_text = str_replace('@party_one_number', $aggriment->party1->mobile, $show->agreement_text);
        $show->agreement_text = str_replace('@party_two_name', $aggriment->party2->name, $show->agreement_text);
        $show->agreement_text = str_replace('@party_two_address', $aggriment->party2->address, $show->agreement_text);
        $show->agreement_text = str_replace('@party_two_number', $aggriment->party2->mobile, $show->agreement_text);
        $show->agreement_text = str_replace('@deposite', $aggriment->security, $show->agreement_text);
        $show->agreement_text = str_replace('@dp_in_word', $deposite_in_word, $show->agreement_text);
        $show->agreement_text = str_replace('@agreement_amount', $aggriment->amount, $show->agreement_text);            // ++ Arjun
        $show->agreement_text = str_replace('@start_date', $startDateTime->format('d-m-Y'), $show->agreement_text);
        $show->agreement_text = str_replace('@end_date', $endDateTime->format('d-m-Y'), $show->agreement_text);
        $show->agreement_text = str_replace('@rent', $aggriment->amount, $show->agreement_text);
        $show->agreement_text = str_replace('@rnt_amount_in_word', $rent_in_word, $show->agreement_text);
        $show->agreement_text = str_replace('@duration', $aggriment->period, $show->agreement_text);
        $show->agreement_text = str_replace('@person_2_image', $person_2_image_base64, $show->agreement_text);
        $show->agreement_text = str_replace('@person_1_image', $person_1_image_base64, $show->agreement_text);
        $show->agreement_text = str_replace('@guarantor', $guarantor_name_array[0] ?? " ", $show->agreement_text);
        $show->agreement_text = str_replace('@guaranto', $guarantor_name_array[1] ?? " ", $show->agreement_text);
        $show->agreement_text = str_replace('@number_1', $guarantor_number_array[0] ?? " ", $show->agreement_text);
        $show->agreement_text = str_replace('@number_2', $guarantor_number_array[1] ?? " ", $show->agreement_text);
        $show->agreement_text = str_replace('@agreement_date', $aggriment->agreement_date, $show->agreement_text);
        $show->agreement_text = str_replace('@reference_no', $aggriment->reference_no, $show->agreement_text);
        $show->agreement_text = str_replace('@address', $aggriment->address, $show->agreement_text);
        $show->agreement_text = str_replace('@note', $aggriment->note, $show->agreement_text);
        
            $attributes = DB::table('agreement_attribute')
                ->leftJoin('category_attributes', 'agreement_attribute.attribute_id', '=', 'category_attributes.id')
                ->where('agreement_attribute.agreement_id', $aggriment->id)
                ->select('agreement_attribute.id', 'agreement_attribute.attribute_id', 'category_attributes.attribute_name', 'category_attributes.attribute_code', 'agreement_attribute.attribute_value')
                ->get();
                
                foreach ($attributes as $attribute){
                    $show->agreement_text = str_replace($attribute->attribute_code, $attribute->attribute_value, $show->agreement_text);
                }


        if ($aggriment->party_2_signature) {
            $show->agreement_text = str_replace(
                '@person2_signature',
                '<img src="data:image/png;base64,' . $aggriment->party_2_signature . '" alt="Party 2 Signature" style="width: 200px; height: auto; border: 0px solid #000;">',
                $show->agreement_text
            );
        }
        if ($aggriment->party_1_signature) {
            $show->agreement_text = str_replace(
                '@person1_signature',
                '<img src="data:image/png;base64,' . $aggriment->party_1_signature . '" alt="Party 1 Signature" style="width: 200px; height: auto; border: 0px solid #000;">',
                $show->agreement_text
            );
        }

        $attribute_list_html = '';
        foreach ($attribute_list as $list) {
            $attribute_list_html .= '<li><strong class="english_data" >' . $list->attribute_name . ':</strong> ' . $list->value . '</li>';
        }

        $show->agreement_text = str_replace('@attribute_values', $attribute_list_html, $show->agreement_text);


        $data = [
            'persone_1_details' => $aggriment->party1,
            'persone_2_details' => $aggriment->party2,
            'start_date' => $startDateTime->format('d-m-Y'),
            'end_date' => $endDateTime->format('d-m-Y'),
            'rent' => $aggriment->repayment_term,
            'deposite' => $aggriment->security,
            'duration' => $aggriment->period,
            'bg_image' => $path,
            'person_2_image_url' => $person_2_image_base64,
            'person_1_image_url' => $person_1_image_base64,
            'show' => $show,
            'attribute_list' => $attribute_list
        ];
        

        if ($show->language_id == 2) {
            $pdf = Pdf::loadView('aggriments.money_aggriment_gu', $data)
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isPhpEnabled', true);

            // return $pdf->stream('affidavit.pdf');
        }


        if ($show->language_id == 1) {

            $pdf = Pdf::loadView('aggriments.money_aggriment_gu', $data)
                ->setOption('isHtml5ParserEnabled', true)  // Enable HTML5 parser
                ->setOption('isPhpEnabled', true);

            // return $pdf->stream('affidavit.pdf');
        }

        if ($request->view == 1) {
            return $pdf->stream('affidavit.pdf');
        }

        $pdf->getDomPDF()->set_option("isFontSubsettingEnabled", true);

        $filePath = 'assets/pdfs/rent_agreement_' . time() . '.pdf';

        File::put(public_path($filePath), $pdf->output());

        $fileUrl = asset($filePath);

        $aggriment->documents = $fileUrl;
        $aggriment->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Deal created successfully',
            'url' => $fileUrl,
            'aggriment_id' =>$aggriment->id,
            'filename' => 'money_agreement.pdf',
        ]);
        } catch (Exception $e) {
            // dd($e);

            // Return a JSON response with a generic error message
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving customers.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    public function list_aggriment(Request $request)
    {
          $user_id = $request->user_id;


        // when user is person 1 in deal 
        $party_1 = Aggriment::with('category')->select(
            'agreements.*',
            'party1.name as party_one_name',
            'party1.mobile as party_one_number',
            'party2.name as party_two_name',
            'party2.mobile as party_two_number',
            'languages.language_name as language_name',
            'languages.language_in_guj as language',
        )->join('customers as party1', 'party1.id', '=', 'agreements.party_1_id')
            ->join('customers as party2', 'party2.id', '=',  'agreements.party_2_id')
            ->join('languages', 'languages.id', '=', 'agreements.aggriment_language_id')
            ->where('party_1_id', $user_id)
            ->orderBy('agreements.id', 'DESC')
            ->get();
            
            // dd($party_1);
            if($party_1->isEmpty()){

                 return response()->json([
            'status' => false,
            'message' => 'No Aggriment created yet',
            'data' => [],
            ]
        );
            }

        foreach ($party_1 as $party) {

            $installments = Installment::where('agreement_id', $party->id)->get();

            // $history = History::where('agreement_id', 1)->get();

            $response[] = [
                'agreement_id' => $party->id,
                'party_two_name' => $party->party_two_name,
                'party_two_number' => $party->party_two_number,
                'party_one_name' => $party->party_one_name,
                'party_one_number' => $party->party_one_number,
                'amount' => $party->amount,
                'period' => $party->period,
                'start_date' => $party->start_date,
                'end_date' => $party->end_date,
                'security' => $party->security,
                'language' => $party->language,
                'language_name' => $party->language_name,
                'agreement_type' => $party->category->category_name,
                'installments' => $installments,
                // 'history' => $history

            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Agreements retrived successfully',
            'data' => $response,
            ]
        );
    }


    // insert remark in deal 

    public function add_remark_in_deal(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'agreement_id' => 'required|exists:agreements,id',
            // 'image' => 'file',
            'text' => 'required'
        ]);

        if ($validator->fails()) {
            return response([
                'status' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $validator->errors(),
            ]);
        }

        if ($request->file('image')) {
            $image = $request->file('image') ? $this->image_resize($request->file('image'), 'remark_images') : 'default.webp';
        }


        $history = new History();
        $history->agreement_id = $request->agreement_id;
        $history->customer_id = $request->customer_id;
        $history->images = $image ?? null;
        $history->text = $request->text;
        $history->save();

        return response()->json([
            'status' => true,
            'message' => 'Remark insert',
            'data' => $history
        ]);
    }

    public function deal_list(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'error' => $validator->errors()
            ]);
        }

        $customer_id = $request->customer_id;

        $party_1 = Aggriment::with(
            'party1',
            'party2',
            'category',
            'attributes.categoryAttribute'
        )
            ->where('party_1_id', $customer_id)
            ->orderby('id', 'DESC')
            ->get();

        $party_2 = Aggriment::with(
            'party1',
            'party2',
            'category',
            'attributes.categoryAttribute'
        )
            ->where('party_2_id', $customer_id)
            ->orderby('id', 'DESC')
            ->get();

        return response()->json([
            'party_1' => $party_1,
            'party_2' => $party_2

        ]);
    }

    public function deal_details(Request $request)
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
        $aggriment_details = Aggriment::with(
            'party1',
            'party2',
            'category',
            // 'attributes.categoryAttribute',
            'installments',
            'histories.cutomers',
             'invoice'

        )->where('id', $agreement_id)->first();
        
         $aggriment_attribute = AgreementAttribute::select('agreement_attribute.id','category_attributes.attribute_name','agreement_attribute.attribute_value')
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


 public function delete_history(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'history_id' => 'required|exists:histories,id'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }
    
        try {
            $history = History::find($request->history_id);
    
            if (!$history) {
                return response()->json([
                    'status' => false,
                    'message' => 'History not found'
                ], 404); // HTTP 404 Not Found
            }
    
            $history->delete();
    
            return response()->json([
                'status' => true,
                'message' => 'History deleted successfully'
            ], 200); // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
}
