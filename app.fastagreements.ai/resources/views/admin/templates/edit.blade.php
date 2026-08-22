@extends('admin.layout.admin')

@section('content')
<!-- Ckeditor5  -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
    .ck-editor__editable {
        min-height: 800px !important;
        max-height: 800px !important;
    }
</style>
<style>
    .copy-item {
        cursor: pointer;
        background-color: #007bff;
        color: white;
        border: solid 1px;
        font-size: x-small;
    }

    .copy-item:hover {
        background-color: #0056b3;
    }
</style>

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
            <div class="col-lg-9">
                <div class="card p-2 pt-4">
                    <div class="card-body">
                        <!-- Update form to use PUT method -->
                        <form id="dealForm" action="{{ route('template.update')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('post') <!-- This is important for PUT requests -->

                            <input type="hidden" name="id" value="{{ $template->id }}">
                            <div class="form-group mb-3">
                                <label for="category_id">Category</label>
                                <div class="custom-dropdown">
                                    <div class="custom-dropdown-trigger">
                                        <input type="text" class="form-control dropdown-search" placeholder="Search Category" value="{{ $template->dealCategory->category_name }}" />
                                        <input type="hidden" name="category_id" id="category_id" class="custom-dropdown-select" value="{{ $template->category_id }}" />
                                    </div>
                                    <div class="custom-dropdown-menu" style="display:none">
                                        <ul class="dropdown-options">
                                            <li data-value="">Select Category</li>
                                            @foreach ($dealCategories as $category)
                                            <li data-value="{{ $category->id }}" {{ $template->category_id == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</li>
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
                                        <input type="text" class="form-control dropdown-search" placeholder="Search Language" value="{{ $template->languages->language_name }}" />
                                        <input type="hidden" name="language_id" id="language_id" class="custom-dropdown-select" value="{{ $template->language_id }}" />
                                    </div>
                                    <div class="custom-dropdown-menu_2" style="display:none">
                                        <ul class="dropdown-options">
                                            <li data-value="">Select Language</li>
                                            @foreach ($languages as $language)
                                            <li data-value="{{ $language->id }}" {{ $template->language_id == $language->id ? 'selected' : '' }}>{{ $language->language_name }}</li>
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
                                <textarea class="form-control" id="editor" name="description" placeholder="Enter Description" rows="19">{{ old('agreement_text', $template->agreement_text) }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card p-2 pt-2">
                    <div class="card-body">
                        <ul class="list-group">
                            @foreach($attributes as $attribute)
                            <li class="list-group-item text-center copy-item" onclick="copyText(this)">
                                <span>{{ Str::words(strip_tags($attribute), 3, '...') }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="card p-2">

                    <div class="card-body">
                        <ul class="list-group">
                            @foreach($category_attributes as $attribute)
                            <li class="list-group-item text-center copy-item" onclick="copyText(this)">
                                <span>{{ $attribute }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            

        </div>
    </section>
</main>

<script>
    ClassicEditor
        .create(document.querySelector('#editor'), {
            ckfinder: {
                uploadUrl: "{{ route('template.update', ['_token' => csrf_token()]) }}"
            }
        })
        .then(editor => {
            console.log(editor);
        })
        .catch(error => {
            let editorContent = editor.getData();
            editorContent = editorContent.replace('@name', '{{ $template->language_name }}');
            editor.setData(editorContent);
            console.error(error);
        });
</script>

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

                // Display dropdown on trigger input click
                triggerInput.addEventListener('click', function() {
                    wrapper.querySelector('.custom-dropdown-menu_2').style.display = 'block';
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

                // Handle option selection
                optionsList.addEventListener('click', function(event) {
                    if (event.target.tagName === 'LI') {
                        const value = event.target.getAttribute('data-value');
                        const text = event.target.textContent;

                        selectInput.value = value;
                        triggerInput.value = text;
                        wrapper.querySelector('.custom-dropdown-menu_2').style.display = 'none';

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

                // Hide dropdown when clicking outside
                document.addEventListener('click', function(event) {
                    if (!wrapper.contains(event.target)) {
                        wrapper.querySelector('.custom-dropdown-menu_2').style.display = 'none';
                    }
                });
            });
        }

        addDropdownSearch();
    });
</script>

<script>
    function copyText(element) {
        // Get the text inside the button
        const text = element.innerText.trim();

        // Copy text to clipboard
        navigator.clipboard.writeText(text).then(() => {
            // Provide user feedback
            element.innerText = "Copied!";
            setTimeout(() => {
                element.innerText = text; // Restore original text
            }, 1000);
        }).catch(err => {
            console.error("Failed to copy: ", err);
        });
    }
</script>
@endsection