<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Receipt - {{ $record->dr_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; color: #000; }
        .header p { margin: 2px 0; font-size: 11px; }
        .title { text-align: center; font-size: 18px; font-weight: bold; margin: 20px 0; text-transform: uppercase; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 4px; vertical-align: top; }
        .info-label { font-weight: bold; width: 120px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th, .items-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .items-table th { background-color: #f5f5f5; text-transform: uppercase; font-size: 11px; }
        .items-table td.qty { text-align: center; }
        .signatures { margin-top: 50px; width: 100%; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .sig-box { width: 45%; text-align: center; }
        .sig-line { border-top: 1px solid #000; margin-top: 50px; padding-top: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>HUENICS INDUSTRIAL SALES INC.</h1>
        <p>Manila Address | Contact Numbers: (02) 123-4567</p>
    </div>

    <div class="title">Delivery Receipt</div>

    <table class="info-table">
        <tr>
            <td class="info-label">DR No:</td>
            <td><strong>{{ $record->dr_number }}</strong></td>
            <td class="info-label">Date:</td>
            <td>{{ $record->delivery_date->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Customer Name:</td>
            <td>{{ $record->purchaseOrder->customer_name }}</td>
            <td class="info-label">PO Reference:</td>
            <td>{{ $record->purchaseOrder->po_number }}</td>
        </tr>
        <tr>
            <td class="info-label">Project/Address:</td>
            <td colspan="3">{{ $record->purchaseOrder->project?->name ?? 'N/A' }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%">Item #</th>
                <th style="width: 45%">Description</th>
                <th style="width: 15%">Qty Delivered</th>
                <th style="width: 10%">Unit</th>
                <th style="width: 25%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td class="qty">{{ number_format($item->qty_delivered, 2) }}</td>
                <td>{{ $item->unit }}</td>
                <td>{{ $item->remarks }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <p><strong>Remarks:</strong> {{ $record->remarks }}</p>
    </div>

    <table style="width: 100%; margin-top: 60px;">
        <tr>
            <td style="width: 45%; text-align: center;">
                <div style="border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 5px; min-height: 20px;">
                    {{ $record->delivered_by ?? '________________________________' }}
                </div>
                <strong>Delivered By</strong><br>
                <span style="font-size: 10px;">(Signature over printed name)</span>
            </td>
            <td style="width: 10%"></td>
            <td style="width: 45%; text-align: center;">
                <div style="border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 5px; min-height: 20px;">
                    {{ $record->received_by ?? '________________________________' }}
                </div>
                <strong>Received By</strong><br>
                <span style="font-size: 10px;">(Customer acknowledgement)</span>
            </td>
        </tr>
    </table>
</body>
</html>
