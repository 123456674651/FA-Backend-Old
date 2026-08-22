<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Legal Notices Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
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
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            color: #fff;
        }
        .bg-warning { background-color: #f0ad4e; color: #000; }
        .bg-success { background-color: #5cb85c; }
        .bg-danger { background-color: #d9534f; }
        .bg-info { background-color: #5bc0de; color: #000; }
        .bg-secondary { background-color: #777; }
    </style>
</head>
<body>
    <h2>Legal Notices Report</h2>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">ID</th>
                <th>Company Name</th>
                <th>Person Name</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">Amount Due</th>
                <th>My Company</th>
                <th class="text-center">Status</th>
                <th>Created Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notices as $notice)
                <tr>
                    <td class="text-center">{{ $notice->id }}</td>
                    <td>{{ $notice->company_name }}</td>
                    <td>{{ $notice->company_person_name }}</td>
                    <td class="text-right">INR {{ number_format($notice->total_amount, 2) }}</td>
                    <td class="text-right">INR {{ number_format($notice->amount_due, 2) }}</td>
                    <td>{{ $notice->my_company_name }}</td>
                    <td class="text-center">
                        @php
                            $badgeClasses = [
                                'Pending' => 'bg-warning',
                                'Approved' => 'bg-success',
                                'Rejected' => 'bg-danger',
                                'In Progress' => 'bg-info',
                                'Closed' => 'bg-secondary'
                            ];
                            $badgeClass = $badgeClasses[$notice->status] ?? 'bg-secondary';
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $notice->status }}</span>
                    </td>
                    <td>{{ $notice->created_at ? $notice->created_at->format('Y-m-d') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
