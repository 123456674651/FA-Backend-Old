<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\DealCategoryController;
use App\Http\Controllers\api\LanguageController;
use App\Http\Controllers\api\PurposeController;
use App\Http\Controllers\api\CustomerController;
use App\Models\Sceme;
use App\Http\Controllers\api\DealController;
use App\Http\Controllers\api\PageController;
use App\Http\Controllers\api\OtpController;
use App\Http\Controllers\api\AadharInfoController;
use App\Http\Controllers\api\SliderController;
use App\Http\Controllers\api\PDFController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\AgreementController;
use App\Models\Customer;
use App\Http\Controllers\api\PhpWordController;
use App\Http\Controllers\LibreOfficeController;
use App\Http\Controllers\api\FeedController;
use App\Http\Controllers\api\SubscriptionApiController;
use App\Http\Controllers\api\InvoiceController;


Route::get('clear', function () {
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    \Artisan::call('route:clear');
    \Artisan::call('optimize:clear');
        $link = public_path('storage');
   

    \Artisan::call('storage:link');

    return 'cleared';
});

Route::get('/advocates', function () {

    return response()->json([
        "status" => true,
        "message" => "Static advocate list",
        "data" => [
            [
                "id" => 1,
                "image" => "https://srv1260879.hstgr.cloud/admin/images/adv/Hardik_C_Brahmbhatt.jpeg",
                "name" => "Adv. Hardik C. Brahmbhatt",
                "lawyer_type" => "Criminal Lawyer",
                "is_verified" => true,
                "price" => 1500,
                "time" => "30 minutes",
                "total_reviews" => 128,
                "experience" => 16,
                "about" => "16 years in revenue expert",
                "languages_known" => ["Hindi", "English", "Gujarati"],
                "video" => "https://example.com/videos/rahul_intro.mp4",
                "document" => "https://example.com/docs/bar_certificate.pdf",
                "expertise" => ["Revenue"],
                "degree" => ["Bsc","LLB"],
                "address" => "Office No.: 203, 3rd Floor, Tulsi Arcade ,Lal Darwaja , Opp. Gabani Hospital , Station Road,Surat-395003",
                "mobile_number" => "xxxxxxxxxx"
            ],
            [
                "id" => 2,
                "image" => "https://srv1260879.hstgr.cloud/admin/images/adv/Chirag_j_patwa.jpeg",
                "name" => "Adv. Chirag j patwa",
                "lawyer_type" => "Civil, Criminal, Family Lawyer",
                "is_verified" => false,
                "price" => 1000,
                "time" => "20 minutes",
                "total_reviews" => 76,
                "experience" => 16,
                "about" => "Specialist in family and matrimonial disputes.",
                "languages_known" => ["Hindi", "English"],
                "video" => null,
                "document" => "https://example.com/docs/family_law_cert.pdf",
                "expertise" => ["Family Law", "Divorce Cases"],
                "degree" => ["Bsc","LLB"],
                "address" => "7/2757 saiyed pura chandulal sheth street surat",
               "mobile_number" => 9824146037

            ],
          [
                "id" => 3,
                "image" => "https://srv1260879.hstgr.cloud/admin/images/adv/Adv.ManishkumarB.Merchant.jpeg",
                "name" => "Adv. Manishkumar B. Merchant",
                "lawyer_type" => "Advocate & Notary",
                "is_verified" => false,
                "price" => 1000,
                "time" => "20 minutes",
                "total_reviews" => 76,
                "experience" => 15,
                "about" => "Advocate & Notary",
                "languages_known" => ["Hindi", "English"],
                "video" => null,
                "document" => "https://example.com/docs/family_law_cert.pdf",
                "expertise"=> ["Revenue", "Notary"],
                "degree" => ["Bsc","LLB"],
                "address" => "Office No. 107, Amradhar Complex, 1st Floor, G.T.D.C Char Rasta, Fulpad Kafrasam Road, Katargam, Surat, Gujarat, India",
                "mobile_number" => 9825482116

            ],
          [
                "id" => 4,
                "image" => "https://srv1260879.hstgr.cloud/admin/images/adv/Shailesh.jpeg",
                "name" => "Adv. Shailesh N. Rajput",
                "lawyer_type" => "Family Lawyer",
                "is_verified" => false,
                "price" => 1000,
                "time" => "20 minutes",
                "total_reviews" => 76,
                "experience" => 20,
                "about" => "Advocate & Notary",
                "languages_known" => ["Hindi", "English"],
                "video" => null,
                "document" => "https://example.com/docs/family_law_cert.pdf",
                "expertise"=> ["Revenue","Notary"],
                "degree" => ["B.Com","LLB"],
                "address" => "Office No. S-7 to S-10, 2nd Floor, Meghdhanush Apartment, Adajan Patiya, Surat, Gujarat, India",
                "mobile_number" => 8866802102

            ],
          [
                "id" => 5,
                "image" => "https://srv1260879.hstgr.cloud/admin/images/adv/TejashLMehta.jpeg",
                "name" => "Adv. Tejash L Mehta",
                "lawyer_type" => "Civil, Criminal",
                "is_verified" => false,
                "price" => 3000,
                "time" => "20 minutes",
                "total_reviews" => 76,
                "experience" => 15,
                "about" => "Trails court, High court and various tribunal like SSRD, GRT, DRR, DRAT, NCLT, NCLAT and INSURANCE OBDUSMAN",
                "languages_known" => ["Hindi", "English","Gujarati"],
                "video" => null,
                "document" => "https://example.com/docs/family_law_cert.pdf",
                "expertise"=> ["Revenue","Notary"],
                "degree" => [" M.Com","M.B.A","LL.M","PH.D running (Researcher)"],
                "address" => "213, Nishal Center,Behind the Sanjiv Kumar Auditorium, Pal, Surat -395009",
                "mobile_number" => "NA"

            ],
          [
                "id" => 6,
                "image" => "https://srv1260879.hstgr.cloud/admin/images/adv/nita_a_patel.jpeg",
                "name" => "Adv. Nita A Patel",
                "lawyer_type" => "Criminal, Family",
                "is_verified" => false,
                "price" => 3000,
                "time" => "20 minutes",
                "total_reviews" => 76,
                "experience" => 27,
                "about" => "Trails court, High court and various tribunal like SSRD, GRT, DRR, DRAT, NCLT, NCLAT and INSURANCE OBDUSMAN",
                "languages_known" => ["Hindi", "English","Gujarati"],
                "video" => null,
                "document" => "https://example.com/docs/family_law_cert.pdf",
                "expertise"=> ["Criminal Case","Family Case"],
                "degree" => ["B. Com","L.L.B"],
                "address" => "Office Add :  G-1, Himanshu Park, Arogya Nagar, Nr. Umra Police station,Athwalines, Surat",
                "mobile_number" =>  9227905173

            ]
        ]
    ]);
});
Route::get('/pdf/preview/{file}', [PhpWordController::class, 'preview'])
    ->where('file', '.*')
    ->name('pdf.preview');

