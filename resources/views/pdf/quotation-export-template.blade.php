<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Vendors Agreement Form - Quotation {{ $quotation->quotation_number }}</title>
    <style>
        @page {
            margin: 12px 14px;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 8px;
            color: #000000;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }
        .outer-border {
            border: 1.5px solid #000000;
            padding: 6px 8px;
        }
        .table-collapse {
            width: 100%;
            border-collapse: collapse;
        }
        .text-bold {
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-red {
            color: #dc2626;
        }
        .border-bottom-black {
            border-bottom: 1px solid #000000;
        }
        .line-items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            margin-bottom: 3px;
        }
        .line-items-table th {
            background-color: #d1d5db;
            border: 1px solid #000000;
            padding: 3px 4px;
            font-size: 7.5px;
            font-weight: bold;
        }
        .line-items-table td {
            border: 1px solid #000000;
            padding: 3.5px 4px;
            font-size: 8px;
            vertical-align: top;
        }
        .checkbox-box {
            border: 1px solid #000000;
            padding: 0 2px;
            display: inline-block;
            font-size: 7px;
            line-height: 9px;
            height: 9px;
            width: 9px;
            text-align: center;
            vertical-align: middle;
            margin-right: 2px;
        }
    </style>
</head>
<body>

    <div class="outer-border">

        {{-- 1. TOP LOGO & HEADER BANNER --}}
        <table class="table-collapse" style="margin-bottom: 4px;">
            <tr>
                <td style="width: 60%; border: 1.5px solid #1e3a8a; padding: 3px 6px;">
                    <table class="table-collapse">
                        <tr>
                            <td style="font-size: 20px; font-weight: bold; color: #1e3a8a; font-family: Arial, sans-serif; width: 68px; vertical-align: middle;">
                                [ HISI ]
                            </td>
                            <td style="vertical-align: middle;">
                                <div style="font-size: 11.5px; font-weight: bold; color: #1e3a8a; letter-spacing: 0.3px;">HUENICS INDUSTRIAL SALES INC.</div>
                                <div style="font-size: 7.5px; font-weight: bold; color: #2563eb;">Colors &bull; Techniques &bull; Technology</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 2%;"></td>
                <td style="width: 38%; background-color: #192756; text-align: center; vertical-align: middle; padding: 4px 6px;">
                    <div style="color: #ffffff; font-size: 12px; font-weight: bold; letter-spacing: 0.8px;">VENDORS AGREEMENT FORM</div>
                </td>
            </tr>
        </table>

        {{-- 2. HEADER METADATA (UNDERLINED FIELDS) --}}
        <table class="table-collapse" style="margin-bottom: 4px; font-size: 8px;">
            <tr>
                <td style="font-weight: bold; width: 90px; padding: 1.5px 0;">Quotation No.</td>
                <td style="border-bottom: 1px solid #000000; padding: 1.5px 3px; width: 230px; font-weight: bold;">
                    {{ $quotation->quotation_number }}
                </td>
                <td style="font-weight: bold; width: 50px; text-align: right; padding-right: 6px;">Date</td>
                <td style="border-bottom: 1px solid #000000; padding: 1.5px 3px; text-align: center; width: 90px;">
                    {{ $quotation->quotation_date ? $quotation->quotation_date->format('m/d/y') : now()->format('m/d/y') }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1.5px 0;">Customer Name</td>
                <td colspan="3" style="border-bottom: 1px solid #000000; padding: 1.5px 3px;">
                    {{ $quotation->customer_name ?: '—' }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1.5px 0;">Company</td>
                <td colspan="3" style="border-bottom: 1px solid #000000; padding: 1.5px 3px; font-weight: bold;">
                    {{ $quotation->customer_company ?: ($quotation->customer?->company_name ?? '—') }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1.5px 0;">Address</td>
                <td colspan="3" style="border-bottom: 1px solid #000000; padding: 1.5px 3px; font-size: 7.5px;">
                    {{ $quotation->address ?: ($quotation->project?->location ?: ($quotation->project_location ?: '2F Starmall Annex, Alabang-Zapote Road, corner Doña Manuela Avenue, Pamplona III, Las Pinas')) }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1.5px 0;">For Project</td>
                <td colspan="3" style="border-bottom: 1px solid #000000; padding: 1.5px 3px; font-weight: bold;">
                    {{ $quotation->project_name ?: ($quotation->project?->name ?? '—') }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1.5px 0;">Project Location</td>
                <td colspan="3" style="border-bottom: 1px solid #000000; padding: 1.5px 3px;">
                    {{ $quotation->project_location ?: ($quotation->project?->location ?? '—') }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1.5px 0;">Phone No.</td>
                <td colspan="3" style="border-bottom: 1px solid #000000; padding: 1.5px 3px;">
                    {{ $quotation->phone_no ?: '—' }}
                </td>
            </tr>
        </table>

        {{-- 3. LINE ITEMS TABLE --}}
        @php
            $hasItems = $quotation->lineItems && $quotation->lineItems->isNotEmpty();
            $calculatedSubtotal = 0;
        @endphp
        <table class="line-items-table">
            <thead>
                <tr>
                    <th style="width: 14%; text-align: left;">Item Code</th>
                    <th style="width: 44%; text-align: left;">Product Description</th>
                    <th style="width: 6%; text-align: center;">Qty</th>
                    <th style="width: 6%; text-align: center;">Unit</th>
                    <th style="width: 10%; text-align: right;">Unit Price</th>
                    <th style="width: 10%; text-align: right;">Discounted Price</th>
                    <th style="width: 10%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @if($hasItems)
                    @foreach($quotation->lineItems as $item)
                        @php
                            $qty = (float)($item->qty ?? 1);
                            $unitPrice = (float)($item->unit_price ?? 0);
                            $discPrice = $item->discounted_price !== null ? (float)$item->discounted_price : 0;
                            $effectivePrice = $discPrice > 0 ? $discPrice : $unitPrice;
                            $lineTot = (float)($item->line_total ?: ($qty * $effectivePrice));
                            $calculatedSubtotal += $lineTot;

                            $product = $item->product;
                            $prodImg = $product?->base64_image;
                        @endphp
                        <tr>
                            <td style="font-weight: bold;">
                                {{ $item->item_code ?: ($product?->sku ?? '—') }}
                            </td>
                            <td>
                                <div>{{ $item->description ?: ($product?->canonical_name ?? '—') }}</div>
                                @if($prodImg)
                                    <div style="margin-top: 3px;">
                                        <img src="{{ $prodImg }}" style="max-height: 32px; max-width: 45px; object-fit: contain;">
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                {{ fmod($qty, 1) === 0.0 ? number_format($qty, 0) : number_format($qty, 2) }}
                            </td>
                            <td style="text-align: center; text-transform: uppercase;">
                                {{ $item->unit ?: 'PC' }}
                            </td>
                            <td style="text-align: right;">
                                {{ number_format($unitPrice, 2) }}
                            </td>
                            <td style="text-align: right;">
                                {{ $discPrice > 0 ? number_format($discPrice, 2) : '—' }}
                            </td>
                            <td style="text-align: right; font-weight: bold;">
                                {{ number_format($lineTot, 2) }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" style="text-align: center; color: #9ca3af; font-style: italic; padding: 12px;">
                            No quotation line items recorded.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        {{-- 4. TOTALS BLOCK --}}
        @php
            $subtotal = (float)($quotation->total_amount ?: $calculatedSubtotal);
            $negotiated = $quotation->negotiated_amount ? (float)$quotation->negotiated_amount : null;
        @endphp
        <table style="width: 250px; margin-left: auto; border-collapse: collapse; margin-bottom: 3px; font-size: 8px;">
            <tr>
                <td style="font-weight: bold; text-align: right; width: 130px; padding: 1.5px 2px;">Total Amount</td>
                <td style="text-align: right; width: 120px; padding: 1.5px 2px; border-bottom: 1px solid #000000; font-weight: bold;">
                    PHP {{ number_format($subtotal, 2) }}
                </td>
            </tr>
            @if($negotiated !== null && $negotiated > 0)
                <tr>
                    <td style="font-weight: bold; text-align: right; padding: 1.5px 2px; color: #dc2626;">Negotiated Amount:</td>
                    <td style="text-align: right; padding: 1.5px 2px; color: #dc2626; font-weight: bold; border-bottom: 2px double #dc2626;">
                        PHP {{ number_format($negotiated, 2) }}
                    </td>
                </tr>
            @endif
        </table>

        <div style="font-size: 7.2px; font-weight: bold; margin-bottom: 4px;">
            Prices are subject to change without prior notice. (VAT INC.)
        </div>

        {{-- 5. TERMS AND CONDITIONS SECTION --}}
        @php
            $rawTerms = strtolower($quotation->terms_and_conditions ?? '');
            $payTerms = strtolower($quotation->payment_terms ?? '');
            $delTerms = strtolower($quotation->delivery_terms ?? '');

            $isStock = str_contains($rawTerms, 'stock') && !str_contains($rawTerms, 'non-stock');
            $is4To7 = str_contains($delTerms, '4-7') || str_contains($delTerms, '7');
            $is10To15 = str_contains($delTerms, '10-15') || str_contains($delTerms, '15');
            $is45To60 = str_contains($delTerms, '45-60') || str_contains($delTerms, '45') || str_contains($delTerms, '60') || (!$is4To7 && !$is10To15);

            $isApprovedTerms = str_contains($payTerms, 'approved') || str_contains($payTerms, 'credit');
            $isOfficialPo = (bool)$quotation->is_official_po || str_contains($rawTerms, 'official');
        @endphp
        <div style="border: 1px solid #000000; margin-bottom: 4px;">
            <div style="background-color: #cbd5e1; font-weight: bold; padding: 1.5px 4px; font-size: 7.5px; border-bottom: 1px solid #000000;">
                Terms and Conditions
            </div>
            <table class="table-collapse" style="font-size: 7px;">
                <tr>
                    <td style="width: 105px; font-weight: bold; padding: 1.5px 4px;">Validity</td>
                    <td style="padding: 1.5px 4px;">15 days</td>
                    <td style="padding: 1.5px 4px;"></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 1.5px 4px;">Stock Availability</td>
                    <td style="padding: 1.5px 4px;">
                        <span class="checkbox-box">{!! $isStock ? '&#10003;' : '&nbsp;' !!}</span> Stock
                    </td>
                    <td style="padding: 1.5px 4px;">
                        <span class="checkbox-box">{!! !$isStock ? '&#10003;' : '&nbsp;' !!}</span> Non-Stock / Special Items / Indent Order
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 1.5px 4px;">Terms Of Delivery</td>
                    <td style="padding: 1.5px 4px;">
                        <span class="checkbox-box">{!! $is4To7 ? '&#10003;' : '&nbsp;' !!}</span> 4-7 days
                    </td>
                    <td style="padding: 1.5px 4px;">
                        <span class="checkbox-box">{!! $is10To15 ? '&#10003;' : '&nbsp;' !!}</span> 10-15 days &nbsp;&nbsp;&nbsp;
                        <span class="checkbox-box">{!! $is45To60 ? '&#10003;' : '&nbsp;' !!}</span> 45-60 days
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 1.5px 4px;">Payment Terms</td>
                    <td style="padding: 1.5px 4px;">
                        <span class="checkbox-box">{!! !$isApprovedTerms ? '&#10003;' : '&nbsp;' !!}</span> COD / 50% DP ; 50% PDC 30 Days
                    </td>
                    <td style="padding: 1.5px 4px;">
                        <span class="checkbox-box">{!! $isApprovedTerms ? '&#10003;' : '&nbsp;' !!}</span> Approved Terms
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 1.5px 4px;">Remarks</td>
                    <td style="padding: 1.5px 4px;">
                        <span class="checkbox-box">{!! $isOfficialPo ? '&#10003;' : '&nbsp;' !!}</span> Serve as an Official P.O.
                    </td>
                    <td style="padding: 1.5px 4px;">
                        <span class="checkbox-box">&#10003;</span> Non-Returnable / Non-Cancelable
                    </td>
                </tr>
            </table>
        </div>

        {{-- 6. NOTES CALLOUT BOX --}}
        <div style="border: 1px solid #dc2626; padding: 2.5px 5px; margin-bottom: 4px; font-size: 6.8px; line-height: 1.15;">
            <div style="color: #dc2626; font-weight: bold; margin-bottom: 1px;">NOTES:</div>
            <div>* Minimum amount of order should be <span style="color: #dc2626; font-weight: bold;">Php 20,000.00</span> above for Free Delivery within Metro Manila. Outside Metro Manila Shipment cost will be applied.</div>
            <div>* Return &amp; Exchange of Items should be within <span style="color: #dc2626; font-weight: bold;">7 days upon delivery</span>.</div>
            <div>* Gate fees or any other entrance fees not included. Additional charges shall be applied for deliveries before or after office hour.</div>
            <div>* Please inspect item before installation. Complaints will not be entertained after items have been installed.</div>
            <div>* Special order, sale/phase out and non-regular items are not allowed for return.</div>
        </div>

        <div style="font-size: 6.8px; font-weight: bold; margin-bottom: 3px;">
            I/We hereby agree and accept the Terms and Conditions written above on this form.
        </div>

        {{-- 7. SIGNATURES & WARRANTY BLOCK --}}
        <table class="table-collapse" style="margin-bottom: 4px;">
            <tr>
                <td style="width: 58%; vertical-align: top; font-size: 7.2px; padding-right: 8px;">
                    <div>
                        <strong>Customer's Name over Signature:</strong> &nbsp;
                        <span style="border-bottom: 1px solid #000000; display: inline-block; min-width: 170px; font-weight: bold;">
                            {{ $quotation->customer_signature_name ?: ($quotation->is_official_po ? $quotation->customer_name : '') }}
                        </span>
                    </div>
                    <div style="margin-top: 10px;">
                        <table class="table-collapse">
                            <tr>
                                <td style="width: 80px; font-weight: bold; vertical-align: bottom;">Prepared by</td>
                                <td style="border-bottom: 1px solid #000000; vertical-align: bottom; min-width: 160px;">
                                    @if(isset($agentSignature) && $agentSignature)
                                        <div style="margin-bottom: -6px;">
                                            <img src="{{ $agentSignature }}" style="max-height: 36px; max-width: 140px; object-fit: contain;">
                                        </div>
                                    @endif
                                    <div style="font-weight: bold; font-size: 7.5px;">{{ $quotation->salesAgent?->name ?? 'Emmanuel Joshua Serrano' }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; vertical-align: bottom; padding-top: 6px;">Approved by</td>
                                <td style="border-bottom: 1px solid #000000; vertical-align: bottom; padding-top: 6px;">
                                    @if(isset($approverSignature) && $approverSignature)
                                        <div style="margin-bottom: -6px;">
                                            <img src="{{ $approverSignature }}" style="max-height: 36px; max-width: 140px; object-fit: contain;">
                                        </div>
                                    @endif
                                    <div style="font-weight: bold; font-size: 7.5px;">{{ $quotation->approver?->name ?? 'Mila S. De Guzman' }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="width: 42%; vertical-align: top;">
                    <div style="border: 1px solid #000000; padding: 3px 4px; font-size: 6.3px; background-color: #f8fafc; line-height: 1.15;">
                        <div style="font-weight: bold; text-align: center; border-bottom: 1px solid #000000; padding-bottom: 1px; margin-bottom: 2px;">How To Claim The Warranty</div>
                        <div>* <strong>1 Yr.</strong> Limited warranty w/o physical damage</div>
                        <div>* <strong>7 days</strong> item change policy provided that it must be in good condition w/ complete accessories.</div>
                        <div>* <strong>1 mo.</strong> Outright replacement if unit is found defective upon checking by Service Technicians.</div>
                        <div>* Items found defective after 30 days are subject for repair or replacement (2-5 working days).</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- 8. FOOTER CONTACT BAR --}}
        <table style="width: 100%; border: 1px solid #000000; border-collapse: collapse; font-size: 6.2px;">
            <tr style="background-color: #e5e7eb; border-bottom: 1px solid #000000; font-weight: bold;">
                <th style="border-right: 1px solid #000000; padding: 2px; width: 38%; text-align: center;">Customer Service No.</th>
                <th style="border-right: 1px solid #000000; padding: 2px; width: 37%; text-align: center;">Office Address</th>
                <th style="padding: 2px; width: 25%; text-align: center;">THE WARRANTY IS VOID IF:</th>
            </tr>
            <tr>
                <td style="border-right: 1px solid #000000; padding: 2.5px 4px; vertical-align: top; line-height: 1.15;">
                    Customer Service No.: +63 968 8500720<br>
                    Technical Service No.: +63 965 6287205<br>
                    Email: crm.huenics777@gmail.com
                </td>
                <td style="border-right: 1px solid #000000; padding: 2.5px 4px; vertical-align: top; line-height: 1.15;">
                    916 Avida Towers Intima Zulueta St., Brgy. 678 Zone 74 Dist. V, 1007 Paco, City of Manila<br>
                    Telefax No.: (02) 8561-6836 | Email: huenicsindustrialsales@gmail.com
                </td>
                <td style="padding: 2.5px 4px; vertical-align: top; line-height: 1.15;">
                    * Corroded due to moisture/dirt<br>
                    * Improperly used or mishandled<br>
                    * Dark spots, damaged, scratched, or dented
                </td>
            </tr>
        </table>

    </div>

</body>
</html>
