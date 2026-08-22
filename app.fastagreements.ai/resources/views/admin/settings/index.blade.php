@extends('admin.layout.admin')

@section('content')
    <main id="main" class="main">
        <div class="row">
            <div class="pagetitle col-lg-6 pt-2">
                <h1>Settings</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ol>
                </nav>
            </div>
            <div class="col-lg-6 text-end pt-2">
                <a href="{{ route('settings.export') }}" class="btn btn-dark btn-sm me-2">
                    <i class="bi bi-download"></i> Export Settings
                </a>
                <button type="button" class="btn btn-secondary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload"></i> Import Settings
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="restoreDefaultsBtn">
                    <i class="bi bi-arrow-counterclockwise"></i> Restore Defaults
                </button>
            </div>
        </div>

        <section class="section">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <div class="row">
                        <!-- Navigation Tabs (Vertical/Left Side) -->
                        <div class="col-lg-3 col-md-4 border-end pe-md-4 mb-4 mb-md-0">
                            <div class="nav flex-column nav-pills" id="settingsTabs" role="tablist" aria-orientation="vertical">
                                <button class="nav-link text-start py-3 px-4 mb-2 rounded-3 {{ $activeTab === 'profile' ? 'active' : '' }}" id="tab-profile-btn" data-bs-toggle="pill" data-bs-target="#tab-profile" type="button" role="tab">
                                    <i class="bi bi-person me-2"></i> Profile
                                </button>
                               <button class="nav-link text-start py-3 px-4 mb-2 rounded-3 {{ $activeTab === 'company' ? 'active' : '' }}" id="tab-company-btn" data-bs-toggle="pill" data-bs-target="#tab-company" type="button" role="tab">
                                    <i class="bi bi-building me-2"></i> Company Settings
                                </button>
                                
                                <button class="nav-link text-start py-3 px-4 mb-2 rounded-3" id="tab-smtp-btn" data-bs-toggle="pill" data-bs-target="#tab-smtp" type="button" role="tab">
                                    <i class="bi bi-envelope me-2"></i> SMTP
                                </button>
                                <button class="nav-link text-start py-3 px-4 mb-2 rounded-3" id="tab-firebase-btn" data-bs-toggle="pill" data-bs-target="#tab-firebase" type="button" role="tab">
                                    <i class="bi bi-phone me-2"></i> Firebase
                                </button>
                               
                               
                            </div>
                        </div>

                        <!-- Tab Content (Right Side) -->
                        <div class="col-lg-9 col-md-8 ps-md-4">
                            <div class="tab-content" id="settingsTabContent">
                                
                                <!-- Profile Tab -->
                                <div class="tab-pane fade {{ $activeTab === 'profile' ? 'show active' : '' }}" id="tab-profile" role="tabpanel">
                                    <h4 class="fw-bold mb-4 text-dark">Profile Settings</h4>
                                    
                                    <!-- Profile Image & Details -->
                                    <div class="row g-4 mb-5">
                                        <div class="col-md-4 text-center border-end">
                                            <label class="form-label fw-bold d-block mb-3">Profile Image</label>
                                            <div class="mb-3 border p-2 rounded bg-light d-inline-block">
                                                <img id="avatar-preview-settings" 
                                                     src="{{ auth()->user()->profile_picture ? asset(auth()->user()->profile_picture) : asset('assets/img/profile-img.jpg') }}" 
                                                     alt="Profile Picture" 
                                                     class="img-thumbnail" 
                                                     style="height: 120px; width: 120px; object-fit: cover; border-radius: 50%;">
                                            </div>
                                            <form action="{{ route('profile.image') }}" method="POST" enctype="multipart/form-data" class="mb-2">
                                                @csrf
                                                <div class="input-group input-group-sm mb-2">
                                                    <input type="file" name="profile_picture" class="form-control" accept="image/*" required>
                                                    <button type="submit" class="btn btn-dark">Upload</button>
                                                </div>
                                            </form>
                                            @if(auth()->user()->profile_picture)
                                                <form action="{{ route('profile.image.delete') }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                                        <i class="bi bi-trash"></i> Remove Image
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        <div class="col-md-8">
                                            <h5 class="fw-bold mb-3 text-dark">Update Details</h5>
                                            <form method="POST" action="{{ route('profile.update') }}">
                                                @csrf
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold text-muted">Full Name</label>
                                                        <input name="name" type="text" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold text-muted">Email Address</label>
                                                        <input name="email" type="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                                                    </div>
                                                    <div class="col-12 text-end pt-3">
                                                        <button type="submit" class="btn btn-dark fw-semibold px-4">Save Profile</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <hr>

                                    <!-- Change Password -->
                                    <div class="row g-4 mt-3 mb-5">
                                        <div class="col-md-12">
                                            <h5 class="fw-bold mb-3 text-dark">Change Password</h5>
                                            <form method="POST" action="{{ route('profile.password') }}">
                                                @csrf
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold text-muted">Current Password</label>
                                                        <input name="current_password" type="password" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold text-muted">New Password</label>
                                                        <input name="new_password" type="password" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold text-muted">Confirm Password</label>
                                                        <input name="new_password_confirmation" type="password" class="form-control" required>
                                                    </div>
                                                    <div class="col-12 text-end pt-3">
                                                        <button type="submit" class="btn btn-dark fw-semibold px-4">Update Password</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <hr>

                                    <!-- Dashboard Logo -->
                                    <div class="row g-4 mt-3">
                                        <div class="col-md-4 text-center border-end pe-md-4">
                                            <label class="form-label fw-bold d-block mb-3">Dashboard Logo</label>
                                            <div class="bg-dark p-3 rounded d-inline-block border mb-3">
                                                <img id="logo-preview-settings" 
                                                     src="{{ asset('assets/img/logo/dashboard_logo.png') }}?v={{ file_exists(public_path('assets/img/logo/dashboard_logo.png')) ? filemtime(public_path('assets/img/logo/dashboard_logo.png')) : time() }}" 
                                                     alt="Dashboard Logo" 
                                                     style="max-height: 80px; width: auto; object-fit: contain;">
                                            </div>
                                        </div>
                                        <div class="col-md-8 ps-md-4 align-self-center">
                                            <h5 class="fw-bold text-dark mb-3">Choose New Logo</h5>
                                            <form method="POST" action="{{ route('logo.update') }}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="mb-3">
                                                    <input name="logo" type="file" class="form-control" id="logoInputProfileSettings" accept="image/*" onchange="previewLogoProfileSettings(event)" required>
                                                    <span class="text-muted small d-block mt-1">PNG, JPG, JPEG, SVG or GIF. Max 2MB. Recommendation: Use horizontal shape with transparent background.</span>
                                                </div>
                                                <div class="text-end pt-2">
                                                    <button type="submit" class="btn btn-dark fw-semibold px-4">Upload Logo</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                              
                               <!-- Company Settings Tab -->
                                <div class="tab-pane fade {{ $activeTab === 'company' ? 'show active' : '' }}" id="tab-company" role="tabpanel">
                                    <h4 class="fw-bold mb-4">Company Settings</h4>
                                    <form class="ajax-settings-form" method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="group" value="company">
                                        <div class="row g-4 mb-4">
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold d-block">Company Logo</label>
                                                <div class="mb-3 border p-2 rounded bg-light d-inline-block">
                                                    <img id="preview-company_logo" 
                                                         src="{{ setting('company_logo') ? asset('storage/' . setting('company_logo')) : asset('assets/img/profile-img.jpg') }}" 
                                                         alt="Company Logo" 
                                                         class="img-thumbnail" 
                                                         style="height: 100px; max-width: 150px; object-fit: contain;">
                                                </div>
                                                <input type="file" name="company_logo" class="form-control branding-file-input" data-preview="preview-company_logo">
                                                <span class="text-muted small d-block mt-1">PNG, JPG, JPEG, SVG or GIF. Max 2MB. Recommendation: Use horizontal shape with transparent background.</span>
                                            </div>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Company Name</label>
                                                <input type="text" name="company_name" class="form-control" value="{{ setting('company_name') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">GSTIN</label>
                                                <input type="text" name="company_gstin" class="form-control" value="{{ setting('company_gstin') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Address Line 1</label>
                                                <input type="text" name="company_address_line_1" class="form-control" value="{{ setting('company_address_line_1') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Address Line 2</label>
                                                <input type="text" name="company_address_line_2" class="form-control" value="{{ setting('company_address_line_2') }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">City</label>
                                                <input type="text" name="company_city" class="form-control" value="{{ setting('company_city') }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">State</label>
                                                <input type="text" name="company_state" class="form-control" value="{{ setting('company_state') }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">PIN Code</label>
                                                <input type="text" name="company_pin_code" class="form-control" value="{{ setting('company_pin_code') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Country</label>
                                                <input type="text" name="company_country" class="form-control" value="{{ setting('company_country') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Phone Number</label>
                                                <input type="text" name="company_phone_number" class="form-control" value="{{ setting('company_phone_number') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email Address</label>
                                                <input type="email" name="company_email_address" class="form-control" value="{{ setting('company_email_address') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Website</label>
                                                <input type="url" name="company_website" class="form-control" value="{{ setting('company_website') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Bank Name</label>
                                                <input type="text" name="company_bank_name" class="form-control" value="{{ setting('company_bank_name') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Account Holder Name</label>
                                                <input type="text" name="company_account_holder_name" class="form-control" value="{{ setting('company_account_holder_name') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Account Number</label>
                                                <input type="text" name="company_account_number" class="form-control" value="{{ setting('company_account_number') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">IFSC Code</label>
                                                <input type="text" name="company_ifsc_code" class="form-control" value="{{ setting('company_ifsc_code') }}">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">UPI ID</label>
                                                <input type="text" name="company_upi_id" class="form-control" value="{{ setting('company_upi_id') }}">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">Invoice Footer</label>
                                                <textarea name="company_invoice_footer" class="form-control" rows="2">{{ setting('company_invoice_footer') }}</textarea>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">Terms & Conditions</label>
                                                <textarea name="company_terms_conditions" class="form-control" rows="4">{{ setting('company_terms_conditions') }}</textarea>
                                            </div>
                                        </div>
                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- General Tab -->
                                <div class="tab-pane fade {{ $activeTab === 'general' ? 'show active' : '' }}" id="tab-general" role="tabpanel">
                                    <h4 class="fw-bold mb-4">General Settings</h4>
                                    <form class="ajax-settings-form" method="POST" action="{{ route('settings.update') }}">
                                        @csrf
                                        <input type="hidden" name="group" value="general">
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Site Name</label>
                                                <input type="text" name="site_name" class="form-control" value="{{ setting('site_name') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Company Name</label>
                                                <input type="text" name="company_name" class="form-control" value="{{ setting('company_name') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Support Email</label>
                                                <input type="email" name="support_email" class="form-control" value="{{ setting('support_email') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Support Mobile</label>
                                                <input type="text" name="support_mobile" class="form-control" value="{{ setting('support_mobile') }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Website URL</label>
                                                <input type="url" name="website_url" class="form-control" value="{{ setting('website_url') }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Company Address</label>
                                                <textarea name="company_address" class="form-control" rows="3">{{ setting('company_address') }}</textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Timezone</label>
                                                <input type="text" name="timezone" class="form-control" value="{{ setting('timezone') }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Currency</label>
                                                <input type="text" name="currency" class="form-control" value="{{ setting('currency') }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Default Language</label>
                                                <input type="text" name="default_language" class="form-control" value="{{ setting('default_language') }}">
                                            </div>
                                        </div>
                                        
                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- Branding Tab -->
                                <div class="tab-pane fade" id="tab-branding" role="tabpanel">
                                    <h4 class="fw-bold mb-4">Branding & Colors</h4>
                                    <form class="ajax-settings-form" method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="group" value="branding">

                                        <div class="row g-4">
                                            @php
                                                $logos = [
                                                    'website_logo' => 'Website Logo',
                                                    'admin_logo' => 'Admin Logo',
                                                    'login_logo' => 'Login Logo',
                                                    'favicon' => 'Favicon',
                                                    'email_logo' => 'Email Logo',
                                                    'default_profile_image' => 'Default Profile Image'
                                                ];
                                            @endphp

                                            @foreach ($logos as $key => $label)
                                                <div class="col-md-4 text-center">
                                                    <label class="form-label fw-bold d-block">{{ $label }}</label>
                                                    <div class="mb-3 border p-2 rounded bg-light d-inline-block">
                                                        <img id="preview-{{ $key }}" 
                                                             src="{{ setting($key) ? asset('storage/' . setting($key)) : asset('assets/img/profile-img.jpg') }}" 
                                                             alt="{{ $label }}" 
                                                             class="img-thumbnail" 
                                                             style="height: 100px; max-width: 150px; object-fit: contain;">
                                                    </div>
                                                    <input type="file" name="{{ $key }}" class="form-control form-control-sm branding-file-input" data-preview="preview-{{ $key }}">
                                                </div>
                                            @endforeach

                                            <div class="col-md-6">
                                                <label class="form-label">Primary Color</label>
                                                <div class="input-group">
                                                    <input type="color" name="primary_color" class="form-control form-control-color" value="{{ setting('primary_color', '#000000') }}">
                                                    <input type="text" class="form-control" readonly value="{{ setting('primary_color', '#000000') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Secondary Color</label>
                                                <div class="input-group">
                                                    <input type="color" name="secondary_color" class="form-control form-control-color" value="{{ setting('secondary_color', '#ffffff') }}">
                                                    <input type="text" class="form-control" readonly value="{{ setting('secondary_color', '#ffffff') }}">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Footer Text</label>
                                                <input type="text" name="footer_text" class="form-control" value="{{ setting('footer_text') }}">
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- SMTP Tab -->
                                <div class="tab-pane fade" id="tab-smtp" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4 class="fw-bold mb-0">SMTP Configurations</h4>
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#testSmtpModal">
                                            <i class="bi bi-envelope-open"></i> Send Test Email
                                        </button>
                                    </div>
                                    <form class="ajax-settings-form" id="smtpForm" method="POST" action="{{ route('settings.update') }}">
                                        @csrf
                                        <input type="hidden" name="group" value="smtp">

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Mail Driver</label>
                                                <input type="text" name="mail_driver" class="form-control" value="{{ setting('mail_driver') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Mail Host</label>
                                                <input type="text" name="mail_host" class="form-control" value="{{ setting('mail_host') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Mail Port</label>
                                                <input type="number" name="mail_port" class="form-control" value="{{ setting('mail_port') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Encryption</label>
                                                <input type="text" name="mail_encryption" class="form-control" value="{{ setting('mail_encryption') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Username</label>
                                                <input type="text" name="mail_username" class="form-control" value="{{ setting('mail_username') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Password</label>
                                                <input type="password" name="mail_password" class="form-control" placeholder="••••••••">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">From Name</label>
                                                <input type="text" name="mail_from_name" class="form-control" value="{{ setting('mail_from_name') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">From Email</label>
                                                <input type="email" name="mail_from_email" class="form-control" value="{{ setting('mail_from_email') }}" required>
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- Firebase Tab -->
                                <div class="tab-pane fade" id="tab-firebase" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4 class="fw-bold mb-0">Firebase Settings</h4>
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#testFirebaseModal">
                                            <i class="bi bi-bell"></i> Send Test Notification
                                        </button>
                                    </div>
                                    <form class="ajax-settings-form" id="firebaseForm" method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="group" value="firebase">

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Project ID</label>
                                                <input type="text" name="firebase_project_id" class="form-control" value="{{ setting('firebase_project_id') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">API Key</label>
                                                <input type="text" name="firebase_api_key" class="form-control" value="{{ setting('firebase_api_key') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Sender ID</label>
                                                <input type="text" name="firebase_sender_id" class="form-control" value="{{ setting('firebase_sender_id') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">App ID</label>
                                                <input type="text" name="firebase_app_id" class="form-control" value="{{ setting('firebase_app_id') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Server Key</label>
                                                <input type="text" name="firebase_server_key" class="form-control" value="{{ setting('firebase_server_key') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">VAPID Key</label>
                                                <input type="text" name="firebase_vapid_key" class="form-control" value="{{ setting('firebase_vapid_key') }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Service Account JSON Upload</label>
                                                <input type="file" name="firebase_service_account_json" class="form-control">
                                                @if(setting('firebase_service_account_json'))
                                                    <div class="form-text text-success">
                                                        <i class="bi bi-file-earmark-check"></i> JSON Key File Saved: <a href="{{ asset('storage/' . setting('firebase_service_account_json')) }}" target="_blank">View File</a>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-12 mt-3">
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" name="enable_push_notification" value="1" class="form-check-input" id="enablePush" {{ setting('enable_push_notification') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="enablePush">Enable Push Notifications</label>
                                                </div>
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- Storage Tab -->
                                <div class="tab-pane fade" id="tab-storage" role="tabpanel">
                                    <h4 class="fw-bold mb-4">Storage Settings</h4>
                                    <form class="ajax-settings-form" method="POST" action="{{ route('settings.update') }}">
                                        @csrf
                                        <input type="hidden" name="group" value="storage">

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Storage Type</label>
                                                <select name="storage_type" id="storage_type" class="form-select">
                                                    <option value="local" {{ setting('storage_type') === 'local' ? 'selected' : '' }}>Local</option>
                                                    <option value="s3" {{ setting('storage_type') === 's3' ? 'selected' : '' }}>Amazon S3</option>
                                                </select>
                                            </div>

                                            <div id="s3-fields" class="row g-3 mt-1 {{ setting('storage_type') === 's3' ? '' : 'd-none' }}">
                                                <div class="col-md-6">
                                                    <label class="form-label">Access Key</label>
                                                    <input type="text" name="s3_access_key" class="form-control" value="{{ setting('s3_access_key') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Secret Key</label>
                                                    <input type="text" name="s3_secret_key" class="form-control" value="{{ setting('s3_secret_key') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Bucket Name</label>
                                                    <input type="text" name="s3_bucket" class="form-control" value="{{ setting('s3_bucket') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Region</label>
                                                    <input type="text" name="s3_region" class="form-control" value="{{ setting('s3_region') }}">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Base URL</label>
                                                    <input type="url" name="s3_base_url" class="form-control" value="{{ setting('s3_base_url') }}">
                                                </div>
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- Payment Gateway Tab -->
                                <div class="tab-pane fade" id="tab-payment" role="tabpanel">
                                    <h4 class="fw-bold mb-4">Payment Gateways</h4>
                                    <form class="ajax-settings-form" method="POST" action="{{ route('settings.update') }}">
                                        @csrf
                                        <input type="hidden" name="group" value="payment">

                                        <div class="row g-4">
                                            <!-- Razorpay -->
                                            <div class="col-md-6 border-end pe-md-4">
                                                <div class="form-check form-switch mb-3">
                                                    <input type="checkbox" name="enable_razorpay" value="1" class="form-check-input" id="enableRazor" {{ setting('enable_razorpay') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="enableRazor">Razorpay</label>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Key ID</label>
                                                    <input type="text" name="razorpay_key" class="form-control" value="{{ setting('razorpay_key') }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Secret Key</label>
                                                    <input type="password" name="razorpay_secret" class="form-control" placeholder="••••••••">
                                                </div>
                                            </div>

                                            <!-- Stripe -->
                                            <div class="col-md-6 ps-md-4">
                                                <div class="form-check form-switch mb-3">
                                                    <input type="checkbox" name="enable_stripe" value="1" class="form-check-input" id="enableStripe" {{ setting('enable_stripe') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="enableStripe">Stripe</label>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Publishable Key</label>
                                                    <input type="text" name="stripe_key" class="form-control" value="{{ setting('stripe_key') }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Secret Key</label>
                                                    <input type="password" name="stripe_secret" class="form-control" placeholder="••••••••">
                                                </div>
                                            </div>

                                            <hr>

                                            <!-- PayPal -->
                                            <div class="col-md-6 border-end pe-md-4">
                                                <div class="form-check form-switch mb-3">
                                                    <input type="checkbox" name="enable_paypal" value="1" class="form-check-input" id="enablePaypal" {{ setting('enable_paypal') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="enablePaypal">PayPal</label>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Client ID</label>
                                                    <input type="text" name="paypal_client_id" class="form-control" value="{{ setting('paypal_client_id') }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Secret Key</label>
                                                    <input type="password" name="paypal_secret" class="form-control" placeholder="••••••••">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Mode</label>
                                                    <select name="paypal_mode" class="form-select">
                                                        <option value="sandbox" {{ setting('paypal_mode') === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                                                        <option value="live" {{ setting('paypal_mode') === 'live' ? 'selected' : '' }}>Live</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Cash -->
                                            <div class="col-md-6 ps-md-4 d-flex align-items-center">
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" name="enable_cash" value="1" class="form-check-input" id="enableCash" {{ setting('enable_cash') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="enableCash">Enable Cash On Delivery</label>
                                                </div>
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- SMS & WhatsApp Tab -->
                                <div class="tab-pane fade" id="tab-sms_whatsapp" role="tabpanel">
                                    <h4 class="fw-bold mb-4">SMS & WhatsApp Configurations</h4>
                                    <form class="ajax-settings-form" method="POST" action="{{ route('settings.update') }}">
                                        @csrf
                                        <input type="hidden" name="group" value="sms_whatsapp">

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Twilio SID</label>
                                                <input type="text" name="twilio_sid" class="form-control" value="{{ setting('twilio_sid') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Twilio Token</label>
                                                <input type="password" name="twilio_token" class="form-control" placeholder="••••••••">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Twilio Phone Number</label>
                                                <input type="text" name="twilio_number" class="form-control" value="{{ setting('twilio_number') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">WhatsApp API URL</label>
                                                <input type="url" name="whatsapp_api_url" class="form-control" value="{{ setting('whatsapp_api_url') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">OTP Length</label>
                                                <input type="number" name="otp_length" class="form-control" value="{{ setting('otp_length', '6') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">OTP Expiry (Minutes)</label>
                                                <input type="number" name="otp_expiry" class="form-control" value="{{ setting('otp_expiry', '10') }}">
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- Social Links Tab -->
                                <div class="tab-pane fade" id="tab-social_links" role="tabpanel">
                                    <h4 class="fw-bold mb-4">Social Media Links</h4>
                                    <form class="ajax-settings-form" method="POST" action="{{ route('settings.update') }}">
                                        @csrf
                                        <input type="hidden" name="group" value="social_links">

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label"><i class="bi bi-facebook text-primary"></i> Facebook</label>
                                                <input type="url" name="social_facebook" class="form-control" value="{{ setting('social_facebook') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label"><i class="bi bi-instagram text-danger"></i> Instagram</label>
                                                <input type="url" name="social_instagram" class="form-control" value="{{ setting('social_instagram') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label"><i class="bi bi-twitter text-info"></i> Twitter</label>
                                                <input type="url" name="social_twitter" class="form-control" value="{{ setting('social_twitter') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label"><i class="bi bi-linkedin text-primary"></i> LinkedIn</label>
                                                <input type="url" name="social_linkedin" class="form-control" value="{{ setting('social_linkedin') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label"><i class="bi bi-youtube text-danger"></i> YouTube</label>
                                                <input type="url" name="social_youtube" class="form-control" value="{{ setting('social_youtube') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label"><i class="bi bi-telegram text-info"></i> Telegram</label>
                                                <input type="url" name="social_telegram" class="form-control" value="{{ setting('social_telegram') }}">
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- Agreement Tab -->
                                <div class="tab-pane fade" id="tab-agreement" role="tabpanel">
                                    <h4 class="fw-bold mb-4">Agreement Configurations</h4>
                                    <form class="ajax-settings-form" method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="group" value="agreement">

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Agreement Number Prefix</label>
                                                <input type="text" name="agreement_prefix" class="form-control" value="{{ setting('agreement_prefix') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Agreement Number Length</label>
                                                <input type="number" name="agreement_number_length" class="form-control" value="{{ setting('agreement_number_length') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Default Expiry Days</label>
                                                <input type="number" name="agreement_default_expiry_days" class="form-control" value="{{ setting('agreement_default_expiry_days') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Default Watermark File</label>
                                                <input type="file" name="agreement_default_watermark" class="form-control">
                                                @if(setting('agreement_default_watermark'))
                                                    <div class="form-text text-success">
                                                        <i class="bi bi-check-circle"></i> Watermark File: <a href="{{ asset('storage/' . setting('agreement_default_watermark')) }}" target="_blank">View File</a>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div class="col-12 mt-3">
                                                <div class="form-check form-switch mb-2">
                                                    <input type="checkbox" name="agreement_enable_auto_approval" value="1" class="form-check-input" id="autoApprove" {{ setting('agreement_enable_auto_approval') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="autoApprove">Enable Auto Approval</label>
                                                </div>
                                                <div class="form-check form-switch mb-2">
                                                    <input type="checkbox" name="agreement_allow_download" value="1" class="form-check-input" id="allowDown" {{ setting('agreement_allow_download') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="allowDown">Allow Download</label>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" name="agreement_allow_sharing" value="1" class="form-check-input" id="allowShare" {{ setting('agreement_allow_sharing') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="allowShare">Allow Sharing</label>
                                                </div>
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- Customer Tab -->
                                <div class="tab-pane fade" id="tab-customer" role="tabpanel">
                                    <h4 class="fw-bold mb-4">Customer Configurations</h4>
                                    <form class="ajax-settings-form" method="POST" action="{{ route('settings.update') }}">
                                        @csrf
                                        <input type="hidden" name="group" value="customer">

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Default Customer Status</label>
                                                <input type="text" name="customer_default_status" class="form-control" value="{{ setting('customer_default_status') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Default Language</label>
                                                <input type="text" name="customer_default_language" class="form-control" value="{{ setting('customer_default_language') }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Default Subscription Plan</label>
                                                <input type="text" name="customer_default_subscription_plan" class="form-control" value="{{ setting('customer_default_subscription_plan') }}">
                                            </div>

                                            <div class="col-12 mt-3">
                                                <div class="form-check form-switch mb-2">
                                                    <input type="checkbox" name="customer_allow_registration" value="1" class="form-check-input" id="allowCustReg" {{ setting('customer_allow_registration') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="allowCustReg">Allow Customer Self-Registration</label>
                                                </div>
                                                <div class="form-check form-switch mb-2">
                                                    <input type="checkbox" name="customer_require_email_verification" value="1" class="form-check-input" id="requireCustEmail" {{ setting('customer_require_email_verification') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="requireCustEmail">Require Email Verification</label>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" name="customer_require_mobile_verification" value="1" class="form-check-input" id="requireCustMob" {{ setting('customer_require_mobile_verification') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="requireCustMob">Require Mobile Verification</label>
                                                </div>
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- Advocate Tab -->
                                <div class="tab-pane fade" id="tab-advocate" role="tabpanel">
                                    <h4 class="fw-bold mb-4">Advocate Configurations</h4>
                                    <form class="ajax-settings-form" method="POST" action="{{ route('settings.update') }}">
                                        @csrf
                                        <input type="hidden" name="group" value="advocate">

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Commission Percentage (%)</label>
                                                <input type="number" name="advocate_commission_percentage" class="form-control" value="{{ setting('advocate_commission_percentage') }}">
                                            </div>
                                            
                                            <div class="col-12 mt-3">
                                                <div class="form-check form-switch mb-2">
                                                    <input type="checkbox" name="advocate_allow_registration" value="1" class="form-check-input" id="allowAdvReg" {{ setting('advocate_allow_registration') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="allowAdvReg">Allow Advocate Self-Registration</label>
                                                </div>
                                                <div class="form-check form-switch mb-2">
                                                    <input type="checkbox" name="advocate_require_verification" value="1" class="form-check-input" id="requireAdvVerify" {{ setting('advocate_require_verification') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="requireAdvVerify">Require Profile Verification</label>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" name="advocate_auto_approval" value="1" class="form-check-input" id="advAutoApp" {{ setting('advocate_auto_approval') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="advAutoApp">Auto Approve Registered Advocates</label>
                                                </div>
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- Notifications Tab -->
                                <div class="tab-pane fade" id="tab-notifications" role="tabpanel">
                                    <h4 class="fw-bold mb-4">Notification Channels & Settings</h4>
                                    <form class="ajax-settings-form" method="POST" action="{{ route('settings.update') }}">
                                        @csrf
                                        <input type="hidden" name="group" value="notifications">

                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="form-check form-switch mb-2">
                                                    <input type="checkbox" name="notify_email" value="1" class="form-check-input" id="notifyEmail" {{ setting('notify_email') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="notifyEmail">Enable Email Notifications</label>
                                                </div>
                                                <div class="form-check form-switch mb-2">
                                                    <input type="checkbox" name="notify_sms" value="1" class="form-check-input" id="notifySms" {{ setting('notify_sms') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="notifySms">Enable SMS Notifications</label>
                                                </div>
                                                <div class="form-check form-switch mb-3">
                                                    <input type="checkbox" name="notify_push" value="1" class="form-check-input" id="notifyPush" {{ setting('notify_push') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="notifyPush">Enable Push Notifications</label>
                                                </div>
                                                
                                                <hr>

                                                <div class="form-check form-switch mb-2">
                                                    <input type="checkbox" name="notify_admin" value="1" class="form-check-input" id="notifyAdmin" {{ setting('notify_admin') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="notifyAdmin">Send Copy to Administrators</label>
                                                </div>
                                                <div class="form-check form-switch mb-2">
                                                    <input type="checkbox" name="notify_customer" value="1" class="form-check-input" id="notifyCust" {{ setting('notify_customer') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="notifyCust">Send Notifications to Customers</label>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" name="notify_advocate" value="1" class="form-check-input" id="notifyAdv" {{ setting('notify_advocate') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="notifyAdv">Send Notifications to Advocates</label>
                                                </div>
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- Security Tab -->
                                <div class="tab-pane fade" id="tab-security" role="tabpanel">
                                    <h4 class="fw-bold mb-4">Security Settings</h4>
                                    <form class="ajax-settings-form" method="POST" action="{{ route('settings.update') }}">
                                        @csrf
                                        <input type="hidden" name="group" value="security">

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Google reCAPTCHA Site Key</label>
                                                <input type="text" name="recaptcha_site_key" class="form-control" value="{{ setting('recaptcha_site_key') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Google reCAPTCHA Secret Key</label>
                                                <input type="text" name="recaptcha_secret_key" class="form-control" value="{{ setting('recaptcha_secret_key') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Login Attempt Limit</label>
                                                <input type="number" name="login_attempt_limit" class="form-control" value="{{ setting('login_attempt_limit', '5') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Session Timeout (Minutes)</label>
                                                <input type="number" name="session_timeout" class="form-control" value="{{ setting('session_timeout', '120') }}">
                                            </div>
                                            <div class="col-12 mt-3">
                                                <div class="form-check form-switch mb-2">
                                                    <input type="checkbox" name="enable_2fa" value="1" class="form-check-input" id="enable2fa" {{ setting('enable_2fa') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="enable2fa">Enable Two Factor Authentication</label>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" name="force_strong_password" value="1" class="form-check-input" id="forcePwd" {{ setting('force_strong_password') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="forcePwd">Force Strong Passwords</label>
                                                </div>
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- API Tab -->
                                <div class="tab-pane fade" id="tab-api" role="tabpanel">
                                    <h4 class="fw-bold mb-4">API Configurations</h4>
                                    <form class="ajax-settings-form" method="POST" action="{{ route('settings.update') }}">
                                        @csrf
                                        <input type="hidden" name="group" value="api">

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">API Base URL</label>
                                                <input type="url" name="api_base_url" class="form-control" value="{{ setting('api_base_url') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">API Key / Token</label>
                                                <input type="text" name="api_token" class="form-control" value="{{ setting('api_token') }}">
                                            </div>
                                            <div class="col-12 mt-3">
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" name="enable_api_logs" value="1" class="form-check-input" id="apiLogs" {{ setting('enable_api_logs') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="apiLogs">Enable API Request Logs</label>
                                                </div>
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- SEO Tab -->
                                <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                                    <h4 class="fw-bold mb-4">SEO Metadata Settings</h4>
                                    <form class="ajax-settings-form" method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="group" value="seo">

                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Meta Title</label>
                                                <input type="text" name="meta_title" class="form-control" value="{{ setting('meta_title') }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Meta Keywords</label>
                                                <input type="text" name="meta_keywords" class="form-control" value="{{ setting('meta_keywords') }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Meta Description</label>
                                                <textarea name="meta_description" class="form-control" rows="3">{{ setting('meta_description') }}</textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Open Graph (OG) Image</label>
                                                <input type="file" name="og_image" class="form-control branding-file-input" data-preview="preview-og_image">
                                                @if(setting('og_image'))
                                                    <div class="form-text text-success">
                                                        <i class="bi bi-check-circle"></i> Image Saved: <a href="{{ asset('storage/' . setting('og_image')) }}" target="_blank">View Image</a>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-md-6 text-center">
                                                <div class="mb-3 border p-2 rounded bg-light d-inline-block">
                                                    <img id="preview-og_image" 
                                                         src="{{ setting('og_image') ? asset('storage/' . setting('og_image')) : asset('assets/img/profile-img.jpg') }}" 
                                                         alt="OG Image Preview" 
                                                         class="img-thumbnail" 
                                                         style="height: 100px; max-width: 150px; object-fit: contain;">
                                                </div>
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- Legal Tab -->
                                <div class="tab-pane fade" id="tab-legal" role="tabpanel">
                                    <h4 class="fw-bold mb-4">Legal Documents & Pages</h4>
                                    <form class="ajax-settings-form" method="POST" action="{{ route('settings.update') }}">
                                        @csrf
                                        <input type="hidden" name="group" value="legal">

                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label fw-bold">Privacy Policy</label>
                                                <textarea name="privacy_policy" class="form-control editor-area" id="editor-privacy_policy">{{ setting('privacy_policy') }}</textarea>
                                            </div>
                                            <div class="col-12 mt-4">
                                                <label class="form-label fw-bold">Terms & Conditions</label>
                                                <textarea name="terms_conditions" class="form-control editor-area" id="editor-terms_conditions">{{ setting('terms_conditions') }}</textarea>
                                            </div>
                                            <div class="col-12 mt-4">
                                                <label class="form-label fw-bold">Refund Policy</label>
                                                <textarea name="refund_policy" class="form-control editor-area" id="editor-refund_policy">{{ setting('refund_policy') }}</textarea>
                                            </div>
                                            <div class="col-12 mt-4">
                                                <label class="form-label fw-bold">About Us</label>
                                                <textarea name="about_us" class="form-control editor-area" id="editor-about_us">{{ setting('about_us') }}</textarea>
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>
                                </div>

                                <!-- Maintenance Tab -->
                                <div class="tab-pane fade" id="tab-maintenance" role="tabpanel">
                                    <h4 class="fw-bold mb-4">Application Maintenance</h4>
                                    
                                    <!-- Maintenance Mode Toggle Form -->
                                    <form class="ajax-settings-form mb-5" method="POST" action="{{ route('settings.update') }}">
                                        @csrf
                                        <input type="hidden" name="group" value="maintenance">

                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="form-check form-switch mb-3">
                                                    <input type="checkbox" name="enable_maintenance_mode" value="1" class="form-check-input" id="enableMaint" {{ setting('enable_maintenance_mode') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="enableMaint">Enable System Maintenance Mode</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Maintenance Mode Message</label>
                                                <textarea name="maintenance_message" class="form-control" rows="3">{{ setting('maintenance_message') }}</textarea>
                                            </div>
                                        </div>

                                        @include('admin.settings.partials.buttons')
                                    </form>

                                    <hr>

                                    <!-- Maintenance Utility Actions -->
                                    <h5 class="fw-bold mb-3 mt-4">Maintenance Operations</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card p-3 border shadow-none bg-light text-center h-100">
                                                <i class="bi bi-database-fill-down fs-2 text-dark mb-2"></i>
                                                <h6 class="fw-bold">Database Backup</h6>
                                                <p class="small text-muted mb-3">Download complete database structure and records backup.</p>
                                                <a href="{{ route('settings.maintenance', ['action' => 'backup_db']) }}" class="btn btn-dark btn-sm mt-auto w-100">
                                                    <i class="bi bi-download"></i> Backup Database
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card p-3 border shadow-none bg-light text-center h-100">
                                                <i class="bi bi-trash fs-2 text-dark mb-2"></i>
                                                <h6 class="fw-bold">Clear Cache</h6>
                                                <p class="small text-muted mb-3">Flush general system application data cache.</p>
                                                <button type="button" class="btn btn-dark btn-sm mt-auto w-100 maintenance-btn" data-action="clear_cache">
                                                    <i class="bi bi-play"></i> Clear Cache
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card p-3 border shadow-none bg-light text-center h-100">
                                                <i class="bi bi-gear-wide-connected fs-2 text-dark mb-2"></i>
                                                <h6 class="fw-bold">Clear Config Cache</h6>
                                                <p class="small text-muted mb-3">Flush all compiled configuration settings.</p>
                                                <button type="button" class="btn btn-dark btn-sm mt-auto w-100 maintenance-btn" data-action="clear_config">
                                                    <i class="bi bi-play"></i> Clear Config
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card p-3 border shadow-none bg-light text-center h-100">
                                                <i class="bi bi-arrow-repeat fs-2 text-dark mb-2"></i>
                                                <h6 class="fw-bold">Clear Route Cache</h6>
                                                <p class="small text-muted mb-3">Flush the compiled application routing map.</p>
                                                <button type="button" class="btn btn-dark btn-sm mt-auto w-100 maintenance-btn" data-action="clear_route">
                                                    <i class="bi bi-play"></i> Clear Routes
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card p-3 border shadow-none bg-light text-center h-100">
                                                <i class="bi bi-aspect-ratio fs-2 text-dark mb-2"></i>
                                                <h6 class="fw-bold">Clear View Cache</h6>
                                                <p class="small text-muted mb-3">Flush the compiled Blade HTML templates cache.</p>
                                                <button type="button" class="btn btn-dark btn-sm mt-auto w-100 maintenance-btn" data-action="clear_view">
                                                    <i class="bi bi-play"></i> Clear Views
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card p-3 border shadow-none bg-light text-center h-100">
                                                <i class="bi bi-lightning-fill fs-2 text-dark mb-2"></i>
                                                <h6 class="fw-bold">Optimize App</h6>
                                                <p class="small text-muted mb-3">Compile route, view, and config files for production performance.</p>
                                                <button type="button" class="btn btn-dark btn-sm mt-auto w-100 maintenance-btn" data-action="optimize">
                                                    <i class="bi bi-play"></i> Optimize App
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Logs Section -->
            <div class="card mt-4 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="bi bi-clock-history"></i> Settings Activity Audit Log</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($activityLogs as $log)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $log->user ? $log->user->name : 'System' }}</div>
                                            <div class="small text-muted">{{ $log->user ? $log->user->email : '' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark">{{ $log->action }}</span>
                                        </td>
                                        <td>
                                            {{ $log->description }}
                                        </td>
                                        <td class="small text-muted">
                                            {{ $log->ip_address }}
                                        </td>
                                        <td>
                                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No settings activities logged yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Modals Section -->
    @include('admin.settings.partials.modals')

    <!-- Custom Toast Notifications -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
        <div id="settingsToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toast-body-content"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Restore Active Tab on page reload from URL Query Parameter
            const activeTabParam = "{{ $activeTab }}";
            if (activeTabParam) {
                const tabTrigger = new bootstrap.Tab(document.querySelector(`#tab-${activeTabParam}-btn`));
                tabTrigger.show();
            }

            // Image Preview Before Saving
            $('.branding-file-input').on('change', function(e) {
                const previewId = $(this).data('preview');
                const fileInput = this;
                if (fileInput.files && fileInput.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $(`#${previewId}`).attr('src', e.target.result);
                    };
                    reader.readAsDataURL(fileInput.files[0]);
                }
            });

            // Initialize ClassicEditor for Legal Rich Text Areas
            const editors = {};
            document.querySelectorAll('.editor-area').forEach((textarea) => {
                ClassicEditor.create(textarea, {
                    toolbar: [
                        'heading', '|', 'bold', 'italic', 'underline', 'fontFamily', 'fontSize', '|',
                        'alignment', '|', 'bulletedList', 'numberedList', '|', 'blockQuote', 'undo', 'redo'
                    ]
                }).then(editor => {
                    editors[textarea.name] = editor;
                }).catch(err => {
                    console.error('Editor initialization error:', err);
                });
            });

            // Show/Hide S3 Credentials dynamically
            $('#storage_type').on('change', function() {
                if ($(this).val() === 's3') {
                    $('#s3-fields').removeClass('d-none');
                } else {
                    $('#s3-fields').addClass('d-none');
                }
            });

            // Toast Trigger Helper
            function showToast(message, isSuccess = true) {
                const toastEl = document.getElementById('settingsToast');
                const content = document.getElementById('toast-body-content');
                content.innerText = message;
                
                toastEl.className = isSuccess ? 'toast align-items-center text-white bg-success border-0' : 'toast align-items-center text-white bg-danger border-0';
                
                const bsToast = new bootstrap.Toast(toastEl);
                bsToast.show();
            }

            // AJAX Form Submission Handler (All Forms in tab sections)
            $('.ajax-settings-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalBtnHtml = submitBtn.html();

                // Sync CKEditor instances data back to textarea
                for (const name in editors) {
                    if (editors.hasOwnProperty(name)) {
                        const editor = editors[name];
                        const textarea = form.find(`textarea[name="${name}"]`);
                        if (textarea.length) {
                            textarea.val(editor.getData());
                        }
                    }
                }

                // Show spinner loading
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

                // Clear previous errors
                form.find('.invalid-feedback').remove();
                form.find('.is-invalid').removeClass('is-invalid');

                const formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        submitBtn.prop('disabled', false).html(originalBtnHtml);
                        if (response.success) {
                            showToast(response.message, true);
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            showToast(response.message || 'Operation failed.', false);
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html(originalBtnHtml);
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            for (const field in errors) {
                                const input = form.find(`[name="${field}"]`);
                                if (input.length) {
                                    input.addClass('is-invalid');
                                    input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                                }
                            }
                            showToast('Validation failed. Please verify all fields.', false);
                        } else {
                            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error saving settings.';
                            showToast(msg, false);
                        }
                    }
                });
            });

            // Action: Restore Default Settings
            $('#restoreDefaultsBtn').on('click', function() {
                if (confirm('Are you sure you want to restore settings to system default? All custom configurations will be overwritten.')) {
                    const btn = $(this);
                    const origHtml = btn.html();
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Restoring...');

                    $.ajax({
                        url: "{{ route('settings.restore') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            btn.prop('disabled', false).html(origHtml);
                            if (response.success) {
                                showToast(response.message, true);
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            } else {
                                showToast(response.message || 'Restore failed.', false);
                            }
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html(origHtml);
                            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error restoring defaults.';
                            showToast(msg, false);
                        }
                    });
                }
            });

            // SMTP Test Email AJAX Submit
            $('#testSmtpForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const testBtn = form.find('button[type="submit"]');
                const origHtml = testBtn.html();

                // Merge values from active SMTP tab inputs into this test request
                form.find('input[name="mail_driver"]').val($('input[name="mail_driver"]').val());
                form.find('input[name="mail_host"]').val($('input[name="mail_host"]').val());
                form.find('input[name="mail_port"]').val($('input[name="mail_port"]').val());
                form.find('input[name="mail_username"]').val($('input[name="mail_username"]').val());
                form.find('input[name="mail_password"]').val($('input[name="mail_password"]').val());
                form.find('input[name="mail_encryption"]').val($('input[name="mail_encryption"]').val());
                form.find('input[name="mail_from_name"]').val($('input[name="mail_from_name"]').val());
                form.find('input[name="mail_from_email"]').val($('input[name="mail_from_email"]').val());

                testBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Sending...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        testBtn.prop('disabled', false).html(origHtml);
                        bootstrap.Modal.getInstance(document.getElementById('testSmtpModal')).hide();
                        showToast(response.message, true);
                    },
                    error: function(xhr) {
                        testBtn.prop('disabled', false).html(origHtml);
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'SMTP Test failed.';
                        alert(msg);
                    }
                });
            });

            // Firebase Notification AJAX Submit
            $('#testFirebaseForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const testBtn = form.find('button[type="submit"]');
                const origHtml = testBtn.html();

                // Import Server Key from active Firebase tab inputs into test request
                form.find('input[name="server_key"]').val($('input[name="firebase_server_key"]').val());

                testBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Sending...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        testBtn.prop('disabled', false).html(origHtml);
                        bootstrap.Modal.getInstance(document.getElementById('testFirebaseModal')).hide();
                        showToast(response.message, true);
                    },
                    error: function(xhr) {
                        testBtn.prop('disabled', false).html(origHtml);
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'FCM notification test failed.';
                        alert(msg);
                    }
                });
            });

            // General Maintenance Operations AJAX Handler
            $('.maintenance-btn').on('click', function(e) {
                e.preventDefault();
                const btn = $(this);
                const origHtml = btn.html();
                const action = btn.data('action');

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Executing...');

                $.ajax({
                    url: "{{ route('settings.maintenance') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        action: action
                    },
                    success: function(response) {
                        btn.prop('disabled', false).html(origHtml);
                        showToast(response.message, true);
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(origHtml);
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Operation failed.';
                        showToast(msg, false);
                    }
                });
            });
        });

        function previewLogoProfileSettings(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('logo-preview-settings');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
@endsection
