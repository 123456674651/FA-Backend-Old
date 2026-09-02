<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DealCategoryController;
use App\Http\Controllers\Admin\DealController;
use App\Http\Controllers\Admin\PurposeController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\ScemeController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\DealHistoryController;
use App\Http\Controllers\Api\PDFController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\CustomerSubscriptionController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SubscriptionInvoiceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\GstReportController;




// Public/Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('admin/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('admin/login', [AuthController::class, 'login']);
});



//Route::prefix('controlpanel')->group(function () {

/*
| Removed: this claimed the name `dashboard.index`, which the real dashboard
| route inside the auth group below also claims. Two routes cannot share a
| name — it made `route:cache` fail with a LogicException, which is why route
| caching could never be enabled. It also rendered the admin dashboard view to
| anyone, with no auth middleware in front of it.
|
| `route('dashboard.index')` still resolves; it now points at the real
| authenticated dashboard at `/`.
|
| Route::get('controlpanel/admin', function () {
|     return view('admin.dashboard.index');
| })->name('dashboard.index');
*/

//Route::get('admin/profile', function () {
 //   return view('admin.profile.index');
//})->name('profile.index');

Route::middleware('auth')->group(function () {
    Route::post('admin/logout', [AuthController::class, 'logout'])->name('logout');

       Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');


    // Admin Profile Routes
    Route::get('admin/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('admin/profile/update', [ProfileController::class, 'updateDetails'])->name('profile.update');
    Route::post('admin/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('admin/profile/image', [ProfileController::class, 'updateImage'])->name('profile.image');
    Route::delete('admin/profile/image', [ProfileController::class, 'destroyImage'])->name('profile.image.delete');
      Route::post('admin/profile/logo', [ProfileController::class, 'updateLogo'])->name('logo.update');
  
  
    // Agreements Route
    Route::resource('agreements', \App\Http\Controllers\Admin\AgreementController::class)->only(['index', 'show']);

  
// Deal Categories Route
Route::resource('deal_categories', DealCategoryController::class);
Route::patch('deal_categories/status_changes/{id}', [DealCategoryController::class, 'status_changes'])->name('status_changes'); 
Route::get('deal_categories/template/{id}',[DealCategoryController::class, 'template' ])->name('template');


// Purpose Route
Route::resource('purposes', PurposeController::class);
Route::patch('purposes/status_changes/{id}', [PurposeController::class, 'status_changes'])->name('status_changes_purpose');

// Scemes Routes
Route::resource('scemes', ScemeController::class);
Route::patch('scemes/status_changes/{id}', [ScemeController::class, 'status_changes'])->name('status_changes_scemes');


// Languages Route
Route::resource('languages', LanguageController::class);
      Route::patch('languages/status_changes/{id}', [LanguageController::class, 'status_changes'])->name('languages.status_changes');


// Customers Route
Route::resource('customers', CustomerController::class);
Route::patch('customer/status_changes/{id}', [CustomerController::class, 'status_changes'])->name('customers.status_changes');
Route::get('deal_categories/attribute/{id}',[DealCategoryController::class, 'category_attribute' ])->name('category_attribute');

// Video Routes
//
// Route::resource already registers index/show/update/destroy under the
// videos.* names. The explicit declarations below re-registered those same
// names, which made route:cache fail. The URLs are kept — something may link
// to them — but the duplicated names are suffixed so each name is unique.
// route('videos.show') and friends now resolve to the resource routes.
Route::resource('videos', VideoController::class);
Route::get('videos', [VideoController::class, 'index'])->name('videos.index');
Route::put('/videos/{video}', [VideoController::class, 'update'])->name('videos.update_explicit');
Route::get('videos/{id}', [VideoController::class, 'show'])->name('videos.show_explicit');
Route::delete('videos/{id}', [VideoController::class, 'destroy'])->name('videos.destroy_explicit');

// Deal Controller 
Route::resource('deals', DealController::class);
Route::get('/mediator/{sakshiId}', [DealController::class, 'getMediatorBySakshi']);
Route::get('/deals/{id}/history', [DealController::class, 'showDealHistory'])->name('deals.history');
Route::post('/post-reorder', [DealCategoryController::class, 'reorder'])->name('post-reorder'); //change data: 2/18/2025
    Route::get('manage-attributes/{id}', [DealCategoryController::class, 'manageAttributes'])->name('manageAttributes');


// Page Routes 
Route::resource('pages', PageController::class);
Route::get('get-pages', [PageController::class, 'getPages'])->name('pages.getPages');
Route::patch('pages/{id}/status', [PageController::class, 'statusChange'])->name('pages.status_change');

// Slider Routes
Route::resource('sliders', SliderController::class);
Route::patch('/sliders/{id}/toggle-status', [SliderController::class, 'toggleStatus'])->name('sliders.toggleStatus');

// aggriments 
Route::get('generate-pdf', [PDFController::class, 'generatePDF']);
// Deal History Route 
// Route::get('/admin/deals', [DealHistoryController::class, 'index'])->name('admin.deals-list');
// Route::get('/admin/deal-history/{id}', [DealHistoryController::class, 'showDealHistory'])->name('admin.deal-history');

Route::get('/print-affidavit', [PDFController::class, 'printAffidavit']);

// templates
Route::get('template/index',[TemplateController::class, 'index' ])->name('template.index');
Route::get('template/create',[TemplateController::class, 'create' ])->name('template.create');
Route::post('template/store',[TemplateController::class, 'store' ])->name('template.store');
Route::get('template/edit/{id}',[TemplateController::class, 'edit' ])->name('template.edit');
Route::delete('template/delete/{id}',[TemplateController::class, 'delete' ])->name('template.delete');
Route::post('template/update',[TemplateController::class, 'update' ])->name('template.update');
Route::get('template/show/{id}',[TemplateController::class, 'show' ])->name('template.show');
Route::get('/duplicate-entry/{id}', [TemplateController::class, 'duplicateEntry'])->name('emplate.duplicate.entry');
Route::patch('/news_toggle-status/{id}', [TemplateController::class, 'toggleStatus'])->name('news_toggleStatus');

// Category Attribute 
Route::get('attribute/index',[AttributeController::class, 'index' ])->name('attribute.index');
Route::get('attribute/create',[AttributeController::class, 'create' ])->name('attribute.create');
Route::post('attribute/store',[AttributeController::class, 'store' ])->name('attribute.store');

// Category Attribute 
Route::get('attribute/index', [AttributeController::class, 'index'])->name('attribute.index');
    Route::get('attribute/create', [AttributeController::class, 'create'])->name('attribute.create');
    Route::get('attribute/edit/{id}', [AttributeController::class, 'edit'])->name('attribute.edit');
    Route::post('attribute/update', [AttributeController::class, 'update'])->name('attribute.update');
    Route::delete('attribute/delete/{id}', [AttributeController::class, 'delete'])->name('attribute.delete');
    Route::post('attribute/store', [AttributeController::class, 'store'])->name('attribute.store');
    Route::get('changeStatus', [AttributeController::class, 'changeStatus'])->name('changeStatus');
    Route::get('attribute/export/{categoryId}', [AttributeController::class, 'export'])->name('attribute.export');
    Route::post('attribute/import', [AttributeController::class, 'import'])->name('attribute.import');
        Route::get('attribute/list', [AttributeController::class, 'list'])->name('attribute.list');
    Route::post('attribute/bulk-delete', [AttributeController::class, 'bulkDelete'])->name('attribute.bulk_delete');
    // Name suffixed: Route::resource('/documents') below already owns
    // documents.update. See the videos block above.
    Route::put('documents/update/{id}', [DocumentController::class, 'update'])->name('documents.update_explicit');


  // Legal Notices Routes
    Route::get('legal-notices/export-pdf', [\App\Http\Controllers\Admin\LegalNoticeController::class, 'exportPdf'])->name('legal-notices.export-pdf');
    Route::get('legal-notices/export-excel', [\App\Http\Controllers\Admin\LegalNoticeController::class, 'exportExcel'])->name('legal-notices.export-excel');
    Route::patch('legal-notices/{id}/status', [\App\Http\Controllers\Admin\LegalNoticeController::class, 'updateStatus'])->name('legal-notices.status');
    Route::resource('legal-notices', \App\Http\Controllers\Admin\LegalNoticeController::class);
  
Route::resource('/documents', DocumentController::class);
    Route::post('documents/upload-docx', [DocumentController::class, 'uploadDocx'])->name('documents.upload_docx');
    Route::get('documents/download/{id}', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('documents/preview/{id}', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::delete('documents/delete/{id}', [DocumentController::class, 'destroy'])->name('documents.destroy_explicit');
    Route::resource('subscription-plans', SubscriptionPlanController::class);
    Route::resource('customer-subscriptions', CustomerSubscriptionController::class);

});

// CMS Pages Routes
    Route::post('cms-pages/upload-image', [\App\Http\Controllers\Admin\CmsPageController::class, 'uploadEditorImage'])->name('cms-pages.upload-image');
    Route::resource('cms-pages', \App\Http\Controllers\Admin\CmsPageController::class);

// Advocate Management
    Route::resource('advocates', \App\Http\Controllers\Admin\AdvocateController::class);
    Route::patch('advocates/{id}/status', [\App\Http\Controllers\Admin\AdvocateController::class, 'toggleStatus'])->name('advocates.status');

// Subscription Plan Controller
 Route::resource('subscription-plans', SubscriptionPlanController::class);
    Route::resource('subscription-invoices', SubscriptionInvoiceController::class)->only(['index', 'show']);
    Route::get('subscription-invoices/{id}/view', [SubscriptionInvoiceController::class, 'viewPdf'])->name('subscription-invoices.view');
    Route::get('subscription-invoices/{id}/download', [SubscriptionInvoiceController::class, 'downloadPdf'])->name('subscription-invoices.download');

// User Management
    Route::resource('users', UserController::class);
    Route::patch('users/{id}/status', [UserController::class, 'toggleStatus'])->name('users.status');

 // Category Warnings
    Route::resource('category-warnings', \App\Http\Controllers\Admin\CategoryWarningController::class);
    Route::patch('category-warnings/{id}/status', [\App\Http\Controllers\Admin\CategoryWarningController::class, 'toggleStatus'])->name('category-warnings.status');

// Settings Routes
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('settings/update', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/test-smtp', [\App\Http\Controllers\Admin\SettingController::class, 'testSmtp'])->name('settings.test-smtp');
    Route::post('settings/test-firebase', [\App\Http\Controllers\Admin\SettingController::class, 'testFirebase'])->name('settings.test-firebase');
    Route::get('settings/export', [\App\Http\Controllers\Admin\SettingController::class, 'export'])->name('settings.export');
    Route::post('settings/import', [\App\Http\Controllers\Admin\SettingController::class, 'import'])->name('settings.import');
    Route::post('settings/restore', [\App\Http\Controllers\Admin\SettingController::class, 'restore'])->name('settings.restore');
    Route::post('settings/maintenance', [\App\Http\Controllers\Admin\SettingController::class, 'maintenanceAction'])->name('settings.maintenance');

  // Customer Reports
    Route::get('customer-reports', [\App\Http\Controllers\Admin\AdminCustomerReportController::class, 'index'])->name('customer-reports.index');
    Route::get('customer-reports/export', [\App\Http\Controllers\Admin\AdminCustomerReportController::class, 'export'])->name('customer-reports.export');

    // Agreement Reports
    Route::get('agreement-reports', [\App\Http\Controllers\Admin\AdminAgreementReportController::class, 'index'])->name('agreement-reports.index');
    Route::get('agreement-reports/export', [\App\Http\Controllers\Admin\AdminAgreementReportController::class, 'export'])->name('agreement-reports.export');
    Route::get('agreement-reports/pdf', [\App\Http\Controllers\Admin\AdminAgreementReportController::class, 'pdf'])->name('agreement-reports.pdf');
    // Push Notifications CRUD and Sending
    Route::post('notification-templates/{notificationTemplate}/toggle', [\App\Http\Controllers\Admin\NotificationTemplateController::class, 'toggleStatus'])->name('notification-templates.toggle');
    Route::resource('notification-templates', \App\Http\Controllers\Admin\NotificationTemplateController::class);
    Route::get('notifications/send', [\App\Http\Controllers\Admin\SendNotificationController::class, 'index'])->name('notifications.send.index');
    Route::post('notifications/send', [\App\Http\Controllers\Admin\SendNotificationController::class, 'send'])->name('notifications.send');
    Route::get('notifications/preview', [\App\Http\Controllers\Admin\SendNotificationController::class, 'preview'])->name('notifications.preview');
    Route::get('notifications/history', [\App\Http\Controllers\Admin\NotificationHistoryController::class, 'index'])->name('notification-history.index');
    Route::get('notifications/history/{id}', [\App\Http\Controllers\Admin\NotificationHistoryController::class, 'show'])->name('notification-history.show');
    Route::post('notifications/history/{id}/resend', [\App\Http\Controllers\Admin\NotificationHistoryController::class, 'resend'])->name('notification-history.resend');
    /*
    | Reports Module Group
    |
    | Every route here pointed at App\Http\Controllers\Admin\ReportController,
    | which has never existed in this repository — all eleven returned 500, and
    | their presence made `php artisan route:list` and `route:cache` fail, so
    | route caching could not be enabled at all. Commented out rather than
    | deleted in case the controller turns up. The reports that do work live at
    | customer-reports, agreement-reports and reports/gst-tr below.
    |
    | Route::prefix('reports')->name('reports.')->group(function () {
    |     Route::get('revenue', [\App\Http\Controllers\Admin\ReportController::class, 'revenue'])->name('revenue');
    |     Route::get('users', [\App\Http\Controllers\Admin\ReportController::class, 'users'])->name('users');
    |     Route::get('agreements', [\App\Http\Controllers\Admin\ReportController::class, 'agreements'])->name('agreements');
    |     Route::get('categories', [\App\Http\Controllers\Admin\ReportController::class, 'categories'])->name('categories');
    |     Route::get('payments', [\App\Http\Controllers\Admin\ReportController::class, 'payments'])->name('payments');
    |     Route::get('advocates', [\App\Http\Controllers\Admin\ReportController::class, 'advocates'])->name('advocates');
    |     Route::get('languages', [\App\Http\Controllers\Admin\ReportController::class, 'languages'])->name('languages');
    |     Route::get('coupons', [\App\Http\Controllers\Admin\ReportController::class, 'coupons'])->name('coupons');
    |     Route::get('daily', [\App\Http\Controllers\Admin\ReportController::class, 'daily'])->name('daily');
    |     Route::get('monthly', [\App\Http\Controllers\Admin\ReportController::class, 'monthly'])->name('monthly');
    |     Route::get('yearly', [\App\Http\Controllers\Admin\ReportController::class, 'yearly'])->name('yearly');
    | });
    */

 Route::get('subscriptions/{id}/details', [CustomerController::class, 'getSubscriptionDetails'])->name('subscriptions.details');

  Route::post('legal-notices/{id}/reply', [\App\Http\Controllers\Admin\LegalNoticeController::class, 'reply'])->name('legal-notices.reply');


    // GST TR Report Routes
    Route::get('reports/gst-tr', [GstReportController::class, 'index'])->name('reports.gst-tr.index');
    Route::get('reports/gst-tr/export/excel', [GstReportController::class, 'exportExcel'])->name('reports.gst-tr.export.excel');
    Route::get('reports/gst-tr/export/pdf', [GstReportController::class, 'exportPdf'])->name('reports.gst-tr.export.pdf');
    Route::get('reports/gst-tr/print', [GstReportController::class, 'print'])->name('reports.gst-tr.print');
