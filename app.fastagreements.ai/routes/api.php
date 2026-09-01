<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AadharInfoController;
use App\Http\Controllers\api\AuthApiController;
use App\Http\Controllers\api\CustomerController;
use App\Http\Controllers\api\DealCategoryController;
use App\Http\Controllers\api\DealController;
use App\Http\Controllers\api\FeedController;
use App\Http\Controllers\api\InvoiceController;
use App\Http\Controllers\api\LanguageController;
use App\Http\Controllers\api\PageController;
use App\Http\Controllers\api\PartyVerificationController;
use App\Http\Controllers\api\PaymentApiController;
use App\Http\Controllers\api\PDFController;
use App\Http\Controllers\api\PhpWordController;
use App\Http\Controllers\api\PurposeController;
use App\Http\Controllers\api\RazorpayWebhookController;
use App\Http\Controllers\api\SliderController;
use App\Http\Controllers\api\SubscriptionApiController;
use App\Http\Controllers\Admin\AttributeController;
use App\Models\Sceme;

/*
|--------------------------------------------------------------------------
| Mobile API
|--------------------------------------------------------------------------
|
| Three tiers:
|
|   public       – reference data drawn before anyone signs in
|   auth.jwt     – a customer, identified by a Firebase-issued session token
|   auth         – an admin, on the existing Blade session guard
|
| Nothing below reads a customer id out of a request body. Identity comes from
| the token, and ownership is checked in the handler.
|
| Removed in the JWT cutover:
|   GET  /clear             unauthenticated cache-clear and storage:link
|   GET  /advocates         a hardcoded closure that shadowed the controller
|   POST /verify_mobile     generated its own OTP and returned it in the body
|   POST /verify_mobile_otp issued no session
|   POST /customer_register  ) both replaced by /auth/firebase-exchange, which
|   POST /registertion       ) provisions the account on first verified sign-in
|
*/

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

// Sign-in. Public by necessity — the caller has no session yet.
Route::post('auth/firebase-exchange', [AuthApiController::class, 'firebaseExchange']);
Route::get('auth/exists', [AuthApiController::class, 'exists']);

// Catalogue and reference data. All read-only; the write halves live in the
// admin group at the bottom of this file.
Route::get('/deal_categories', [DealCategoryController::class, 'index'])->name('api.dealCategories.index');
Route::get('/deal_categories/show/{deal_category}', [DealCategoryController::class, 'show'])->name('api.dealCategories.show');
Route::post('attribute/list', [AttributeController::class, 'list'])->name('api.attribute.list');

// Razorpay's server-to-server callback. Deliberately unauthenticated: the
// gateway carries no token, and the HMAC over the raw body is the auth.
Route::post('webhooks/razorpay', [RazorpayWebhookController::class, 'handle']);

Route::get('/languages', [LanguageController::class, 'index'])->name('api.languages.index');
Route::get('/purposes', [PurposeController::class, 'index'])->name('api.purposes.index');
Route::get('/purposes/show/{purpose}', [PurposeController::class, 'show'])->name('api.purposes.show');

Route::get('/pages', [PageController::class, 'index'])->name('api.pages.index');
Route::get('/pages/show/{page}', [PageController::class, 'show'])->name('api.pages.show');
Route::get('/pages_legal', [PageController::class, 'indexlegal'])->name('api.pages.indexlegal');
Route::get('legal/{legal}', [\App\Http\Controllers\api\LegalController::class, 'index']);

Route::get('/cms-pages', [\App\Http\Controllers\api\CMSController::class, 'index'])->name('api.cms-pages.index');
Route::get('/cms-pages/{slug}', [\App\Http\Controllers\api\CMSController::class, 'show'])->name('api.cms-pages.show');

Route::get('sliders', [SliderController::class, 'index'])->name('api.sliders.index');
Route::get('sliders/{id}', [SliderController::class, 'show'])->name('api.sliders.show');

Route::get('/category-warnings', [\App\Http\Controllers\api\CategoryWarningApiController::class, 'index']);
Route::get('subscription-plans', [SubscriptionApiController::class, 'subscription_plane_list']);

