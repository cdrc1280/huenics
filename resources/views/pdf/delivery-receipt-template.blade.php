<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Receipt - {{ $record->dr_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 12mm 15mm 12mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.35;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
            background-color: #fff9fa;
        }
        .container {
            border: 2px solid #b71c1c;
            padding: 16px;
            background-color: #fff;
            position: relative;
        }
        .header {
            text-align: center;
            border-bottom: 1.5px solid #b71c1c;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 {
            margin: 0 0 4px 0;
            font-size: 20px;
            letter-spacing: 0.5px;
            color: #b71c1c;
            text-transform: uppercase;
        }
        .header p {
            margin: 1px 0;
            font-size: 9px;
            color: #333;
        }
        .header .tin {
            font-weight: bold;
            font-size: 9.5px;
            margin-top: 3px;
        }
        .doc-title-row {
            width: 100%;
            margin-bottom: 10px;
        }
        .doc-title {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #111;
        }
        .doc-number {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #d32f2f;
            font-family: 'Courier New', Courier, monospace;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .meta-table td {
            padding: 3px 4px;
            vertical-align: top;
            font-size: 10.5px;
        }
        .meta-label {
            font-weight: bold;
            width: 95px;
            color: #222;
        }
        .meta-line {
            border-bottom: 1px dotted #888;
            padding-bottom: 2px;
            font-weight: 500;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .items-table th {
            border: 1.5px solid #222;
            background-color: #ffebee;
            color: #b71c1c;
            padding: 6px 8px;
            text-transform: uppercase;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .items-table td {
            border-left: 1.5px solid #222;
            border-right: 1.5px solid #222;
            border-bottom: 1px dotted #ccc;
            padding: 6px 8px;
            vertical-align: top;
            font-size: 10px;
        }
        .items-table tr.last-row td {
            border-bottom: 1.5px solid #222;
        }
        .ref-box {
            background-color: #fafafa;
            border: 1px dashed #b71c1c;
            padding: 8px 12px;
            margin-top: 10px;
            font-size: 10px;
            line-height: 1.5;
        }
        .warning-text {
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            color: #b71c1c;
            text-transform: uppercase;
            margin: 12px 0 6px 0;
            letter-spacing: 0.5px;
        }
        .signatures-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .signatures-table td {
            vertical-align: top;
            padding: 4px;
        }
        .sig-underline {
            border-bottom: 1px solid #111;
            height: 28px;
            margin-bottom: 4px;
            text-align: center;
            font-weight: bold;
            line-height: 38px;
        }
        .sig-caption {
            font-size: 9px;
            text-align: center;
            color: #333;
            text-transform: uppercase;
        }
        .seal {
            border: 2px solid #1976d2;
            color: #1976d2;
            border-radius: 50%;
            width: 90px;
            height: 90px;
            text-align: center;
            margin: 0 auto;
            font-size: 8px;
            font-weight: bold;
            padding-top: 16px;
            box-sizing: border-box;
            transform: rotate(-10deg);
        }
        .acctg-badge {
            float: right;
            font-size: 12px;
            font-weight: bold;
            color: #1976d2;
            letter-spacing: 1px;
            border: 1.5px solid #1976d2;
            padding: 2px 8px;
            text-transform: uppercase;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>HUENICS INDUSTRIAL SALES INC.</h1>
        <p>916 Avida Towers Intima Zulueta St., Brgy. 678 Zone 74, Dist. V, 1007 Paco, NCR, City of Manila, First District, Philippines</p>
        <p class="tin">VAT Reg. TIN: 010-707-000-00000</p>
    </div>

    <table class="doc-title-row">
        <tr>
            <td class="doc-title">DELIVERY RECEIPT</td>
            <td class="doc-number">№ {{ $record->dr_number }}</td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Delivered to:</td>
            <td style="width: 55%;"><div class="meta-line">{{ strtoupper($record->customer_name ?: ($record->purchaseOrder?->customer_name ?? '—')) }}</div></td>
            <td class="meta-label" style="text-align: right; width: 60px;">Date:</td>
            <td><div class="meta-line">{{ $record->delivery_date ? $record->delivery_date->format('m - d - Y') : now()->format('m - d - Y') }}</div></td>
        </tr>
        <tr>
            <td class="meta-label">TIN:</td>
            <td><div class="meta-line">{{ $record->customer_tin ?: '005-129-052-00000' }}</div></td>
            <td class="meta-label" style="text-align: right;">Terms:</td>
            <td><div class="meta-line">{{ $record->terms ?: '—' }}</div></td>
        </tr>
        <tr>
            <td class="meta-label">Address:</td>
            <td colspan="3"><div class="meta-line">{{ strtoupper($record->delivery_address ?: ($record->purchaseOrder?->project?->location ?? '—')) }}</div></td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 14%; text-align: center;">QUANTITY</th>
                <th style="width: 12%; text-align: center;">UNIT</th>
                <th style="width: 74%; text-align: left;">ARTICLES</th>
            </tr>
        </thead>
        <tbody>
            @forelse($record->items as $index => $item)
            <tr class="{{ $loop->last ? 'last-row' : '' }}">
                <td style="text-align: center; font-weight: bold;">{{ number_format($item->qty_delivered, 0) }}</td>
                <td style="text-align: center; text-transform: uppercase;">{{ $item->unit ?: 'pcs' }}</td>
                <td>
                    <div style="font-weight: bold;">{{ strtoupper($item->description) }}</div>
                    @if(!empty($item->remarks) && $item->remarks !== $item->description)
                        <div style="font-size: 8.5px; color: #555; margin-top: 2px;">{{ $item->remarks }}</div>
                    @endif
                </td>
            </tr>
            @empty
            <tr class="last-row">
                <td colspan="3" style="text-align: center; padding: 20px; color: #888;">No line items specified</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="ref-box">
        <strong>{{ strtoupper($record->delivery_type ?? 'COMPLETE') }} DELIVERY</strong><br>
        <strong>PO#</strong> {{ $record->purchaseOrder?->po_number ?? '—' }} &nbsp;|&nbsp;
        <strong>SI#</strong> {{ $record->sales_invoice_numbers ?: '—' }} &nbsp;|&nbsp;
        <strong>RS#</strong> {{ $record->rs_number ?: '—' }}<br>
        <strong>PROJECT:</strong> {{ strtoupper($record->project_name ?: ($record->purchaseOrder?->project?->name ?? 'PALANZA TOWER')) }}
    </div>

    <div class="warning-text">"THIS DOCUMENT IS NOT VALID FOR CLAIM OF INPUT TAXES"</div>

    <table class="signatures-table">
        <tr>
            <td style="width: 25%;">
                <div class="sig-underline">{{ $record->prepared_by ?: 'Huenics Staff' }}</div>
                <div class="sig-caption">Prepared by</div>
            </td>
            <td style="width: 5%;"></td>
            <td style="width: 25%;">
                <div class="sig-underline">{{ $record->approved_by ?: 'Operations Manager' }}</div>
                <div class="sig-caption">Approved by</div>
            </td>
            <td style="width: 5%;"></td>
            <td style="width: 40%;">
                <div class="sig-underline">{{ $record->received_by ?: '________________________' }}</div>
                <div class="sig-caption">Received the above goods in good order & condition<br><strong>Customer Signature over Printed Name</strong></div>
                @if($record->received_date)
                    <div style="text-align: center; font-size: 8.5px; color: #444; margin-top: 2px;">Date: {{ $record->received_date->format('m-d-Y') }}</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="acctg-badge">ACCTG. COPY</div>
    <div style="clear: both;"></div>
</div>
</body>
</html>
