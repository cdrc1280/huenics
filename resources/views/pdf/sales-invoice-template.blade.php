<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Invoice - {{ $record->si_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; color: #000; }
        .header p { margin: 2px 0; font-size: 11px; }
        .title { text-align: center; font-size: 18px; font-weight: bold; margin: 20px 0; text-transform: uppercase; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 4px; vertical-align: top; }
        .info-label { font-weight: bold; width: 120px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .items-table th { background-color: #f5f5f5; text-transform: uppercase; font-size: 11px; }
        .items-table td.num { text-align: right; }
        .totals-table { width: 50%; float: right; border-collapse: collapse; margin-bottom: 30px; }
        .totals-table td { padding: 6px; border: 1px solid #000; }
        .totals-table td.label { font-weight: bold; text-align: right; background-color: #f5f5f5; }
        .totals-table td.amount { text-align: right; font-weight: bold; }
        .clear { clear: both; }
        .footer { margin-top: 50px; page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="header">
        <h1>HUENICS INDUSTRIAL SALES INC.</h1>
        <p>Manila Address | Contact Numbers: (02) 123-4567 | TIN: 000-000-000-000</p>
    </div>

    <div class="title">Sales Invoice</div>

    <table class="info-table">
        <tr>
            <td class="info-label">SI No:</td>
            <td><strong>{{ $record->si_number }}</strong></td>
            <td class="info-label">Invoice Date:</td>
            <td>{{ $record->invoice_date->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Customer Name:</td>
            <td>{{ $record->customer_name }}</td>
            <td class="info-label">Due Date:</td>
            <td>{{ $record->due_date ? $record->due_date->format('F d, Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="info-label">Billing Address:</td>
            <td>{{ $record->billing_address ?? 'N/A' }}</td>
            <td class="info-label">PO Ref / DR Ref:</td>
            <td>{{ $record->purchaseOrder->po_number }} / {{ $record->deliveryReceipt?->dr_number ?? 'N/A' }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 40%">Description</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 10%; text-align: center;">Unit</th>
                <th style="width: 15%; text-align: right;">Unit Price</th>
                <th style="width: 20%; text-align: right;">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td style="text-align: center;">{{ number_format($item->qty, 2) }}</td>
                <td style="text-align: center;">{{ $item->unit }}</td>
                <td class="num">₱ {{ number_format($item->unit_price, 2) }}</td>
                <td class="num">₱ {{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td class="label">Subtotal</td>
            <td class="amount">₱ {{ number_format($record->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label">12% VAT</td>
            <td class="amount">₱ {{ number_format($record->vat_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="label" style="font-size: 14px;">GRAND TOTAL</td>
            <td class="amount" style="font-size: 14px; color: #b30000;">₱ {{ number_format($record->total_amount, 2) }}</td>
        </tr>
    </table>
    
    <div class="clear"></div>

    <div style="margin-top: 20px;">
        <p><strong>Payment Status:</strong> {{ ucfirst($record->payment_status) }}</p>
        <p><strong>Notes:</strong> {{ $record->notes }}</p>
    </div>

    <div class="footer">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center;">
                    <div style="border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 5px; min-height: 20px;">
                        <!-- Signature here -->
                    </div>
                    <strong>Authorized Signature</strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
