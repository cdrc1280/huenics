<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Executive Sales & Performance Report — {{ $periodLabel }}</title>
    <style>
        @page {
            margin: 28px 32px;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #0f766e;
            letter-spacing: 0.5px;
        }
        .company-subtitle {
            font-size: 9px;
            color: #475569;
            margin-top: 2px;
        }
        .report-title-cell {
            text-align: right;
            vertical-align: bottom;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-period {
            font-size: 10px;
            font-weight: bold;
            color: #0f766e;
            margin-top: 3px;
        }
        .report-meta {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 2px;
        }

        /* KPI Cards Grid */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 14px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 10px;
            text-align: center;
        }
        .kpi-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .kpi-value {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }
        .kpi-sub {
            font-size: 8px;
            color: #10b981;
            margin-top: 2px;
        }

        /* Section Heading */
        .section-header {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 10px;
            margin-bottom: 8px;
        }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .data-table th {
            background-color: #0f766e;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 5px 6px;
            text-align: left;
            border: 1px solid #0f766e;
        }
        .data-table td {
            padding: 5px 6px;
            font-size: 9px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-info { background-color: #e0f2fe; color: #0369a1; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }

        /* Footer */
        .footer {
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="company-name">HUENICS INDUSTRIAL SALES INC.</div>
                <div class="company-subtitle">
                    Direct Importer & Wholesale Distributor • Electrical, Lighting & Industrial Supplies<br>
                    2F Starmall EDSA-Shaw, Mandaluyong City, Metro Manila • Tel: #8561 6836
                </div>
            </td>
            <td class="report-title-cell" style="width: 45%;">
                <div class="report-title">Executive Sales Report</div>
                <div class="report-period">{{ $periodLabel }}</div>
                <div class="report-meta">Generated on: {{ $generatedAt }} | Scope: {{ $scopeLabel }}</div>
            </td>
        </tr>
    </table>

    <!-- Executive KPI Grid -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Sales Achieved</div>
                <div class="kpi-value" style="color: #0f766e;">₱ {{ number_format($kpis['total_sales'], 2) }}</div>
                <div class="kpi-sub">Total Closed PO Revenue</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Gross Margin</div>
                <div class="kpi-value" style="color: #2563eb;">₱ {{ number_format($kpis['total_profit'], 2) }}</div>
                <div class="kpi-sub">{{ $kpis['margin_pct'] }}% Realized Profit Margin</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Conversion Win Rate</div>
                <div class="kpi-value" style="color: #d97706;">{{ $kpis['win_rate'] }}%</div>
                <div class="kpi-sub">{{ $kpis['total_pos'] }} Won / {{ $kpis['total_quotations'] }} Quotes</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Active Sales Agents</div>
                <div class="kpi-value">{{ count($leaderboard) }}</div>
                <div class="kpi-sub">Quota Performers in Scope</div>
            </td>
        </tr>
    </table>

    <!-- Leaderboard Table -->
    <div class="section-header">Sales Executive Performance Leaderboard</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 25%;">Sales Executive</th>
                <th style="width: 15%;">Role / Account</th>
                <th style="width: 17%;" class="text-right">Sales Achieved (₱)</th>
                <th style="width: 15%;" class="text-right">Realized Profit (₱)</th>
                <th style="width: 8%;" class="text-center">Quotes</th>
                <th style="width: 7%;" class="text-center">POs</th>
                <th style="width: 8%;" class="text-center">Win Rate</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaderboard as $index => $row)
            <tr>
                <td class="text-center font-bold">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $row['name'] }}</td>
                <td>
                    <span class="badge {{ $row['is_owner'] ? 'badge-warning' : 'badge-info' }}">
                        {{ $row['role_label'] }}
                    </span>
                </td>
                <td class="text-right font-bold" style="color: #0f766e;">₱ {{ number_format($row['sales_achieved'], 2) }}</td>
                <td class="text-right" style="color: #2563eb;">₱ {{ number_format($row['profit'], 2) }}</td>
                <td class="text-center">{{ $row['quotations'] }}</td>
                <td class="text-center font-bold">{{ $row['pos'] }}</td>
                <td class="text-center font-bold">
                    <span class="badge {{ $row['win_rate_val'] >= 50 ? 'badge-success' : ($row['win_rate_val'] > 0 ? 'badge-info' : '') }}">
                        {{ $row['win_rate'] }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 12px; color: #64748b;">No sales activity recorded for this period.</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($leaderboard) > 0)
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="3" class="text-right">TOTALS / OVERALL:</td>
                <td class="text-right" style="color: #0f766e;">₱ {{ number_format($kpis['total_sales'], 2) }}</td>
                <td class="text-right" style="color: #2563eb;">₱ {{ number_format($kpis['total_profit'], 2) }}</td>
                <td class="text-center">{{ $kpis['total_quotations'] }}</td>
                <td class="text-center">{{ $kpis['total_pos'] }}</td>
                <td class="text-center">{{ $kpis['win_rate'] }}%</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Operations & Delivery Breakdown -->
    <div class="section-header">Commercial Fulfillment & Pipeline Overview</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%;">Metric Indicator</th>
                <th style="width: 15%;" class="text-center">Count</th>
                <th style="width: 25%;" class="text-right">Financial Value (₱)</th>
                <th style="width: 25%;">Operational Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">Total Confirmed Purchase Orders (Won)</td>
                <td class="text-center font-bold">{{ $kpis['total_pos'] }}</td>
                <td class="text-right font-bold">₱ {{ number_format($kpis['total_sales'], 2) }}</td>
                <td><span class="badge badge-success">Commercial Win</span></td>
            </tr>
            <tr>
                <td class="font-bold">Total Client Quotations Issued</td>
                <td class="text-center font-bold">{{ $kpis['total_quotations'] }}</td>
                <td class="text-right font-bold">₱ {{ number_format($kpis['total_quoted_amount'], 2) }}</td>
                <td><span class="badge badge-info">Active Pipeline</span></td>
            </tr>
            <tr>
                <td class="font-bold">Completed & Delivered Realized Orders</td>
                <td class="text-center font-bold">{{ $kpis['delivered_pos'] }}</td>
                <td class="text-right font-bold">₱ {{ number_format($kpis['delivered_amount'], 2) }}</td>
                <td><span class="badge badge-success">Fulfilled & DR Issued</span></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Confidential — Internal Commercial Report of Huenics Industrial Sales Inc. • Page 1 of 1 • System Generated
    </div>

</body>
</html>
