<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AadharInfoController;
use App\Http\Controllers\api\AdvocateApiController;
use App\Http\Controllers\api\AgreementReportController;
use App\Http\Controllers\api\AuthApiController;
use App\Http\Controllers\api\CategoryWarningApiController;
use App\Http\Controllers\api\CMSController;
use App\Http\Controllers\api\CustomerController;
use App\Http\Controllers\api\CustomerReportController;
use App\Http\Controllers\api\DealCategoryController;
use App\Http\Controllers\api\DealController;
use App\Http\Controllers\api\FeedController;
use App\Http\Controllers\api\InvoiceController;
use App\Http\Controllers\api\LanguageController;
use App\Http\Controllers\api\LegalController;
use App\Http\Controllers\api\LegalNoticeController;
use App\Http\Controllers\api\NotificationController;
use App\Http\Controllers\api\PageController;
use App\Http\Controllers\api\PartyVerificationController;
use App\Http\Controllers\api\PaymentApiController;
use App\Http\Controllers\api\PDFController;
use App\Http\Controllers\api\PhpWordController;
use App\Http\Controllers\api\PushNotificationApiController;
use App\Http\Controllers\api\PurposeController;
use App\Http\Controllers\api\RazorpayWebhookController;
use App\Http\Controllers\api\SliderController;
use App\Http\Controllers\api\SubscriptionApiController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\api\V2\AuthController as AuthControllerV2;
use App\Http\Controllers\api\V2\CommonController as CommonControllerV2;
use App\Http\Middleware\EnsureMinimumAppVersion;
use App\Models\Sceme;
use App\Support\ApiResponse;

/*
|--------------------------------------------------------------------------
| Mobile API
|--------------------------------------------------------------------------
|
| Three tiers:
|
|   public       – reference data drawn before anyone signs in
|   auth.jwt     – a customer, identified by a session token issued after an
|                  MSG91-verified phone number
|   auth         – an admin, on the existing Blade session guard
|
| Identity comes from the token, and ownership is checked in the handler. The
| two exceptions are /update_aggriment/v1 and /feed/publish, which sit in the
| public tier and take a customer_id in the body — see the note above them.
|
| Removed in the JWT cutover:
|   GET  /clear             unauthenticated cache-clear and storage:link
|   GET  /advocates         a hardcoded closure that shadowed the controller
|   POST /verify_mobile     generated its own OTP and returned it in the body
|   POST /verify_mobile_otp issued no session
|   POST /customer_register  ) both replaced by /auth/otp-exchange, which
|   POST /registertion       ) provisions the account on first verified sign-in
|
*/

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

// Auth
Route::post('auth/otp-exchange', [AuthApiController::class, 'otpExchange']);
Route::post('auth/firebase-exchange', fn() => ApiResponse::error(
    410,
    'ENDPOINT_RETIRED',
    'This app version is no longer supported. Please update to continue.',
));
Route::get('auth/exists', [AuthApiController::class, 'exists']);

// Auth (v2 - MSG91 mobile OTP)
Route::post('v2/login', [AuthControllerV2::class, 'login']);
Route::post('v2/verified-otp', [AuthControllerV2::class, 'verifiedOtp']);
Route::post('v2/register', [AuthControllerV2::class, 'register']);

// Master & Reference Data
Route::get('/deal_categories', [DealCategoryController::class, 'index'])->name('api.dealCategories.index');
Route::get('/deal_categories/show/{deal_category}', [DealCategoryController::class, 'show'])->name('api.dealCategories.show');
Route::post('attribute/list', [AttributeController::class, 'list'])->name('api.attribute.list');
Route::get('/languages', [LanguageController::class, 'index'])->name('api.languages.index');
Route::get('/purposes', [PurposeController::class, 'index'])->name('api.purposes.index');
Route::get('/purposes/show/{purpose}', [PurposeController::class, 'show'])->name('api.purposes.show');
Route::get('/category-warnings', [CategoryWarningApiController::class, 'index']);
Route::get('subscription-plans', [SubscriptionApiController::class, 'subscription_plane_list']);
Route::get('/advocates', [AdvocateApiController::class, 'index']);
Route::get('/advocates/{id}', [AdvocateApiController::class, 'show']);

// Pages & CMS
Route::get('/pages', [PageController::class, 'index'])->name('api.pages.index');
Route::get('/pages/show/{page}', [PageController::class, 'show'])->name('api.pages.show');
Route::get('/pages_legal', [PageController::class, 'indexlegal'])->name('api.pages.indexlegal');
Route::get('legal/{legal}', [LegalController::class, 'index']);
Route::get('/cms-pages', [CMSController::class, 'index'])->name('api.cms-pages.index');
Route::get('/cms-pages/{slug}', [CMSController::class, 'show'])->name('api.cms-pages.show');
Route::get('sliders', [SliderController::class, 'index'])->name('api.sliders.index');
Route::get('sliders/{id}', [SliderController::class, 'show'])->name('api.sliders.show');