// Now served from the advocates table. The hardcoded closure that used to sit
// above this line shadowed it, so admin-managed advocates never appeared.
Route::get('/advocates', [\App\Http\Controllers\api\AdvocateApiController::class, 'index']);
Route::get('/advocates/{id}', [\App\Http\Controllers\Api\AdvocateApiController::class, 'show']);

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

Route::get('scemelist', function () {
    return Sceme::select('id', 'emi_pay_method')->get();
});

/*
|--------------------------------------------------------------------------
| Customer — requires a session token
|--------------------------------------------------------------------------
*/

Route::middleware('auth.jwt')->group(function () {

    // Account
    Route::get('auth/me', [AuthApiController::class, 'me']);
    Route::match(['put', 'patch'], 'auth/profile', [AuthApiController::class, 'updateProfile']);
    // The caller's own profile page, subscription and invoices. Same payload as
    // the admin `customers/show/{customer}`, but scoped to the token holder —
    // the app needs this about itself and must not go through the admin route.
    Route::get('profile', [CustomerController::class, 'showSelf']);
    Route::patch('/customers/allow-prompt', [CustomerController::class, 'updateAllowPrompt']);
    Route::get('get_customer_by_mobile', [CustomerController::class, 'getCustomerByMobile']);
    Route::post('upload_image', [CustomerController::class, 'upload_image']);

    /*
     * Phone confirmation for parties and guarantors.
     *
     * Collected before the agreement is created: create_aggriment builds the
     * row and renders the document in one call, so there is no window in which
     * an existing agreement could sit waiting on confirmations.
     */
    Route::post('party-verifications/firebase', [PartyVerificationController::class, 'verifyPhone']);
    Route::post('party-verifications/pending', [PartyVerificationController::class, 'pendingForCreation']);
    Route::get('agreements/{agreement}/verifications', [PartyVerificationController::class, 'forAgreement'])
        ->whereNumber('agreement');

    // Agreements
    Route::post('/create_aggriment/v1', [PhpWordController::class, 'create_aggriment']);
    // Content edits to an existing agreement, re-rendering the document.
    // POST, not PATCH: PHP populates no $_FILES for a PATCH body, and an edit
    // may re-upload party, Aadhaar or vehicle images. Parties, category and
    // language are fixed at creation — the handler refuses to change them.
    Route::post('/update_aggriment/v1', [PhpWordController::class, 'update_aggriment']);
    Route::post('/convert_Word_to_pdf/v1', [PhpWordController::class, 'convertWordToPdf']);
    Route::post('create_aggriment', [PDFController::class, 'create_aggriment']);
    Route::post('list_aggriment', [PDFController::class, 'list_aggriment']);
    Route::post('deal_list', [PDFController::class, 'deal_list']);
    Route::post('deal_details', [PDFController::class, 'deal_details']);
    Route::post('add_remark', [PDFController::class, 'add_remark_in_deal']);
    Route::post('delete_history', [PDFController::class, 'delete_history']);
    Route::get('/deal_history/{id}', [DealController::class, 'showDealHistory']);
    Route::get('party-wise-agreements/{id}', [DealController::class, 'partyWiseAggrimentsApi'])->whereNumber('id');

    // Generated documents. The filename is sanitised in the controller — the
    // route pattern still allows slashes, so the guard lives there.
    Route::get('/pdf/preview/{file}', [PhpWordController::class, 'preview'])->where('file', '.*')->name('pdf.preview');
    Route::get('/pdf/download/{file}', [PhpWordController::class, 'download'])->where('file', '.*')->name('pdf.download');

    // KYC
    Route::post('/aadhar_info', [AadharInfoController::class, 'store']);
    Route::get('/aadhar_info/{id}', [AadharInfoController::class, 'show']);
    Route::post('/aadhar_info/{id}', [AadharInfoController::class, 'update']);
    Route::delete('/aadhar_info/{id}', [AadharInfoController::class, 'destroy']);

    // Money
    Route::get('subscription/status/{customer_id}', [SubscriptionApiController::class, 'status']);
    Route::post('payment/order', [PaymentApiController::class, 'createOrder']);
    Route::post('payment/verify', [PaymentApiController::class, 'verify']);
    Route::post('subscription/renew', [SubscriptionApiController::class, 'renew']);
    Route::get('subscription-invoices/pdf-url/{id}', [InvoiceController::class, 'getInvoicePdfUrl']);
    Route::get('subscription-invoices/view/{id}', [InvoiceController::class, 'viewPdf']);
    Route::get('subscription-invoices/download/{id}', [InvoiceController::class, 'downloadPdf']);

    // Feed
    Route::get('/feed', [FeedController::class, 'index']);
    // Opt-in share, called from the post-payment prompt. Agreement creation
    // does not post to the feed on its own.
    Route::post('/feed/publish', [FeedController::class, 'publish']);
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

    // Notifications and legal notices
    Route::get('/notifications', [\App\Http\Controllers\api\NotificationController::class, 'index'])->name('api.notifications.index');
    Route::patch('/legal-notices/{id}/status', [\App\Http\Controllers\api\LegalNoticeController::class, 'updateStatus'])->name('api.legal-notices.status');
    Route::apiResource('/legal-notices', \App\Http\Controllers\api\LegalNoticeController::class)->names([
        'index' => 'api.legal-notices.index',
        'store' => 'api.legal-notices.store',
        'show' => 'api.legal-notices.show',
        'update' => 'api.legal-notices.update',
        'destroy' => 'api.legal-notices.destroy',
    ]);
});

