<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Agreement Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.4;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        h4 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 10px;
            color: #666;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 4px 5px;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: bold;
            color: #fff;
        }

        .bg-warning {
            background-color: #f0ad4e;
            color: #000;
        }

        .bg-success {
            background-color: #5cb85c;
        }

        .bg-danger {
            background-color: #d9534f;
        }

        .summary-box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 8px;
            margin-bottom: 15px;
            font-size: 10px;
        }

        .summary-title {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 4px;
            border-bottom: 1px solid #eee;
            padding-bottom: 2px;
        }

        .summary-row {
            margin-bottom: 2px;
        }
    </style>
</head>

<body>
    <h2>Agreement Report</h2>
    <h4>Type: {{ ucwords(str_replace('_', ' ', $reportType)) }} | Generated on: {{ date('Y-m-d H:i:s') }}</h4>

    @if($reportType === 'revenue_wise' && $summaryStats)
        <div class="summary-box">
            <div class="summary-title">Revenue Summary Statistics</div>
            <div class="summary-row"><strong>Total Agreements:</strong> {{ number_format($summaryStats->count) }}</div>
            <div class="summary-row"><strong>Total Revenue:</strong> INR {{ number_format($summaryStats->total, 2) }}</div>
            <div class="summary-row"><strong>Average Agreement Value:</strong> INR
                {{ number_format($summaryStats->average, 2) }}</div>
            <div class="summary-row"><strong>Highest Agreement Value:</strong> INR
                {{ number_format($summaryStats->highest, 2) }}</div>
            <div class="summary-row"><strong>Lowest Agreement Value:</strong> INR
                {{ number_format($summaryStats->lowest, 2) }}</div>
        </div>
    @endif

    <table>
        @if($reportType === 'revenue_wise')
            <thead>
                <tr>
                    <th>Group Name</th>
                    <th class="text-center">Total Agreements</th>
                    <th class="text-right">Total Revenue</th>
                    <th class="text-right">Average Revenue</th>
                    <th class="text-right">Highest Revenue</th>
                    <th class="text-right">Lowest Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td><strong>{{ $row->group_name ?? 'N/A' }}</strong></td>
                        <td class="text-center">{{ number_format($row->total_agreements) }}</td>
                        <td class="text-right">INR {{ number_format($row->total_revenue, 2) }}</td>
                        <td class="text-right">INR {{ number_format($row->average_revenue, 2) }}</td>
                        <td class="text-right">INR {{ number_format($row->highest_revenue, 2) }}</td>
                        <td class="text-right">INR {{ number_format($row->lowest_revenue, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        @elseif(in_array($reportType, ['category_wise', 'language_wise', 'state_wise', 'city_wise']))
            <thead>
                <tr>
                    <th>Group Name</th>
                    <th class="text-center">Total Agreements</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td><strong>{{ $row->group_name ?? 'N/A' }}</strong></td>
                        <td class="text-center">{{ number_format($row->total_agreements) }}</td>
                    </tr>
                @endforeach
            </tbody>
        @elseif(in_array($reportType, ['user_wise', 'advocate_wise']))
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th class="text-center">Total Agreements</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td><strong>{{ $row->group_name ?? 'N/A' }}</strong></td>
                        <td>{{ $row->mobile ?? 'N/A' }}</td>
                        <td>{{ $row->email ?? 'N/A' }}</td>
                        <td class="text-center">{{ number_format($row->total_agreements) }}</td>
                    </tr>
                @endforeach
            </tbody>
        @else
            <thead>
                <tr>
                    <th class="text-center" style="width: 4%;">ID</th>
                    <th>Party 1</th>
                    <th>Party 2</th>
                    <th>Category</th>
                    <th>Language</th>
                    <th>Plan</th>
                    <th>Price</th>
                    <th class="text-right">Agreement Amount</th>
                    <th>Date</th>
                    <th class="text-center">View</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    @php
                        $subscription = \App\Models\CustomerSubscription::with('plan')
                            ->where('customer_id', $row->party_1_id)
                            ->orderBy('id', 'desc')
                            ->first();
                        $planName = ($subscription && $subscription->plan) ? $subscription->plan->name : 'No Plan';
                        $planPrice = ($subscription && $subscription->plan) ? 'INR ' . number_format($subscription->plan->price, 2) : 'N/A';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $row->id }}</td>
                        <td>{{ $row->party1->name ?? 'N/A' }}</td>
                        <td>{{ $row->party2->name ?? 'N/A' }}</td>
                        <td>{{ $row->category ? ($row->category->category_name . ($row->subCategory ? ' - ' . $row->subCategory->category_name : '')) : 'N/A' }}</td>
                        <td>{{ $row->language->language_name ?? 'N/A' }}</td>
                        <td>{{ $planName }}</td>
                        <td>{{ $planPrice }}</td>
                        <td class="text-right">INR {{ number_format($row->amount, 2) }}</td>
                        <td>{{ $row->created_at ? $row->created_at->format('Y-m-d') : '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('agreements.show', $row->id) }}">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        @endif
    </table>
</body>

</html>