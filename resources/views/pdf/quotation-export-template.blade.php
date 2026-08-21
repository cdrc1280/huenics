<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .details-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .details-table td { padding: 4px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #000; padding: 6px; }
        .totals { width: 50%; float: right; border-collapse: collapse; }
        .totals td { padding: 4px; border: 1px solid #000; }
        .signatures { clear: both; width: 100%; margin-top: 40px; border-collapse: collapse; }
        .signatures td { width: 33%; vertical-align: bottom; text-align: center; }
        .sign-line { border-bottom: 1px solid #000; width: 80%; margin: 0 auto 5px auto; height: 50px; }
        .sign-img { max-height: 50px; max-width: 100%; }
    </style>
</head>
<body>
    <div class="header">
        <h2>HISI Vendors Agreement Form - Quotation</h2>
    </div>
    
    <table class="details-table">
        <tr>
            <td width="15%"><strong>Quotation #:</strong></td>
            <td width="35%">{{ $quotation->quotation_number }}</td>
            <td width="15%"><strong>Date:</strong></td>
            <td width="35%">{{ $quotation->quotation_date ? $quotation->quotation_date->format('F d, Y') : now()->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td><strong>Customer:</strong></td>
            <td>{{ $quotation->customer_name }}</td>
            <td><strong>Company:</strong></td>
            <td>{{ $quotation->customer_company }}</td>
        </tr>
        <tr>
            <td><strong>Project:</strong></td>
            <td>{{ $quotation->project_name ?: ($quotation->project?->name ?? '—') }}</td>
            <td><strong>Location:</strong></td>
            <td>{{ $quotation->project_location ?: ($quotation->project?->location ?? '—') }}</td>
        </tr>
        <tr>
            <td><strong>Phone:</strong></td>
            <td>{{ $quotation->phone_no ?: '—' }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 14%;">Item Code</th>
                <th style="width: 12%;">Image</th>
                <th style="width: 32%;">Product Description</th>
                <th style="width: 6%;">Qty</th>
                <th style="width: 6%;">Unit</th>
                <th style="width: 10%;">Unit Price (₱)</th>
                <th style="width: 10%;">Disc Price (₱)</th>
                <th style="width: 10%;">Total (₱)</th>
            </tr>
        </thead>
        <tbody>
            @if($quotation->lineItems)
                @foreach($quotation->lineItems as $item)
                @php
                    $prodImage = $item->product?->base64_image;
                    $qty = $item->qty ?? ($item->quantity ?? 1);
                    $unitPrice = (float)$item->unit_price;
                    $discPrice = $item->discounted_price !== null ? (float)$item->discounted_price : ($item->discount_price !== null ? (float)$item->discount_price : 0);
                    $effPrice = $discPrice > 0 ? $discPrice : $unitPrice;
                    $lineTotal = (float)($item->line_total ?: ($qty * $effPrice));
                @endphp
                <tr>
                    <td><strong>{{ $item->item_code ?: '—' }}</strong></td>
                    <td style="text-align: center; vertical-align: middle; padding: 2px;">
                        @if($prodImage)
                            <img src="{{ $prodImage }}" style="max-height: 38px; max-width: 45px; object-fit: contain;">
                        @else
                            <span style="color: #94a3b8; font-size: 8px;">—</span>
                        @endif
                    </td>
                    <td>{{ $item->description }}</td>
                    <td style="text-align: center;">{{ $qty }}</td>
                    <td style="text-align: center;">{{ $item->unit }}</td>
                    <td style="text-align: right;">{{ number_format($unitPrice, 2) }}</td>
                    <td style="text-align: right;">{{ $discPrice > 0 ? number_format($discPrice, 2) : '—' }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($lineTotal, 2) }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td><strong>Subtotal:</strong></td>
            <td style="text-align: right;">₱ {{ number_format($quotation->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td><strong>12% VAT:</strong></td>
            <td style="text-align: right;">₱ {{ number_format($quotation->vat_amount, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Total Amount:</strong></td>
            <td style="text-align: right;">₱ {{ number_format($quotation->total_amount, 2) }}</td>
        </tr>
        @if($quotation->negotiated_amount)
        <tr>
            <td><strong>Negotiated Amount:</strong></td>
            <td style="text-align: right;">₱ {{ number_format($quotation->negotiated_amount, 2) }}</td>
        </tr>
        @endif
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="sign-line">
                    @if(isset($agentSignature) && $agentSignature)
                        <img src="{{ $agentSignature }}" class="sign-img">
                    @endif
                </div>
                <strong>Prepared by</strong><br>
                {{ $quotation->salesAgent?->name ?? 'Sales Agent' }}
            </td>
            <td>
                <div class="sign-line">
                    @if(isset($approverSignature) && $approverSignature)
                        <img src="{{ $approverSignature }}" class="sign-img">
                    @endif
                </div>
                <strong>Approved by</strong><br>
                {{ $quotation->approver?->name ?? 'Approver' }}
            </td>
            <td>
                <div class="sign-line">
                    @if($quotation->is_official_po)
                        [Signed]
                    @endif
                </div>
                <strong>Customer Signature</strong><br>
                {{ $quotation->customer_signature_name ?? $quotation->customer_name }}
            </td>
        </tr>
    </table>
</body>
</html>
