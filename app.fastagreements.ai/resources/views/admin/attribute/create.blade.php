@extends('admin.layout.admin')

@section('content')

<main id="main" class="main">
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Create New attribute</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('deals.index') }}">attribute List</a></li>
                    <li class="breadcrumb-item active">Create attribute</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card p-2 pt-4">
                    <div class="card-body">
                        <form id="dealForm" action="{{ route('attribute.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
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

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="attribute_name">Attribute Name</label>
                                    <input type="text" class="form-control" id="attribute_name" name="attribute_name" value="{{ old('attribute_name') }}">
                                    @error('attribute_name')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="attribute_value">Attribute Value</label>
                                    <input type="text" class="form-control" id="attribute_value" name="attribute_value" value="{{ old('attribute_value') }}">
                                    @error('attribute_value')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                            <label for="input_type">Input Type</label>
                                <select class="form-control" name="input_type">
                                    <option value="">Select Input Type</option>
                                    <option value="1">Text</option>
                                    <option value="2">Dropdown</option>
                                    <option value="3">Switch</option>
                                    <option value="4">Date</option>
                                    <option value="5">Heading</option>
                                    <option value="6">Text</option>
                                    <option value="7">Chart</option>

                                </select>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="default_value">Default Value</label>
                                    <input type="text" class="form-control" id="default_value" name="default_value" value="{{ old('default_value') }}">
                                    @error('default_value')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                            <label for="is_required">Input Type</label>
                                <select class="form-control" name="is_required">
                                    <option value="">Select require</option>
                                    <option value="1">require</option>
                                    <option value="0">not require</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
</main>

<script>
    ClassicEditor.create(document.querySelector('#editor'), {
            ckfinder: {
                uploadUrl: "{{ route('template.store', ['_token' => csrf_token()]) }}"
            },
            toolbar: [
                'heading', '|', 'bold', 'italic', 'underline', 'fontFamily', 'fontSize', '|',
                'alignment', '|', 'bulletedList', 'numberedList', '|', 'blockQuote', 'undo', 'redo'
            ],
            fontFamily: {
                options: [
                    'default',
                    'Arial, Helvetica, sans-serif',
                    'Courier New, Courier, monospace',
                    'Georgia, serif',
                    'LMG-Arun', // Your custom font
                    'Times New Roman, Times, serif',
                    'Verdana, Geneva, sans-serif'
                ],
                supportAllValues: true // Allow entering custom fonts manually
            },
            fontFamily_default: 'LMG-Arun', // Set default font to your custom font
            fontSize: {
                options: [10, 12, 14, 16, 18, 20, 24, 28, 32, 36],
                supportAllValues: true
            }
        })
        .then(editor => {
            console.log(editor);
        })
        .catch(error => {
            console.error(error);
        });
</script>

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
        display: none;
        /* Hide the original select */
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
    document.addEventListener('DOMContentLoaded', function() {
        function addDropdownSearch() {
            const dropdownWrappers = document.querySelectorAll('.custom-dropdown');

            dropdownWrappers.forEach(wrapper => {
                const searchInput = wrapper.querySelector('.dropdown-search');
                const optionsList = wrapper.querySelector('.dropdown-options');
                const selectInput = wrapper.querySelector('.custom-dropdown-select');
                const triggerInput = wrapper.querySelector('.custom-dropdown-trigger input');

                // Display dropdown on trigger input click
                triggerInput.addEventListener('click', function() {
                    wrapper.querySelector('.custom-dropdown-menu').style.display = 'block';
                });

                // Filter options based on search input
                searchInput.addEventListener('input', function() {
                    const filter = this.value.toLowerCase();
                    const options = optionsList.querySelectorAll('li');

                    options.forEach(option => {
                        option.style.display = option.textContent.toLowerCase().includes(filter) ? '' : 'none';
                    });
                });

                // Handle option selection
                optionsList.addEventListener('click', function(event) {
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
                document.addEventListener('click', function(event) {
                    if (!wrapper.contains(event.target)) {
                        wrapper.querySelector('.custom-dropdown-menu').style.display = 'none';
                    }
                });
            });
        }

        addDropdownSearch();

        dealForm.addEventListener('submit', function(event) {
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