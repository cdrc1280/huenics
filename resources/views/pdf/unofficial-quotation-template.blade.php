<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Vendors Agreement Form - {{ $quote['quotation_number'] ?? 'HISI-QUOTATION' }}</title>
    <style>
        @page {
            margin: 10mm 12mm 8mm 12mm;
            size: A4 portrait;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5px;
            color: #111827;
            line-height: 1.25;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Top Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .logo-bracket {
            display: inline-block;
            border: 2px solid #1e3a8a;
            padding: 2px 7px;
            font-weight: 900;
            font-size: 22px;
            color: #1e3a8a;
            letter-spacing: 2px;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1;
        }

        .company-header-text {
            font-size: 16px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .company-tagline {
            font-size: 9.5px;
            color: #3b82f6;
            font-weight: 700;
            margin-top: 2px;
        }

        .vendors-banner {
            background-color: #1e285a;
            color: #ffffff;
            padding: 7px 12px;
            font-size: 13px;
            font-weight: 800;
            text-align: center;
            letter-spacing: 0.75px;
            text-transform: uppercase;
        }

        /* Customer & Project Details */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 6px;
        }

        .meta-table td {
            padding: 1.5px 0;
            vertical-align: bottom;
        }

        .meta-label {
            font-weight: bold;
            color: #000;
            white-space: nowrap;
        }

        .meta-val-underlined {
            border-bottom: 1px solid #000;
            padding: 1px 4px;
            color: #111;
        }

        /* Line Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-top: 4px;
        }

        .items-table th {
            border: 1px solid #000;
            background-color: #ffffff;
            font-weight: bold;
            font-size: 8.5px;
            padding: 3px 2px;
            text-align: center;
            color: #000;
        }

        .items-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 8.5px;
            vertical-align: top;
            color: #111;
        }

        .item-thumb {
            max-height: 32px;
            max-width: 48px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }


        /* Terms and Conditions */
        .terms-container {
            margin-top: 4px;
            font-size: 8px;
        }

        .terms-title {
            font-weight: bold;
            font-size: 8.5px;
            text-decoration: underline;
            margin-bottom: 2px;
        }

        .terms-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            font-size: 8px;
        }

        .terms-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            vertical-align: middle;
        }

        .terms-table .term-label {
            font-weight: bold;
            width: 16%;
            background-color: #ffffff;
        }

        /* Notes Box */
        .notes-box {
            border: 1px solid #000;
            padding: 3px 5px;
            margin-top: 4px;
            margin-bottom: 4px;
            font-size: 7.5px;
            line-height: 1.35;
        }

        /* Signatures & Warranty */
        .sig-warranty-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
        }

        .sig-warranty-table td {
            vertical-align: top;
        }

        .warranty-box {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 6.8px;
            line-height: 1.28;
        }

        .warranty-void-box {
            border: 1px solid #000;
            padding: 2px 4px;
            margin-top: 2.5px;
            text-align: center;
        }

        /* Two-Column Footer */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-top: 4px;
            font-size: 7.5px;
        }

        .footer-table td {
            padding: 2.5px 6px;
            vertical-align: top;
        }

        .footer-header {
            text-align: center;
            font-weight: bold;
            border-bottom: 0.5px solid #666;
            margin-bottom: 2px;
            padding-bottom: 1px;
        }

        /* Browser Print Controls */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0 !important;
                padding: 0 !important;
            }
        }

        .check-box-checked {
            display: inline-block;
            font-weight: bold;
            color: #000;
        }

        .check-box-empty {
            display: inline-block;
            color: #555;
        }
    </style>
</head>

