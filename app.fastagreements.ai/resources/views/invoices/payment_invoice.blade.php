<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $payment->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #6366f1;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header table {
            width: 100%;
        }
        .title {
            font-size: 26px;
            font-weight: bold;
            color: #6366f1;
        }
        .invoice-details {
            text-align: right;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 8px;
            color: #1f2937;
        }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 25px;
        }
        table.items-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 600;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        table.items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .total-box {
            width: 40%;
            margin-left: auto;
            border-top: 2px solid #e5e7eb;
            padding-top: 10px;
        }
        .badge {
            background-color: #10b981;
            color: #ffffff;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <div class="title">INVOICE</div>
                    <div>{{ config('app.name', 'Fast Agreements') }}</div>
                </td>
                <td class="invoice-details">
                    <div><strong>Invoice No:</strong> {{ $payment->invoice_number }}</div>
                    <div><strong>Date:</strong> {{ $payment->paid_at ? $payment->paid_at->format('d M Y') : date('d M Y') }}</div>
                    <div><strong>Status:</strong> <span class="badge">PAID</span></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table style="width: 100%;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div class="section-title">Billed To:</div>
                    <div><strong>{{ $user->name ?? 'Customer' }}</strong></div>
                    <div>Mobile: {{ $user->mobile ?? '-' }}</div>
                    @if(!empty($user->email))
                        <div>Email: {{ $user->email }}</div>
                    @endif
                    @if(!empty($user->address))
                        <div>Address: {{ $user->address }}</div>
                    @endif
                    @if(!empty($user->company_name))
                        <div>Company: {{ $user->company_name }}</div>
                    @endif
                    @if(!empty($user->gst_number))
                        <div>GSTIN: {{ $user->gst_number }}</div>
                    @endif
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <div class="section-title">Payment Info:</div>
                    <div><strong>Payment Mode:</strong> Online (Razorpay)</div>
                    <div><strong>Order ID:</strong> {{ $payment->razorpay_order_id }}</div>
                    <div><strong>Transaction ID:</strong> {{ $payment->razorpay_payment_id }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 10%;">#</th>
                <th style="width: 65%;">Description</th>
                <th style="width: 25%;" class="text-right">Amount (INR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ $payment->purpose ?? 'Service / Agreement Payment' }}</td>
                <td class="text-right">₹ {{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total-box">
        <table style="width: 100%;" cellpadding="4" cellspacing="0">
            <tr>
                <td><strong>Grand Total:</strong></td>
                <td class="text-right"><strong>₹ {{ number_format($payment->amount, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>This is a computer-generated invoice and does not require a physical signature.</p>
    </div>

</body>
</html>