/*
|--------------------------------------------------------------------------
| Admin — existing Blade session guard
|--------------------------------------------------------------------------
|
| These were reachable by anyone before. They are back-office operations, so
| they reuse the session the admin panel already establishes rather than
| growing a second permission system on the API side.
|
*/

Route::middleware('auth')->group(function () {

    // Customer administration. Self-service lives on /auth/profile.
    Route::get('/customers', [CustomerController::class, 'index'])->name('api.customers.index');
    Route::post('/customers/create', [CustomerController::class, 'store'])->name('api.customers.store');
    Route::get('/customers/show/{customer}', [CustomerController::class, 'show'])->name('api.customers.show');
    Route::post('/customers/update/{customer}', [CustomerController::class, 'update'])->name('api.customers.update');
    Route::delete('/customers/delete/{customer}', [CustomerController::class, 'destroy'])->name('api.customers.destroy');

    // Catalogue writes
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

    // Reporting
    Route::get('/customer-reports', [\App\Http\Controllers\Api\CustomerReportController::class, 'index']);
    Route::get('/admin/agreement-reports', [\App\Http\Controllers\Api\AgreementReportController::class, 'index']);

    // Push notifications
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
});

/*
| Email templates were routed to App\Http\Controllers\Api\EmailTemplateApiController,
| which has never existed in this repository. All nine routes returned 500, and
| their presence made `php artisan route:list` and `route:cache` fail outright —
| so route caching could not be enabled at all. Left here, commented, in case
| the controller turns up; delete this block if the feature was abandoned.
|
| Route::prefix('admin/emails')->group(function () {
|     Route::get('templates', [EmailTemplateApiController::class, 'listTemplates']);
|     Route::post('templates', [EmailTemplateApiController::class, 'storeTemplate']);
|     Route::get('templates/{id}', [EmailTemplateApiController::class, 'showTemplate']);
|     Route::put('templates/{id}', [EmailTemplateApiController::class, 'updateTemplate']);
|     Route::delete('templates/{id}', [EmailTemplateApiController::class, 'destroyTemplate']);
|     Route::post('templates/{id}/test', [EmailTemplateApiController::class, 'testSendTemplate']);
|     Route::get('logs', [EmailTemplateApiController::class, 'listLogs']);
|     Route::get('logs/{id}', [EmailTemplateApiController::class, 'showLog']);
|     Route::post('logs/{id}/resend', [EmailTemplateApiController::class, 'resendLog']);
| });
*/