Route::get('/pdf/download/{file}', [PhpWordController::class, 'download'])
    ->where('file', '.*')
    ->name('pdf.download');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Deal Category Routes
Route::get('/deal_categories', [DealCategoryController::class, 'index'])->name('api.dealCategories.index');
Route::post('/deal_categories/create', [DealCategoryController::class, 'store'])->name('api.dealCategories.store');
Route::get('/deal_categories/show/{deal_category}', [DealCategoryController::class, 'show'])->name('api.dealCategories.show');
Route::post('/deal_categories/update/{deal_category}', [DealCategoryController::class, 'update'])->name('api.dealCategories.update');
Route::delete('/deal_categories/delete/{deal_category}', [DealCategoryController::class, 'destroy'])->name('api.dealCategories.destroy');
Route::put('/deal_categories/status_changes/{id}', [DealCategoryController::class, 'status_changes'])->name('api.dealCategories.statusChanges');

// Language Routes
Route::get('/languages', [LanguageController::class, 'index'])->name('api.languages.index');


// Purposes Routes
Route::get('/purposes', [PurposeController::class, 'index'])->name('api.purposes.index');
Route::post('/purposes/create', [PurposeController::class, 'store'])->name('api.purposes.store');
Route::get('/purposes/show/{purpose}', [PurposeController::class, 'show'])->name('api.purposes.show');
Route::post('/purposes/update/{purpose}', [PurposeController::class, 'update'])->name('api.purposes.update');
Route::delete('/purposes/delete/{purpose}', [PurposeController::class, 'destroy'])->name('api.purposes.destroy');

