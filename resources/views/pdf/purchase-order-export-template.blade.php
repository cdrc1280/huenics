<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $purchaseOrder->po_number }}</title>
    <style>
        @page {
            margin: 10px 14px;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 7.5px;
            color: #000000;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }
        .outer-border {
            border: 1.5px solid #000000;
            padding: 5px 7px;
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
        .material-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            margin-bottom: 3px;
        }
        .material-table th {
            background-color: #f1f5f9;
            border: 1px solid #000000;
            padding: 3px 4px;
            font-size: 7.5px;
            font-weight: bold;
        }
        .material-table td {
            border: 1px solid #000000;
            padding: 3px 4px;
            font-size: 7.5px;
            vertical-align: top;
        }
        .meta-box {
            border: 1px solid #000000;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .meta-box td {
            padding: 2px 4px;
            font-size: 7.2px;
            vertical-align: top;
        }
        .covenants-box {
            border: 1px solid #000000;
            padding: 3px 5px;
            font-size: 6.3px;
            line-height: 1.15;
            margin-bottom: 4px;
        }
        .cert-footer {
            border-top: 1px solid #000000;
            padding-top: 2px;
            font-size: 6px;
            color: #334155;
            line-height: 1.1;
        }
    </style>
</head>
<body>

    <div class="outer-border">

        {{-- 1. HEADER & BUYER IDENTITY --}}
        <table class="table-collapse" style="margin-bottom: 3px;">
            <tr>
                <td style="width: 70%; vertical-align: top;">
                    <div style="font-size: 13px; font-weight: bold; color: #0f172a; text-transform: uppercase;">
                        {{ $purchaseOrder->customer_name ?: ($purchaseOrder->quotation?->customer_company ?: 'MGS CONSTRUCTION, INC.') }}
                    </div>
                    <div style="font-size: 7.2px; color: #334155;">
                        {{ $purchaseOrder->project?->location ?: ($purchaseOrder->quotation?->project_location ?: '2F Starmall Annex, Alabang-Zapote Road, Las Pinas City') }}
                    </div>
                    <div style="font-size: 7px; color: #334155;">
                        TIN: 000-482-911-000 &nbsp;|&nbsp; Tel. No.: {{ $purchaseOrder->quotation?->phone_no ?: '(02) 8874-5000' }}
                    </div>
                </td>
                <td style="width: 30%; text-align: right; vertical-align: top;">
                    <div style="font-size: 11px; font-weight: bold; color: #000000;">
                        No. <span style="color: #dc2626;">{{ $purchaseOrder->po_number }}</span>
                    </div>
                    <div style="font-size: 6.8px; color: #64748b; margin-top: 1px;">
                        Date: {{ $purchaseOrder->order_date ? $purchaseOrder->order_date->format('m/d/Y') : now()->format('m/d/Y') }}
                    </div>
                </td>
            </tr>
        </table>

        {{-- DOCUMENT TITLE --}}
        <div style="text-align: center; margin-bottom: 4px; padding: 2px 0; background-color: #f8fafc; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
            <span style="font-size: 13px; font-weight: bold; letter-spacing: 1.5px; color: #0f172a;">PURCHASE ORDER</span>
        </div>

        {{-- 2. TWO-COLUMN ORDER & VENDOR METADATA --}}
        <table class="meta-box">
            <tr>
                {{-- Vendor Details (Left Column) --}}
                <td style="width: 50%; border-right: 1px solid #000000; padding: 3px 5px;">
                    <table class="table-collapse">
                        <tr>
                            <td style="width: 85px; font-weight: bold;">Vendor:</td>
                            <td style="font-weight: bold; color: #1e3a8a;">HUENICS INDUSTRIAL SALES INC.</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Vendor Code:</td>
                            <td>V-00129</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Address:</td>
                            <td>916 Avida Towers Intima Zulueta St., Paco, Manila</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">TIN:</td>
                            <td>225-846-912-000 (VAT Reg.)</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Contact Person:</td>
                            <td>{{ $purchaseOrder->salesAgent?->name ?? 'Emmanuel Joshua Serrano' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Telephone / Mobile:</td>
                            <td>(02) 8561-6836 / +63 968 8500720</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Email:</td>
                            <td>huenicsindustrialsales@gmail.com</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Remarks:</td>
                            <td>{{ $purchaseOrder->notes ?: 'Official Purchase Order for Project Delivery' }}</td>
                        </tr>
                    </table>
                </td>

                {{-- Delivery & Billing Details (Right Column) --}}
                <td style="width: 50%; padding: 3px 5px;">
                    <table class="table-collapse">
                        <tr>
                            <td style="width: 95px; font-weight: bold;">Date:</td>
                            <td>{{ $purchaseOrder->order_date ? $purchaseOrder->order_date->format('m/d/Y') : now()->format('m/d/Y') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Delivery Date:</td>
                            <td style="font-weight: bold; color: #b45309;">
                                {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('m/d/Y') : 'As Scheduled (45-60 Days)' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Ref. PR / Quotation:</td>
                            <td style="font-weight: bold;">{{ $purchaseOrder->quotation?->quotation_number ?: 'PR-2026-0091' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Terms of Payment:</td>
                            <td>{{ $purchaseOrder->payment_terms ?: 'COD 50% DP; 50% PDC 30 Days' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Deliver To:</td>
                            <td style="font-weight: bold;">{{ $purchaseOrder->project?->name ?: ($purchaseOrder->quotation?->project_name ?: 'Palanza Tower Project') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Site Address:</td>
                            <td>{{ $purchaseOrder->project?->location ?: ($purchaseOrder->quotation?->project_location ?: 'Palanza St. cor. Guirayan St., Doña Imelda, Q.C.') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Attention To:</td>
                            <td>{{ $purchaseOrder->customer_name ?: 'Engr. Ronald Rey Sandoval' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Send Invoice To:</td>
                            <td>Finance &amp; Accounting Department</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- 3. MATERIAL LEDGER TABLE --}}
        @php
            $hasPoItems = $purchaseOrder->lineItems && $purchaseOrder->lineItems->isNotEmpty();
            $calculatedTotal = 0;
        @endphp
        <table class="material-table">
            <thead>
                <tr>
                    <th style="width: 7%; text-align: center;">Item No.</th>
                    <th style="width: 15%; text-align: left;">Material Code</th>
                    <th style="width: 8%; text-align: center;">Qty</th>
                    <th style="width: 7%; text-align: center;">UoM</th>
                    <th style="width: 39%; text-align: left;">Material Description</th>
                    <th style="width: 12%; text-align: right;">Unit Cost (PHP)</th>
                    <th style="width: 12%; text-align: right;">Total Cost (PHP)</th>
                </tr>
            </thead>
            <tbody>
                @if($hasPoItems)
                    @foreach($purchaseOrder->lineItems as $idx => $item)
                        @php
                            $lineNum = ($idx + 1) * 10;
                            $qty = (float)($item->qty ?? 1);
                            $unitPrice = (float)($item->unit_price ?? 0);
                            $discPrice = $item->discounted_price !== null ? (float)$item->discounted_price : 0;
                            $effectivePrice = $discPrice > 0 ? $discPrice : $unitPrice;
                            $lineTotal = (float)($item->line_total ?: ($qty * $effectivePrice));
                            $calculatedTotal += $lineTotal;

                            $product = $item->product;
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: bold;">{{ $lineNum }}</td>
                            <td style="font-weight: bold;">{{ $item->item_code ?: ($product?->sku ?? '—') }}</td>
                            <td style="text-align: center;">{{ fmod($qty, 1) === 0.0 ? number_format($qty, 0) : number_format($qty, 2) }}</td>
                            <td style="text-align: center; text-transform: uppercase;">{{ $item->unit ?: 'PC' }}</td>
                            <td>
                                <div style="font-weight: 500;">{{ $item->description ?: ($product?->canonical_name ?? 'Item Description') }}</div>
                                @if($purchaseOrder->has_warranty)
                                    <div style="font-size: 6.5px; color: #0284c7; margin-top: 1px;">
                                        Warranty: {{ $purchaseOrder->warranty_period ?: '1 Year' }} standard coverage
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: right;">{{ number_format($effectivePrice, 2) }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($lineTotal, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" style="text-align: center; color: #94a3b8; font-style: italic; padding: 10px;">
                            No purchase order material items recorded.
                        </td>
                    </tr>
                @endif

                {{-- NOTHING FOLLOWS SEPARATOR --}}
                <tr style="background-color: #fafafa;">
                    <td colspan="7" style="text-align: center; font-weight: bold; letter-spacing: 2px; font-size: 7px; padding: 3px 0; color: #64748b;">
                        ********************************* NOTHING FOLLOWS *********************************
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- 4. TOTALS BLOCK (SUBTOTAL, VAT, TOTAL) --}}
        @php
            $grandTotal = (float)($purchaseOrder->order_amount ?: $calculatedTotal);
            $netVat = round($grandTotal / 1.12, 2);
            $vatAmount = round($grandTotal - $netVat, 2);
        @endphp
        <table style="width: 260px; margin-left: auto; border-collapse: collapse; margin-bottom: 4px; font-size: 7.5px;">
            <tr>
                <td style="font-weight: bold; text-align: right; width: 140px; padding: 1.5px 4px;">SUBTOTAL (Net of VAT):</td>
                <td style="text-align: right; width: 120px; padding: 1.5px 4px; border-bottom: 1px solid #cbd5e1;">
                    PHP {{ number_format($netVat, 2) }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; text-align: right; padding: 1.5px 4px;">VAT (12%):</td>
                <td style="text-align: right; padding: 1.5px 4px; border-bottom: 1px solid #cbd5e1;">
                    PHP {{ number_format($vatAmount, 2) }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; text-align: right; padding: 2px 4px; font-size: 8.5px; color: #0f172a;">TOTAL COST (VAT INC.):</td>
                <td style="text-align: right; padding: 2px 4px; font-weight: bold; font-size: 8.5px; border-bottom: 2px double #000000;">
                    PHP {{ number_format($grandTotal, 2) }}
                </td>
            </tr>
        </table>

        {{-- 5. SIGNATURES BLOCK (5 COLUMNS) --}}
        <table style="width: 100%; border: 1px solid #000000; border-collapse: collapse; margin-bottom: 4px; text-align: center;">
            <tr style="background-color: #f1f5f9; border-bottom: 1px solid #000000; font-size: 6.8px; font-weight: bold;">
                <th style="width: 20%; border-right: 1px solid #000000; padding: 2px;">Prepared By:</th>
                <th style="width: 20%; border-right: 1px solid #000000; padding: 2px;">Checked By:</th>
                <th style="width: 20%; border-right: 1px solid #000000; padding: 2px;">Recommended By:</th>
                <th style="width: 20%; border-right: 1px solid #000000; padding: 2px;">Approved By:</th>
                <th style="width: 20%; padding: 2px;">Conformed By:</th>
            </tr>
            <tr style="height: 38px; font-size: 6.8px; vertical-align: bottom;">
                <td style="border-right: 1px solid #000000; padding: 2px 3px;">
                    <div style="font-weight: bold;">{{ $purchaseOrder->salesAgent?->name ?? 'Emmanuel Joshua Serrano' }}</div>
                    <div style="font-size: 6px; color: #64748b;">Procurement / Sales</div>
                </td>
                <td style="border-right: 1px solid #000000; padding: 2px 3px;">
                    <div style="font-weight: bold;">Engr. Carlos Mendoza</div>
                    <div style="font-size: 6px; color: #64748b;">QS / Cost Engineer</div>
                </td>
                <td style="border-right: 1px solid #000000; padding: 2px 3px;">
                    <div style="font-weight: bold;">Arch. Ferdinand Lopez</div>
                    <div style="font-size: 6px; color: #64748b;">Project Manager</div>
                </td>
                <td style="border-right: 1px solid #000000; padding: 2px 3px;">
                    <div style="font-weight: bold;">Mila S. De Guzman</div>
                    <div style="font-size: 6px; color: #64748b;">VP / Managing Director</div>
                </td>
                <td style="padding: 2px 3px;">
                    <div style="font-weight: bold; color: #1e3a8a;">HUENICS INDUSTRIAL</div>
                    <div style="font-size: 6px; color: #64748b;">Authorized Signature</div>
                </td>
            </tr>
        </table>

        {{-- 6. CONTRACTUAL COVENANTS (INSTRUCTIONS 1 TO 7) --}}
        <div class="covenants-box">
            <div style="font-weight: bold; margin-bottom: 1.5px; text-decoration: underline;">INSTRUCTIONS &amp; CONDITIONS TO VENDOR:</div>
            <div><strong>1.</strong> Duplicate copy of Delivery Receipt (DR) indicating Purchase Order and PR numbers must be surrendered upon receipt of materials.</div>
            <div><strong>2.</strong> Buyer reserves the right to inspect all deliveries and reject/return defective or substandard items at Vendor's exclusive cost.</div>
            <div><strong>3.</strong> All proprietary pricing, technical specifications, and project documents shall be kept strictly confidential.</div>
            <div><strong>4.</strong> Vendor warrants all delivered goods are brand new, genuine, and backed by the stipulated commercial warranty.</div>
            <div><strong>5.</strong> Liquidated damages of 1/10 of 1% per calendar day shall be assessed on any unexcused delivery delay beyond the specified delivery date.</div>
            <div><strong>6.</strong> All billings, official receipts, and invoices must strictly comply with Philippine BIR regulations and 12% VAT withholding laws.</div>
            <div><strong>7.</strong> Any legal dispute arising under this Purchase Order shall be settled exclusively within the proper courts of Metro Manila.</div>
        </div>

        {{-- 7. OFFICIAL CERTIFICATE FOOTER --}}
        <div class="cert-footer">
            <table class="table-collapse">
                <tr>
                    <td style="width: 50%;">
                        <div>This is a computer-generated document. Electronic verification is registered in the Huenics ERP ledger.</div>
                        <div>Date &amp; Time Generated: {{ now()->format('m/d/Y h:i:s A') }} &nbsp;|&nbsp; Page 1 of 1</div>
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <div><strong>Acknowledgement Certificate No. :</strong> AC_126_072024_000568</div>
                        <div>Date Issued : 07/29/2024 &nbsp;|&nbsp; Series Range : 4010000001 - 4019999999</div>
                        <div style="font-weight: bold; color: #b91c1c;">"THIS DOCUMENT IS NOT VALID FOR CLAIMING OF INPUT TAXES"</div>
                    </td>
                </tr>
            </table>
        </div>

    </div>

</body>
</html>
