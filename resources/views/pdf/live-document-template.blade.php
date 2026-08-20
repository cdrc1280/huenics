<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vendors Agreement Form Preview</title>
    <style>
        @page {
            margin: 10px 14px;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8px;
            color: #000000;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }
        .highlight {
            background-color: #fef08a !important;
            border: 1px solid #d97706 !important;
            padding: 0 2px;
            font-weight: bold;
            color: #78350f;
        }
        .badge-mod {
            background-color: #d97706;
            color: #ffffff;
            font-size: 6px;
            font-weight: bold;
            padding: 0.5px 2px;
            margin-left: 2px;
            border-radius: 2px;
            vertical-align: middle;
        }
        .outer-border {
            border: 1.5px solid #000000;
            padding: 6px 8px;
        }
    </style>
</head>
<body>

    <div class="outer-border">

        {{-- TOP LOGO & HEADER BANNER --}}
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
            <tr>
                <td style="width: 60%; border: 1.5px solid #1e3a8a; padding: 3px 5px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 20px; font-weight: bold; color: #1e3a8a; font-family: Arial, sans-serif; width: 70px; vertical-align: middle;">
                                |HISI|
                            </td>
                            <td style="vertical-align: middle;">
                                <div style="font-size: 11px; font-weight: bold; color: #1e3a8a;">HUENICS INDUSTRIAL SALES INC.</div>
                                <div style="font-size: 7.5px; font-weight: bold; color: #2563eb;">Colors • Techniques • Technology</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 2%;"></td>
                <td style="width: 38%; background-color: #192756; text-align: center; vertical-align: middle; padding: 4px;">
                    <div style="color: #ffffff; font-size: 12px; font-weight: bold; letter-spacing: 0.5px;">VENDORS AGREEMENT FORM</div>
                </td>
            </tr>
        </table>

        {{-- HEADER METADATA --}}
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 8px;">
            <tr>
                <td style="font-weight: bold; width: 90px; padding: 1px 0;">Quotation No.</td>
                <td style="border-bottom: 1px solid #000; padding: 1px 2px; width: 230px;">
                    <span class="{{ !empty($mod['documentNumber']) ? 'highlight' : '' }}">
                        {{ $documentNumber ?: '261001- P' }}
                    </span>
                    @if(!empty($mod['documentNumber'])) <span class="badge-mod">MODIFIED</span> @endif
                </td>
                <td style="font-weight: bold; width: 50px; text-align: right; padding-right: 6px;">Date</td>
                <td style="border-bottom: 1px solid #000; padding: 1px 2px; text-align: center; width: 90px;">
                    <span class="{{ !empty($mod['documentDate']) ? 'highlight' : '' }}">
                        {{ $documentDate ?: '01/05/26' }}
                    </span>
                    @if(!empty($mod['documentDate'])) <span class="badge-mod">MODIFIED</span> @endif
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1px 0;">Customer Name</td>
                <td colspan="3" style="border-bottom: 1px solid #000; padding: 1px 2px;">
                    <span class="{{ !empty($mod['customerName']) ? 'highlight' : '' }}">
                        {{ $customerName ?: 'Engr. Ronald Rey Sandoval' }}
                    </span>
                    @if(!empty($mod['customerName'])) <span class="badge-mod">MODIFIED</span> @endif
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1px 0;">Company</td>
                <td colspan="3" style="border-bottom: 1px solid #000; padding: 1px 2px;">
                    <span class="{{ !empty($mod['customerCompany']) ? 'highlight' : '' }}">
                        {{ $customerCompany ?: 'MGS CONSTRUCTION, INC.' }}
                    </span>
                    @if(!empty($mod['customerCompany'])) <span class="badge-mod">MODIFIED</span> @endif
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1px 0;">Address</td>
                <td colspan="3" style="border-bottom: 1px solid #000; padding: 1px 2px; font-size: 7px;">
                    2F Starmall Annex, Alabang-Zapote Road, corner Doña Manuela Avenue, Pamplona III, Las Pinas,
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1px 0;">For Project</td>
                <td colspan="3" style="border-bottom: 1px solid #000; padding: 1px 2px;">
                    <span class="{{ !empty($mod['projectName']) ? 'highlight' : '' }}">
                        {{ $projectName ?: 'Palanza Tower' }}
                    </span>
                    @if(!empty($mod['projectName'])) <span class="badge-mod">MODIFIED</span> @endif
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1px 0;">Project Location</td>
                <td colspan="3" style="border-bottom: 1px solid #000; padding: 1px 2px;">
                    <span class="{{ !empty($mod['projectLocation']) ? 'highlight' : '' }}">
                        {{ $projectLocation ?: 'Palanza St. corner Guirayan st., Dona Imelda, Q.C' }}
                    </span>
                    @if(!empty($mod['projectLocation'])) <span class="badge-mod">MODIFIED</span> @endif
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1px 0;">Phone No.</td>
                <td colspan="3" style="border-bottom: 1px solid #000; padding: 1px 2px;">
                    <span class="{{ !empty($mod['phoneNo']) ? 'highlight' : '' }}">
                        {{ $phoneNo ?: '0906-144-2553' }}
                    </span>
                    @if(!empty($mod['phoneNo'])) <span class="badge-mod">MODIFIED</span> @endif
                </td>
            </tr>
        </table>

        @php
            $hasClientRef = count($items) > 3 || str_contains(strtolower($documentNumber ?? ''), 'rev');
        @endphp

        {{-- EXTRACTED LINE ITEMS TABLE --}}
        <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000; margin-bottom: 3px;">
            <thead>
                <tr style="background-color: #d1d5db; border-bottom: 1.5px solid #000; font-size: 7.5px; font-weight: bold;">
                    <th style="border-right: 1px solid #000; padding: 3px 4px; width: 14%;">Item Code</th>
                    <th style="border-right: 1px solid #000; padding: 3px 4px; width: {{ $hasClientRef ? '34%' : '48%' }};">Product Description</th>
                    @if($hasClientRef)
                        <th style="border-right: 1px solid #000; padding: 3px 4px; width: 14%;">References from Client</th>
                    @endif
                    <th style="border-right: 1px solid #000; padding: 3px 4px; width: 5%; text-align: center;">Qty</th>
                    <th style="border-right: 1px solid #000; padding: 3px 4px; width: 5%; text-align: center;">Unit</th>
                    <th style="border-right: 1px solid #000; padding: 3px 4px; width: 9.5%; text-align: right;">Unit Price</th>
                    <th style="border-right: 1px solid #000; padding: 3px 4px; width: 9.5%; text-align: right;">Discounted Price</th>
                    <th style="padding: 3px 4px; width: 9%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @forelse($items as $idx => $item)
                    @php
                        $isModLine = !empty($mod['items'][$idx]);
                        $effPrice = (!empty($item['discounted_price']) && (float)$item['discounted_price'] > 0) ? (float)$item['discounted_price'] : (float)($item['unit_price'] ?? 0);
                        $lineTot = (float)($item['printed_total'] ?? ($item['qty'] * $effPrice));
                        $grandTotal += $lineTot;
                    @endphp
                    <tr style="border-bottom: 1px solid #000; font-size: 8px; {{ $isModLine ? 'background-color: #fffbeb;' : '' }}">
                        <td style="border-right: 1px solid #000; padding: 4px; font-weight: bold; vertical-align: top;">
                            <span class="{{ !empty($mod['items'][$idx]['material_code']) ? 'highlight' : '' }}">
                                {{ $item['material_code'] ?: '—' }}
                            </span>
                        </td>
                        <td style="border-right: 1px solid #000; padding: 4px; vertical-align: top;">
                            <span class="{{ !empty($mod['items'][$idx]['description']) ? 'highlight' : '' }}">
                                {{ $item['description'] }}
                            </span>
                        </td>
                        @if($hasClientRef)
                            <td style="border-right: 1px solid #000; padding: 4px; vertical-align: top; text-align: center; color: #64748b; font-style: italic;">
                                —
                            </td>
                        @endif
                        <td style="border-right: 1px solid #000; padding: 4px; text-align: right; vertical-align: top;">
                            <span class="{{ !empty($mod['items'][$idx]['qty']) ? 'highlight' : '' }}">
                                {{ $item['qty'] }}
                            </span>
                        </td>
                        <td style="border-right: 1px solid #000; padding: 4px; text-align: center; uppercase vertical-align: top;">
                            <span class="{{ !empty($mod['items'][$idx]['unit']) ? 'highlight' : '' }}">
                                {{ $item['unit'] }}
                            </span>
                        </td>
                        <td style="border-right: 1px solid #000; padding: 4px; text-align: right; vertical-align: top;">
                            <span class="{{ !empty($mod['items'][$idx]['unit_price']) ? 'highlight' : '' }}">
                                {{ number_format((float)($item['unit_price'] ?? 0), 2) }}
                            </span>
                        </td>
                        <td style="border-right: 1px solid #000; padding: 4px; text-align: right; vertical-align: top;">
                            <span class="{{ !empty($mod['items'][$idx]['discounted_price']) ? 'highlight' : '' }}">
                                {{ number_format((float)($item['discounted_price'] ?? 0), 2) }}
                            </span>
                        </td>
                        <td style="padding: 4px; text-align: right; vertical-align: top; font-weight: bold;">
                            <span class="{{ !empty($mod['items'][$idx]['printed_total']) ? 'highlight' : '' }}">
                                {{ number_format($lineTot, 2) }}
                            </span>
                            @if($isModLine) <span class="badge-mod">MODIFIED</span> @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $hasClientRef ? 8 : 7 }}" style="text-align: center; color: #9ca3af; font-style: italic; padding: 12px;">No line items extracted</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- TOTALS BLOCK --}}
        <table style="width: 240px; margin-left: auto; border-collapse: collapse; margin-bottom: 3px; font-size: 8px;">
            <tr>
                <td style="font-weight: bold; text-align: right; width: 130px; padding: 1.5px 2px;">Total Amount</td>
                <td style="text-align: right; width: 110px; padding: 1.5px 2px; border-bottom: 1px solid #000; font-weight: bold;">
                    {{ number_format($grandTotal, 2) }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; text-align: right; padding: 1.5px 2px; color: #dc2626;">Negotiated Amount:</td>
                <td style="text-align: right; padding: 1.5px 2px; color: #dc2626; text-decoration: underline; font-weight: bold; border-bottom: 2px double #dc2626;">
                    {{ number_format($grandTotal > 1000000 ? 1050000 : ($grandTotal > 900000 ? 950000 : $grandTotal), 2) }}
                </td>
            </tr>
        </table>

        <div style="font-size: 7px; font-weight: bold; margin-bottom: 4px;">
            Prices are subject to change without prior notice. (VAT INC.)
        </div>

        {{-- TERMS AND CONDITIONS SECTION --}}
        <div style="border: 1px solid #000; margin-bottom: 4px;">
            <div style="background-color: #cbd5e1; font-weight: bold; padding: 1.5px 4px; font-size: 7.5px; border-bottom: 1px solid #000;">
                Terms and Conditions
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 7px;">
                <tr>
                    <td style="width: 100px; font-weight: bold; padding: 1px 3px;">Validity</td>
                    <td style="padding: 1px 3px;">15 days</td>
                    <td></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 1px 3px;">Stock Availability</td>
                    <td style="padding: 1px 3px;"><span style="border: 1px solid #000; padding: 0 2px; display: inline-block;">&nbsp;</span> Stock</td>
                    <td style="padding: 1px 3px;"><span style="border: 1px solid #000; padding: 0 1.5px; display: inline-block; font-weight: bold;">✔</span> Non-Stock / Special Items/Indent Order</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 1px 3px;">Terms Of Delivery</td>
                    <td style="padding: 1px 3px;"><span style="border: 1px solid #000; padding: 0 2px; display: inline-block;">&nbsp;</span> 4-7 days</td>
                    <td style="padding: 1px 3px;"><span style="border: 1px solid #000; padding: 0 2px; display: inline-block;">&nbsp;</span> 10-15 days &nbsp;&nbsp;&nbsp; <span style="border: 1px solid #000; padding: 0 1.5px; display: inline-block; font-weight: bold;">✔</span> {{ $hasClientRef ? '30-45days' : '45-60 days' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 1px 3px;">Payment Terms</td>
                    <td style="padding: 1px 3px;"><span style="border: 1px solid #000; padding: 0 1.5px; display: inline-block; font-weight: bold;">✔</span> COD / 50% DP ; 50% PDC 30 Days</td>
                    <td style="padding: 1px 3px;"><span style="border: 1px solid #000; padding: 0 2px; display: inline-block;">&nbsp;</span> Approved Terms</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 1px 3px;">Remarks</td>
                    <td style="padding: 1px 3px;"><span style="border: 1px solid #000; padding: 0 2px; display: inline-block;">&nbsp;</span> Serve as an Official P.O.</td>
                    <td style="padding: 1px 3px;"><span style="border: 1px solid #000; padding: 0 1.5px; display: inline-block; font-weight: bold;">✔</span> Non- Returnable/ Non- Cancealable</td>
                </tr>
            </table>
        </div>

        {{-- NOTES CALLOUT BOX --}}
        <div style="border: 1px solid #dc2626; padding: 2px 4px; margin-bottom: 4px; font-size: 6.8px; line-height: 1.15;">
            <div style="color: #dc2626; font-weight: bold; margin-bottom: 1px;">NOTES:</div>
            <div>* Minimum amount of order should be <span style="color: #dc2626; font-weight: bold;">Php 20,000 .00</span> above for Free Delivery within Metro Manila. Outside Metro Manila Shipment cost will be applied.</div>
            <div>* Return & Exchange of Items should be within <span style="color: #dc2626; font-weight: bold;">7 days upon delivery</span>.</div>
            <div>* Gate fees or any other entrance fees not included. Additional charges shall be applied for deliveries before or after office hour.</div>
            <div>* Please inspect item before installation. Complaints will not be entertained after items have been installed.</div>
            <div>* Special order, sale/phase out and non-regular items are not allowed for return.</div>
        </div>

        <div style="font-size: 6.8px; font-weight: bold; margin-bottom: 3px;">
            I/We hereby agree and accept the Terms and Conditions written above on this form.
        </div>

        {{-- SIGNATURES & WARRANTY --}}
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 3px;">
            <tr>
                <td style="width: 58%; vertical-align: top; font-size: 7px;">
                    <div><strong>Customer's Name over Signature:</strong> ____________________</div>
                    <br>
                    <div><strong>Prepared by</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span style="border-bottom: 1px solid #000; display: inline-block; width: 150px;">Emmanuel Joshua Serrano</span></div>
                    <div style="margin-top: 2px;"><strong>Approved by</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span style="border-bottom: 1px solid #000; display: inline-block; width: 150px;">Mila S. De Guzman</span></div>
                </td>
                <td style="width: 42%; vertical-align: top;">
                    <div style="border: 1px solid #000; padding: 2px 3px; font-size: 6.2px; background-color: #f8fafc; line-height: 1.1;">
                        <div style="font-weight: bold; text-align: center; border-bottom: 1px solid #000; padding-bottom: 1px; margin-bottom: 1px;">How To Claim The Warranty</div>
                        <div>* __1__Yr. Limited warranty w/o physical damage</div>
                        <div>* 7 days item change policy provided that it must be in good Condition w/ complete accessories.</div>
                        <div>* 1 mo. Outright replacement if unit is found defective upon checking by Service Technicians.</div>
                        <div>* Items found defective after 30 days are subject For repair or replacement. 2-5 working days.</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- FOOTER CONTACT BAR --}}
        <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 6.2px;">
            <tr style="background-color: #e5e7eb; border-bottom: 1px solid #000; font-weight: bold;">
                <th style="border-right: 1px solid #000; padding: 1.5px; width: 40%; text-align: center;">Customer Service No.</th>
                <th style="border-right: 1px solid #000; padding: 1.5px; width: 35%; text-align: center;">Office Add</th>
                <th style="padding: 1.5px; width: 25%; text-align: center;">THE WARRANTY IS VOID IF THE PRODUCT IS:</th>
            </tr>
            <tr>
                <td style="border-right: 1px solid #000; padding: 2px; vertical-align: top; line-height: 1.1;">
                    Customer Service No.: +63 968 8500720<br>
                    Technical Service No.: +63 965 6287205<br>
                    Email : crm.huenics777@gmail.com
                </td>
                <td style="border-right: 1px solid #000; padding: 2px; vertical-align: top; line-height: 1.1;">
                    916 Avida Towers Intima Zulueta St.Brgy.678 Zone 74 dist V 1007 Paco NCR, City of Manila<br>
                    Telefax No.:(02) 8561-6836 | Email: huenicsindustrialsales@gmail.com
                </td>
                <td style="padding: 2px; vertical-align: top; line-height: 1.1;">
                    *Corroded due to moisture/dirt<br>
                    *Improperly used or mishandled<br>
                    *Items w/ dark spot, damaged, scratched and dented.
                </td>
            </tr>
        </table>

    </div>

</body>
</html>