// Utilities
Route::get('scemelist', fn() => Sceme::select('id', 'emi_pay_method')->get());
Route::get('/forms', function () {
    return response()->json([
        'status' => true,
        'message' => 'Forms list fetched successfully',
        'data' => [
            ['id' => 1, 'title' => 'Birth & Death Registration', 'subtitle' => 'જન્મ - મરણ નોંધણી', 'download_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/Birth_Form1.pdf?ver=7629', 'sample_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/Birth_Form1_SAMPLE.pdf?ver=6159'],
            ['id' => 2, 'title' => 'Marriage Registration', 'subtitle' => 'લગ્ન નોંધણી', 'download_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/Marriage_Memorandum.zip?ver=1239', 'sample_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/Marriage_Form1_MarriageCerti_SAMPLE.pdf?ver=2147'],
            ['id' => 3, 'title' => 'Property Tax', 'subtitle' => 'મિલકત વેરો', 'download_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/81_PropTax_NameTransfer.pdf?ver=1163', 'sample_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/81_PropTax_NameTransfer_SAMPLE.pdf?ver=5463'],
            ['id' => 4, 'title' => 'Tax on Profession', 'subtitle' => 'વ્યવસાય વેરો', 'download_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/3499_ProfTax_RC.zip?ver=5434', 'sample_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/3499_ProfTax_RC_SAMPLE.pdf?ver=8591'],
            ['id' => 5, 'title' => 'Shops and Establishment', 'subtitle' => 'ગુમાસ્તા ધારા', 'download_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2254_FormD_NominationGroupInsurance.pdf?ver=7602', 'sample_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2254_FormD_NominationGroupInsurance_SAMPLE.pdf?ver=853'],
            ['id' => 6, 'title' => 'Municipal Library', 'subtitle' => 'મ્યુનિસિપલ લાઇબ્રેરી', 'download_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2405_Membership%20to%20Narmad%20Lib.pdf?ver=6987', 'sample_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2405_MembershiptoNarmadLib_SAMPLE.pdf?ver=4906'],
            ['id' => 7, 'title' => 'Water Supply', 'subtitle' => 'પાણી પુરવઠો', 'download_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/297_NewWaterConnection.pdf?ver=8998', 'sample_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/297_NewWaterConnection_SAMPLE.pdf?ver=1998'],
            ['id' => 8, 'title' => 'Drainage System', 'subtitle' => 'ગટર વ્યવસ્થા', 'download_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2250_NewDrainageConnection.pdf?ver=8702', 'sample_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2250_NewDrainageConnection_SAMPLE.pdf?ver=5125'],
            ['id' => 9, 'title' => 'Public Health', 'subtitle' => 'આરોગ્ય', 'download_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/18_PetDogs_SAMPLE.pdf?ver=5164', 'sample_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/18_PetDogs.pdf?ver=956'],
            ['id' => 10, 'title' => 'Community Hall', 'subtitle' => 'કોમ્યુનિટી હોલ', 'download_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/CZ_CommHallPartyPlotBooking.pdf?ver=2926', 'sample_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/CZ_CommHallPartyPlotBooking_SAMPLE.pdf?ver=8770'],
            ['id' => 11, 'title' => 'Urban Community Development', 'subtitle' => 'અર્બન કમ્યુનિટી ડેવલપમેન્ટ', 'download_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/UCD_SHGRegForm.pdf?ver=7652', 'sample_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/UCD_SHGRegForm_SAMPLE.pdf?ver=7215'],
            ['id' => 12, 'title' => 'SUMAN High Schools', 'subtitle' => 'સુમન હાઈસ્કૂલ', 'download_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2944_SUMAN_AdmissionForm.pdf?ver=7044', 'sample_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2944_SUMAN_AdmissionForm_SAMPLE.pdf?ver=6296'],
            ['id' => 13, 'title' => 'Sports & Other Facilities', 'subtitle' => 'ક્રીયાત્મક અને અન્ય સગવડ', 'download_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/Medical%20Certi_Online%20Manual.pdf?ver=9629', 'sample_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/Medical%20Certi_Online%20Manual_SAMPLE.pdf?ver=9625'],
            ['id' => 14, 'title' => 'Indoor Stadium', 'subtitle' => 'ઇન્ડોર સ્ટેડિયમ', 'download_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2899_IndoorStadiumApplicationForm.pdf?ver=5679', 'sample_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2899_IndoorStadiumApplicationForm_SAMPLE.pdf?ver=2208'],
            ['id' => 15, 'title' => 'Hall Booking', 'subtitle' => 'હોલ બુકિંગ', 'download_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2000_RangUpvan_BookingForm.pdf?ver=8495', 'sample_url' => 'https://www.suratmunicipal.gov.in/Content/Documents/Onlineforms/2000_RangUpvan_BookingForm_SAMPLE.pdf?ver=6529'],
        ],
    ]);
});