// Customers Routes
Route::get('/customers', [CustomerController::class, 'index'])->name('api.customers.index');
Route::post('/customers/create', [CustomerController::class, 'store'])->name('api.customers.store');
Route::get('/customers/show/{customer}', [CustomerController::class, 'show'])->name('api.customers.show');
Route::post('/customers/update/{customer}', [CustomerController::class, 'update'])->name('api.customers.update');
Route::delete('/customers/delete/{customer}', [CustomerController::class, 'destroy'])->name('api.customers.destroy');
Route::get('get_customer_by_mobile', [CustomerController::class, 'getCustomerByMobile']);
Route::post('upload_image', [CustomerController::class, 'upload_image']);
Route::post('registertion', [CustomerController::class, 'registertion']);


Route::get('scemelist', function () {
    return Sceme::select('id', 'emi_pay_method')->get();
});

// Pages Route
Route::get('/pages', [PageController::class, 'index'])->name('api.pages.index');
Route::post('/pages/create', [PageController::class, 'store'])->name('api.pages.store');
Route::get('/pages/show/{page}', [PageController::class, 'show'])->name('api.pages.show');
Route::post('/pages/update/{page}', [PageController::class, 'update'])->name('api.pages.update');
Route::delete('/pages/delete/{page}', [PageController::class, 'destroy'])->name('api.pages.destroy');
Route::get('/pages_legal', [PageController::class, 'indexlegal'])->name('api.pages.indexlegal');

// Deal Route
Route::get('/deal_history/{id}', [DealController::class, 'showDealHistory']);

// OTP Verify Route
Route::post('/customer_register', [OtpController::class, 'registerCustomer']);
Route::post('/verify_mobile', [OtpController::class, 'sendOtp'])->name('otp.send');
Route::post('/verify_mobile_otp', [OtpController::class, 'verifyOtp'])->name('otp.verify');

// Aadhar Info Route
Route::post('/aadhar_info', [AadharInfoController::class, 'store']);
Route::get('/aadhar_info/{id}', [AadharInfoController::class, 'show']);
Route::post('/aadhar_info/{id}', [AadharInfoController::class, 'update']);
Route::delete('/aadhar_info/{id}', [AadharInfoController::class, 'destroy']);

// Slider Routes
Route::get('sliders', [SliderController::class, 'index'])->name('sliders.index'); // Get all sliders
Route::post('sliders/create', [SliderController::class, 'store'])->name('sliders.store'); // Create a new slider
Route::get('sliders/{id}', [SliderController::class, 'show'])->name('sliders.show'); // Get a specific slider by ID
Route::post('sliders/update/{id}', [SliderController::class, 'update'])->name('sliders.update'); // Update a specific slider by ID
Route::delete('sliders/{id}', [SliderController::class, 'destroy'])->name('sliders.destroy'); // Delete a specific slider by ID

Route::get('generate-pdf', [PDFController::class, 'generatePDF']);
// Route::get('/print-affidavit', [PDFController::class, 'printAffidavit']);
Route::get('/agreement', [PDFController::class, 'printAffidavit']);
Route::post('attribute/list',[AttributeController::class, 'list' ])->name('attribute.list');
Route::post('create_aggriment', [PDFController::class, 'create_aggriment']);
Route::post('list_aggriment', [PDFController::class, 'list_aggriment']);
Route::post('add_remark',[PDFController::class, 'add_remark_in_deal']);
Route::post('deal_list', [PDFController::class, 'deal_list']);
Route::post('deal_details', [PDFController::class, 'deal_details']);
Route::post('delete_history', [PDFController::class, 'delete_history']);

