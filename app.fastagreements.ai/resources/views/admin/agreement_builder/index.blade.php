@extends('admin.layout.admin')

@section('content')
    <main id="main" class="main p-4">
        <div class="pagetitle mb-3 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-0">Agreement Document Designer</h4>
                <small class="text-muted">Design agreements in English, Gujarati, and Hindi with PDF Preview</small>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary px-4 fw-semibold" onclick="previewDocument()">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Preview PDF
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="clearEditor()">
                    <i class="bi bi-eraser me-1"></i> Clear
                </button>
            </div>
        </div>

        <style>
            /* Custom font declarations for editor */
            @font-face {
                font-family: 'LMG-Arun';
                src: url('/fonts/LMG-Arun.woff2') format('woff2'),
                    url('/fonts/LMG-Arun.woff') format('woff'),
                    url('/fonts/LMG-Arun.ttf') format('truetype');
                font-weight: normal;
                font-style: normal;
            }

            @font-face {
                font-family: 'Shruti';
                src: url('/fonts/shrutib.ttf') format('truetype');
                font-weight: normal;
                font-style: normal;
            }

            /* Hide CKEditor version warning notification banner completely */
            .cke_notifications_area,
            .cke_notification,
            .cke_notification_warning {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                height: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        </style>

        <!-- CKEditor Card -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <textarea id="documentEditor" name="document_content"></textarea>
            </div>
        </div>

        <!-- PDF Preview Modal -->
        <div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-labelledby="pdfPreviewModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 90vw; height: 92vh;">
                <div class="modal-content h-100 shadow-lg">
                    <div class="modal-header py-2 px-3 bg-light">
                        <h5 class="modal-title fs-6 fw-bold text-dark mb-0" id="pdfPreviewModalLabel">
                            <i class="bi bi-file-pdf-fill text-danger me-2"></i> PDF Preview
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openPdfInTab()">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Fullscreen
                            </button>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="modal-body p-0 position-relative" style="height: calc(92vh - 60px); background: #525659;">
                        <div id="pdfLoadingSpinner"
                            class="position-absolute top-50 start-50 translate-middle text-center text-white"
                            style="display: none; z-index: 10;">
                            <div class="spinner-border mb-2" role="status"></div>
                            <div>Generating PDF Preview...</div>
                        </div>
                        <iframe id="pdfPreviewFrame" src="about:blank"
                            style="width: 100%; height: 100%; border: none;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Official Stable CKEditor 4 Full CDN -->
    <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>

    <script>
        let editorInstance = null;
        let currentPdfUrl = null;
        let pdfModal = null;

        document.addEventListener('DOMContentLoaded', function () {
            pdfModal = new bootstrap.Modal(document.getElementById('pdfPreviewModal'));

            // Load custom local fonts & Google fonts inside CKEditor iframe
            const editorCustomCss = `
                    @font-face {
                        font-family: 'LMG-Arun';
                        src: url('/fonts/LMG-Arun.woff2') format('woff2'),
                             url('/fonts/LMG-Arun.woff') format('woff'),
                             url('/fonts/LMG-Arun.ttf') format('truetype');
                    }
                    @font-face {
                        font-family: 'Shruti';
                        src: url('/fonts/shrutib.ttf') format('truetype');
                    }
                @font-face {
                    font-family: 'Noto Sans Gujarati';
                    src: url('/fonts/NotoSansGujarati-Regular.ttf') format('truetype');
                }
                
                /* A4 Page Styling for CKEditor Body */
                html {
                    background-color: #f3f4f6 !important;
                }
                body {
                    width: 210mm !important;
                    min-height: 297mm !important;
                    margin: 20px auto !important;
                    padding: 20mm !important; /* Simulate document margins */
                    background: white !important;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15) !important;
                    border: 1px solid #d1d5db !important;
                    box-sizing: border-box !important;
                }
            `;

        editorInstance = CKEDITOR.replace('documentEditor', {
            height: 700,
            // MS Word-like Toolbar Layout
            toolbar: [
                { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll'] },
                { name: 'links', items: ['Link', 'Unlink'] },
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak'] },
                { name: 'tools', items: ['Maximize'] },
                '/',
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
                '/',
                { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
                { name: 'colors', items: ['TextColor', 'BGColor'] }
            ],
            contentsCss: [
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
                'https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;600;700&family=Noto+Sans+Devanagari:wght@400;600;700&display=swap'
            ],
            // 3 Languages Fonts (DO NOT CHANGE)
            font_names: 'English (Times New Roman)/Times New Roman, Times, serif;' +
                'Hindi (Mangal)/Mangal, Nirmala, Devanagari, sans-serif;' +
                'Gujarati (LMG Arun)/LMG-Arun, Shruti, Noto Sans Gujarati, sans-serif;',
            fontSize_sizes: '9/9pt;10/10pt;11/11pt;12/12pt;13/13pt;14/14pt;16/16pt;18/18pt;20/20pt;24/24pt;28/28pt;32/32pt;36/36pt;'
        });

            editorInstance.on('instanceReady', function () {
                const notifArea = document.querySelector('.cke_notifications_area');
                if (notifArea) notifArea.remove();

                // Inject local fonts style into CKEditor iframe head
                const frameDoc = editorInstance.document.$;
                if (frameDoc) {
                    const styleEl = frameDoc.createElement('style');
                    styleEl.innerHTML = editorCustomCss;
                    frameDoc.head.appendChild(styleEl);
                }
            });
        });

        function previewDocument() {
            if (!editorInstance) return;

            const content = editorInstance.getData();
            const spinner = document.getElementById('pdfLoadingSpinner');
            const frame = document.getElementById('pdfPreviewFrame');

            spinner.style.display = 'block';
            frame.src = 'about:blank';
            pdfModal.show();

            fetch("{{ route('agreement-designer.preview') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    content: content
                })
            })
                .then(response => {
                    if (!response.ok) throw new Error('PDF Generation failed');
                    return response.blob();
                })
                .then(blob => {
                    if (currentPdfUrl) {
                        URL.revokeObjectURL(currentPdfUrl);
                    }
                    const pdfBlob = new Blob([blob], { type: 'application/pdf' });
                    currentPdfUrl = URL.createObjectURL(pdfBlob);
                    frame.src = currentPdfUrl;
                    spinner.style.display = 'none';
                })
                .catch(err => {
                    spinner.style.display = 'none';
                    alert('Error generating PDF preview: ' + err.message);
                });
        }

        function openPdfInTab() {
            if (currentPdfUrl) {
                window.open(currentPdfUrl, '_blank');
            }
        }

        function clearEditor() {
            if (confirm('Are you sure you want to clear the editor?')) {
                editorInstance.setData('');
            }
        }
    </script>
@endsection