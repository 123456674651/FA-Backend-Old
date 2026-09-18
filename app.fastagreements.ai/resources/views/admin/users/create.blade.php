@extends('admin.layout.layout')

@section('css')
<style>
    /* Custom Table Styling */
    .permissions-table-container {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }
    .permissions-table {
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .permissions-table thead {
        background-color: #f8fafc;
    }
    .permissions-table th {
        border-bottom: 1px solid #e2e8f0;
        border-top: none;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px 10px;
    }
    .permissions-table td {
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        padding: 6px 10px;
    }
    .permissions-table tr:last-child td {
        border-bottom: none;
    }
    .th-icon-text {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
    /* Custom Switch */
    .custom-switch-md .form-check-input {
        width: 2.5rem;
        height: 1.25rem;
        cursor: pointer;
    }
    .custom-switch-md .form-check-input:checked {
        background-color: #3458A4;
        border-color: #3458A4;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .th-icon-text i {
        font-size: 15px;
        color: #64748b;
    }
    .th-icon-text span {
        font-size: 9px;
    }
    
    /* Custom Checkbox: Rely on Bootstrap's native SVG styling but override size */
    .custom-cb {
        width: 15px;
        height: 15px;
        cursor: pointer;
        margin: 0 auto;
        display: block;
        border-color: #94a3b8;
    }
    .custom-cb:focus {
        box-shadow: none;
        border-color: #3b82f6;
    }
    .custom-cb:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }
    
    .permissions-table td:first-child, .permissions-table th:first-child {
        padding-left: 15px !important;
    }
</style>
@endsection

@section('content')
<main id="main" class="main p-4">
    <!-- Header Section -->
    <div class="mb-4">
        <h4 class="mb-1 fw-bold text-dark" style="font-size: 22px;">New Admin</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size: 13px; font-weight: 500; color: #667085;">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none text-muted">Users</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">New Admin</li>
            </ol>
        </nav>
    </div>

    <!-- Form Section -->
    <section class="section">
        <div class="legal-form">
            <div class="card settings-section-card border-0 mb-4">
                <div class="card-body p-4">
                    <div class="settings-section-header">
                        <span class="settings-section-icon" style="background-color: #eef2fa; color: #4b6bfb; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 8px;"><i class="bi bi-person fs-5"></i></span>
                        <div>
                            <h5>Admin Information</h5>
                            <p>Fill out the details below to add a new admin.</p>
                        </div>
                    </div>
                    
                    <form id="addAdminForm" action="{{ route('users.store') }}" method="POST">
                        @csrf
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Jane Doe" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="e.g. jane@company.com" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="" disabled selected>Select assigned role</option>
                                    <option value="ADMIN" {{ old('role') == 'ADMIN' ? 'selected' : '' }}>Admin</option>
                                    <option value="SUPER_ADMIN" {{ old('role') == 'SUPER_ADMIN' ? 'selected' : '' }}>Super Admin</option>
                                </select>
                                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Account Status</label>
                                <select name="status" class="form-select">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <div class="settings-password-field">
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                    <i class="bi bi-eye settings-toggle-password"></i>
                                </div>
                                <small class="form-text text-muted mt-2 d-block" style="font-size: 12px;">Must be at least 8 characters long.</small>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <div class="settings-password-field">
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                    <i class="bi bi-eye settings-toggle-password"></i>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4 mb-3">
                            <div class="col-12">
                                <div class="settings-section-header mb-3">
                                    <span class="settings-section-icon" style="background-color: #eef2fa; color: #4b6bfb; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 8px;"><i class="bi bi-shield-lock-fill fs-5"></i></span>
                                    <div>
                                        <h5>Module Permissions</h5>
                                        <p>Select actions for each module.</p>
                                    </div>
                                </div>
                                
                                <div class="card shadow-sm border-0 mb-4 rounded-3">
                                    <div class="card-body p-4">
                                        <div class="row gx-5">
                                            @php
                                                $col1 = [
                                                    ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2'],
                                                    ['key' => 'agreements', 'label' => 'Agreements', 'icon' => 'bi-file-earmark-check'],
                                                    ['key' => 'categories', 'label' => 'Categories', 'icon' => 'bi-tags'],
                                                    ['key' => 'legal_notices', 'label' => 'Legal Notices', 'icon' => 'bi-file-earmark-medical'],
                                                    ['key' => 'plans', 'label' => 'Plans', 'icon' => 'bi-card-list'],
                                                    ['key' => 'invoices', 'label' => 'Invoices', 'icon' => 'bi-receipt'],
                                                    ['key' => 'transactions', 'label' => 'Transactions', 'icon' => 'bi-currency-exchange'],
                                                ];
                                                $col2 = [
                                                    ['key' => 'cms_pages', 'label' => 'CMS Pages', 'icon' => 'bi-journal-text'],
                                                    ['key' => 'purpose', 'label' => 'Purpose', 'icon' => 'bi-bullseye'],
                                                    ['key' => 'language', 'label' => 'Language', 'icon' => 'bi-translate'],
                                                    ['key' => 'customers', 'label' => 'Customers', 'icon' => 'bi-people'],
                                                    ['key' => 'admin', 'label' => 'Admin', 'icon' => 'bi-person-badge'],
                                                    ['key' => 'settings', 'label' => 'Settings', 'icon' => 'bi-gear'],
                                                    ['key' => 'reports', 'label' => 'Reports', 'icon' => 'bi-graph-up'],
                                                ];
                                            @endphp

                                            <!-- Column 1 -->
                                            <div class="col-md-6">
                                                <div class="d-flex flex-column">
                                                    @foreach($col1 as $index => $menu)
                                                    <div class="d-flex align-items-center justify-content-between py-3 {{ $loop->last ? '' : 'border-bottom' }}" style="border-color: #f1f5f9 !important;">
                                                        <div class="d-flex align-items-center">
                                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 bg-light text-secondary" style="width: 38px; height: 38px;">
                                                                <i class="bi {{ $menu['icon'] }} fs-5"></i>
                                                            </div>
                                                            <label class="fw-semibold text-dark mb-0 user-select-none" for="perm_{{ $menu['key'] }}" style="cursor:pointer; font-size: 14.5px;">{{ $menu['label'] }}</label>
                                                        </div>
                                                        <div class="form-check form-switch custom-switch-md mb-0">
                                                            <input class="form-check-input shadow-none cursor-pointer" type="checkbox" role="switch" id="perm_{{ $menu['key'] }}" name="permissions[]" value="access {{ $menu['key'] }}">
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <!-- Column 2 -->
                                            <div class="col-md-6">
                                                <div class="d-flex flex-column h-100">
                                                    @foreach($col2 as $index => $menu)
                                                    <div class="d-flex align-items-center justify-content-between py-3 {{ $loop->last ? '' : 'border-bottom' }}" style="border-color: #f1f5f9 !important;">
                                                        <div class="d-flex align-items-center">
                                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 bg-light text-secondary" style="width: 38px; height: 38px;">
                                                                <i class="bi {{ $menu['icon'] }} fs-5"></i>
                                                            </div>
                                                            <label class="fw-semibold text-dark mb-0 user-select-none" for="perm_{{ $menu['key'] }}" style="cursor:pointer; font-size: 14.5px;">{{ $menu['label'] }}</label>
                                                        </div>
                                                        <div class="form-check form-switch custom-switch-md mb-0">
                                                            <input class="form-check-input shadow-none cursor-pointer" type="checkbox" role="switch" id="perm_{{ $menu['key'] }}" name="permissions[]" value="access {{ $menu['key'] }}">
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" id="submitBtn" class="btn px-4 py-2" style="background-color: #3458A4; color: #FFFFFF; border: none; border-radius: 6px; font-size: 13px; font-weight: 500;">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Permissions Matrix logic
        $('.permission-all').on('change', function() {
            $(this).closest('tr').find('.permission-cb').prop('checked', $(this).prop('checked'));
        });
        
        $('.permission-cb').on('change', function() {
            let row = $(this).closest('tr');
            let total = row.find('.permission-cb').length;
            let checked = row.find('.permission-cb:checked').length;
            row.find('.permission-all').prop('checked', total === checked && total > 0);
        });

        // Password fields: show/hide toggle
        $('.settings-toggle-password').on('click', function () {
            const $input = $(this).siblings('input');
            const isHidden = $input.attr('type') === 'password';
            $input.attr('type', isHidden ? 'text' : 'password');
            $(this).toggleClass('bi-eye bi-eye-slash');
        });

        // Remove error styling instantly when user types or selects
        $('#addAdminForm').find('input, select').on('input change', function() {
            $(this).removeClass('is-invalid');
            if($(this).parent('.settings-password-field').length) {
                $(this).parent().next('.invalid-feedback.ajax-error').remove();
            } else {
                $(this).next('.invalid-feedback.ajax-error').remove();
            }
        });

        // AJAX Form Submission
        $('#addAdminForm').on('submit', function(e) {
            e.preventDefault();
            
            let form = $(this);
            let btn = $('#submitBtn');
            let originalText = btn.html();
            
            // Show loading state, but DO NOT clear errors yet to prevent form height blinking
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                headers: {
                    'Accept': 'application/json'
                },
                success: function(response) {
                    // Clear errors on success
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.ajax-error').remove();
                    
                    if(response.status === 'success') {
                        window.location.href = response.redirect;
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalText);
                    
                    // Clear previous errors only right before displaying new ones to prevent blink
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.ajax-error').remove();
                    
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            let input = form.find('[name="'+key+'"]');
                            input.addClass('is-invalid');
                            
                            // Standard Bootstrap Validation HTML
                            let errorHtml = '<div class="invalid-feedback ajax-error d-block">' + value[0] + '</div>';
                            
                            if(input.parent('.settings-password-field').length) {
                                input.parent().after(errorHtml);
                            } else {
                                input.after(errorHtml);
                            }
                        });
                    } else {
                        alert('Something went wrong. Please try again.');
                    }
                }
            });
        });
    });
</script>
@endsection