Route::post('/create_aggriment/v1', [PhpWordController::class, 'create_aggriment']);
Route::Post('/convert_Word_to_pdf/v1', [PhpWordController::class, 'convertWordToPdf']);
//Route::get('/create_aggriment/v1', [PhpWordController::class, 'test']);

//Route::get('/download_pdf', [LibreOfficeController::class, 'convertWordToPdf']);

Route::get('subscription/status/{customer_id}', [SubscriptionApiController::class, 'status']);
Route::post('subscription/renew', [SubscriptionApiController::class, 'renew']);
Route::get('subscription-plans',[SubscriptionApiController::class, 'subscription_plane_list']);

//_____________________________FEED_______________________________________________________________

Route::get('/feed', [FeedController::class, 'index']);
Route::post('/feed', [FeedController::class, 'store']);
Route::put('/feed', [FeedController::class, 'update']);
Route::delete('/feed/delete/{feed}', [FeedController::class, 'destroy']);
Route::post('/feed/toggle_like', [FeedController::class, 'toggle_like']);
Route::post('/feed/comment', [FeedController::class, 'addComment']);
Route::delete('/feed/delete_comment/{comment}', [FeedController::class, 'deleteComment']);
Route::get('/feed/like_customers/{id}', [FeedController::class, 'getFeedLikeCustomers']);
Route::get('/feed/comments/{id}', [FeedController::class, 'getFeedComments']);
Route::post('/feed/toggle_comment_like', [FeedController::class, 'toggle_comment_like']);
Route::post('/feed/comment/report', [FeedController::class, 'addCommentReport']);
Route::put('/feed/comment/report/status', [FeedController::class, 'updateCommentReportStatus']);
Route::delete('/feed/comment/report/delete/{id}', [FeedController::class, 'deleteFeedCommentReport']);
Route::get('/feed/comment/like_customers/{id}', [FeedController::class, 'getFeedCommentLikeCustomers']);
Route::post('/feed/report', [FeedController::class, 'addFeedReport']);
Route::put('/feed/report/status', [FeedController::class, 'updateFeedReportStatus']);
Route::delete('/feed/report/delete/{id}', [FeedController::class, 'deleteFeedReport']);

//_____________________________END FEED_______________________________________________________________