// Webhooks
Route::post('webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])
    ->withoutMiddleware(EnsureMinimumAppVersion::class)
    ->middleware('throttle:60,1');


/*
 * Agreement edit and feed share, deliberately outside the token group.
 *
 * Both used to read the caller out of the session token. They now take a
 * `customer_id` in the body instead: update_aggriment still refuses an
 * agreement the given customer is not a party to, and feed/publish still
 * refuses one they did not create — but with no token behind those ids, the
 * checks catch a client mistake, not a forged caller. Anyone can pass any
 * customer_id and edit or share that customer's agreements.
 */
Route::post('/update_aggriment/v1', [PhpWordController::class, 'update_aggriment']);
Route::post('/feed/publish', [FeedController::class, 'publish']);

/*
|--------------------------------------------------------------------------
| 2. Customer Routes (Requires JWT Authentication: auth.jwt)
|--------------------------------------------------------------------------
*/

/* Login */
Route::get('get_customer_by_mobile', [CustomerController::class, 'getCustomerByMobile']);

Route::middleware('auth.jwt')->group(function () {

    // User Profile
    Route::get('auth/me', [AuthApiController::class, 'me']);
    Route::match(['put', 'patch'], 'auth/profile', [AuthApiController::class, 'updateProfile']);
    Route::get('profile', [CustomerController::class, 'showSelf']);
    Route::patch('/customers/allow-prompt', [CustomerController::class, 'updateAllowPrompt']);

    Route::post('upload_image', [CustomerController::class, 'upload_image']);

    // Common OTP (v2) - e.g. confirming a new mobile number for an already-signed-in customer
    Route::post('v2/send-otp', [CommonControllerV2::class, 'sendOtp']);
    Route::post('v2/verify-otp', [CommonControllerV2::class, 'verifyOtp']);

    Route::post('party-verifications/msg91', [PartyVerificationController::class, 'verifyPhone']);
    Route::post('party-verifications/pending', [PartyVerificationController::class, 'pendingForCreation']);
    Route::get('agreements/{agreement}/verifications', [PartyVerificationController::class, 'forAgreement'])->whereNumber('agreement');

    // Agreements & Deals
    Route::post('/create_aggriment/v1', [PhpWordController::class, 'create_aggriment']);
    // `/update_aggriment/v1` used to sit here. It is now unauthenticated —
    // see the public section above.
    Route::post('/convert_Word_to_pdf/v1', [PhpWordController::class, 'convertWordToPdf']);
    Route::post('create_aggriment', [PDFController::class, 'create_aggriment']);
    Route::post('list_aggriment', [PDFController::class, 'list_aggriment']);
    Route::post('deal_list', [PDFController::class, 'deal_list']);
    Route::post('deal_details', [PDFController::class, 'deal_details']);
    Route::post('add_remark', [PDFController::class, 'add_remark_in_deal']);
    Route::post('delete_history', [PDFController::class, 'delete_history']);
    Route::get('/deal_history/{id}', [DealController::class, 'showDealHistory']);
    Route::get('party-wise-agreements/{id}', [DealController::class, 'partyWiseAggrimentsApi'])->whereNumber('id');

    // PDF Documents
    Route::get('/pdf/preview/{file}', [PhpWordController::class, 'preview'])->where('file', '.*')->name('pdf.preview');
    Route::get('/pdf/download/{file}', [PhpWordController::class, 'download'])->where('file', '.*')->name('pdf.download');

    // KYC & Aadhaar
    Route::post('/aadhar_info', [AadharInfoController::class, 'store']);
    Route::get('/aadhar_info/{id}', [AadharInfoController::class, 'show']);
    Route::post('/aadhar_info/{id}', [AadharInfoController::class, 'update']);
    Route::delete('/aadhar_info/{id}', [AadharInfoController::class, 'destroy']);

    // Subscriptions & Payments
    Route::get('subscription/status/{customer_id}', [SubscriptionApiController::class, 'status']);
    Route::post('payment/order', [PaymentApiController::class, 'createOrder']);
    Route::post('payment/verify', [PaymentApiController::class, 'verify']);
    Route::get('subscription-invoices/pdf-url/{id}', [InvoiceController::class, 'getInvoicePdfUrl']);
    Route::get('subscription-invoices/view/{id}', [InvoiceController::class, 'viewPdf']);
    Route::get('subscription-invoices/download/{id}', [InvoiceController::class, 'downloadPdf']);

    // Feeds & Community
    Route::get('/feed', [FeedController::class, 'index']);
    // `/feed/publish` used to sit here. It is now unauthenticated — see the
    // public section above.
    Route::post('/feed', [FeedController::class, 'store']);
    Route::put('/feed', [FeedController::class, 'update']);
    Route::post('/feed/publish', [FeedController::class, 'publish']);
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

    // Notifications & Legal Notices
    Route::get('/notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::patch('/legal-notices/{id}/status', [LegalNoticeController::class, 'updateStatus'])->name('api.legal-notices.status');
    Route::apiResource('/legal-notices', LegalNoticeController::class)->names([
        'index' => 'api.legal-notices.index',
        'store' => 'api.legal-notices.store',
        'show' => 'api.legal-notices.show',
        'update' => 'api.legal-notices.update',
        'destroy' => 'api.legal-notices.destroy',
    ]);

    // Common Users Mobile No. Verification
    Route::post('party-verifications/send-otp', [PartyVerificationController::class, 'sendOtp']);
    Route::post('party-verifications/verify-otp', [PartyVerificationController::class, 'verifyOtp']);
});


/*
|--------------------------------------------------------------------------
| 3. Admin Routes (Requires Admin Session Authentication: auth)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Customers Administration
    Route::get('/customers', [CustomerController::class, 'index'])->name('api.customers.index');
    Route::post('/customers/create', [CustomerController::class, 'store'])->name('api.customers.store');
    Route::get('/customers/show/{customer}', [CustomerController::class, 'show'])->name('api.customers.show');
    Route::post('/customers/update/{customer}', [CustomerController::class, 'update'])->name('api.customers.update');
    Route::delete('/customers/delete/{customer}', [CustomerController::class, 'destroy'])->name('api.customers.destroy');

    // Catalogue Management
    Route::post('/deal_categories/create', [DealCategoryController::class, 'store'])->name('api.dealCategories.store');
    Route::post('/deal_categories/update/{deal_category}', [DealCategoryController::class, 'update'])->name('api.dealCategories.update');
    Route::delete('/deal_categories/delete/{deal_category}', [DealCategoryController::class, 'destroy'])->name('api.dealCategories.destroy');
    Route::put('/deal_categories/status_changes/{id}', [DealCategoryController::class, 'status_changes'])->name('api.dealCategories.statusChanges');

    Route::post('/purposes/create', [PurposeController::class, 'store'])->name('api.purposes.store');
    Route::post('/purposes/update/{purpose}', [PurposeController::class, 'update'])->name('api.purposes.update');
    Route::delete('/purposes/delete/{purpose}', [PurposeController::class, 'destroy'])->name('api.purposes.destroy');

    Route::post('/pages/create', [PageController::class, 'store'])->name('api.pages.store');
    Route::post('/pages/update/{page}', [PageController::class, 'update'])->name('api.pages.update');
    Route::delete('/pages/delete/{page}', [PageController::class, 'destroy'])->name('api.pages.destroy');

    Route::post('sliders/create', [SliderController::class, 'store'])->name('api.sliders.store');
    Route::post('sliders/update/{id}', [SliderController::class, 'update'])->name('api.sliders.update');
    Route::delete('sliders/{id}', [SliderController::class, 'destroy'])->name('api.sliders.destroy');

    // Reports
    Route::get('/customer-reports', [CustomerReportController::class, 'index']);
    Route::get('/admin/agreement-reports', [AgreementReportController::class, 'index']);

    // Admin Push Notifications
    Route::prefix('admin/notifications')->group(function () {
        Route::get('templates', [PushNotificationApiController::class, 'listTemplates']);
        Route::post('templates', [PushNotificationApiController::class, 'storeTemplate']);
        Route::get('templates/{id}', [PushNotificationApiController::class, 'showTemplate']);
        Route::put('templates/{id}', [PushNotificationApiController::class, 'updateTemplate']);
        Route::delete('templates/{id}', [PushNotificationApiController::class, 'destroyTemplate']);
        Route::post('send', [PushNotificationApiController::class, 'sendNotification']);
        Route::get('history', [PushNotificationApiController::class, 'listHistory']);
        Route::get('history/{id}', [PushNotificationApiController::class, 'showHistory']);
    });
});
