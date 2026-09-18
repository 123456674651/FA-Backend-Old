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
                                
                                <div class="permissions-table-container">
                                    <table class="table permissions-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 25%; text-align: left; padding-left: 20px;">MODULES</th>
                                                <th class="text-center">
                                                    <div class="th-icon-text">
                                                        <i class="bi bi-check-circle-fill" style="color: #3b82f6;"></i>
                                                        <span>ALL</span>
                                                    </div>
                                                </th>
                                                <th class="text-center">
                                                    <div class="th-icon-text">
                                                        <i class="bi bi-list-ul"></i>
                                                        <span>LIST</span>
                                                    </div>
                                                </th>
                                                <th class="text-center">
                                                    <div class="th-icon-text">
                                                        <i class="bi bi-plus-circle"></i>
                                                        <span>CREATE</span>
                                                    </div>
                                                </th>
                                                <th class="text-center">
                                                    <div class="th-icon-text">
                                                        <i class="bi bi-pencil"></i>
                                                        <span>EDIT</span>
                                                    </div>
                                                </th>
                                                <th class="text-center">
                                                    <div class="th-icon-text">
                                                        <i class="bi bi-eye"></i>
                                                        <span>VIEW</span>
                                                    </div>
                                                </th>
                                                <th class="text-center">
                                                    <div class="th-icon-text">
                                                        <i class="bi bi-trash"></i>
                                                        <span>DELETE</span>
                                                    </div>
                                                </th>
                                                <th class="text-center">
                                                    <div class="th-icon-text">
                                                        <i class="bi bi-arrow-down-up"></i>
                                                        <span>IMPORT/EXPORT</span>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dashboard -->
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center" style="font-weight: 600; font-size: 14px; color: #0f172a;">
                                                        <i class="bi bi-speedometer2 me-2 fs-5" style="color: #334155;"></i> Dashboard
                                                    </div>
                                                </td>
                                                <td class="text-center"><input class="form-check-input permission-all custom-cb shadow-none" type="checkbox"></td>
                                                <td class="text-center"></td>
                                                <td class="text-center"></td>
                                                <td class="text-center"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="view dashboard"></td>
                                                <td class="text-center"></td>
                                                <td class="text-center"></td>
                                            </tr>
                                            <!-- Legal Notices -->
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center" style="font-weight: 600; font-size: 14px; color: #0f172a;">
                                                        <i class="bi bi-file-earmark-text me-2 fs-5" style="color: #64748b;"></i> Legal Notices
                                                    </div>
                                                </td>
                                                <td class="text-center"><input class="form-check-input permission-all custom-cb shadow-none" type="checkbox"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="list legal notices"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="create legal notices"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="edit legal notices"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="view legal notices"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="delete legal notices"></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                            </tr>

                                            <!-- Subscription Plan -->
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center" style="font-weight: 600; font-size: 14px; color: #0f172a;">
                                                        <i class="bi bi-card-checklist me-2 fs-5" style="color: #64748b;"></i> Subscription Plan
                                                    </div>
                                                </td>
                                                <td class="text-center"><input class="form-check-input permission-all custom-cb shadow-none" type="checkbox"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="list subscription plan"></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                            </tr>

                                            <!-- Subscription Invoices -->
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center" style="font-weight: 600; font-size: 14px; color: #0f172a;">
                                                        <i class="bi bi-receipt me-2 fs-5" style="color: #64748b;"></i> Subscription Invoices
                                                    </div>
                                                </td>
                                                <td class="text-center"><input class="form-check-input permission-all custom-cb shadow-none" type="checkbox"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="list subscription invoices"></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="view subscription invoices"></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="import_export subscription invoices"></td>
                                            </tr>

                                            <!-- CMS Pages -->
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center" style="font-weight: 600; font-size: 14px; color: #0f172a;">
                                                        <i class="bi bi-layout-text-window-reverse me-2 fs-5" style="color: #64748b;"></i> CMS
                                                    </div>
                                                </td>
                                                <td class="text-center"><input class="form-check-input permission-all custom-cb shadow-none" type="checkbox"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="list cms"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="create cms"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="edit cms"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="view cms"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="delete cms"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="import_export cms"></td>
                                            </tr>

                                            <!-- Purpose -->
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center" style="font-weight: 600; font-size: 14px; color: #0f172a;">
                                                        <i class="bi bi-bullseye me-2 fs-5" style="color: #64748b;"></i> Purpose
                                                    </div>
                                                </td>
                                                <td class="text-center"><input class="form-check-input permission-all custom-cb shadow-none" type="checkbox"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="list purpose"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="create purpose"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="edit purpose"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="view purpose"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="delete purpose"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="import_export purpose"></td>
                                            </tr>

                                            <!-- Language -->
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center" style="font-weight: 600; font-size: 14px; color: #0f172a;">
                                                        <i class="bi bi-translate me-2 fs-5" style="color: #64748b;"></i> Language
                                                    </div>
                                                </td>
                                                <td class="text-center"><input class="form-check-input permission-all custom-cb shadow-none" type="checkbox"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="list language"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="create language"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="edit language"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="view language"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="delete language"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="import_export language"></td>
                                            </tr>

                                            <!-- Users (Admin) -->
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center" style="font-weight: 600; font-size: 14px; color: #0f172a;">
                                                        <i class="bi bi-people me-2 fs-5" style="color: #64748b;"></i> Users (Admins)
                                                    </div>
                                                </td>
                                                <td class="text-center"><input class="form-check-input permission-all custom-cb shadow-none" type="checkbox"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="list users"></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="import_export users"></td>
                                            </tr>

                                            <!-- Customer -->
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center" style="font-weight: 600; font-size: 14px; color: #0f172a;">
                                                        <i class="bi bi-person-badge me-2 fs-5" style="color: #64748b;"></i> Customer
                                                    </div>
                                                </td>
                                                <td class="text-center"><input class="form-check-input permission-all custom-cb shadow-none" type="checkbox"></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="list customer"></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                                <td class="text-center"><span style="color:#94a3b8;">-</span></td>
                                                <td class="text-center"><input class="form-check-input permission-cb custom-cb shadow-none" type="checkbox" name="permissions[]" value="import_export customer"></td>
                                            </tr>


                                        </tbody>
                                    </table>
                                </div>
                        </div>

                        <!-- Deal Categories Custom Permissions Table -->
                        <div class="card shadow-sm border-0 mb-4 rounded-3">
                            <div class="card-header bg-white border-bottom py-3 settings-section-header">
                                <h6 class="mb-0" style="color: #0f172a; font-weight: 600; font-size: 15px;">
                                    <i class="bi bi-tags text-primary me-2"></i>Agreement Categories Permissions (Deal Categories)
                                </h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table mb-0 permissions-table align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 250px; font-weight: 600; color: #475569;">Feature</th>
                                            <th style="font-weight: 600; color: #475569;">Permissions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div style="font-weight: 600; font-size: 14px; color: #0f172a;">
                                                    Agreement Category List
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-4">
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-all" type="checkbox" id="cat_all">
                                                        <label class="form-check-label" for="cat_all">All</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="add category" id="cat_add">
                                                        <label class="form-check-label" for="cat_add">Add</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="edit category" id="cat_edit">
                                                        <label class="form-check-label" for="cat_edit">Edit</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="view sub agreements" id="cat_sub">
                                                        <label class="form-check-label" for="cat_sub">Sub Agreements</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="warning category" id="cat_warn">
                                                        <label class="form-check-label" for="cat_warn">Warning</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="attribute list category" id="cat_attr">
                                                        <label class="form-check-label" for="cat_attr">Attribute List</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="manage attributes category" id="cat_mattr">
                                                        <label class="form-check-label" for="cat_mattr">Manage Attributes</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Sub Page: View Sub Agreements -->
                                        <tr style="background-color: #f8fafc;">
                                            <td style="padding-left: 45px !important;">
                                                <div class="d-flex align-items-center" style="font-weight: 500; font-size: 13px; color: #475569;">
                                                    <i class="bi bi-arrow-return-right me-2" style="font-size: 14px; color: #94a3b8;"></i> View Sub Agreements
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-4">
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-all" type="checkbox" id="sub_all">
                                                        <label class="form-check-label" for="sub_all">All</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="add sub agreement" id="sub_add">
                                                        <label class="form-check-label" for="sub_add">Add</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="edit sub agreement" id="sub_edit_btn">
                                                        <label class="form-check-label" for="sub_edit_btn">Edit</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="warning sub agreement" id="sub_warn">
                                                        <label class="form-check-label" for="sub_warn">Warning</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="attributes sub agreement" id="sub_attr">
                                                        <label class="form-check-label" for="sub_attr">Attributes</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="manage attributes sub agreement" id="sub_mattr">
                                                        <label class="form-check-label" for="sub_mattr">Manage Attributes</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Sub Page: Attributes -->
                                        <tr style="background-color: #f8fafc;">
                                            <td style="padding-left: 75px !important;">
                                                <div class="d-flex align-items-center" style="font-weight: 500; font-size: 13px; color: #475569;">
                                                    <i class="bi bi-arrow-return-right me-2" style="font-size: 14px; color: #94a3b8;"></i> Attributes <span class="ms-2 text-muted fw-normal" style="font-size: 11px;">(from Attribute List)</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-4">
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-all" type="checkbox" id="attr_all">
                                                        <label class="form-check-label" for="attr_all">All</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="list attributes" id="attr_list">
                                                        <label class="form-check-label" for="attr_list">List</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="add attributes" id="attr_add">
                                                        <label class="form-check-label" for="attr_add">Add</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="edit attributes" id="attr_edit">
                                                        <label class="form-check-label" for="attr_edit">Edit</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="delete attributes" id="attr_del">
                                                        <label class="form-check-label" for="attr_del">Delete</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Sub Page: Manage Sub Categories -->
                                        <tr style="background-color: #f8fafc;">
                                            <td style="padding-left: 75px !important;">
                                                <div class="d-flex align-items-center" style="font-weight: 500; font-size: 13px; color: #475569;">
                                                    <i class="bi bi-arrow-return-right me-2" style="font-size: 14px; color: #94a3b8;"></i> Manage Sub Categories <span class="ms-2 text-muted fw-normal" style="font-size: 11px;">(from Manage Attributes)</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-4">
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-all" type="checkbox" id="msc_all">
                                                        <label class="form-check-label" for="msc_all">All</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="list manage sub categories" id="msc_list">
                                                        <label class="form-check-label" for="msc_list">List</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="add manage sub categories" id="msc_add">
                                                        <label class="form-check-label" for="msc_add">Add</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="attributes manage sub categories" id="msc_attr">
                                                        <label class="form-check-label" for="msc_attr">Attributes</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="edit manage sub categories" id="msc_edit">
                                                        <label class="form-check-label" for="msc_edit">Edit</label>
                                                    </div>
                                                    <div class="form-check custom-checkbox-container">
                                                        <input class="form-check-input custom-cb shadow-none permission-cb" type="checkbox" name="permissions[]" value="download manage sub categories" id="msc_dl">
                                                        <label class="form-check-label" for="msc_dl">Download</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