Route::get('/forms', function () {
    return response()->json([
        "status" => true,
        "message" => "Forms list fetched successfully",
        "data" => [
            [
                "id" => 1,
                "title" => "Birth & Death Registration",
                "subtitle" => "જન્મ - મરણ નોંધણી",
                "download_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/Birth_Form1.pdf?ver=7629",
                "sample_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/Birth_Form1_SAMPLE.pdf?ver=6159"
            ],
            [
                "id" => 2,
                "title" => "Marriage Registration",
                "subtitle" => "લગ્ન નોંધણી",
                "download_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/Marriage_Memorandum.zip?ver=1239",
                "sample_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/Marriage_Form1_MarriageCerti_SAMPLE.pdf?ver=2147"
            ],
            [
                "id" => 3,
                "title" => "Property Tax",
                "subtitle" => "મિલકત વેરો",
                "download_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/81_PropTax_NameTransfer.pdf?ver=1163",
                "sample_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/81_PropTax_NameTransfer_SAMPLE.pdf?ver=5463"
            ],
            [
                "id" => 4,
                "title" => "Tax on Profession",
                "subtitle" => "વ્યવસાય વેરો",
                "download_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/3499_ProfTax_RC.zip?ver=5434",
                "sample_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/3499_ProfTax_RC_SAMPLE.pdf?ver=8591"
            ],
            [
                "id" => 5,
                "title" => "Shops and Establishment",
                "subtitle" => "ગુમાસ્તા ધારા",
                "download_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2254_FormD_NominationGroupInsurance.pdf?ver=7602",
                "sample_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2254_FormD_NominationGroupInsurance_SAMPLE.pdf?ver=853"
            ],
            [
                "id" => 6,
                "title" => "Municipal Library",
                "subtitle" => "મ્યુનિસિપલ લાઇબ્રેરી",
                "download_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2405_Membership%20to%20Narmad%20Lib.pdf?ver=6987",
                "sample_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2405_MembershiptoNarmadLib_SAMPLE.pdf?ver=4906"
            ],
            [
                "id" => 7,
                "title" => "Water Supply",
                "subtitle" => "પાણી પુરવઠો",
                "download_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/297_NewWaterConnection.pdf?ver=8998",
                "sample_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/297_NewWaterConnection_SAMPLE.pdf?ver=1998"
            ],
            [
                "id" => 8,
                "title" => "Drainage System",
                "subtitle" => "ગટર વ્યવસ્થા",
                "download_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2250_NewDrainageConnection.pdf?ver=8702",
                "sample_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2250_NewDrainageConnection_SAMPLE.pdf?ver=5125"
            ],
            [
                "id" => 9,
                "title" => "Public Health",
                "subtitle" => "આરોગ્ય",
                "download_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/18_PetDogs_SAMPLE.pdf?ver=5164",
                "sample_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/18_PetDogs.pdf?ver=956"
            ],
            [
                "id" => 10,
                "title" => "Community Hall",
                "subtitle" => "કોમ્યુનિટી હોલ",
                "download_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/CZ_CommHallPartyPlotBooking.pdf?ver=2926",
                "sample_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/CZ_CommHallPartyPlotBooking_SAMPLE.pdf?ver=8770"
            ],
            [
                "id" => 11,
                "title" => "Urban Community Development",
                "subtitle" => "અર્બન કમ્યુનિટી ડેવલપમેન્ટ",
                "download_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/UCD_SHGRegForm.pdf?ver=7652",
                "sample_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/UCD_SHGRegForm_SAMPLE.pdf?ver=7215"
            ],
            [
                "id" => 12,
                "title" => "SUMAN High Schools",
                "subtitle" => "સુમન હાઈસ્કૂલ",
                "download_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2944_SUMAN_AdmissionForm.pdf?ver=7044",
                "sample_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2944_SUMAN_AdmissionForm_SAMPLE.pdf?ver=6296"
            ],
            [
                "id" => 13,
                "title" => "Sports & Other Facilities",
                "subtitle" => "ક્રીયાત્મક અને અન્ય સગવડ",
                "download_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/Medical%20Certi_Online%20Manual.pdf?ver=9629",
                "sample_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/Medical%20Certi_Online%20Manual_SAMPLE.pdf?ver=9625"
            ],
            [
                "id" => 14,
                "title" => "Indoor Stadium",
                "subtitle" => "ઇન્ડોર સ્ટેડિયમ",
                "download_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2899_IndoorStadiumApplicationForm.pdf?ver=5679",
                "sample_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2899_IndoorStadiumApplicationForm_SAMPLE.pdf?ver=2208"
            ],
            [
                "id" => 15,
                "title" => "Hall Booking",
                "subtitle" => "હોલ બુકિંગ",
                "download_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2000_RangUpvan_BookingForm.pdf?ver=8495",
                "sample_url" => "https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2000_RangUpvan_BookingForm_SAMPLE.pdf?ver=6529"
            ]
        ]
    ]);
});

Route::get(
    'party-wise-agreements/{id}',
    [DealController::class, 'partyWiseAggrimentsApi']
)->whereNumber('id');
Route::get('legal/{legal}', [App\Http\Controllers\api\LegalController::class, 'index']);

