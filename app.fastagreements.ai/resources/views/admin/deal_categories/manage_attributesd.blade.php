@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">

    {{-- Page Title --}}
    <div class="row">
        <div class="pagetitle col-lg-6 pt-2">
            <h1>Manage Sub Categories</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('deal_categories.index') }}">Deal Categories</a></li>
                    <li class="breadcrumb-item active">Manage Sub Categories</li>
                </ol>
            </nav>
        </div>
        <div class="pagetitle col-lg-6 text-end pt-2">
            <a href="{{ route('deal_categories.index') }}">
                <button type="button" class="btn btn-secondary text-white">
                    <i class="bi bi-arrow-left-square"></i> Back
                </button>
            </a>
            <button type="button" class="btn button-color text-white ms-2" data-bs-toggle="modal" data-bs-target="#createSubCategoryModal">
                <i class="bi bi-plus-square"></i> Add Sub Category
            </button>
            <button type="button" class="btn btn-primary text-white ms-2" data-bs-toggle="modal" data-bs-target="#uploadDocxModal">
                <i class="bi bi-file-earmark-word"></i> Upload Document
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Documents Section --}}
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card p-2 pt-4">
                    <div class="card-body">
                        <h5 class="card-title">Uploaded Documents</h5>
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle" style="width:5%">Sr#</th>
                                    <th class="text-center align-middle">Sub Category</th>
                                    <th class="text-center align-middle">Language</th>
                                    <th class="text-center align-middle">File Path</th>
                                    <th class="text-center align-middle">Uploaded At</th>
                                    <th class="text-center align-middle" style="width:15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $docIndex = 1; @endphp
                                @forelse($category as $sub)
                                    @if(isset($documents[$sub->id]))
                                        @foreach($documents[$sub->id] as $doc)
                                        <tr>
                                            <td class="text-center align-middle">{{ $docIndex++ }}</td>
                                            <td class="align-middle">{{ $sub->category_name }}</td>
                                            <td class="text-center align-middle">
                                                {{ $doc->language->language_name ?? '—' }}
                                            </td>
                                            <td class="align-middle">
                                                <small class="text-muted">{{ $doc->file_path }}</small>
                                            </td>
                                            <td class="text-center align-middle">
                                                {{ $doc->created_at }}
                                            </td>
                                            <td class="text-center align-middle">
                                                {{-- Attributes --}}
                                                <button type="button" class="btn btn-warning btn-sm text-white attr-btn"
                                                    data-id="{{ $sub->id }}"
                                                    data-name="{{ $sub->category_name }}"
                                                    title="Manage Attributes">
                                                    <i class="bi bi-sliders"></i>
                                                </button>
                                                {{-- Preview --}}
                                                <button type="button" class="btn btn-info btn-sm text-white"
                                                    onclick="previewDoc({{ $doc->id }}, '{{ $sub->category_name }} - {{ $doc->language->language_name ?? '' }}')"
                                                    title="Preview">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                {{-- Download --}}
                                                <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-success btn-sm" title="Download">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                {{-- Delete --}}
                                                <a data-bs-toggle="modal" href="#deleteDocModal_{{ $doc->id }}" class="btn btn-danger btn-sm" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </a>

                                                {{-- Delete Modal --}}
                                                <div id="deleteDocModal_{{ $doc->id }}" class="modal fade" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Delete document for <strong>{{ $sub->category_name }}</strong>
                                                                ({{ $doc->language->language_name ?? '—' }})?
                                                                This will also remove the file from storage.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" style="display:inline;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif
                                @empty
                                @endforelse
                                @if($docIndex === 1)
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No documents uploaded yet.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Attributes Section (shown when Attributes button clicked) --}}
    <section class="section d-none" id="attributeSection">
        <div class="row">
            <div class="col-lg-12">
                <div class="card p-2 pt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">
                                Attributes for: <span id="attrCategoryName" class="text-primary"></span>
                            </h5>
                            <div class="d-flex gap-2">
                                {{-- Export --}}
                                <a id="exportBtn" href="#" class="btn btn-success btn-sm text-white">
                                    <i class="bi bi-download"></i> Export
                                </a>
                                {{-- Import --}}
                                <button type="button" class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="bi bi-upload"></i> Import
                                </button>
                                {{-- Add Attribute --}}
                                <a id="addAttributeBtn" href="#" class="btn button-color btn-sm text-white">
                                    <i class="bi bi-plus-square"></i> Add Attribute
                                </a>
                            </div>
                        </div>

                        <div id="attributeTableWrapper">
                            <table class="table table-striped table-bordered table-hover" id="attributeTable">
                                <thead>
                                    <tr>
                                        <th class="text-center align-middle" style="width:40px;"></th>
                                        <th class="text-center align-middle" style="width:5%">Sr#</th>
                                        <th class="text-center align-middle">Attribute Name</th>
                                        <th class="text-center align-middle">Input Type</th>
                                        <th class="text-center align-middle">Required</th>
                                        <th class="text-center align-middle">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="attributeTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Click "Attributes" on a sub category to load.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

