@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Legal Notice Details</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('legal-notices.index') }}">Legal Notices</a></li>
                    <li class="breadcrumb-item active">View Details</li>
                </ol>
            </nav>
        </div>
        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('legal-notices.index') }}" class="btn btn-secondary me-1">
                <i class="bi bi-arrow-left-square"></i> Back
            </a>
            <a href="{{ route('legal-notices.edit', $notice->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> Edit
            </a>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <!-- Left Column: Details -->
            <div class="col-lg-8">
                <!-- Opponent Company Details -->
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h4 class="card-title p-0 m-0">Opponent Details</h4>
                            @php
                                $badgeClasses = [
                                    'Pending' => 'bg-warning text-dark',
                                    'Approved' => 'bg-success text-white',
                                    'Rejected' => 'bg-danger text-white',
                                    'In Progress' => 'bg-info text-dark',
                                    'Replied' => 'bg-primary text-white',
                                    'Closed' => 'bg-secondary text-white'
                                ];
                                $badgeClass = $badgeClasses[$notice->status] ?? 'bg-light';
                            @endphp
                            <span class="badge {{ $badgeClass }} fs-6">{{ $notice->status }}</span>
                        </div>
                        <hr class="mt-0">

                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold"><strong>Company Name:</strong></div>
                            <div class="col-md-8 text-secondary">{{ $notice->company_name }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold"><strong>Contact Person:</strong></div>
                            <div class="col-md-8 text-secondary">{{ $notice->company_person_name }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold"><strong>Designation:</strong></div>
                            <div class="col-md-8 text-secondary">{{ $notice->company_person_designation }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold"><strong>Company Address:</strong></div>
                            <div class="col-md-8 text-secondary" style="white-space: pre-line;">{{ $notice->company_address }}</div>
                        </div>
                    </div>
                </div>

                <!-- My Company Details -->
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <h4 class="card-title p-0 mb-3">My Company Details</h4>
                        <hr class="mt-0">

                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold"><strong>My Company Name:</strong></div>
                            <div class="col-md-8 text-secondary">{{ $notice->my_company_name }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold"><strong>Business Nature:</strong></div>
                            <div class="col-md-8 text-secondary">{{ $notice->my_company_business_nature }}</div>
                        </div>
                    </div>
                </div>

                <!-- Previous Replies -->
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <h4 class="card-title p-0 mb-3">Previous Replies</h4>
                        <hr class="mt-0">

                        @forelse($notice->replies as $reply)
                            <div class="p-3 mb-3 border rounded bg-light position-relative">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold text-primary">
                                        <i class="bi bi-person-fill"></i> {{ $reply->admin->name ?? 'Administrator' }}
                                    </span>
                                    <span class="text-muted small">
                                        <i class="bi bi-clock"></i> {{ $reply->created_at ? $reply->created_at->format('Y-m-d H:i') : 'N/A' }}
                                    </span>
                                </div>
                                <div class="text-secondary mb-2" style="white-space: pre-wrap;">{{ $reply->message }}</div>
                                <div class="d-flex align-items-center">
                                    <span class="small text-muted me-2">Status at reply:</span>
                                    @php
                                        $badgeClasses = [
                                            'Pending' => 'bg-warning text-dark',
                                            'Approved' => 'bg-success text-white',
                                            'Rejected' => 'bg-danger text-white',
                                            'In Progress' => 'bg-info text-dark',
                                            'Replied' => 'bg-primary text-white',
                                            'Closed' => 'bg-secondary text-white'
                                        ];
                                        $replyBadgeClass = $badgeClasses[$reply->status] ?? 'bg-light text-dark';
                                    @endphp
                                    <span class="badge {{ $replyBadgeClass }}">{{ $reply->status }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-chat-left-dots fs-3"></i>
                                <p class="mt-2 mb-0">No replies sent yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Send Reply Form -->
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <h4 class="card-title p-0 mb-3">Send Reply</h4>
                        <hr class="mt-0">

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('legal-notices.reply', $notice->id) }}" method="POST" id="replyForm">
                            @csrf
                            <div class="mb-3">
                                <label for="customerEmail" class="form-label fw-bold">Customer Email</label>
                                <input type="email" name="customer_email" id="customerEmail" class="form-control" placeholder="customer@example.com" value="{{ old('customer_email', $notice->customer->email ?? '') }}">
                                <div class="form-text text-muted">You can update the email address if you want to notify a different address.</div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" name="send_email" id="sendEmail" class="form-check-input" value="1" {{ old('send_email', '1') == '1' ? 'checked' : '' }}>
                                <label for="sendEmail" class="form-check-label fw-bold">Send email notification to customer</label>
                            </div>

                            <div class="mb-3">
                                <label for="replyMessage" class="form-label fw-bold">Message <span class="text-danger">*</span></label>
                                <textarea name="message" id="replyMessage" rows="5" class="form-control" placeholder="Type your reply message here..." required>{{ old('message') }}</textarea>
                                <div class="invalid-feedback">Please enter a reply message.</div>
                            </div>

                            <div class="row align-items-center">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="replyStatus" class="form-label fw-bold">Update Status <span class="text-danger">*</span></label>
                                    <select name="status" id="replyStatus" class="form-select" required>
                                        <option value="Pending" {{ old('status', $notice->status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="In Progress" {{ old('status', $notice->status) == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="Replied" {{ old('status', $notice->status) == 'Replied' ? 'selected' : '' }}>Replied</option>
                                        <option value="Closed" {{ old('status', $notice->status) == 'Closed' ? 'selected' : '' }}>Closed</option>
                                    </select>
                                </div>
                                <div class="col-md-6 text-end pt-4">
                                    <button type="submit" class="btn btn-dark w-100 py-2 fw-semibold">
                                        <i class="bi bi-send-fill me-1"></i> Send Reply
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Financial & Meta Info -->
            <div class="col-lg-4">
                <!-- Financial Card -->
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <h4 class="card-title p-0 mb-3">Financial Information</h4>
                        <hr class="mt-0">

                        <div class="mb-3">
                            <strong>Total Amount:</strong>
                            <h3 class="text-primary mt-1">₹{{ number_format($notice->total_amount, 2) }}</h3>
                        </div>

                        <div class="mb-3">
                            <strong>Amount Due:</strong>
                            <h3 class="text-danger mt-1">₹{{ number_format($notice->amount_due, 2) }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Auditing / Audit Log Info Card -->
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <h4 class="card-title p-0 mb-3">Auditing Details</h4>
                        <hr class="mt-0">

                        <div class="mb-2">
                            <strong>Created By Customer:</strong>
                            <p class="text-secondary">{{ $notice->customer ? $notice->customer->name : 'N/A' }} (ID: {{ $notice->user_id }})</p>
                        </div>

                        <div class="mb-2">
                            <strong>Created At:</strong>
                            <p class="text-secondary">{{ $notice->created_at ? $notice->created_at->format('Y-m-d H:i:s') : '-' }}</p>
                        </div>

                        <div class="mb-2">
                            <strong>Last Updated At:</strong>
                            <p class="text-secondary">{{ $notice->updated_at ? $notice->updated_at->format('Y-m-d H:i:s') : '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