// CMS Pages API Routes
Route::get('/cms-pages', [\App\Http\Controllers\api\CMSController::class, 'index'])->name('api.cms-pages.index');
Route::get('/cms-pages/{slug}', [\App\Http\Controllers\api\CMSController::class, 'show'])->name('api.cms-pages.show');
// Legal Notice and Notification API routes (Sanctum protected)
Route::get('/notifications', [\App\Http\Controllers\api\NotificationController::class, 'index'])->name('api.notifications.index');
Route::patch('/legal-notices/{id}/status', [\App\Http\Controllers\api\LegalNoticeController::class, 'updateStatus'])->name('api.legal-notices.status');
Route::apiResource('/legal-notices', \App\Http\Controllers\api\LegalNoticeController::class)->names([
    'index' => 'api.legal-notices.index',
    'store' => 'api.legal-notices.store',
    'show' => 'api.legal-notices.show',
    'update' => 'api.legal-notices.update',
    'destroy' => 'api.legal-notices.destroy',
]);

Route::patch('/customers/allow-prompt', [CustomerController::class, 'updateAllowPrompt']);

// Advocate Management APIs
Route::get('/advocates', [\App\Http\Controllers\api\AdvocateApiController::class, 'index']);
Route::get('/advocates/{id}', [\App\Http\Controllers\Api\AdvocateApiController::class, 'show']);

// Subscription Invoice PDF Routes
Route::get('subscription-invoices/pdf-url/{id}', [InvoiceController::class, 'getInvoicePdfUrl']);
Route::get('subscription-invoices/view/{id}', [InvoiceController::class, 'viewPdf']);
Route::get('subscription-invoices/download/{id}', [InvoiceController::class, 'downloadPdf']);


Route::get('/category-warnings', [\App\Http\Controllers\api\CategoryWarningApiController::class, 'index']);

// Customer Reports APIs
Route::get('/customer-reports', [\App\Http\Controllers\Api\CustomerReportController::class, 'index']);

// Agreement Reports APIs
Route::get('/admin/agreement-reports', [\App\Http\Controllers\Api\AgreementReportController::class, 'index']);
// Push Notifications APIs
Route::prefix('admin/notifications')->group(function () {
    Route::get('templates', [\App\Http\Controllers\Api\PushNotificationApiController::class, 'listTemplates']);
    Route::post('templates', [\App\Http\Controllers\Api\PushNotificationApiController::class, 'storeTemplate']);
    Route::get('templates/{id}', [\App\Http\Controllers\Api\PushNotificationApiController::class, 'showTemplate']);
    Route::put('templates/{id}', [\App\Http\Controllers\Api\PushNotificationApiController::class, 'updateTemplate']);
    Route::delete('templates/{id}', [\App\Http\Controllers\Api\PushNotificationApiController::class, 'destroyTemplate']);
    Route::post('send', [\App\Http\Controllers\Api\PushNotificationApiController::class, 'sendNotification']);
    Route::get('history', [\App\Http\Controllers\Api\PushNotificationApiController::class, 'listHistory']);
    Route::get('history/{id}', [\App\Http\Controllers\Api\PushNotificationApiController::class, 'showHistory']);
});
// Email Templates APIs
Route::prefix('admin/emails')->group(function () {
    Route::get('templates', [\App\Http\Controllers\Api\EmailTemplateApiController::class, 'listTemplates']);
    Route::post('templates', [\App\Http\Controllers\Api\EmailTemplateApiController::class, 'storeTemplate']);
    Route::get('templates/{id}', [\App\Http\Controllers\Api\EmailTemplateApiController::class, 'showTemplate']);
    Route::put('templates/{id}', [\App\Http\Controllers\Api\EmailTemplateApiController::class, 'updateTemplate']);
    Route::delete('templates/{id}', [\App\Http\Controllers\Api\EmailTemplateApiController::class, 'destroyTemplate']);
    Route::post('templates/{id}/test', [\App\Http\Controllers\Api\EmailTemplateApiController::class, 'testSendTemplate']);
    Route::get('logs', [\App\Http\Controllers\Api\EmailTemplateApiController::class, 'listLogs']);
    Route::get('logs/{id}', [\App\Http\Controllers\Api\EmailTemplateApiController::class, 'showLog']);
    Route::post('logs/{id}/resend', [\App\Http\Controllers\Api\EmailTemplateApiController::class, 'resendLog']);
});


