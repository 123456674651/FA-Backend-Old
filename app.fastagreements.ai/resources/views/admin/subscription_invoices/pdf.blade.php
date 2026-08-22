<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
            padding: 25px;
            background: #fff;
        }

        .clearfix {
            clear: both;
        }

        .header {
            width: 100%;
            margin-bottom: 25px;
        }

        .left {
            float: left;
            width: 60%;
        }

        .right {
            float: right;
            width: 40%;
            text-align: right;
        }

        .title {
            font-size: 30px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .small {
            font-size: 11px;
            color: #555;
        }

        .info-box {
            border: 1px solid #000;
            background: #f5f5f5;
            padding: 15px;
            margin-top: 20px;
            margin-bottom: 25px;
        }

        .company {
            width: 48%;
            float: left;
        }

        .customer {
            width: 48%;
            float: right;
            text-align: right;
        }

        h3 {
            margin-bottom: 8px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .items {
            margin-top: 15px;
        }

        .items th {
            background: #000;
            color: #fff;
            border: 1px solid #000;
            padding: 10px;
            font-size: 11px;
        }

        .items td {
            border: 1px solid #000;
            padding: 10px;
        }

        .text-right {
            text-align: right;
        }

        .left-part {
            width: 55%;
            float: left;
            margin-top: 25px;
        }

        .right-part {
            width: 40%;
            float: right;
            margin-top: 25px;
        }

        .summary td {
            padding: 8px;
            border-bottom: 1px solid #ccc;
        }

        .grand {
            background: #000;
            color: #fff;
            font-weight: bold;
        }

        .badge {
            padding: 4px 10px;
            font-size: 10px;
            border-radius: 3px;
            color: #fff;
            display: inline-block;
        }

        .paid {
            background: #000;
        }

        .pending {
            background: #666;
        }

        .failed {
            background: #444;
        }

        ul {
            margin-top: 8px;
            padding-left: 18px;
        }

        li {
            margin-bottom: 4px;
        }

        .signature {
            margin-top: 60px;
            text-align: right;
        }

        .footer {
            margin-top: 60px;
            border-top: 1px solid #000;
            padding-top: 10px;
            text-align: center;
            color: #555;
            font-size: 11px;
        }
    </style>
</head>

<body>

    @php

        $date = $invoice->invoice_date
            ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y')
            : '-';

        $status = strtolower($invoice->payment_status);

    @endphp

    <div class="header">

        <div class="left">
            @if(setting('company_logo') && file_exists(public_path('storage/' . setting('company_logo'))))
                <img src="{{ public_path('storage/' . setting('company_logo')) }}" style="max-height: 50px; margin-bottom: 10px; display: block;">
            @elseif(setting('admin_logo') && file_exists(public_path('storage/' . setting('admin_logo'))))
                <img src="{{ public_path('storage/' . setting('admin_logo')) }}" style="max-height: 50px; margin-bottom: 10px; display: block;">
            @endif
            <div class="title">TAX INVOICE</div>
        </div>

        <div class="right">
            <div class="small">DATE</div>
            <strong>{{ $date }}</strong>

            <br><br>

            <div class="small">INVOICE NO.</div>
            <strong>{{ $invoice->invoice_number }}</strong>
        </div>

    </div>

    <div class="clearfix"></div>

    <div class="info-box">

        <div class="company">

            <h3>{{ setting('company_name') ?? 'Company Name' }}</h3>

            @if(setting('company_gstin') || setting('gst_number'))
                GSTIN : {{ setting('company_gstin') ?? setting('gst_number') }}<br>
            @endif

            @if(setting('company_address_line_1') || setting('company_address_line_2') || setting('company_city') || setting('company_state') || setting('company_pin_code') || setting('company_country'))
                @if(setting('company_address_line_1'))
                    {{ setting('company_address_line_1') }}<br>
                @endif
                @if(setting('company_address_line_2'))
                    {{ setting('company_address_line_2') }}<br>
                @endif
                @php
                    $cityStateZip = array_filter([setting('company_city'), setting('company_state'), setting('company_pin_code')]);
                @endphp
                @if(!empty($cityStateZip))
                    {{ implode(', ', $cityStateZip) }}<br>
                @endif
                @if(setting('company_country'))
                    {{ setting('company_country') }}<br>
                @endif
            @elseif(setting('address'))
                {{ setting('address') }}<br>
            @endif

            @if(setting('company_phone_number') || setting('phone'))
                Phone : {{ setting('company_phone_number') ?? setting('phone') }}<br>
            @endif

            @if(setting('company_email_address') || setting('email'))
                Email : {{ setting('company_email_address') ?? setting('email') }}<br>
            @endif

            @if(setting('company_website'))
                Website : {{ setting('company_website') }}<br>
            @endif

        </div>

        <div class="customer">

            <h3>Bill To</h3>

            <strong>{{ $invoice->customer->name ?? '-' }}</strong><br>

            {{ $invoice->customer->email ?? '-' }}<br>

            {{ $invoice->customer->mobile ?? '-' }}

        </div>

        <div class="clearfix"></div>

    </div>

    <table class="items">

        <thead>

            <tr>

                <th width="8%">#</th>

                <th>Description</th>

                <th width="22%">Plan</th>

                <th width="15%">Price</th>

                <th width="10%">Qty</th>

                <th width="18%">Total</th>

            </tr>

        </thead>

        <tbody>

            <tr>

                <td>1</td>

                <td>Subscription Purchase</td>

                <td>{{ $invoice->subscriptionPlan->name ?? '-' }}</td>

                <td class="text-right">₹{{ number_format($invoice->amount, 2) }}</td>

                <td class="text-right">1</td>

                <td class="text-right">₹{{ number_format($invoice->amount, 2) }}</td>

            </tr>

        </tbody>

    </table>

    <div class="left-part">

        <h3>Payment Details</h3>

        <table>

            <tr>
                <td width="40%">Payment Method</td>
                <td>{{ ucfirst($invoice->payment_method ?? '-') }}</td>
            </tr>

            <tr>
                <td>Payment Status</td>
                <td>

                    <span class="badge {{ $status == 'paid' ? 'paid' : ($status == 'failed' ? 'failed' : 'pending') }}">
                        {{ ucfirst($invoice->payment_status) }}
                    </span>

                </td>
            </tr>

            <tr>
                <td>Transaction ID</td>
                <td>{{ $invoice->transaction_id ?? '-' }}</td>
            </tr>

        </table>

        <br>

        <h3>Notes</h3>

        <ul>

            <li>Payment received for subscription plan.</li>

            <li>This is a computer-generated invoice.</li>

            <li>No signature is required.</li>

            <li>Thank you for your business.</li>

        </ul>

        @if(setting('company_bank_name') || setting('company_account_number') || setting('company_upi_id'))
            <br>
            <h3>Bank Transfer Details</h3>
            <table>
                @if(setting('company_bank_name'))
                    <tr>
                        <td width="40%">Bank Name</td>
                        <td>{{ setting('company_bank_name') }}</td>
                    </tr>
                @endif
                @if(setting('company_account_holder_name'))
                    <tr>
                        <td>Account Holder</td>
                        <td>{{ setting('company_account_holder_name') }}</td>
                    </tr>
                @endif
                @if(setting('company_account_number'))
                    <tr>
                        <td>Account Number</td>
                        <td>{{ setting('company_account_number') }}</td>
                    </tr>
                @endif
                @if(setting('company_ifsc_code'))
                    <tr>
                        <td>IFSC Code</td>
                        <td>{{ setting('company_ifsc_code') }}</td>
                    </tr>
                @endif
                @if(setting('company_upi_id'))
                    <tr>
                        <td>UPI ID</td>
                        <td>{{ setting('company_upi_id') }}</td>
                    </tr>
                @endif
            </table>
        @endif

        @if(setting('company_terms_conditions'))
            <br>
            <h3>Terms & Conditions</h3>
            <div style="font-size: 10px; color: #555; line-height: 1.4; white-space: pre-line;">{{ setting('company_terms_conditions') }}</div>
        @endif

    </div>

    <div class="right-part">

        <table class="summary">

            <tr>

                <td>Subtotal</td>

                <td class="text-right">
                    ₹{{ number_format($invoice->amount, 2) }}
                </td>

            </tr>

            <tr>

                <td>Discount</td>

                <td class="text-right">₹0.00</td>

            </tr>

            <tr>

                <td>CGST</td>

                <td class="text-right">₹0.00</td>

            </tr>

            <tr>

                <td>SGST</td>

                <td class="text-right">₹0.00</td>

            </tr>

            <tr class="grand">

                <td>Total</td>

                <td class="text-right">
                    ₹{{ number_format($invoice->amount, 2) }}
                </td>

            </tr>

        </table>

        <div class="signature">

            <strong>For {{ setting('company_name') ?? 'Company Name' }}</strong>

            <br><br><br><br>

            _______________________

            <br>

            Authorized Signatory

        </div>

    </div>

    <div class="clearfix"></div>

    <div class="footer">
        @if(setting('company_invoice_footer'))
            {{ setting('company_invoice_footer') }}
        @else
            {{ setting('company_name') ?? 'Company Name' }} | {{ setting('company_email_address') ?? setting('email') ?? '-' }} | {{ setting('company_phone_number') ?? setting('phone') ?? '-' }}
        @endif
    </div>

</body>

</html>