<body>

    @if (!empty($isPrintView))
        <div class="no-print" style="position: fixed; top: 0; left: 0; right: 0; background: #0f172a; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.25); z-index: 99999; font-family: system-ui, -apple-system, sans-serif;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="background: #2563eb; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; letter-spacing: 0.5px;">
                    HUENICS INDUSTRIAL SALES
                </div>
                <span style="font-weight: 600; font-size: 13px;">Official Quotation (Vendors Agreement Form) &bull; Ref: {{ $quote['quotation_number'] ?? 'HISI-QUOTATION' }}</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <button onclick="window.print()" style="background: #2563eb; color: white; border: none; padding: 7px 14px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 6px;">
                    <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Quotation
                </button>
                <a href="{{ route('customer.quotation.download-pdf') }}" style="background: #059669; color: white; text-decoration: none; padding: 7px 14px; border-radius: 6px; font-weight: bold; font-size: 12px; display: flex; align-items: center; gap: 6px;">
                    <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download PDF
                </a>
                <button onclick="window.close()" style="background: #334155; color: white; border: none; padding: 7px 12px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 12px;">
                    Close
                </button>
            </div>
        </div>
        <div class="no-print" style="height: 52px;"></div>
        <script>
            window.addEventListener('load', function() {
                setTimeout(function() {
                    window.print();
                }, 400);
            });
        </script>
    @endif

    <!-- 1. Header: Logo & Vendors Agreement Banner -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <table style="border-collapse: collapse; width: auto;">
                    <tr>
                        <td style="vertical-align: middle;">
                            <div class="logo-bracket">[ HISI ]</div>
                        </td>
                        <td style="padding-left: 8px; vertical-align: middle;">
                            <div class="company-header-text">HUENICS INDUSTRIAL SALES INC.</div>
                            <div class="company-tagline">Colors &bull; Techniques &bull; Technology</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 36%; vertical-align: top; text-align: right;">
                <div class="vendors-banner">
                    VENDORS AGREEMENT FORM
                </div>
            </td>
        </tr>
    </table>

    <!-- 2. Customer & Project Meta Table (Underlined format matching reference PDF) -->
    <table class="meta-table">
        <tr>
            <td class="meta-label" style="width: 14%;">Quotation No.</td>
            <td class="meta-val-underlined" style="width: 50%; font-weight: bold;">
                {{ $quote['quotation_number'] ?? ('26' . date('md') . ' - P') }}
            </td>
            <td class="meta-label" style="width: 8%; text-align: right; padding-right: 6px;">Date</td>
            <td class="meta-val-underlined" style="width: 28%; text-align: center;">
                {{ isset($quote['quotation_date']) ? date('m/d/y', strtotime($quote['quotation_date'])) : date('m/d/y') }}
            </td>
        </tr>
        <tr>
            <td class="meta-label">Customer Name</td>
            <td colspan="3" class="meta-val-underlined">
                {{ $quote['customer_name'] ?? 'Walk-in Client' }}
            </td>
        </tr>
        <tr>
            <td class="meta-label">Company</td>
            <td colspan="3" class="meta-val-underlined">
                {{ $quote['customer_company'] ?? 'Direct Technical Buyer' }}
            </td>
        </tr>
        <tr>
            <td class="meta-label">Address</td>
            <td colspan="3" class="meta-val-underlined">
                {{ $quote['customer_address'] ?? ($quote['project_location'] ?? 'Metro Manila') }}
            </td>
        </tr>
        <tr>
            <td class="meta-label">For Project</td>
            <td colspan="3" class="meta-val-underlined">
                {{ $quote['project_name'] ?? 'General Procurement Project' }}
            </td>
        </tr>
        <tr>
            <td class="meta-label">Project Location</td>
            <td colspan="3" class="meta-val-underlined">
                {{ $quote['project_location'] ?? 'Metro Manila' }}
            </td>
        </tr>
        <tr>
            <td class="meta-label">Phone No.</td>
            <td colspan="3" class="meta-val-underlined">
                {{ $quote['phone_no'] ?? 'N/A' }}
            </td>
        </tr>
    </table>

    <!-- 3. Line Items Table (Columns: Item Code, Description, References from Client, Qty, Unit) -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 18%;">Item Code</th>
                <th style="width: 50%;">Product Description</th>
                <th style="width: 16%;">References from Client</th>
                <th style="width: 8%;">Qty</th>
                <th style="width: 8%;">Unit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quote['items'] ?? [] as $item)
                @php
                    $prod = !empty($item['product_id']) ? \App\Models\Product::find($item['product_id']) : null;
                    $prodImg = $item['base64_image'] ?? $prod?->base64_image;
                    $qty = (float) ($item['quantity'] ?? ($item['qty'] ?? 1));
                @endphp
                <tr>
                    <td style="font-weight: bold;">{{ $item['item_code'] ?? ($item['sku'] ?? 'GEN-ITEM') }}</td>
                    <td>
                        <div>{{ $item['description'] ?? ($item['canonical_name'] ?? 'Product Line Item') }}</div>
                        <div style="font-size: 7.5px; color: #555; margin-top: 1px;">Warranty: 2 yrs</div>
                    </td>
                    <td style="text-align: center; vertical-align: middle; padding: 2px;">
                        @if ($prodImg)
                            <img src="{{ $prodImg }}" class="item-thumb" alt="Ref">
                        @else
                            <span style="color: #9ca3af; font-size: 7.5px;">—</span>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($qty, 0) }}</td>
                    <td style="text-align: center;">{{ $item['unit'] ?? ($item['unit_default'] ?? 'pcs') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 12px; color: #6b7280;">No line items added to this quotation.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 4. Terms and Conditions (Exact Checkboxes from Reference PDF) -->
    <div class="terms-container">
        <div class="terms-title">Terms and Conditions</div>
        <table class="terms-table">
            <tr>
                <td class="term-label">Validity</td>
                <td colspan="3">15 days</td>
            </tr>
            <tr>
                <td class="term-label">Stock Availability</td>
                <td style="width: 42%;">
                    <span class="check-box-checked">&#10004;</span> Stock
                </td>
                <td colspan="2">
                    <span class="check-box-checked">&#10004;</span> Non-Stock / Special Items / Indent Order
                </td>
            </tr>
            <tr>
                <td class="term-label">Terms Of Delivery</td>
                <td>
                    <span class="check-box-empty">[ &nbsp; ]</span> 4-7 days &nbsp;&nbsp;&nbsp;&nbsp;
                    <span class="check-box-empty">[ &nbsp; ]</span> 10-15 days
                </td>
                <td colspan="2">
                    <span class="check-box-checked">&#10004;</span> 30-45days
                </td>
            </tr>
            <tr>
                <td class="term-label">Payment Terms</td>
                <td>
                    <span class="check-box-checked">&#10004;</span> 50% DP, BAL. COD PDC 30 Days
                </td>
                <td colspan="2">
                    <span class="check-box-empty">[ &nbsp; ]</span> PDC 30 Days / Approved Terms
                </td>
            </tr>
            <tr>
                <td class="term-label">Remarks</td>
                <td>
                    <span class="check-box-empty">[ &nbsp; ]</span> Serve as an Official P.O.
                </td>
                <td colspan="2">
                    <span class="check-box-checked">&#10004;</span> Non- Returnable/ Non- Cancealable
                </td>
            </tr>
        </table>
    </div>

    <!-- 5. NOTES Box (Exact 5 Bullets from Reference PDF) -->
    <div class="notes-box">
        <strong style="color: #b91c1c;">NOTES:</strong><br>
        * Minimum amount of order should be Php 20,000.00 above for Free Delivery within Metro Manila. Outside Metro Manila Shipment cost will be applied.<br>
        * Return &amp; Exchange of Items should be within 7 days upon delivery.<br>
        * Gate fees or any other entrance fees not included. Additional charges shall be applied for deliveries before or after office hour.<br>
        * Please inspect item before installation. Complaints will not be entertained after items have been installed.<br>
        * Special order, sale/phase out and non-regular items are not allowed for return.
    </div>

    <!-- 6. Agreement & Signatures + How To Claim The Warranty (Two Columns) -->
    <div style="font-size: 7.8px; font-weight: bold; margin-bottom: 2px;">
        I/We hereby agree and accept the Terms and Conditions written above on this form.
    </div>

    <table class="sig-warranty-table">
        <tr>
            <td style="width: 58%; padding-right: 12px;">
                <div style="margin-bottom: 10px; font-size: 8px;">
                    Customer's Name over Signature: ___________________________________
                </div>

                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; vertical-align: top;">
                            <div style="font-size: 7.5px; color: #444; margin-bottom: 14px;">Prepared by</div>
                            <div style="border-bottom: 1px solid #000; width: 140px; text-align: center; padding-bottom: 1px; font-weight: bold; font-size: 8px;">
                                Emmanuel Joshua Serrano
                            </div>
                        </td>
                        <td style="width: 50%; vertical-align: top;">
                            <div style="font-size: 7.5px; color: #444; margin-bottom: 14px;">Approved by</div>
                            <div style="border-bottom: 1px solid #000; width: 140px; text-align: center; padding-bottom: 1px; font-weight: bold; font-size: 8px;">
                                Mila S. De Guzman
                            </div>
                        </td>
                    </tr>
                </table>
            </td>

            <td style="width: 42%;">
                <div class="warranty-box">
                    <div style="text-align: center; font-weight: bold; border-bottom: 1px solid #000; margin-bottom: 2px; padding-bottom: 1px; font-size: 7.2px;">
                        How To Claim The Warranty
                    </div>
                    <u>&nbsp;&nbsp;1&nbsp;&nbsp;</u>Yr. Limited warranty w/o physical damage<br>
                    * 7 days item change policy provided that it must be in good Condition w/ complete accessories, packaging. Any form of Physical damage is not covered by 7 days replacement. Present items with sales invoice or warranty slip- upon checking by our Service /Technicians.<br>
                    * 1 mo. Outright replacement (depends on stock availability) if item/unit is found to be defective warranty slip- upon checking by our Service /Technicians.<br>
                    * Item/Units are found defective after 30 days are subject For repair or replacement. 2-5 working days:

                    <div class="warranty-void-box">
                        <div style="font-weight: bold; font-size: 6.8px;">THE WARRANTY IS VOID IF THE PRODUCT IS:</div>
                        <div style="font-size: 6.2px; color: #111;">
                            *Corroded due to moisture/dirt &bull; *Improperly used or mishandled<br>
                            *Items w/ dark spot, damaged, scratched and dented.
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- 7. Footer Box (Customer Service No. on left, Office Add on right) -->
    <table class="footer-table">
        <tr>
            <td style="width: 45%; border-right: 1px solid #000;">
                <div class="footer-header">Customer Service No.</div>
                Customer Service No.: +63 968 8500720<br>
                Technical Service No.: +63 965 6287205<br>
                Email : crm.huenics777@gmail.com
            </td>
            <td style="width: 55%;">
                <div class="footer-header">Office Add</div>
                916 Avida Towers Intima Zulueta St.Brgy.678 Zone 74 dist V 1007 Paco NCR, City of Manila First District Philippines, Manila<br>
                Telefax No.:(02) 8561-6836 &bull; Email: huenicsindustrialsales@gmail.com
            </td>
        </tr>
    </table>

</body>

</html>
