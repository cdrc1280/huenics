<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Invoice - {{ $record->si_number }}</title>
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
            background-color: #f7fbf8;
        }
        .container {
            border: 2px solid #2e7d32;
            padding: 16px;
            background-color: #fff;
            position: relative;
        }
        .header {
            text-align: center;
            border-bottom: 1.5px solid #2e7d32;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 {
            margin: 0 0 4px 0;
            font-size: 20px;
            letter-spacing: 0.5px;
            color: #2e7d32;
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
            font-size: 10px;
        }
        .meta-label {
            font-weight: bold;
            width: 85px;
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
            margin-bottom: 10px;
        }
        .items-table th {
            border: 1.5px solid #222;
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 5px 6px;
            text-transform: uppercase;
            font-size: 9.5px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .items-table td {
            border-left: 1.5px solid #222;
            border-right: 1.5px solid #222;
            border-bottom: 1px dotted #ccc;
            padding: 5px 6px;
            vertical-align: top;
            font-size: 9.5px;
        }
        .items-table tr.last-row td {
            border-bottom: 1.5px solid #222;
        }
        .split-box-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .split-box-table td {
            vertical-align: top;
        }
        .cross-ref-box {
            border: 1.5px solid #222;
            padding: 8px 10px;
            background-color: #fafafa;
            min-height: 140px;
            font-size: 10px;
            line-height: 1.6;
        }
        .tax-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #222;
        }
        .tax-table td {
            border: 1px solid #ccc;
            padding: 3.5px 6px;
            font-size: 9px;
        }
        .tax-label {
            background-color: #f9f9f9;
            font-weight: bold;
            width: 60%;
        }
        .tax-amount {
            text-align: right;
            font-weight: bold;
            font-family: 'Courier New', Courier, monospace;
        }
        .total-due-row td {
            background-color: #e8f5e9;
            border-top: 1.5px solid #2e7d32;
            color: #1b5e20;
            font-weight: 900;
            font-size: 10.5px;
        }
        .sig-section {
            margin-top: 25px;
            width: 100%;
            border-collapse: collapse;
        }
        .sig-underline {
            border-bottom: 1px solid #111;
            height: 26px;
            margin-bottom: 4px;
            text-align: center;
            font-weight: bold;
            line-height: 34px;
        }
        .sig-caption {
            font-size: 9px;
            text-align: center;
            color: #333;
            text-transform: uppercase;
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
            <td class="doc-title">SALES INVOICE</td>
            <td class="doc-number">№ {{ $record->si_number }}</td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Sold To:</td>
            <td style="width: 55%;"><div class="meta-line">{{ strtoupper($record->customer_name ?: ($record->purchaseOrder?->customer_name ?? '—')) }}</div></td>
            <td class="meta-label" style="text-align: right; width: 60px;">Date:</td>
            <td><div class="meta-line">{{ $record->invoice_date ? $record->invoice_date->format('M. d, Y') : now()->format('M. d, Y') }}</div></td>
        </tr>
        <tr>
            <td class="meta-label">TIN:</td>
            <td><div class="meta-line">{{ $record->customer_tin ?: '005-129-052-00000' }}</div></td>
            <td class="meta-label" style="text-align: right;">Terms:</td>
            <td><div class="meta-line">{{ $record->terms ?: '—' }}</div></td>
        </tr>
        <tr>
            <td class="meta-label">Address:</td>
            <td><div class="meta-line">{{ strtoupper($record->billing_address ?: ($record->purchaseOrder?->project?->location ?? '—')) }}</div></td>
            <td class="meta-label" style="text-align: right;">OSCA/PWD:</td>
            <td><div class="meta-line">{{ $record->osca_pwd_id ?: '—' }}</div></td>
        </tr>
        <tr>
            <td class="meta-label">Bus. Style:</td>
            <td colspan="3"><div class="meta-line">{{ strtoupper($record->business_style ?: ($record->customer_name ?: '—')) }}</div></td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 10%; text-align: center;">QTY.</th>
                <th style="width: 10%; text-align: center;">UNIT</th>
                <th style="width: 50%; text-align: left;">ARTICLES</th>
                <th style="width: 15%; text-align: right;">UNIT PRICE</th>
                <th style="width: 15%; text-align: right;">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($record->items as $index => $item)
            <tr class="{{ $loop->last ? 'last-row' : '' }}">
                <td style="text-align: center; font-weight: bold;">{{ number_format($item->qty, 0) }}</td>
                <td style="text-align: center; text-transform: uppercase;">{{ $item->unit ?: 'pcs' }}</td>
                <td>
                    <div style="font-weight: bold;">{{ strtoupper($item->description) }}</div>
                </td>
                <td style="text-align: right; font-family: 'Courier New', Courier, monospace;">{{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align: right; font-weight: bold; font-family: 'Courier New', Courier, monospace;">{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @empty
            <tr class="last-row">
                <td colspan="5" style="text-align: center; padding: 20px; color: #888;">No billable items specified</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="split-box-table">
        <tr>
            <td style="width: 48%; padding-right: 8px;">
                <div class="cross-ref-box">
                    <strong style="color: #2e7d32; text-decoration: underline;">CROSS REFERENCES:</strong><br><br>
                    <strong>PO#</strong> {{ $record->purchaseOrder?->po_number ?? '—' }}<br>
                    <strong>DR#</strong> {{ $record->delivery_receipt_numbers ?: ($record->deliveryReceipt?->dr_number ?? '—') }}<br>
                    <strong>RC#</strong> {{ $record->collection_receipt_numbers ?: '—' }}<br>
                    <strong>RS#</strong> {{ $record->rs_number ?: '—' }}
                </div>
            </td>
            <td style="width: 52%; padding-left: 8px;">
                @php
                    $subtotal = (float) $record->subtotal;
                    $discount = (float) ($record->discount_amount ?? 0);
                    $netOfVat = (float) ($record->net_of_vat ?: (($subtotal - $discount) / 1.12));
                    $vatAmount = (float) ($record->vat_amount ?: ($netOfVat * 0.12));
                    $totalDue = (float) ($record->total_amount ?: ($netOfVat + $vatAmount));
                @endphp
                <table class="tax-table">
                    <tr>
                        <td class="tax-label">Total Sales (VAT Inclusive)</td>
                        <td class="tax-amount">{{ number_format($subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="tax-label">Less: Discount</td>
                        <td class="tax-amount">{{ number_format($discount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="tax-label">Amount Net of VAT</td>
                        <td class="tax-amount">{{ number_format($netOfVat, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="tax-label">VATable Sales</td>
                        <td class="tax-amount">{{ number_format($netOfVat, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="tax-label">VAT-Exempt Sales</td>
                        <td class="tax-amount">{{ number_format((float) ($record->vat_exempt_sales ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="tax-label">Zero Rated Sales</td>
                        <td class="tax-amount">{{ number_format((float) ($record->zero_rated_sales ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="tax-label">VAT-Amount (12%)</td>
                        <td class="tax-amount">{{ number_format($vatAmount, 2) }}</td>
                    </tr>
                    @if((float)($record->withholding_tax ?? 0) > 0)
                    <tr>
                        <td class="tax-label">Less: Withholding Tax</td>
                        <td class="tax-amount">{{ number_format((float)$record->withholding_tax, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="total-due-row">
                        <td class="tax-label" style="background-color: #e8f5e9;">TOTAL AMOUNT DUE</td>
                        <td class="tax-amount">{{ number_format($totalDue, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="sig-section">
        <tr>
            <td style="width: 55%;">
                <div style="font-size: 9px; color: #555;">
                    Payment Status: <strong>{{ strtoupper($record->payment_status) }}</strong><br>
                    @if($record->notes)
                        Notes: {{ $record->notes }}
                    @endif
                </div>
            </td>
            <td style="width: 45%;">
                <div class="sig-underline">{{ $record->cashier_representative ?: 'Authorized Representative' }}</div>
                <div class="sig-caption">CASHIER / AUTHORIZED REPRESENTATIVE</div>
                @if($record->cashier_signature_date)
                    <div style="text-align: center; font-size: 8.5px; color: #444; margin-top: 2px;">Date: {{ $record->cashier_signature_date->format('m/d/Y') }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>
</body>
</html>
