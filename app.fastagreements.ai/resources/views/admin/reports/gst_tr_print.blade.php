<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GST Transaction Report - Print</title>
    <!-- Bootstrap CSS for layout styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            background-color: #fff;
            padding: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 15px;
        }
        .table th, .table td {
            border: 1px solid #000 !important;
            padding: 4px 6px !important;
        }
        .table th {
            background-color: #f2f2f2 !important;
            font-weight: bold;
        }
        .summary-header {
            border: 1px solid #000;
            padding: 10px;
            background-color: #fafafa;
            margin-bottom: 15px;
        }
        .text-right {
            text-align: right;
        }
        .footer-section {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 8px;
            font-size: 10px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
            @page {
                size: landscape;
                margin: 1cm;
            }
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <!-- Print Header Controls -->
        <div class="row mb-3 no-print">
            <div class="col-12 text-end">
                <button onclick="window.print();" class="btn btn-primary btn-sm me-2">
                    <i class="bi bi-printer"></i> Click to Print
                </button>
                <button onclick="window.close();" class="btn btn-secondary btn-sm">
                    Close Window
                </button>
            </div>
        </div>

        <!-- Document Title -->
        <div class="text-center mb-4">
            <h3 class="fw-bold m-0">GST TRANSACTION REPORT</h3>
            <div class="text-muted small mt-1">
                Generated: {{ now()->format('Y-m-d H:i:s') }} | Company: {{ setting('company_name') ?? 'Fast Agreement' }}
            </div>
            @if(!empty($filters['from_date']) || !empty($filters['to_date']))
                <div class="small mt-1">
                    <strong>Period:</strong> 
                    {{ !empty($filters['from_date']) ? $filters['from_date'] : 'Start' }} 
                    to 
                    {{ !empty($filters['to_date']) ? $filters['to_date'] : 'End' }}
                </div>
            @endif
        </div>

        <!-- Summary Section -->
        <div class="summary-header">
            <div class="row text-center">
                <div class="col-3">
                    <div class="text-muted small fw-semibold">Total Invoices</div>
                    <h5 class="fw-bold m-0 mt-1">{{ count($invoices) }}</h5>
                </div>
                <div class="col-3">
                    <div class="text-muted small fw-semibold text-primary">Total Taxable Amount</div>
                    <h5 class="fw-bold m-0 mt-1">₹{{ number_format($totalTaxableAmount, 2) }}</h5>
                </div>
                <div class="col-3">
                    <div class="text-muted small fw-semibold text-success">Total GST</div>
                    <h5 class="fw-bold m-0 mt-1">₹{{ number_format($totalGstSum, 2) }}</h5>
                </div>
                <div class="col-3">
                    <div class="text-muted small fw-semibold text-dark">Grand Total</div>
                    <h5 class="fw-bold m-0 mt-1">₹{{ number_format($totalInvoiceAmount, 2) }}</h5>
                </div>
            </div>
            <hr class="my-2" style="border-top: 1px solid #000;">
            <div class="row text-center mt-2">
                <div class="col-4">
                    <div class="text-muted small fw-semibold text-info">Total CGST</div>
                    <h6 class="fw-bold m-0">₹{{ number_format($totalCgst, 2) }}</h6>
                </div>
                <div class="col-4">
                    <div class="text-muted small fw-semibold text-info">Total SGST</div>
                    <h6 class="fw-bold m-0">₹{{ number_format($totalSgst, 2) }}</h6>
                </div>
                <div class="col-4">
                    <div class="text-muted small fw-semibold text-warning">Total IGST</div>
                    <h6 class="fw-bold m-0">₹{{ number_format($totalIgst, 2) }}</h6>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">Sr.No</th>
                    <th>Invoice Date</th>
                    <th>Invoice No.</th>
                    <th>Customer Name</th>
                    <th>GSTIN</th>
                    <th>State</th>
                    <th>Place of Supply</th>
                    <th>HSN Code</th>
                    <th class="text-right">Taxable Amt</th>
                    <th>GST %</th>
                    <th class="text-right">CGST</th>
                    <th class="text-right">SGST</th>
                    <th class="text-right">IGST</th>
                    <th class="text-right">Total GST</th>
                    <th class="text-right">Invoice Total</th>
                    <th>Created By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $index => $row)
                    @php
                        $amount = (float) $row->amount;
                        $taxable = $amount / (1 + ($gstRate / 100));
                        $gstVal = $amount - $taxable;
                        $custState = $row->customer && $row->customer->state ? trim(strtolower($row->customer->state->name)) : '';
                        $isSame = ($custState === $companyState);

                        $dateStr = 'N/A';
                        if ($row->invoice_date) {
                            $dateStr = $row->invoice_date instanceof \DateTimeInterface
                                ? $row->invoice_date->format('Y-m-d')
                                : \Carbon\Carbon::parse($row->invoice_date)->format('Y-m-d');
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $dateStr }}</td>
                        <td>{{ $row->invoice_number }}</td>
                        <td>{{ $row->customer?->name ?? 'N/A' }}</td>
                        <td>{{ $row->customer?->gst_number ?? 'N/A' }}</td>
                        <td>{{ $row->customer?->state?->name ?? 'N/A' }}</td>
                        <td>{{ $row->customer?->state?->name ?? 'N/A' }}</td>
                        <td>9983</td>
                        <td class="text-right">₹{{ number_format($taxable, 2) }}</td>
                        <td>{{ $gstRate }}%</td>
                        <td class="text-right">₹{{ $isSame ? number_format($gstVal / 2, 2) : '0.00' }}</td>
                        <td class="text-right">₹{{ $isSame ? number_format($gstVal / 2, 2) : '0.00' }}</td>
                        <td class="text-right">₹{{ !$isSame ? number_format($gstVal, 2) : '0.00' }}</td>
                        <td class="text-right">₹{{ number_format($gstVal, 2) }}</td>
                        <td class="text-right">₹{{ number_format($amount, 2) }}</td>
                        <td>System</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="16" class="text-center">No transaction records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Custom Footer -->
        <div class="footer-section">
            <div class="row">
                <div class="col-4">
                    <strong>Total Records:</strong> {{ count($invoices) }}
                </div>
                <div class="col-4 text-center">
                    <strong>Generated By:</strong> {{ auth()->user()->name }}
                </div>
                <div class="col-4 text-end">
                    <strong>Generated Date & Time:</strong> {{ now()->format('Y-m-d H:i:s') }}
                </div>
            </div>
        </div>
    </div>

    <script>
        // Trigger auto print dialog on load
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
