<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Bill of Quantities &amp; RFQ - {{ $quote['quotation_number'] ?? 'RFQ-INQUIRY' }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }
        .company-subtitle {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
            font-weight: 500;
        }
        .company-meta {
            font-size: 9.5px;
            color: #475569;
            margin-top: 4px;
            line-height: 1.35;
        }
        .estimate-badge-box {
            text-align: right;
        }
        .unofficial-badge {
            display: inline-block;
            background-color: #eff6ff;
            border: 1.5px solid #214fe0;
            color: #214fe0;
            font-size: 10px;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .ref-number {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 6px;
        }
        .ref-date {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Notice Banner */
        .disclaimer-box {
            background-color: #f8fafc;
            border-left: 4px solid #f59e0b;
            border-top: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 12px;
            margin-bottom: 15px;
            border-radius: 2px;
        }
        .disclaimer-title {
            font-weight: bold;
            color: #b45309;
            font-size: 9.5px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .disclaimer-text {
            font-size: 9px;
            color: #475569;
            line-height: 1.3;
        }

        /* Customer & Project Info Grid */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .info-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            padding: 5px 8px;
            border-bottom: 1px solid #cbd5e1;
        }
        .info-table td {
            padding: 4px 8px;
            font-size: 10px;
            color: #334155;
            vertical-align: top;
        }
        .info-label {
            font-weight: 600;
            color: #475569;
            width: 18%;
        }
        .info-val {
            color: #0f172a;
            width: 32%;
        }

        /* Line Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .items-table th {
            background-color: #214fe0;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 8px;
            border: 1px solid #214fe0;
            text-align: left;
        }
        .items-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 10px;
            vertical-align: middle;
        }
        .items-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* Totals Table */
        .totals-wrapper {
            width: 100%;
            margin-bottom: 18px;
        }
        .totals-table {
            width: 48%;
            float: right;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            font-size: 10px;
        }
        .totals-table tr.grand-total td {
            background-color: #214fe0;
            color: #ffffff;
            font-weight: bold;
            font-size: 12px;
            border-color: #214fe0;
        }
        .clear {
            clear: both;
        }

        /* Terms & Conditions */
        .terms-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            border-radius: 4px;
            margin-top: 10px;
        }
        .terms-title {
            font-weight: bold;
            color: #0f172a;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .terms-list {
            margin: 0;
            padding-left: 14px;
            font-size: 9px;
            color: #475569;
            line-height: 1.35;
        }

        /* Footer */
        .footer {
            margin-top: 25px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 8px;
            text-align: center;
            font-size: 8.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <!-- Header with Authentic HISI Logo from PDF -->
    <table class="header-table">
        <tr>
            <td style="width: 58%;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 52px; vertical-align: middle; border: none; padding: 0;">
                            <div style="border-left: 2.5px solid #214fe0; border-right: 2.5px solid #214fe0; padding: 3px 2px; text-align: center; background-color: #f0f4ff;">
                                <div style="font-size: 15px; font-weight: 900; color: #214fe0; letter-spacing: 1px; line-height: 1;">HISI</div>
                                <div style="font-size: 5px; font-weight: bold; color: #1e3a8a; margin-top: 2px;">Colors &bull; Tech</div>
                            </div>
                        </td>
                        <td style="vertical-align: middle; border: none; padding: 0 0 0 10px;">
                            <div class="company-title">Huenics Industrial Sales Inc.</div>
                            <div class="company-subtitle" style="color: #214fe0; font-weight: bold;">Colors &bull; Techniques &bull; Technology</div>
                        </td>
                    </tr>
                </table>
                <div class="company-meta" style="margin-top: 6px;">
                    Unit 916 Avida Towers Intima, 497 Pres. Quirino Ave. Ext. Cor. Zulueta St., Paco, Manila<br>
                    Tel. #8561 6836 &bull; CS: +63 968 8500720 &bull; Tech: +63 965 6287205<br>
                    Email: huenicsindustrialsales@gmail.com &bull; crm.huenics777@gmail.com
                </div>
            </td>
            <td style="width: 42%;" class="estimate-badge-box">
                <div class="unofficial-badge">Bill of Quantities &amp; RFQ</div>
                <div class="ref-number">Ref: {{ $quote['quotation_number'] ?? 'RFQ-'.date('Ymd-His') }}</div>
                <div class="ref-date">Generated: {{ isset($quote['quotation_date']) ? date('F d, Y', strtotime($quote['quotation_date'])) : now()->format('F d, Y') }}</div>
                <div class="ref-date">Inquiry Status: Pending Sales Review</div>
            </td>
        </tr>
    </table>

    <!-- Disclaimer Banner -->
    <div class="disclaimer-box">
        <div class="disclaimer-title">Bill of Quantities / Project Inquiry (Pricing Upon Request)</div>
        <div class="disclaimer-text">
            This document specifies item quantities, technical specifications, and project scope for official quotation evaluation. To protect trade margins and project volume tiers, commercial pricing and delivery timelines are officially computed and issued by Huenics Industrial Sales Inc. upon direct customer inquiry.
        </div>
    </div>

    <!-- Customer & Project Info -->
    <table class="info-table">
        <tr>
            <th colspan="2">Customer Details</th>
            <th colspan="2">Project & Delivery Details</th>
        </tr>
        <tr>
            <td class="info-label">Customer Name:</td>
            <td class="info-val">{{ $quote['customer_name'] ?? 'Walk-in Client' }}</td>
            <td class="info-label">Project Name:</td>
            <td class="info-val">{{ $quote['project_name'] ?? 'General Procurement' }}</td>
        </tr>
        <tr>
            <td class="info-label">Company:</td>
            <td class="info-val">{{ $quote['customer_company'] ?? 'Individual / Direct Buyer' }}</td>
            <td class="info-label">Location / Site:</td>
            <td class="info-val">{{ $quote['project_location'] ?? 'Metro Manila' }}</td>
        </tr>
        <tr>
            <td class="info-label">Contact Phone:</td>
            <td class="info-val">{{ $quote['phone_no'] ?? 'N/A' }}</td>
            <td class="info-label">Email:</td>
            <td class="info-val">{{ $quote['email'] ?? 'N/A' }}</td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 18%;">Item Code / SKU</th>
                <th style="width: 10%;" class="text-center">Image</th>
                <th style="width: 38%;">Product Description</th>
                <th style="width: 7%;" class="text-center">Qty</th>
                <th style="width: 7%;" class="text-center">Unit</th>
                <th style="width: 15%;" class="text-center">Commercial Pricing</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quote['items'] ?? [] as $index => $item)
            @php
                $prod = !empty($item['product_id']) ? \App\Models\Product::find($item['product_id']) : null;
                $prodImg = $prod?->base64_image;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $item['item_code'] ?? ($item['sku'] ?? 'GEN-ITEM') }}</td>
                <td class="text-center" style="vertical-align: middle; padding: 2px;">
                    @if($prodImg)
                        <img src="{{ $prodImg }}" style="max-height: 32px; max-width: 40px; object-fit: contain;">
                    @else
                        <span style="color: #94a3b8; font-size: 8px;">—</span>
                    @endif
                </td>
                <td>{{ $item['description'] ?? ($item['canonical_name'] ?? 'Product Line Item') }}</td>
                <td class="text-center font-bold">{{ number_format($item['quantity'] ?? $item['qty'] ?? 1, 0) }}</td>
                <td class="text-center">{{ $item['unit'] ?? ($item['unit_default'] ?? 'pcs') }}</td>
                <td class="text-center" style="color: #214fe0; font-weight: bold; font-size: 9.5px;">Quote Upon Request</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 15px; color: #64748b;">No line items added to this quotation request.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Totals Table -->
    <div class="totals-wrapper">
        <table class="totals-table">
            <tr>
                <td style="width: 55%;"><strong>Total Line Items:</strong></td>
                <td style="width: 45%;" class="text-right font-bold">{{ count($quote['items'] ?? []) }} item(s)</td>
            </tr>
            <tr>
                <td><strong>Tax Classification:</strong></td>
                <td class="text-right font-bold">12% BIR VAT-Inclusive</td>
            </tr>
            <tr>
                <td><strong>Commercial Pricing:</strong></td>
                <td class="text-right font-bold" style="color: #b45309;">Official Quote Upon Inquiry</td>
            </tr>
            <tr class="grand-total">
                <td><strong>PRICING STATUS:</strong></td>
                <td class="text-right">Awaiting Sales Review</td>
            </tr>
        </table>
        <div class="clear"></div>
    </div>

    @if(!empty($quote['notes']))
    <div style="margin-bottom: 12px; font-size: 9.5px; background: #fff; border: 1px solid #e2e8f0; padding: 6px 10px;">
        <strong>Customer Notes / Remarks:</strong> {{ $quote['notes'] }}
    </div>
    @endif

    <!-- Terms & Instructions -->
    <div class="terms-box">
        <div class="terms-title">Standard Terms & Conditions (Per Official Vendors Agreement):</div>
        <ol class="terms-list">
            <li><strong>Official Conversion:</strong> To convert this estimate into an official binding Quotation / Purchase Order, present reference number ({{ $quote['quotation_number'] ?? 'UNOFF-ESTIMATE' }}) or email <strong>huenicsindustrialsales@gmail.com</strong> / CS: <strong>+63 968 8500720</strong>.</li>
            <li><strong>Pricing:</strong> All prices are in Philippine Pesos (PHP) inclusive of 12% VAT (VAT INC.). Prices are subject to change without prior notice.</li>
            <li><strong>Free Delivery Policy:</strong> Minimum amount of order should be <strong>Php 20,000.00 above</strong> for Free Delivery within Metro Manila. Outside Metro Manila shipment cost will be applied.</li>
            <li><strong>Return & Exchange Policy:</strong> Return & exchange of items must be within <strong>7 days upon delivery</strong> with complete accessories, packaging, and sales invoice/warranty slip. Physical damage is not covered.</li>
            <li><strong>Warranty Terms:</strong> 1–2 Years limited warranty w/o physical damage. 1 mo. outright replacement for defective units (subject to stock availability); after 30 days subject to repair/replacement in 2-5 working days.</li>
        </ol>
    </div>

    <!-- Footer -->
    <div class="footer">
        Huenics Industrial Sales Inc. (HISI) &bull; Colors &bull; Techniques &bull; Technology &bull; Telefax: (02) 8561-6836 &bull; CS: +63 968 8500720
    </div>

</body>
</html>
