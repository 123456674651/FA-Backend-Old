@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Create New Deal</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('deals.index') }}">Deal List</a></li>
                    <li class="breadcrumb-item active">Create Deal</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card p-2 pt-4">
                    <div class="card-body">
                        <form id="dealForm" action="{{ route('deals.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="person_1">Person 1</label>
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-trigger">
                                        <input type="text" class="form-control dropdown-search" placeholder="Search Person 1" />
                                        <input type="hidden" name="person_1" id="person_1" class="custom-dropdown-select" />
                                    </div>
                                    <div class="custom-dropdown-menu">
                                        <ul class="dropdown-options">
                                            <li data-value="">Select Person 1</li>
                                            @foreach ($customers as $customer)
                                                <li data-value="{{ $customer->id }}">{{ $customer->name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @error('person_1')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="person_2">Person 2</label>
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-trigger">
                                        <input type="text" class="form-control dropdown-search" placeholder="Search Person 2" />
                                        <input type="hidden" name="person_2" id="person_2" class="custom-dropdown-select" />
                                    </div>
                                    <div class="custom-dropdown-menu">
                                        <ul class="dropdown-options">
                                            <li data-value="">Select Person 2</li>
                                            @foreach ($customers as $customer)
                                                <li data-value="{{ $customer->id }}">{{ $customer->name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @error('person_2')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Amount -->
                            {{-- <div class="form-group mb-3">
                                <label for="amount">Amount</label>
                                <input type="number" name="amount" id="amount" class="form-control" step="0.01" value="{{ old('amount') }}" required />
                                @error('amount')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div> --}}
                            <div class="form-group mb-3">
                                <label for="amount">Amount</label>
                                <input type="number" name="amount" id="amount" class="form-control" step="0.01" value="{{ old('amount') }}" required />
                                @error('amount')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="purpose">Purpose</label>
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-trigger">
                                        <input type="text" class="form-control dropdown-search" placeholder="Search Purpose" />
                                        <input type="hidden" name="purpose" id="purpose" class="custom-dropdown-select" />
                                    </div>
                                    <div class="custom-dropdown-menu">
                                        <ul class="dropdown-options">
                                            <li data-value="">Select Purpose</li>
                                            @foreach ($purposes as $purpose)
                                                <li data-value="{{ $purpose->id }}">{{ $purpose->purpose_name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @error('purpose')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="category_id">Category</label>
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-trigger">
                                        <input type="text" class="form-control dropdown-search" placeholder="Search Category" />
                                        <input type="hidden" name="category_id" id="category_id" class="custom-dropdown-select" />
                                    </div>
                                    <div class="custom-dropdown-menu">
                                        <ul class="dropdown-options">
                                            <li data-value="">Select Category</li>
                                            @foreach ($dealCategories as $category)
                                                <li data-value="{{ $category->id }}">{{ $category->category_name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @error('category_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="language_id">Language</label>
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-trigger">
                                        <input type="text" class="form-control dropdown-search" placeholder="Search Language" />
                                        <input type="hidden" name="language_id" id="language_id" class="custom-dropdown-select" />
                                    </div>
                                    <div class="custom-dropdown-menu">
                                        <ul class="dropdown-options">
                                            <li data-value="">Select Language</li>
                                            @foreach ($languages as $language)
                                                <li data-value="{{ $language->id }}">{{ $language->language_name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @error('language_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Contract Template -->
                            <div class="form-group mb-3">
                                <label for="contract_template_file_name_id">Contract Template</label>
                                <input type="number" name="contract_template_file_name_id" id="contract_template_file_name_id" class="form-control" value="{{ old('contract_template_file_name_id') }}" required />
                                @error('contract_template_file_name_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- From Date -->
                            <div class="form-group mb-3">
                                <label for="from_date">From Date</label>
                                <input type="date" name="from_date" id="from_date" class="form-control" value="{{ old('from_date') }}" required />
                                @error('from_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- To Date -->
                            <div class="form-group mb-3">
                                <label for="to_date">To Date</label>
                                <input type="date" name="to_date" id="to_date" class="form-control" value="{{ old('to_date') }}" required />
                                @error('to_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Contract Date -->
                            <div class="form-group mb-3">
                                <label for="contract_date">Contract Date</label>
                                <input type="date" name="contract_date" id="contract_date" class="form-control" value="{{ old('contract_date') }}" required />
                                @error('contract_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="form-group mb-3">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Draft" {{ old('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="Completed" {{ old('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                                @error('status')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Latitude -->
                            <div class="form-group mb-3">
                                <label for="lat">Latitude</label>
                                <input type="text" name="lat" id="lat" class="form-control" value="{{ old('lat') }}" />
                                @error('lat')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Longitude -->
                            <div class="form-group mb-3">
                                <label for="lon">Longitude</label>
                                <input type="text" name="lon" id="lon" class="form-control" value="{{ old('lon') }}" />
                                @error('lon')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Allow Live Location -->
                            <div class="form-group mb-3">
                                <label>Allow Live Location</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="allow_live_location" id="allow_live_location_yes" value="yes" {{ old('allow_live_location') == 'yes' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_live_location_yes">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="allow_live_location" id="allow_live_location_no" value="no" {{ old('allow_live_location') == 'no' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_live_location_no">No</label>
                                </div>
                                @error('allow_live_location')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Interest Rate -->
                            {{-- <div class="form-group mb-3">
                                <label for="interest_rate">Interest Rate</label>
                                <input type="number" name="interest_rate" id="interest_rate" class="form-control" step="0.01" value="{{ old('interest_rate') }}" />
                                @error('interest_rate')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div> --}}
                            <div class="form-group mb-3">
                                <label for="interest_rate">Interest Rate (%)</label>
                                <input type="number" name="interest_rate" id="interest_rate" class="form-control" step="0.01" value="{{ old('interest_rate') }}" />
                                @error('interest_rate')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            

                            <!-- Payable Amount -->
                            {{-- <div class="form-group mb-3">
                                <label for="payable_amount">Payable Amount</label>
                                <input type="number" name="payable_amount" id="payable_amount" class="form-control" step="0.01" value="{{ old('payable_amount') }}" />
                                @error('payable_amount')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div> --}}

                            <div class="form-group mb-3">
                                <label for="payable_amount">Payable Amount</label>
                                <input type="number" name="payable_amount" id="payable_amount" class="form-control" step="0.01" value="{{ old('payable_amount') }}" readonly />
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
                                        <option value="{{ $scheme->id }}" {{ old('interest_term_in_month') == $scheme->id ? 'selected' : '' }}>
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
                                        <option value="{{ $numericValue }}" {{ old('interest_term_in_month') == $numericValue ? 'selected' : '' }}>
                                            {{ $scheme->emi_pay_method }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('interest_term_in_month')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            

                            <!-- Sakshi -->
                                <div class="form-group mb-3">
                                    <label for="sakshi">Sakshi</label>
                                    <div class="custom-dropdown">
                                        <div class="custom-dropdown-trigger">
                                            <input type="text" class="form-control dropdown-search" placeholder="Search Sakshi" />
                                            <input type="hidden" name="sakshi" id="sakshi" class="custom-dropdown-select" />
                                        </div>
                                        <div class="custom-dropdown-menu">
                                            <ul class="dropdown-options">
                                                <li data-value="">Select Sakshi</li>
                                                @foreach ($customers as $customer)
                                                    <li data-value="{{ $customer->id }}">{{ $customer->name }}</li>
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
                                <input type="text" name="mediator_id" id="mediator_id" class="form-control" readonly value="{{ old('mediator_id') }}" />
                                @error('mediator_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Currency -->
                            <div class="form-group mb-3">
                                <label for="currency">Currency</label>
                                <input type="text" name="currency" id="currency" class="form-control" value="{{ old('currency') }}" required />
                                @error('currency')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="form-group mb-3">
                                <label for="notes">Notes</label>
                                <textarea name="notes" id="notes" class="form-control">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- File Inputs -->
                            <div class="form-group mb-3">
                                <label for="video_recording">Video Recording</label>
                                <input type="file" name="video_recording" id="video_recording" class="form-control" />
                                @error('video_recording')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="audio_recording">Audio Recording</label>
                                <input type="file" name="audio_recording" id="audio_recording" class="form-control" />
                                @error('audio_recording')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="image">Image</label>
                                <input type="file" name="image" id="image" class="form-control" />
                                @error('image')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">Submit</button>
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
    display: inline-block;
    width: 100%;
}

.custom-dropdown-trigger {
    position: relative;
}

.custom-dropdown-select {
    display: none; /* Hide the original select */
}

.custom-dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    z-index: 1000;
    display: none;
    max-height: 200px;
    overflow-y: auto;
}

.dropdown-search {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
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
    background: #f0f0f0;
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
                // Example: Payable Amount = Amount + (Amount * (Interest Rate / 100))
                const totalPayable = amount + (amount * (interestRate / 100));
                payableAmountInput.value = totalPayable.toFixed(2); // Display the result with 2 decimal places
            } else {
                payableAmountInput.value = ''; // Reset if inputs are invalid
            }
        }

        amountInput.addEventListener('input', calculatePayableAmount);
        interestRateInput.addEventListener('input', calculatePayableAmount);
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function addDropdownSearch() {
        const dropdownWrappers = document.querySelectorAll('.custom-dropdown');

        dropdownWrappers.forEach(wrapper => {
            const searchInput = wrapper.querySelector('.dropdown-search');
            const optionsList = wrapper.querySelector('.dropdown-options');
            const selectInput = wrapper.querySelector('.custom-dropdown-select');
            const triggerInput = wrapper.querySelector('.custom-dropdown-trigger input');
            
            // Display dropdown on trigger input click
            triggerInput.addEventListener('click', function () {
                wrapper.querySelector('.custom-dropdown-menu').style.display = 'block';
            });

            // Filter options based on search input
            searchInput.addEventListener('input', function () {
                const filter = this.value.toLowerCase();
                const options = optionsList.querySelectorAll('li');

                options.forEach(option => {
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
                    wrapper.querySelector('.custom-dropdown-menu').style.display = 'none';

                    // Fetch and update mediator_id based on selected sakshi
                    if (value) {
                        fetch(`/mediator/${value}`)
                            .then(response => response.json())
                            .then(data => {
                                const mediatorInput = document.getElementById('mediator_id');
                                if (data.mediator_id !== null) {
                                    mediatorInput.value = data.mediator_id;
                                } else {
                                    mediatorInput.value = '';
                                }
                            })
                            .catch(error => console.error('Error fetching mediator:', error));
                    }
                }
            });

            // Hide dropdown when clicking outside
            document.addEventListener('click', function (event) {
                if (!wrapper.contains(event.target)) {
                    wrapper.querySelector('.custom-dropdown-menu').style.display = 'none';
                }
            });
        });
    }

    addDropdownSearch();

    dealForm.addEventListener('submit', function (event) {
        clearErrors();

        const requiredFields = [
            'person_1', 'person_2', 'amount', 'purpose', 'category_id', 'language_id', 'contract_template_file_name_id',
            'from_date', 'to_date', 'contract_date', 'status', 'currency',
        ];

        let hasErrors = false;

        requiredFields.forEach(field => {
            const input = document.getElementById(field);
            if (input && !input.value.trim()) {
                showError(field, 'This field is required.');
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
});
</script>
@endsection
