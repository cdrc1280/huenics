<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Huenics Industrial Sales — Settled Payment History Ledger</title>
    <style>
        @page {
            margin: 20px 25px;
            size: A4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 8.5px;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #16a34a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 15px;
            font-weight: bold;
            color: #15803d;
            letter-spacing: 0.5px;
        }
        .company-sub {
            font-size: 8.5px;
            color: #475569;
            margin-top: 2px;
        }
        .report-title-cell {
            text-align: right;
            vertical-align: bottom;
        }
        .report-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .report-meta {
            font-size: 8px;
            color: #64748b;
            margin-top: 2px;
        }
        .kpi-card {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 12px;
            display: inline-block;
        }
        .kpi-val {
            font-size: 14px;
            font-weight: bold;
            color: #15803d;
        }
        .kpi-lbl {
            font-size: 7.5px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        .data-table th {
            background-color: #15803d;
            color: #ffffff;
            font-weight: bold;
            padding: 5px 6px;
            text-align: left;
            border: 1px solid #15803d;
        }
        .data-table td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 7px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .footer {
            margin-top: 15px;
            border-top: 1px solid #cbd5e1;
            padding-top: 6px;
            font-size: 7.5px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="company-name">HUENICS INDUSTRIAL SALES INC.</div>
                <div class="company-sub">Unit 916 Avida Towers Intima, Paco, Manila | Tel: (02) 8561-6836</div>
                <div class="company-sub">Finance & Treasury Settlement Ledger</div>
            </td>
            <td class="report-title-cell">
                <div class="report-title">Historical Payment Collections Ledger</div>
                <div class="report-meta">Generated: {{ $generatedAt->format('F d, Y h:i A') }}</div>
            </td>
        </tr>
    </table>

    <div class="kpi-card">
        <div class="kpi-val">PHP {{ number_format($totalPaid, 2) }}</div>
        <div class="kpi-lbl">Total Realized & Cleared Collections Across {{ $orders->count() }} Transactions</div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">PO #</th>
                <th style="width: 18%;">Customer Account</th>
                <th style="width: 11%;">Settled / Cleared Date</th>
                <th style="width: 11%; text-align: right;">Amount (PHP)</th>
                <th style="width: 13%;">Payment Term Type</th>
                <th style="width: 12%;">PDC Check #</th>
                <th style="width: 13%;">Bank / Branch</th>
                <th style="width: 12%;">Account / Counter Tag</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $po)
            <tr>
                <td style="font-weight: bold;">{{ $po->po_number }}</td>
                <td>
                    <strong>{{ $po->customer_name }}</strong>
                    @if($po->customer_company && $po->customer_company !== $po->customer_name)
                        <div style="color: #64748b; font-size: 7.5px;">{{ $po->customer_company }}</div>
                    @endif
                </td>
                <td>{{ $po->paid_at ? $po->paid_at->format('m/d/Y h:i A') : ($po->actual_delivery_date ? $po->actual_delivery_date->format('m/d/Y') : '—') }}</td>
                <td style="text-align: right; font-weight: bold; color: #15803d;">{{ number_format((float) $po->order_amount, 2) }}</td>
                <td><span class="badge badge-success">{{ $po->payment_term_type ? strtoupper($po->payment_term_type) : 'PAID' }}</span></td>
                <td>{{ $po->pdc_check_number ?: 'N/A' }}</td>
                <td>{{ $po->pdc_bank ?: 'N/A' }}</td>
                <td>{{ $po->payment_account ?: '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 15px; color: #64748b;">No settled payment transactions found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Huenics Industrial Sales Inc. &bull; Treasury & Settlement Ledger Statement &bull; Page 1 of 1
    </div>
</body>
</html>
