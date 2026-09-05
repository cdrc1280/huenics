<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Huenics Industrial Sales — Accounts Receivable & Aging Ledger</title>
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
            border-bottom: 2px solid #2563eb;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 15px;
            font-weight: bold;
            color: #1e3a8a;
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
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 12px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
            text-align: center;
        }
        .kpi-val {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }
        .kpi-lbl {
            font-size: 7.5px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
            margin-top: 2px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        .data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            padding: 5px 6px;
            text-align: left;
            border: 1px solid #1e3a8a;
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
        .badge-danger { background-color: #fee2e2; color: #b91c1c; }
        .badge-warning { background-color: #fef3c7; color: #b45309; }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-gray { background-color: #f1f5f9; color: #475569; }
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
                <div class="company-sub">Accounting & Receivables Management Department</div>
            </td>
            <td class="report-title-cell">
                <div class="report-title">Accounts Receivable & Aging Ledger</div>
                <div class="report-meta">Generated: {{ $generatedAt->format('F d, Y h:i A') }} | Strictly Confidential</div>
            </td>
        </tr>
    </table>

    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="border-left: 3px solid #2563eb;">
                <div class="kpi-val">PHP {{ number_format($totalReceivables, 2) }}</div>
                <div class="kpi-lbl">Total Outstanding Receivables</div>
            </td>
            <td class="kpi-card" style="border-left: 3px solid #16a34a;">
                <div class="kpi-val">PHP {{ number_format($totalCollected, 2) }}</div>
                <div class="kpi-lbl">Total Realized Collections</div>
            </td>
            <td class="kpi-card" style="border-left: 3px solid #dc2626;">
                <div class="kpi-val" style="color: #dc2626;">{{ $overdueCount }} POs</div>
                <div class="kpi-lbl">Overdue Orders (Past 30 Days)</div>
            </td>
            <td class="kpi-card" style="border-left: 3px solid #d97706;">
                <div class="kpi-val" style="color: #d97706;">{{ $warningCount }} POs</div>
                <div class="kpi-lbl">Due within 10 Days (Follow-Up)</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">PO #</th>
                <th style="width: 18%;">Customer Account</th>
                <th style="width: 14%;">Project / Location</th>
                <th style="width: 10%; text-align: right;">Amount (PHP)</th>
                <th style="width: 12%;">Payment Term</th>
                <th style="width: 8%;">Delivery</th>
                <th style="width: 8%;">Due Date</th>
                <th style="width: 10%;">Aging Status</th>
                <th style="width: 10%;">Check / Account Ref</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $po)
            <tr>
                <td style="font-weight: bold;">{{ $po->po_number }}</td>
                <td>
                    <div><strong>{{ $po->customer_name }}</strong></div>
                    @if($po->customer_company && $po->customer_company !== $po->customer_name)
                        <div style="color: #64748b; font-size: 7.5px;">{{ $po->customer_company }}</div>
                    @endif
                </td>
                <td>{{ $po->project?->name ?? 'General Delivery' }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format((float) $po->order_amount, 2) }}</td>
                <td>{{ $po->payment_terms ?: ($po->payment_term_type ? strtoupper($po->payment_term_type) : 'Not Set') }}</td>
                <td>{{ $po->actual_delivery_date ? $po->actual_delivery_date->format('m/d/Y') : '—' }}</td>
                <td>{{ $po->payment_due_date ? $po->payment_due_date->format('m/d/Y') : '—' }}</td>
                <td>
                    @if($po->isPaid())
                        <span class="badge badge-success">PAID</span>
                    @elseif($po->days_until_due !== null && $po->days_until_due < 0)
                        <span class="badge badge-danger">OVERDUE ({{ abs($po->days_until_due) }}d)</span>
                    @elseif($po->days_until_due !== null && $po->days_until_due <= 10)
                        <span class="badge badge-warning">DUE IN {{ $po->days_until_due }}d</span>
                    @else
                        <span class="badge badge-gray">PENDING ({{ $po->days_until_due ?? 0 }}d)</span>
                    @endif
                </td>
                <td>
                    @if($po->pdc_check_number)
                        <div>CHK: {{ $po->pdc_check_number }}</div>
                        <div style="font-size: 7px; color: #64748b;">{{ $po->pdc_bank }}</div>
                    @elseif($po->payment_account)
                        <div>{{ $po->payment_account }}</div>
                    @else
                        —
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 15px; color: #64748b;">No purchase order receivables found for the selected ledger criteria.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Huenics Industrial Sales Inc. &bull; Official Accounts Receivable Statement &bull; Page 1 of 1 &bull; System Generated Record
    </div>
</body>
</html>
