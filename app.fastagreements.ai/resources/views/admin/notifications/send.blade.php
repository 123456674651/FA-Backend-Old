@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Send Push Notification</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Send Notification</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Left Panel: Campaign Builder -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form id="send-notification-form" action="{{ route('notifications.send') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="template_image" name="template_image" value="">

                            <!-- 1. Select Template -->
                            <div class="mb-4">
                                <label for="template_id" class="form-label fw-bold text-dark">Notification Template <span class="text-muted">(Optional)</span></label>
                                <select id="template_id" class="form-select select2-basic">
                                    <option value="">-- Customize from scratch --</option>
                                    @foreach($templates as $temp)
                                        <option value="{{ $temp->id }}">{{ $temp->title }} ({{ ucwords(str_replace('_', ' ', $temp->notification_type)) }})</option>
                                    @endforeach
                                </select>
                                <span class="form-text text-muted">Selecting a template auto-populates the payload fields below.</span>
                            </div>

                            <hr>

                            <!-- 2. Notification Payload -->
                            <h5 class="fw-bold mb-3 text-dark">Notification Payload</h5>

                            <div class="row">
                                <!-- Title -->
                                <div class="col-md-6 mb-3">
                                    <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required placeholder="e.g. Action Required: Account Expiration">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Type -->
                                <div class="col-md-6 mb-3">
                                    <label for="notification_type" class="form-label fw-semibold">Notification Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('notification_type') is-invalid @enderror" id="notification_type" name="notification_type" required>
                                        <option value="" disabled selected>Select Type</option>
                                        @foreach($types as $key => $val)
                                            <option value="{{ $key }}" {{ old('notification_type') === $key ? 'selected' : '' }}>{{ $val }}</option>
                                        @endforeach
                                    </select>
                                    @error('notification_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Message (TinyMCE) -->
                            <div class="mb-3">
                                <label for="message" class="form-label fw-semibold">Message / Content <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message">{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row align-items-center mb-4">
                                <!-- Upload Image Override -->
                                <div class="col-md-7">
                                    <label for="image_file" class="form-label fw-semibold">Upload Banner Image <span class="text-muted">(Optional, overrides template image)</span></label>
                                    <input type="file" class="form-control @error('image_file') is-invalid @enderror" id="image_file" name="image_file" accept="image/*" onchange="previewUploadImage(event);">
                                    @error('image_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-5 text-center mt-3 mt-md-0">
                                    <img id="template_img_preview" src="" alt="Template Preview" style="max-height: 100px; display: none;" class="img-thumbnail">
                                    <img id="upload_img_preview" src="" alt="Uploaded Preview" style="max-height: 100px; display: none;" class="img-thumbnail">
                                </div>
                            </div>

                            <hr>

                            <!-- 3. Audience Filters -->
                            <h5 class="fw-bold mb-3 text-dark">Target Recipient Filters</h5>

                            <div class="row g-3 mb-4">
                                <!-- Target Audience Type -->
                                <div class="col-md-4">
                                    <label for="audience_type" class="form-label fw-semibold">Audience Type <span class="text-danger">*</span></label>
                                    <select id="audience_type" name="audience_type" class="form-select">
                                        <option value="all">All Users</option>
                                        <option value="category">Category Users</option>
                                        <option value="state">State Wise Users</option>
                                        <option value="city">City Wise Users</option>
                                        <option value="new_users">New Users (Date Range)</option>
                                    </select>
                                </div>

                                <!-- Dynamic Filters Container -->
                                <div class="col-md-8">
                                    <!-- Category Selector -->
                                    <div id="div-category" class="audience-subfield" style="display: none;">
                                        <label for="target_category_id" class="form-label fw-semibold">Select Category</label>
                                        <select id="target_category_id" name="target_category_id" class="form-select select2-basic">
                                            <option value="">-- Choose Category --</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- State Selector -->
                                    <div id="div-state" class="audience-subfield" style="display: none;">
                                        <label for="target_state_id" class="form-label fw-semibold">Select State</label>
                                        <select id="target_state_id" name="target_state_id" class="form-select select2-basic">
                                            <option value="">-- Choose State --</option>
                                            @foreach($states as $st)
                                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- City Selector -->
                                    <div id="div-city" class="audience-subfield" style="display: none;">
                                        <label for="target_city_id" class="form-label fw-semibold">Select City</label>
                                        <select id="target_city_id" name="target_city_id" class="form-select select2-basic">
                                            <option value="">-- Choose City --</option>
                                            @foreach($cities as $ct)
                                                <option value="{{ $ct->id }}">{{ $ct->city }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- New Users Date Ranges -->
                                    <div id="div-new-users" class="audience-subfield" style="display: none;">
                                        <div class="row">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold">Reg From</label>
                                                <input type="date" name="reg_from_date" class="form-control">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold">Reg To</label>
                                                <input type="date" name="reg_to_date" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Filters Collapsible -->
                            <div class="accordion mb-4" id="accordionFilters">
                                <div class="accordion-item border rounded shadow-none">
                                    <h2 class="accordion-header" id="headingFilters">
                                        <button class="accordion-button collapsed fw-semibold py-2 px-3 bg-light text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilters" aria-expanded="false">
                                            <i class="bi bi-funnel me-2"></i> Additional Filters & Search Combinations
                                        </button>
                                    </h2>
                                    <div id="collapseFilters" class="accordion-collapse collapse" data-bs-parent="#accordionFilters">
                                        <div class="accordion-body p-3 bg-white">
                                            <div class="row g-3">
                                                <!-- Specific Customer -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small text-muted">Customer</label>
                                                    <select id="customer_id" name="customer_id" class="form-select select2-basic">
                                                        <option value="">All Customers</option>
                                                        @foreach($customers as $cust)
                                                            <option value="{{ $cust->id }}">{{ $cust->name }} ({{ $cust->mobile }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Specific Language -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small text-muted">Language</label>
                                                    <select id="language_id" name="language_id" class="form-select select2-basic">
                                                        <option value="">All Languages</option>
                                                        @foreach($languages as $lang)
                                                            <option value="{{ $lang->id }}">{{ $lang->language_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Status toggle -->
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold small text-muted">Account Status</label>
                                                    <select name="active_users" id="active_users" class="form-select">
                                                        <option value="">All Statuses</option>
                                                        <option value="1">Active Users</option>
                                                        <option value="0">Inactive Users</option>
                                                    </select>
                                                </div>

                                                <!-- General Creation dates -->
                                                <div class="col-md-8">
                                                    <label class="form-label fw-semibold small text-muted">Registration Date Range</label>
                                                    <div class="d-flex align-items-center">
                                                        <input type="date" name="from_date" class="form-control me-2">
                                                        <span class="me-2 text-muted">to</span>
                                                        <input type="date" name="to_date" class="form-control">
                                                    </div>
                                                </div>

                                                <!-- String search -->
                                                <div class="col-md-12">
                                                    <label class="form-label fw-semibold small text-muted">Search Query</label>
                                                    <input type="text" name="search" id="search" class="form-control" placeholder="Search by name, mobile number, or email...">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- 4. Schedule Campaign -->
                            <h5 class="fw-bold mb-3 text-dark">Delivery Schedule</h5>

                            <div class="row g-3 mb-4 align-items-center">
                                <div class="col-md-5">
                                    <div class="form-check form-check-inline me-4">
                                        <input class="form-check-input" type="radio" name="schedule_type" id="sched_immediate" value="immediate" checked>
                                        <label class="form-check-input-label fw-semibold text-dark" for="sched_immediate">Send Immediately</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="schedule_type" id="sched_later" value="schedule">
                                        <label class="form-check-input-label fw-semibold text-dark" for="sched_later">Schedule Campaign</label>
                                    </div>
                                </div>

                                <div class="col-md-7" id="div-schedule-time" style="display: none;">
                                    <label class="form-label fw-semibold text-muted small">Select Scheduled Date & Time</label>
                                    <input type="datetime-local" id="scheduled_date_time" name="scheduled_date_time" class="form-control">
                                </div>
                            </div>

                            <!-- Form Submit Action -->
                            <div class="border-top pt-3 d-flex justify-content-end">
                                <button type="button" onclick="confirmCampaignSubmit();" class="btn btn-dark btn-md fw-semibold px-4">
                                    <i class="bi bi-send me-1"></i> Send Campaign Notification
                                </button>
                            </div>

                            <!-- Modal Campaign Confirmation -->
                            <div class="modal fade" id="campaignConfirmModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-dark text-white">
                                            <h5 class="modal-title fw-bold">Confirm Campaign Launch</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4 text-dark">
                                            <p class="mb-3">You are about to launch a push notification campaign with the following configuration:</p>
                                            <table class="table table-bordered small">
                                                <tr>
                                                    <th style="width: 150px;">Campaign Title</th>
                                                    <td id="modal-campaign-title"></td>
                                                </tr>
                                                <tr>
                                                    <th>Total Recipients</th>
                                                    <td><strong class="text-success" id="modal-recipient-count">0</strong> customers</td>
                                                </tr>
                                                <tr>
                                                    <th>Delivery Time</th>
                                                    <td id="modal-delivery-time">Immediate</td>
                                                </tr>
                                            </table>
                                            <p class="text-danger small mb-0"><i class="bi bi-info-circle me-1"></i> Note: This campaign will process asynchronously in the background. Please ensure all details are correct.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-dark fw-bold px-4">Launch Campaign</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Recipient Preview Log -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 80px; z-index: 10;">
                    <div class="card-header bg-light border-0 py-3">
                        <h5 class="fw-bold m-0 text-dark">Recipient Preview</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center py-4 bg-light rounded border mb-4">
                            <span class="d-block text-muted small text-uppercase fw-semibold">Target User Count</span>
                            <span id="preview-count" class="d-block display-4 fw-bold text-dark my-2">0</span>
                            <span class="text-muted small">Estimated notifications payload</span>
                        </div>

                        <h6 class="fw-bold mb-2 small text-muted">First Few Recipients Preview</h6>
                        <ul id="preview-user-list" class="list-group list-group-flush border rounded" style="max-height: 250px; overflow-y: auto;">
                            <li class="list-group-item text-center text-muted small py-3">No matching users found.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@section('js')
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- TinyMCE Editor -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>

<script>
    var templates = @json($templates);

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 dropdown elements
        $('.select2-basic').select2({
            width: '100%'
        });

        // Initialize TinyMCE for notification message
        tinymce.init({
            selector: '#message',
            plugins: 'lists link image table code',
            toolbar: 'undo redo | formatselect | bold italic | bullist numlist outdent indent | link | code',
            height: 250,
            setup: function (editor) {
                editor.on('change', function () {
                    tinymce.triggerSave();
                });
            }
        });

        // Toggle Dynamic Audience Field Groups
        $('#audience_type').on('change', function() {
            var aud = $(this).val();
            $('.audience-subfield').hide();
            if (aud === 'category') {
                $('#div-category').show();
            } else if (aud === 'state') {
                $('#div-state').show();
            } else if (aud === 'city') {
                $('#div-city').show();
            } else if (aud === 'new_users') {
                $('#div-new-users').show();
            }
            updatePreview();
        });

        // Handle Send Schedule Options Toggle
        $('input[name="schedule_type"]').on('change', function() {
            if ($(this).val() === 'schedule') {
                $('#div-schedule-time').show();
            } else {
                $('#div-schedule-time').hide();
                $('#scheduled_date_time').val('');
            }
        });

        // Auto-fill form fields when template selected
        $('#template_id').on('change', function() {
            var id = $(this).val();
            if (!id) {
                // Clear fields
                $('#title').val('');
                $('#notification_type').val('');
                $('#template_image').val('');
                $('#template_img_preview').hide();
                tinymce.get('message').setContent('');
                updatePreview();
                return;
            }

            var selected = templates.find(t => t.id == id);
            if (selected) {
                $('#title').val(selected.title);
                $('#notification_type').val(selected.notification_type);
                $('#template_image').val(selected.image);
                if (selected.image) {
                    $('#template_img_preview').attr('src', '/' + selected.image).show();
                } else {
                    $('#template_img_preview').hide();
                }
                tinymce.get('message').setContent(selected.message);
                updatePreview();
            }
        });

        // Dynamic change triggers to refresh live counts
        $('#audience_type, #target_category_id, #target_state_id, #target_city_id, #customer_id, #language_id, #active_users, #search').on('change input', function() {
            updatePreview();
        });

        $('input[name="reg_from_date"], input[name="reg_to_date"], input[name="from_date"], input[name="to_date"]').on('change input', function() {
            updatePreview();
        });

        // Initialize preview count
        updatePreview();
    });

    // Fetch user counts dynamically via Ajax
    function updatePreview() {
        var formData = $('#send-notification-form').serialize();
        $.ajax({
            url: "{{ route('notifications.preview') }}",
            type: "GET",
            data: formData,
            success: function(response) {
                if (response.status) {
                    $('#preview-count').text(response.total_recipients);
                    $('#modal-recipient-count').text(response.total_recipients);
                    
                    var listHtml = '';
                    if (response.preview.length > 0) {
                        response.preview.forEach(function(user) {
                            listHtml += '<li class="list-group-item d-flex justify-content-between align-items-center small">' +
                                        '<span><strong>' + user.name + '</strong> (' + user.email + ')</span>' +
                                        '<span class="text-muted">' + user.mobile + '</span>' +
                                        '</li>';
                        });
                    } else {
                        listHtml = '<li class="list-group-item text-center text-muted small py-3">No matching users found.</li>';
                    }
                    $('#preview-user-list').html(listHtml);
                }
            }
        });
    }

    // Custom image upload preview handler
    function previewUploadImage(event) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('upload_img_preview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // Modal preview and submit validation
    function confirmCampaignSubmit() {
        var title = $('#title').val();
        var message = tinymce.get('message').getContent();
        var type = $('#notification_type').val();
        var sched = $('input[name="schedule_type"]:checked').val();
        var schedTime = $('#scheduled_date_time').val();

        if (!title || !message || !type) {
            alert('Please populate the required Title, Message, and Notification Type fields.');
            return;
        }

        if (sched === 'schedule' && !schedTime) {
            alert('Please select a scheduled Date and Time.');
            return;
        }

        // Auto-fill confirmation info
        $('#modal-campaign-title').text(title);
        if (sched === 'schedule') {
            $('#modal-delivery-time').html('<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Scheduled: ' + schedTime + '</span>');
        } else {
            $('#modal-delivery-time').html('<span class="badge bg-success"><i class="bi bi-lightning-charge me-1"></i> Immediate Send</span>');
        }

        // Show confirmation dialog
        var myModal = new bootstrap.Modal(document.getElementById('campaignConfirmModal'));
        myModal.show();
    }
</script>
@endsection
