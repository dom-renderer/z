<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: white;
            padding: 0;
            font-size: 12px;
        }
        
        .email-container {
            max-width: 100%;
            margin: 0 auto;
            background-color: white;
        }
        
        .header {
            background-color: #5e0002;
            color: white;
            padding: 15px 20px;
            position: relative;
            text-align: center;
        }
        
        .logo img {
            width: 40px;
            height: auto;
        }
        
        .title {
            background-color: #1976d2;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            display: inline-block;
        }
        
        .content {
            padding: 20px;
            color: #333;
        }
        
        .task-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: white;
            font-size: 11px;
        }
        
        .task-table th {
            background-color: #e9ecef;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            color: #495057;
            border: 1px solid #dee2e6;
            font-size: 10px;
            vertical-align: top;
            word-wrap: break-word;
        }
        
        .task-table td {
            padding: 8px 4px;
            border: 1px solid #dee2e6;
            vertical-align: top;
            word-wrap: break-word;
            max-width: 120px;
        }
        
        .production-number {
            max-width: 120px;
            word-break: break-word;
        }
        
        .production-date {
            max-width: 100px;
            word-break: break-word;
        }
        
        .products-col {
            max-width: 200px;
            word-break: break-word;
        }
        
        .users-col {
            max-width: 150px;
            word-break: break-word;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            color: white;
            font-size: 9px;
            font-weight: bold;
        }
        
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        
        .footer-text {
            margin-bottom: 15px;
            color: #666;
            line-height: 1.4;
            font-size: 12px;
        }
        
        .portal-button {
            background-color: #5e0002;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .bottom-footer {
            background-color: #5e0002;
            color: white;
            padding: 12px;
            text-align: center;
            font-size: 11px;
        }
        
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            
            .email-container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .task-table {
                page-break-inside: avoid;
            }
            
            .task-table th,
            .task-table td {
                padding: 6px 3px;
                font-size: 9px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">
                <img src="{{ public_path('assets/logo.webp') }}" alt="Tea Post Logo">
            </div>
        </div>
        
        <div class="content">
            <table>
                <tbody>
                    <tr>
                        <td colspan="2"> Report Generated Date </td>
                        <td colspan="8"> 
                            <div style="width: 100%;text-align:right;float: right;">
                                {{ date('d F Y, H:i') }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <center>
                <h3>
                    <div>Production Report</div>
                </h3>
            </center> <br> <br>

            <table class="task-table">
                <thead>
                    <tr>
                        <th class="production-number">Production Number</th>
                        <th class="users-col">Employee</th>
                        <th class="production-date">Production Date</th>
                        <th class="production-date">Production Shift</th>
                        <th class="products-col">Product</th>
                        <th class="products-col">Unit</th>
                        <th>Quantity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productions as $item)
                        @php
                            if ($item->production->status == 'pending') {
                                $total += $item->quantity;
                            } else if ($item->production->status == 'expire') {
                                $wastage += $item->quantity;
                            }
                        @endphp
                        <tr>
                            <td class="production-number">{{ $item->production->production_number }}</td>
                            <td class="users-col"> {{ $item->user->name ?? 'N/A' }} </td>
                            <td class="production-date">{{ date('d-m-Y H:i', strtotime($item->production->production_date)) }}</td>
                            <td>{{ $item->production->shift->title ?? 'N/A' }}</td>
                            <td class="products-col"> {{ $item->product->name ?? 'N/A' }} </td>
                            <td class="products-col"> {{ $item->unit->name ?? 'N/A' }} </td>
                            <td>{{ $item->quantity }}</td>
                            <td>
                                @php
                                    $color = \App\Helpers\Helper::$productionStatusColors[$item->production->status] ?? 'secondary';
                                    $text = \App\Helpers\Helper::$productionStatuses[$item->production->status] ?? ucfirst($item->production->status);
                                @endphp
                                <span class="status-badge badge-{{ $color }}">{{ $text }}</span>
                            </td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px;">No production data found</td>
                    </tr>
                    @endforelse
                    <tr>
                        <td colspan="6"> <strong> Total </strong> </td>
                        <td colspan="2"> <strong> {{ number_format($total, 2) }} </strong> </td>
                    </tr>
                    <tr>
                        <td colspan="6"> <strong> Wastage </strong> </td>
                        <td colspan="2"> <strong> {{ number_format($wastage, 2) }} </strong> </td>
                    </tr>
                    <tr>
                        <td colspan="6"> <strong> Grand Total </strong> </td>
                        <td colspan="2"> <strong> {{ number_format($total - $wastage, 2) }} </strong> </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="footer-text" style="margin-top: 60px!important;">
                You can log in to the admin portal for full details and take necessary actions.
            </div>
            
            <a href="{{ route('production.index') }}" class="portal-button">Go to Portal</a>
        </div>
        
        <div class="bottom-footer">
            © {{ date('Y') }} {{ APP_NAME }}
        </div>
    </div>
</body>
</html>
