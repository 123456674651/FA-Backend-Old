@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Edit Deal</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('deals.index') }}">Deal List</a></li>
                    <li class="breadcrumb-item active">Edit Deal</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card p-2 pt-4">
                    <div class="card-body">
                        <form id="dealForm" action="{{ route('deals.update', $deal->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Person 1 Dropdown -->
                            <div class="form-group mb-3">
                                <label for="person_1">Person 1</label>
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-trigger">
                                        <input type="text" class="form-control dropdown-search" placeholder="Search Person 1" />
                                        <input type="hidden" name="person_1" id="person_1" class="custom-dropdown-select" value="{{ old('person_1', $deal->person_1) }}" />
                                    </div>
                                    <div class="custom-dropdown-menu">
                                        <ul class="dropdown-options">
                                            <li data-value="">Select Person 1</li>
                                            @foreach ($customers as $customer)
                                                <li data-value="{{ $customer->id }}" @if ($customer->id == $deal->person_1) class="selected" @endif>{{ $customer->name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @error('person_1')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Person 2 Dropdown -->
                            <div class="form-group mb-3">
                                <label for="person_2">Person 2</label>
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-trigger">
                                        <input type="text" class="form-control dropdown-search" placeholder="Search Person 2" />
                                        <input type="hidden" name="person_2" id="person_2" class="custom-dropdown-select" value="{{ old('person_2', $deal->person_2) }}" />
                                    </div>
                                    <div class="custom-dropdown-menu">
                                        <ul class="dropdown-options">
                                            <li data-value="">Select Person 2</li>
                                            @foreach ($customers as $customer)
                                                <li data-value="{{ $customer->id }}" @if ($customer->id == $deal->person_2) class="selected" @endif>{{ $customer->name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @error('person_2')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Amount Field -->
                            {{-- <div class="form-group mb-3">
                                <label for="amount">Amount</label>
                                <input type="text" id="amount" name="amount" class="form-control" value="{{ old('amount', $deal->amount) }}" />
                                @error('amount')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div> --}}

                            <div class="form-group mb-3"> 
                                <label for="amount">Amount</label>
                                <input type="number" id="amount" name="amount" class="form-control" step="0.01" value="{{ old('amount', $deal->amount) }}" required />
                                @error('amount')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Purpose Dropdown -->
                            <div class="form-group mb-3">
                                <label for="purpose">Purpose</label>
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-trigger">
                                        <input type="text" class="form-control dropdown-search" placeholder="Search Purpose" />
                                        <input type="hidden" name="purpose" id="purpose" class="custom-dropdown-select" value="{{ old('purpose', $deal->purpose) }}" />
                                    </div>
                                    <div class="custom-dropdown-menu">
                                        <ul class="dropdown-options">
                                            <li data-value="">Select Purpose</li>
                                            @foreach ($purposes as $purpose)
                                                <li data-value="{{ $purpose->id }}" @if ($purpose->id == $deal->purpose) class="selected" @endif>{{ $purpose->purpose_name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @error('purpose')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Category Dropdown -->
                            <div class="form-group mb-3">
                                <label for="category_id">Category</label>
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-trigger">
                                        <input type="text" class="form-control dropdown-search" placeholder="Search Category" />
                                        <input type="hidden" name="category_id" id="category_id" class="custom-dropdown-select" value="{{ old('category_id', $deal->category_id) }}" />
                                    </div>
                                    <div class="custom-dropdown-menu">
                                        <ul class="dropdown-options">
                                            <li data-value="">Select Category</li>
                                            @foreach ($dealCategories as $category)
                                                <li data-value="{{ $category->id }}" @if ($category->id == $deal->category_id) class="selected" @endif>{{ $category->category_name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @error('category_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Language Dropdown -->
                            <div class="form-group mb-3">
                                <label for="language_id">Language</label>
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-trigger">
                                        <input type="text" class="form-control dropdown-search" placeholder="Search Language" />
                                        <input type="hidden" name="language_id" id="language_id" class="custom-dropdown-select" value="{{ old('language_id', $deal->language_id) }}" />
                                    </div>
                                    <div class="custom-dropdown-menu">
                                        <ul class="dropdown-options">
                                            <li data-value="">Select Language</li>
                                            @foreach ($languages as $language)
                                                <li data-value="{{ $language->id }}" @if ($language->id == $deal->language_id) class="selected" @endif>{{ $language->language_name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @error('language_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Contract Template File Name Dropdown -->
                            <div class="form-group mb-3">
                                <label for="contract_template_file_name_id">Contract Template</label>
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-trigger">
                                        <input type="text" id="contract_template_file_name_id" name="contract_template_file_name_id" class="form-control" value="{{ old('contract_template_file_name_id', $deal->contract_template_file_name_id) }}" />
                                    </div>
                                </div>
                                @error('contract_template_file_name_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- From Date Field -->
                            <div class="form-group mb-3">
                                <label for="from_date">From Date</label>
                                <input type="date" id="from_date" name="from_date" class="form-control" value="{{ old('from_date', $deal->from_date) }}" />
                                @error('from_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- To Date Field -->
                            <div class="form-group mb-3">
                                <label for="to_date">To Date</label>
                                <input type="date" id="to_date" name="to_date" class="form-control" value="{{ old('to_date', $deal->to_date) }}" />
                                @error('to_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Contract Date Field -->
                            <div class="form-group mb-3">
                                <label for="contract_date">Contract Date</label>
                                <input type="date" id="contract_date" name="contract_date" class="form-control" value="{{ old('contract_date', $deal->contract_date) }}" />
                                @error('contract_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Status Dropdown -->
                            <div class="form-group mb-3">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="Active" {{ old('status', $deal->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Draft" {{ old('status', $deal->status) == 'Draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="Completed" {{ old('status', $deal->status) == 'Completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                                @error('status')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            

                            <!-- Currency Dropdown -->
                            <div class="form-group mb-3">
                                <label for="currency">Currency</label>
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-trigger">
                                        <input type="text" id="currency" name="currency" class="form-control" value="{{ old('currency', $deal->currency) }}" />
                                    </div>
                                </div>
                                @error('currency')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Latitude -->
                            <div class="form-group mb-3">
                                <label for="lat">Latitude</label>
                                <input type="text" name="lat" id="lat" class="form-control" value="{{ old('lat', $deal->lat) }}" />
                                @error('lat')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Longitude -->
                            <div class="form-group mb-3">
                                <label for="lon">Longitude</label>
                                <input type="text" name="lon" id="lon" class="form-control" value="{{ old('lon', $deal->lon) }}" />
                                @error('lon')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Allow Live Location -->
                            <div class="form-group mb-3">
                                <label>Allow Live Location</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="allow_live_location" id="allow_live_location_yes" value="yes" {{ old('allow_live_location', $deal->allow_live_location) == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_live_location_yes">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="allow_live_location" id="allow_live_location_no" value="no" {{ old('allow_live_location', $deal->allow_live_location) == '0' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_live_location_no">No</label>
                                </div>
                                @error('allow_live_location')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- <!-- Interest Rate -->
                            <div class="form-group mb-3">
                                <label for="interest_rate">Interest Rate</label>
                                <input type="number" name="interest_rate" id="interest_rate" class="form-control" step="0.01" value="{{ old('interest_rate', $deal->interest_rate) }}" />
                                @error('interest_rate')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Payable Amount -->
                            <div class="form-group mb-3">
                                <label for="payable_amount">Payable Amount</label>
                                <input type="number" name="payable_amount" id="payable_amount" class="form-control" step="0.01" value="{{ old('payable_amount', $deal->payable_amount) }}" />
                                @error('payable_amount')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div> --}}

                            <div class="form-group mb-3">
                                <label for="interest_rate">Interest Rate (%)</label>
                                <input type="number" name="interest_rate" id="interest_rate" class="form-control" step="0.01" value="{{ old('interest_rate', $deal->interest_rate) }}" />
                                @error('interest_rate')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <!-- Payable Amount -->
                            <div class="form-group mb-3">
                                <label for="payable_amount">Payable Amount</label>
                                <input type="number" name="payable_amount" id="payable_amount" class="form-control" step="0.01" value="{{ old('payable_amount', $deal->payable_amount) }}" readonly />
                                @error('payable_amount')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Interest Term (Months) -->
                            {{-- <div class="form-group mb-3">
                                <label for="interest_term_in_month">Interest Term (Months)</label>
                                <select name="interest_term_in_month" id="interest_term_in_month" class="form-control" required>
                                    <option value="">Select Interest Term</option>
                                    @foreach ($scemes as $scheme)
                                        <option value="{{ $scheme->id }}" {{ old('interest_term_in_month', $deal->interest_term_in_month) == $scheme->id ? 'selected' : '' }}>
                                            {{ $scheme->emi_pay_method }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('interest_term_in_month')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div> --}}
                            <div class="form-group mb-3">
                                <label for="interest_term_in_month">Interest Term (Months)</label>
                                <select name="interest_term_in_month" id="interest_term_in_month" class="form-control" required>
                                    <option value="">Select Interest Term</option>
                                    @foreach ($scemes as $scheme)
                                        @php
                                            // Extract numeric part from emi_pay_method
                                            $numericValue = preg_replace('/[^0-9]/', '', $scheme->emi_pay_method);
                                        @endphp
                                        <option value="{{ $numericValue }}" {{ old('interest_term_in_month', $deal->interest_term_in_month) == $numericValue ? 'selected' : '' }}>
                                            {{ $scheme->emi_pay_method }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('interest_term_in_month')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            

                            <!-- Sakshi Dropdown -->
                            <div class="form-group mb-3">
                                <label for="sakshi">Sakshi</label>
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-trigger">
                                        <input type="text" class="form-control dropdown-search" placeholder="Search Sakshi" readonly />
                                        <input type="hidden" name="sakshi" id="sakshi" class="custom-dropdown-select" value="{{ old('sakshi', $deal->sakshi) }}" />
                                    </div>
                                    <div class="custom-dropdown-menu">
                                        <ul class="dropdown-options">
                                            <li data-value="">Select Sakshi</li>
                                            @foreach ($customers as $customer)
                                                <li data-value="{{ $customer->id }}" @if ($customer->id == $deal->sakshi) class="selected" @endif>{{ $customer->name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @error('sakshi')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Mediator ID -->
                            <div class="form-group mb-3">
                                <label for="mediator_id">Mediator ID</label>
                                <input type="text" name="mediator_id" id="mediator_id" class="form-control" readonly value="{{ old('mediator_id', $deal->mediator_id) }}" />
                                @error('mediator_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="form-group mb-3">
                                <label for="notes">Notes</label>
                                <textarea name="notes" id="notes" class="form-control">{{ old('notes', $deal->notes) }}</textarea>
                                @error('notes')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="video_recording">Video Recording</label>
                                <input type="file" name="video_recording" id="video_recording" class="form-control" />
                            
                                @if(isset($existingFileVideo) && !empty($existingFileVideo))
                                    <p>Current file: <a href="{{ asset($existingFileVideo) }}" target="_blank">{{ basename($existingFileVideo) }}</a></p>
                                @endif
                            
                                @error('video_recording')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label for="audio_recording">Audio Recording</label>
                                <input type="file" name="audio_recording" id="audio_recording" class="form-control" />
                            
                                @if(isset($existingFileAudio) && !empty($existingFileAudio))
                                    <p>Current file: <a href="{{ asset($existingFileAudio) }}" target="_blank">{{ basename($existingFileAudio) }}</a></p>
                                @endif
                            
                                @error('audio_recording')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label for="image">Image</label>
                                <input type="file" name="image" id="image" class="form-control" />
                            
                                @if(isset($existingFileImage) && !empty($existingFileImage))
                                    <p>Current file: <a href="{{ asset($existingFileImage) }}" target="_blank">{{ basename($existingFileImage) }}</a></p>
                                @endif
                            
                                @error('image')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .custom-dropdown {
    position: relative;
}

.custom-dropdown-trigger {
    cursor: pointer;
}

.custom-dropdown-menu {
    display: none; /* Hidden by default */
    position: absolute;
    background: white;
    border: 1px solid #ccc;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
}

.custom-dropdown-menu.show {
    display: block;
}

.dropdown-options {
    list-style: none;
    padding: 0;
    margin: 0;
}

.dropdown-options li {
    padding: 8px;
    cursor: pointer;
}

.dropdown-options li:hover {
    background-color: #f0f0f0;
}

.dropdown-options li.selected {
    background-color: #e0e0e0;
}

</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.getElementById('amount');
        const interestRateInput = document.getElementById('interest_rate');
        const payableAmountInput = document.getElementById('payable_amount');

        function calculatePayableAmount() {
            const amount = parseFloat(amountInput.value) || 0;
            const interestRate = parseFloat(interestRateInput.value) || 0;

            // Calculate the total payable amount based on the entered amount and interest rate
            if (amount > 0 && interestRate >= 0) {
                const totalPayable = amount + (amount * (interestRate / 100));
                payableAmountInput.value = totalPayable.toFixed(2); // Display the result with 2 decimal places
            } else {
                payableAmountInput.value = ''; // Reset if inputs are invalid
            }
        }

        amountInput.addEventListener('input', calculatePayableAmount);
        interestRateInput.addEventListener('input', calculatePayableAmount);

        // Initial calculation on page load
        calculatePayableAmount();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initializeDropdown() {
            const dropdownWrappers = document.querySelectorAll('.custom-dropdown');

            dropdownWrappers.forEach(wrapper => {
                const searchInput = wrapper.querySelector('.dropdown-search');
                const optionsList = wrapper.querySelector('.dropdown-options');
                const selectInput = wrapper.querySelector('.custom-dropdown-select');
                const triggerInput = wrapper.querySelector('.custom-dropdown-trigger input');
                const dropdownMenu = wrapper.querySelector('.custom-dropdown-menu');

                if (!selectInput || !triggerInput || !dropdownMenu) {
                    console.warn('Missing elements in the custom dropdown');
                    return;
                }

                // Initialize search field with selected value
                const selectedValue = selectInput.value;
                const selectedOption = optionsList.querySelector(`li[data-value="${selectedValue}"]`);
                if (selectedOption) {
                    triggerInput.value = selectedOption.textContent;
                }

                // Show dropdown on click
                triggerInput.addEventListener('click', function () {
                    dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
                });

                // Filter options based on search input
                searchInput.addEventListener('input', function () {
                    const filter = this.value.toLowerCase();
                    optionsList.querySelectorAll('li').forEach(option => {
                        option.style.display = option.textContent.toLowerCase().includes(filter) ? '' : 'none';
                    });
                });

                // Handle option selection
                optionsList.addEventListener('click', function (event) {
                    if (event.target.tagName === 'LI') {
                        const value = event.target.getAttribute('data-value');
                        const text = event.target.textContent;

                        selectInput.value = value;
                        triggerInput.value = text;
                        dropdownMenu.style.display = 'none';

                        // Update the Mediator ID field if this is the Sakshi dropdown
                        if (selectInput.id === 'sakshi') {
                            const mediatorInput = document.getElementById('mediator_id');
                            mediatorInput.value = value; // Update mediator ID based on Sakshi selection
                        }
                    }
                });

                // Hide dropdown when clicking outside
                document.addEventListener('click', function (event) {
                    if (!wrapper.contains(event.target) && dropdownMenu.style.display === 'block') {
                        dropdownMenu.style.display = 'none';
                    }
                });
            });
        }

        // Initialize dropdowns and search functionality
        initializeDropdown();

        // Form validation on submit
        const dealForm = document.getElementById('dealForm');
        if (dealForm) {
            dealForm.addEventListener('submit', function (event) {
                clearErrors();

                const requiredFields = [
                    'person_1', 'person_2', 'amount', 'purpose', 'category_id', 'language_id', 'contract_template_file_name_id',
                    'from_date', 'to_date', 'contract_date', 'status', 'currency'
                ];

                let hasErrors = false;

                requiredFields.forEach(fieldId => {
                    const input = document.getElementById(fieldId);
                    if (input && !input.value.trim()) {
                        showError(fieldId, 'This field is required.');
                        hasErrors = true;
                    }
                });

                const amount = document.getElementById('amount').value;
                if (amount && isNaN(amount)) {
                    showError('amount', 'Amount must be a number.');
                    hasErrors = true;
                }

                const fromDate = document.getElementById('from_date').value;
                const toDate = document.getElementById('to_date').value;
                if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
                    showError('to_date', 'To Date must be after From Date.');
                    hasErrors = true;
                }

                if (hasErrors) {
                    event.preventDefault();
                }

                function showError(fieldId, message) {
                    const field = document.getElementById(fieldId);
                    const existingError = field.parentElement.querySelector('.text-danger');
                    if (!existingError) {
                        const error = document.createElement('span');
                        error.className = 'text-danger';
                        error.textContent = message;
                        field.parentElement.appendChild(error);
                    }
                }

                function clearErrors() {
                    document.querySelectorAll('.text-danger').forEach(el => el.remove());
                }
            });
        }
    });
</script>

@endsection
