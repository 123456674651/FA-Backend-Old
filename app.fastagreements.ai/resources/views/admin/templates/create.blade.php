@extends('admin.layout.admin')

@section('content')
<!-- Ckeditor5  -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
    .ck-editor__editable {
        min-height: 450px !important;
        max-height: 450px !important;
    }
</style>
<style>
    @font-face {
        font-family: 'LMG-Arun';
        src: url('/path-to-fonts/LMG-Arun.ttf') format('truetype');
    }

    p {
        font-family: 'LMG-Arun';
        font-weight: 100;
        font-style: normal;
        font-size: 30px;
    }
</style>
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
            <div class="col-lg-9">
                <div class="card p-2 pt-4">
                    <div class="card-body">
                        <form id="dealForm" action="{{ route('template.store') }}" method="POST" enctype="multipart/form-data">
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

                            <div class="container py-3">
                                <label>Description:</label>
                                <textarea class="form-control" id="editor" name="description" placeholder="Enter Description" rows="19">{{ old('description') }}</textarea>
                            </div>


                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>

           <div class="col-lg-3">
                <div class="card p-2 pt-4">
                    <div class="card-body d-flex flex-wrap gap-2">
                        @foreach($attributes as $attribute)
                        <div class="btn btn-primary btn-sm w-100 text-center" onclick="copyText(this)">
                            <span>{{ Str::words(strip_tags($attribute), 3, '...') }}</span>

                        </div>
                        @endforeach
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