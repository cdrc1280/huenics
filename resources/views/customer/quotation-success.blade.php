@extends('layouts.customer')

@section('title', 'Quotation Estimate Generated - ' . ($quote['quotation_number'] ?? 'UNOFF-ESTIMATE'))

@section('content')
<section class="py-12 bg-slate-100 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Success Alert / Action Banner (Hidden on Print) -->
        <div class="print:hidden bg-white border border-emerald-200 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl font-bold shrink-0">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900">
                        Unofficial Quotation Generated Successfully!
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Reference Number: <span class="font-mono font-bold text-slate-900">{{ $quote['quotation_number'] }}</span> &bull; Valid for 30 days
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                <a href="{{ route('customer.quotation.download-pdf', ['payload' => base64_encode(json_encode($quote))]) }}" 
                   class="flex-1 md:flex-initial bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center justify-center gap-2 shadow-sm">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Download PDF</span>
                </a>

                <button type="button" onclick="window.print()" 
                        class="flex-1 md:flex-initial bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs px-4 py-2.5 rounded-xl transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-print"></i>
                    <span>Print</span>
                </button>

                <a href="{{ route('customer.quotation-builder') }}" 
                   class="flex-1 md:flex-initial bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs px-3 py-2.5 rounded-xl transition flex items-center justify-center gap-1 border border-slate-300">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Edit Estimate</span>
                </a>
            </div>
        </div>

        <!-- Printable Document Container -->
        <div class="bg-white border border-slate-200 rounded-2xl p-8 sm:p-12 shadow-lg space-y-8 print:border-none print:shadow-none print:p-0">
            
            <!-- Document Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start gap-6 border-b border-slate-200 pb-6">
                <div>
                    <div class="text-xl font-extrabold text-slate-900 tracking-tight uppercase">
                        Huenics Industrial Sales Inc.
                    </div>
                    <div class="text-xs text-blue-600 font-semibold mt-0.5">
                        Colors &bull; Techniques &bull; Technology
                    </div>
                    <div class="text-xs text-slate-600 mt-2 space-y-0.5">
                        <div>916 Avida Towers Intima, Zulueta St., Brgy. 678, Zone 74, Dist. V, 1007 Paco, Manila</div>
                        <div>Telefax: (02) 8561-6836 &bull; CS: +63 968 8500720 &bull; Tech: +63 965 6287205</div>
                        <div>Email: huenicsindustrialsales@gmail.com &bull; crm.huenics777@gmail.com</div>
                    </div>
                </div>

                <div class="text-left sm:text-right space-y-1.5">
                    <span class="inline-block bg-amber-100 text-amber-900 border border-amber-300 font-bold text-xs px-3 py-1 rounded-md uppercase tracking-wider">
                        ★ Unofficial Quotation Estimate
                    </span>
                    <div class="text-sm font-bold font-mono text-slate-900">
                        {{ $quote['quotation_number'] }}
                    </div>
                    <div class="text-xs text-slate-500">
                        Date: <span class="font-semibold text-slate-700">{{ date('F d, Y', strtotime($quote['quotation_date'])) }}</span>
                    </div>
                    <div class="text-xs text-slate-500">
                        Valid Until: <span class="font-semibold text-slate-700">{{ date('F d, Y', strtotime($quote['valid_until'])) }}</span>
                    </div>
                </div>
            </div>

            <!-- Disclaimer Notice -->
            <div class="bg-amber-50/80 border-l-4 border-amber-500 p-4 rounded-r-lg text-xs text-slate-700 space-y-1">
                <div class="font-bold text-amber-900 uppercase">Notice to Customer / Estimator:</div>
                <p class="leading-relaxed text-[11px] text-slate-600">
                    This document is an automated customer estimate for preliminary budgeting and procurement planning. Final material availability, formal contract pricing, delivery commitments, and terms are subject to official review and approval by an authorized Huenics sales representative upon submission of a formal Purchase Order (PO).
                </p>
            </div>

            <!-- Customer & Project Info Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-5 rounded-xl border border-slate-200 text-xs">
                <div class="space-y-2">
                    <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] border-b border-slate-200 pb-1">
                        Customer Information
                    </h3>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500">Name:</span>
                        <span class="col-span-2 font-bold text-slate-800">{{ $quote['customer_name'] }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500">Company:</span>
                        <span class="col-span-2 text-slate-800">{{ $quote['customer_company'] }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500">Phone:</span>
                        <span class="col-span-2 text-slate-800">{{ $quote['phone_no'] }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500">Email:</span>
                        <span class="col-span-2 text-slate-800">{{ $quote['email'] }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] border-b border-slate-200 pb-1">
                        Project & Delivery
                    </h3>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500">Project:</span>
                        <span class="col-span-2 font-bold text-slate-800">{{ $quote['project_name'] }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500">Location:</span>
                        <span class="col-span-2 text-slate-800">{{ $quote['project_location'] }}</span>
                    </div>
                    @if(!empty($quote['notes']))
                    <div class="grid grid-cols-3 gap-1">
                        <span class="text-slate-500">Remarks:</span>
                        <span class="col-span-2 text-slate-800">{{ $quote['notes'] }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-slate-200">
                    <thead class="bg-slate-900 text-white font-semibold uppercase text-[11px]">
                        <tr>
                            <th class="py-2.5 px-3 w-10 text-center">#</th>
                            <th class="py-2.5 px-3 w-28">Item Code</th>
                            <th class="py-2.5 px-3">Description</th>
                            <th class="py-2.5 px-3 w-16 text-center">Qty</th>
                            <th class="py-2.5 px-3 w-16 text-center">Unit</th>
                            <th class="py-2.5 px-3 w-28 text-right">Unit Price (₱)</th>
                            <th class="py-2.5 px-3 w-32 text-right">Line Total (₱)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($quote['items'] as $index => $item)
                        <tr class="{{ $index % 2 === 1 ? 'bg-slate-50' : 'bg-white' }}">
                            <td class="py-2.5 px-3 text-center text-slate-400 font-bold">{{ $index + 1 }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-700">{{ $item['item_code'] }}</td>
                            <td class="py-2.5 px-3 font-medium text-slate-900">{{ $item['description'] }}</td>
                            <td class="py-2.5 px-3 text-center font-bold text-slate-800">{{ number_format($item['quantity'], 0) }}</td>
                            <td class="py-2.5 px-3 text-center text-slate-600">{{ $item['unit'] }}</td>
                            <td class="py-2.5 px-3 text-right text-slate-700">₱ {{ number_format($item['unit_price'], 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-bold text-slate-900">₱ {{ number_format($item['line_total'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals & VAT Breakdown -->
            <div class="flex justify-end">
                <div class="w-full sm:w-80 bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2.5 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Net Vatable Sales:</span>
                        <span class="font-bold text-slate-800">₱ {{ number_format($quote['subtotal'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>12% Philippine VAT:</span>
                        <span class="font-bold text-amber-700">₱ {{ number_format($quote['vat_amount'], 2) }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-300 flex justify-between items-baseline font-bold text-slate-900 text-sm">
                        <span>ESTIMATED GRAND TOTAL:</span>
                        <span class="text-blue-600 text-base font-black">₱ {{ number_format($quote['grand_total'], 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Terms & Standard Guidelines -->
            <div class="border-t border-slate-200 pt-6 text-[11px] text-slate-500 space-y-2">
                <div class="font-bold text-slate-800 uppercase tracking-wider text-xs">Standard Quotation Terms & Notes:</div>
                <ul class="list-disc list-inside space-y-1 leading-relaxed">
                    <li>To convert this estimate into an official binding Quotation / Purchase Order, email <strong>huenicsindustrialsales@gmail.com</strong> or contact CS: <strong>+63 968 8500720</strong>.</li>
                    <li>Minimum order should be <strong>Php 20,000.00 above</strong> for Free Delivery within Metro Manila. Outside Metro Manila shipment cost will be applied.</li>
                    <li>Prices are subject to change without prior notice (VAT INC.). Return & Exchange of items must be within 7 days upon delivery.</li>
                    <li>1 to 2 Years limited warranty without physical damage. 1 mo. outright replacement for defective units upon verification.</li>
                </ul>
            </div>

        </div>

    </div>
</section>
@endsection
