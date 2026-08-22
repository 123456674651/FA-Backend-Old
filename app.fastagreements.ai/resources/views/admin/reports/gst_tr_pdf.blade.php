<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>GST Transaction Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 7.5px;
            color: #333;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
        }
        .header h2 {
            margin: 0 0 3px 0;
            font-size: 13px;
            text-transform: uppercase;
            color: #111;
        }
        .header p {
            margin: 0;
            font-size: 9px;
            color: #555;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #ccc;
            padding: 4px 6px;
            text-align: left;
        }
        .summary-table th {
            background-color: #f7f7f7;
            font-weight: bold;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .main-table th, .main-table td {
            border: 1px solid #ddd;
            padding: 3px 4px;
            text-align: left;
        }
        .main-table th {
            background-color: #eaeaea;
            font-weight: bold;
            color: #222;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            width: 100%;
            border-top: 1px solid #ccc;
            padding-top: 6px;
            margin-top: 15px;
            font-size: 7px;
            color: #666;
        }
        .footer-table {
            width: 100%;
            border: none;
        }
        .footer-table td {
            border: none;
            padding: 0;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>GST Transaction Report</h2>
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }} | Company: {{ setting('company_name') ?? 'Fast Agreement' }}</p>
        @if(!empty($filters['from_date']) || !empty($filters['to_date']))
            <p>
                Period: 
                {{ !empty($filters['from_date']) ? $filters['from_date'] : 'Start' }} 
                to 
                {{ !empty($filters['to_date']) ? $filters['to_date'] : 'End' }}
            </p>
        @endif
    </div>

    <!-- Summary Box Table -->
    <table class="summary-table">
        <thead>
            <tr>
                <th>Total Invoices</th>
                <th class="text-right">Total Taxable Amount</th>
                <th class="text-right">Total CGST</th>
                <th class="text-right">Total SGST</th>
                <th class="text-right">Total IGST</th>
                <th class="text-right">Total GST</th>
                <th class="text-right">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ count($invoices) }}</td>
                <td class="text-right">₹{{ number_format($totalTaxableAmount, 2) }}</td>
                <td class="text-right">₹{{ number_format($totalCgst, 2) }}</td>
                <td class="text-right">₹{{ number_format($totalSgst, 2) }}</td>
                <td class="text-right">₹{{ number_format($totalIgst, 2) }}</td>
                <td class="text-right">₹{{ number_format($totalGstSum, 2) }}</td>
                <td class="text-right" style="font-weight: bold;">₹{{ number_format($totalInvoiceAmount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Main Report Table -->
    <table class="main-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">Sr.No</th>
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
                    <td colspan="16" class="text-center" style="padding: 10px;">No transaction records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td><strong>Total Records:</strong> {{ count($invoices) }}</td>
                <td class="text-center"><strong>Generated By:</strong> {{ auth()->user()->name }}</td>
                <td class="text-right"><strong>Generated Date & Time:</strong> {{ now()->format('Y-m-d H:i:s') }}</td>
            </tr>
        </table>
    </div>

</body>
</html>