{{-- Document Preview Modal --}}
<div class="modal fade" id="docPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark-word text-primary me-1"></i> <span id="previewModalTitle">Document Preview</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="min-height:400px;">
                <div id="previewLoading" class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Loading preview...</p>
                </div>
                <div id="previewContent" class="d-none p-3" style="font-family: 'Times New Roman', serif; font-size:14px; line-height:1.8; border:1px solid #eee; border-radius:4px; background:#fff;"></div>
                <div id="previewError" class="d-none alert alert-warning">Unable to render preview. Please download the file to view it.</div>
            </div>
            <div class="modal-footer">
                <a id="previewDownloadBtn" href="#" class="btn btn-success btn-sm"><i class="bi bi-download"></i> Download</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Create Sub Category Modal --}}
<div class="modal fade" id="createSubCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('deal_categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="parent_id" value="{{ request()->route('id') }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Sub Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" class="form-control" required placeholder="Enter sub category name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deal Price</label>
                        <input type="number" step="0.01" name="deal_price" class="form-control" placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Is On Interest</label>
                        <select name="is_on_interest" class="form-select">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn button-color text-white">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Upload DOCX Modal --}}
<div class="modal fade" id="uploadDocxModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="uploadDocxForm" action="{{ route('documents.upload_docx') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="category_id" id="docxCategoryId" value="{{ $id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-word text-primary"></i> Upload DOCX Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Sub Category <span class="text-danger">*</span></label>
                        <select name="sub_category_id" id="docxSubCategoryId" class="form-select" required>
                            <option value="">Select Sub Category</option>
                            @foreach($category as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Language <span class="text-danger">*</span></label>
                        <select name="language_id" id="docxLanguageSelect" class="form-select" required>
                            <option value="">Select Language</option>
                            @foreach(\App\Models\Language::where('is_active', 1)->orderBy('language_name')->get() as $lang)
                                <option value="{{ $lang->id }}">{{ $lang->language_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">DOCX File <span class="text-danger">*</span></label>
                        <input type="file" name="document" id="docxFileInput" class="form-control" accept=".doc,.docx" required>
                        <small class="text-muted">Accepted: .doc, .docx — If a file already exists it will be replaced.</small>
                    </div>
                    <div id="docxExtractedVars" class="d-none">
                        <div class="alert alert-success py-2 mb-0">
                            <small><strong>Variables found in file:</strong></small>
                            <div id="docxVarList" class="mt-1 d-flex flex-wrap gap-1"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary text-white" id="docxUploadBtn">
                        <i class="bi bi-upload"></i> Upload & Extract
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="importForm" action="{{ route('attribute.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="category_id" id="importCategoryId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Attributes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Upload CSV / Excel File <span class="text-danger">*</span></label>
                        <input type="file" name="import_file" class="form-control" accept=".csv,.xlsx,.xls" required>
                        <small class="text-muted">Accepted formats: .csv, .xlsx, .xls</small>
                    </div>
                    <div class="alert alert-info py-2 mb-0">
                        <small>
                            Required columns: <code>attribute_name, attribute_values, input_type, default_value, is_required</code>
                        </small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <a href="{{ asset('demo/attributes_import_demo.csv') }}" download class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-file-earmark-arrow-down"></i> Download Demo File
                    </a>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info text-white">Import</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<style>
    .drag-handle { cursor: grab; color: #aaa; }
    .drag-handle:active { cursor: grabbing; }
    .sortable-ghost { opacity: 0.4; background: #e8f4ff; }
    .sortable-chosen { background: #f0f8ff; }
</style>
<script>
    let currentCategoryId = null;
    let currentSubCategoryName = null;
    let sortableInstance = null;

    document.querySelectorAll('.attr-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            currentCategoryId = this.dataset.id;
            currentSubCategoryName = this.dataset.name;

            document.getElementById('attrCategoryName').textContent = currentSubCategoryName;
            document.getElementById('importCategoryId').value = currentCategoryId;
            document.getElementById('addAttributeBtn').href = '{{ route("attribute.create") }}?category_id=' + currentCategoryId;
            document.getElementById('exportBtn').href = '{{ url("attribute/export") }}/' + currentCategoryId;

            const section = document.getElementById('attributeSection');
            section.classList.remove('d-none');
            section.scrollIntoView({ behavior: 'smooth' });

            loadAttributes(currentCategoryId);
        });
    });

    function loadAttributes(categoryId) {
        const tbody = document.getElementById('attributeTableBody');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center"><div class="spinner-border spinner-border-sm text-primary"></div> Loading...</td></tr>';

        fetch('{{ url("attribute/list") }}?category_id=' + categoryId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(function(response) {
            const data = response.data;
            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No attributes found for this category.</td></tr>';
                return;
            }

            let html = '';
            data.forEach(function(attr, i) {
                const editUrl = '{{ url("attribute/edit") }}/' + attr.id;
                const deleteUrl = '{{ url("attribute/delete") }}/' + attr.id;
                const csrfToken = '{{ csrf_token() }}';

                html += `<tr data-id="${attr.id}">
                    <td class="text-center align-middle drag-handle" title="Drag to reorder">
                        <i class="bi bi-grip-vertical fs-5"></i>
                    </td>
                    <td class="text-center align-middle row-num">${i + 1}</td>
                    <td class="align-middle">${attr.attribute_name}</td>
                    <td class="text-center align-middle">${attr.input_type_name ?? attr.input_type}</td>
                    <td class="text-center align-middle">${attr.is_required_name ?? (attr.is_required ? 'Required' : 'Not Required')}</td>
                    <td class="text-center align-middle">
                        <a href="${editUrl}" class="btn btn-primary btn-sm" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <button type="button" class="btn btn-danger btn-sm" title="Delete"
                            onclick="confirmDelete('${deleteUrl}', '${csrfToken}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;

            if (sortableInstance) sortableInstance.destroy();
            sortableInstance = Sortable.create(tbody, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function() {
                    tbody.querySelectorAll('tr').forEach(function(tr, idx) {
                        tr.querySelector('.row-num').textContent = idx + 1;
                    });
                    saveOrder();
                }
            });
        })
        .catch(function() {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Failed to load attributes.</td></tr>';
        });
    }

    function saveOrder() {
        const tbody = document.getElementById('attributeTableBody');
        const order = [];
        tbody.querySelectorAll('tr[data-id]').forEach(function(tr, idx) {
            order.push({ id: tr.dataset.id, sort_order: idx + 1 });
        });
        fetch('{{ route("post-reorder") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ sort_order: order })
        })
        .then(res => res.json())
        .then(function(res) { showToast(res.message ?? 'Order saved!', 'success'); })
        .catch(function() { showToast('Failed to save order.', 'danger'); });
    }

    function showToast(message, type) {
        let toast = document.getElementById('sortToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'sortToast';
            toast.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;min-width:200px;';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<div class="alert alert-${type} shadow mb-0 py-2">${message}</div>`;
        setTimeout(() => { toast.innerHTML = ''; }, 2500);
    }

    function confirmDelete(url, token) {
        if (!confirm('Are you sure you want to delete this attribute?')) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.innerHTML = `<input type="hidden" name="_token" value="${token}">
                          <input type="hidden" name="_method" value="DELETE">`;
        document.body.appendChild(form);
        form.submit();
    }

    // DOCX upload via AJAX
    document.getElementById('uploadDocxForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('docxUploadBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Uploading...';

        fetch('{{ route("documents.upload_docx") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: new FormData(this)
        })
        .then(res => res.json())
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-upload"></i> Upload & Extract';
            if (res.success) {
                const varBox = document.getElementById('docxExtractedVars');
                const varList = document.getElementById('docxVarList');
                if (res.variables && res.variables.length > 0) {
                    varList.innerHTML = res.variables.map(v => `<span class="badge bg-primary">${v}</span>`).join('');
                    varBox.classList.remove('d-none');
                } else {
                    varBox.classList.add('d-none');
                }
                showToast(res.message ?? 'Uploaded!', 'success');
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                showToast(res.message ?? 'Upload failed.', 'danger');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-upload"></i> Upload & Extract';
            showToast('Upload failed. Please try again.', 'danger');
        });
    });
    // Document preview
    function previewDoc(docId, title) {
        document.getElementById('previewModalTitle').textContent = title;
        document.getElementById('previewLoading').classList.remove('d-none');
        document.getElementById('previewContent').classList.add('d-none');
        document.getElementById('previewError').classList.add('d-none');
        document.getElementById('previewDownloadBtn').href = '{{ url("documents/download") }}/' + docId;

        const modal = new bootstrap.Modal(document.getElementById('docPreviewModal'));
        modal.show();

        fetch('{{ url("documents/preview") }}/' + docId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(function(res) {
            document.getElementById('previewLoading').classList.add('d-none');
            if (res.success && res.html) {
                document.getElementById('previewContent').innerHTML = res.html;
                document.getElementById('previewContent').classList.remove('d-none');
            } else {
                document.getElementById('previewError').classList.remove('d-none');
            }
        })
        .catch(function() {
            document.getElementById('previewLoading').classList.add('d-none');
            document.getElementById('previewError').classList.remove('d-none');
        });
    }
</script>
@endsection
