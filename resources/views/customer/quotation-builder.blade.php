@extends('layouts.customer')

@section('title', 'Instant Quotation Builder - Huenics Industrial Sales Inc.')

@section('content')
<!-- Header Banner (PDF Crisp White in Light / Sleek Obsidian in Dark) -->
<section class="bg-white dark:bg-[#070b14] ambient-mesh-hero py-10 border-b border-slate-200 dark:border-slate-800/80 relative overflow-hidden hisi-geometric-accent transition-colors duration-200 animate-fade-in-up">
    <!-- Diagonal Stripes Accent -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-[#214fe0]/15 dark:from-blue-500/10 via-blue-500/5 to-transparent pointer-events-none"></div>
    <div class="absolute -bottom-10 -left-10 w-64 h-64 pointer-events-none opacity-25 dark:opacity-15" style="background: repeating-linear-gradient(45deg, rgba(33, 79, 224, 0.08), rgba(33, 79, 224, 0.08) 3px, transparent 3px, transparent 12px);"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 bg-blue-50 dark:bg-blue-950/70 text-[#214fe0] dark:text-[#60a5fa] border border-blue-200 dark:border-blue-800/60 px-3 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Commercial Estimate Generator</span>
                </span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-950 dark:text-white mt-2">
                    Commercial Quotation & BOQ Builder
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 max-w-2xl font-normal">
                    Assemble line items, specify project specifications, and submit for formal commercial quotation or download your itemized Bill of Quantities (BOQ).
                </p>
            </div>
            <a href="{{ route('customer.products') }}" 
               class="inline-flex items-center gap-2 bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl shadow-sm dark:shadow-[0_0_15px_rgba(33,79,224,0.3)] btn-interactive transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Add More From Catalog</span>
            </a>
        </div>
    </div>
</section>

<!-- Quotation Builder Workspace -->
<section class="py-10 bg-slate-50 dark:bg-[#070b14] transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <form id="quotation-form" method="POST" action="{{ route('customer.quotation.generate') }}" target="_blank">
            @csrf
            <input type="hidden" name="action" id="form-action" value="view">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Left Column (8 cols): Line Items Table & Add Custom Items -->
                <div class="lg:col-span-8 space-y-6">
                    
                    <!-- Line Items Card -->
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden transition-colors">
                        <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-slate-50/50 dark:bg-[#161f38]/60">
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                    <span>Selected Line Items</span>
                                </h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Specify quantities and project materials. Official pricing is provided upon review by our sales engineering desk.</p>
                            </div>

                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <button type="button" 
                                        id="btn-add-product"
                                        onclick="addProductRow()" 
                                        class="flex-1 sm:flex-initial bg-[#214fe0] hover:bg-[#1a42be] text-white text-xs font-bold px-3.5 py-2 rounded-lg btn-interactive transition flex items-center justify-center gap-1.5 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    <span>Add Product</span>
                                </button>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 dark:bg-[#161f38] text-slate-700 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800">
                                        <th class="py-3 px-3 w-10 text-center">#</th>
                                        <th class="py-3 px-3 min-w-[220px]">Product / Description</th>
                                        <th class="py-3 px-3 w-24 text-center">Unit</th>
                                        <th class="py-3 px-3 w-24 text-center">Qty</th>
                                        <th class="py-3 px-3 text-center">Commercial Pricing</th>
                                        <th class="py-3 px-3 w-10 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody id="items-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
                                    <!-- Bento Pulse Skeleton Placeholder (Replaced by JS on cart load) -->
                                    @for($sk = 0; $sk < 3; $sk++)
                                    <tr class="bento-pulse">
                                        <td class="py-4 px-3 text-center"><div class="h-4 w-5 rounded bg-slate-200/80 dark:bg-slate-700/70 mx-auto"></div></td>
                                        <td class="py-4 px-3">
                                            <div class="h-4 w-{{ ['2/3', '3/4', '1/2'][$sk] }} rounded bg-slate-200/80 dark:bg-slate-700/70 mb-1.5"></div>
                                            <div class="h-3 w-1/3 rounded bg-slate-200/50 dark:bg-slate-800/60"></div>
                                        </td>
                                        <td class="py-4 px-3 text-center"><div class="h-5 w-14 rounded bg-slate-200/70 dark:bg-slate-700/70 mx-auto"></div></td>
                                        <td class="py-4 px-3 text-center"><div class="h-7 w-16 rounded-lg bg-slate-200/80 dark:bg-slate-700/80 mx-auto"></div></td>
                                        <td class="py-4 px-3 text-center"><div class="h-5 w-24 rounded-full bg-blue-100/70 dark:bg-blue-950/70 mx-auto"></div></td>
                                        <td class="py-4 px-3 text-center"><div class="h-6 w-6 rounded bg-slate-200/60 dark:bg-slate-700/60 mx-auto"></div></td>
                                    </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State Container -->
                        <div id="empty-cart-state" class="hidden text-center py-12 px-4">
                            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-[#161f38] text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/></svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Your quotation estimate is currently empty</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                                Add products from our catalog or enter custom line items to compute your project totals.
                            </p>
                            <div class="mt-4 flex justify-center gap-3">
                                <button type="button" id="btn-empty-add-product" onclick="addProductRow()" class="bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold text-xs px-4 py-2 rounded-lg transition shadow-sm flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    <span>Add Product</span>
                                </button>
                            </div>
                        </div>

                        <!-- Clear / Reset Table Footer -->
                        <div class="p-3 bg-slate-50 dark:bg-[#161f38]/40 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center text-xs">
                            <span class="text-slate-500 dark:text-slate-400" id="table-row-count">0 items selected</span>
                            <button type="button" onclick="clearAllItems()" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-semibold hover:underline flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Clear All Items</span>
                            </button>
                        </div>
                    </div>

                    <!-- Customer & Project Information Card -->
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4 transition-colors">
                        <div class="border-b border-slate-200 dark:border-slate-800 pb-3">
                            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                <span>Customer & Project Information</span>
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">These details will appear on the generated quotation header.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Customer / Contact Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="customer_name" required placeholder="e.g. Engr. Roberto Santos" title="Customer or Contact Name"
                                       class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-slate-50 dark:bg-[#161f38] text-slate-900 dark:text-white focus:bg-white dark:focus:bg-[#161f38] font-medium">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Company / Contractor Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="customer_company" required placeholder="e.g. MGS Construction & Supply Corp." title="Company or Contractor Name"
                                       class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-slate-50 dark:bg-[#161f38] text-slate-900 dark:text-white focus:bg-white dark:focus:bg-[#161f38]">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Contact Phone / Mobile <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" name="phone_no" required placeholder="e.g. 0917-123-4567 / (02) 8987-6543" title="Contact Phone Number" pattern="^[0-9\-\+\(\)\s]*$"
                                       class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-slate-50 dark:bg-[#161f38] text-slate-900 dark:text-white focus:bg-white dark:focus:bg-[#161f38]">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Email Address
                                </label>
                                <input type="email" name="email" placeholder="e.g. procurement@contractor.ph" title="Email Address"
                                       class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-slate-50 dark:bg-[#161f38] text-slate-900 dark:text-white focus:bg-white dark:focus:bg-[#161f38]">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Project Name
                                </label>
                                <input type="text" name="project_name" placeholder="e.g. Palanza Tower High-Rise Project" title="Project Name"
                                       class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-slate-50 dark:bg-[#161f38] text-slate-900 dark:text-white focus:bg-white dark:focus:bg-[#161f38]">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Jobsite / Delivery Location
                                </label>
                                <input type="text" name="project_location" placeholder="e.g. Palanza St. cor. Santol, Quezon City" title="Jobsite Delivery Location"
                                       class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-slate-50 dark:bg-[#161f38] text-slate-900 dark:text-white focus:bg-white dark:focus:bg-[#161f38]">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Special Notes / Delivery Instructions
                                </label>
                                <textarea name="notes" rows="2" placeholder="e.g. Staggered delivery required. Schedule 40 pipes must include manufacturer test certificates." title="Special Notes or Delivery Instructions"
                                          class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-slate-50 dark:bg-[#161f38] text-slate-900 dark:text-white focus:bg-white dark:focus:bg-[#161f38]"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (4 cols): Summary & Actions -->
                <div class="lg:col-span-4 space-y-6 sticky top-24">
                    
                    <!-- Procurement Summary Card -->
                    <div class="bg-slate-900 dark:bg-[#0c1220] text-white border border-slate-800 rounded-2xl p-6 shadow-xl space-y-5">
                        <div class="border-b border-slate-800 pb-3 flex justify-between items-center">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300">
                                Procurement Summary
                            </h3>
                            <span class="text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2 py-0.5 rounded">
                                Quote Upon Inquiry
                            </span>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between text-slate-300">
                                <span>Selected Line Items:</span>
                                <span id="summary-items-count" class="font-bold text-white text-sm font-mono tabular-nums">0 items</span>
                            </div>

                            <div class="flex justify-between text-slate-300">
                                <span>Commercial Pricing:</span>
                                <span class="font-bold text-amber-400 text-xs">Official Quote Upon Inquiry</span>
                            </div>

                            <div class="flex justify-between text-slate-300">
                                <span>Tax Classification:</span>
                                <span class="font-bold text-slate-200 text-xs">12% BIR VAT-Inclusive (Official SI)</span>
                            </div>

                            <div class="pt-3 border-t border-slate-800 flex justify-between items-baseline">
                                <div>
                                    <div class="text-xs uppercase font-extrabold text-slate-400">Pricing Schedule:</div>
                                    <div class="text-[10px] text-slate-500">(Direct Sales Inquiry)</div>
                                </div>
                                <span class="font-black text-sm text-[#60a5fa] uppercase tracking-wider">Awaiting Inquiry</span>
                            </div>
                        </div>

                        <!-- Notice Box -->
                        <div class="bg-slate-800/80 dark:bg-[#161f38]/80 rounded-xl p-3.5 border border-slate-700 text-[11px] text-slate-400 leading-relaxed">
                            <div class="flex items-center gap-1.5 text-amber-400 font-bold mb-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Commercial Pricing Notice</span>
                            </div>
                            Official project pricing, volume tier discounts, and delivery schedules are calculated and issued by Huenics sales executives upon inquiry. Submit your request below or download an itemized BOQ.
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-2.5 pt-2">
                            <!-- Primary Official Request Button -->
                            <button type="submit" 
                                    onclick="document.getElementById('form-action').value='request_quotation'"
                                    class="w-full bg-emerald-600 hover:bg-emerald-500 btn-interactive text-white font-bold py-3 px-4 rounded-xl shadow-lg hover:shadow-emerald-500/25 transition-all duration-200 flex items-center justify-center gap-2 text-xs sm:text-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                <span>Request Official Quotation (Encode)</span>
                            </button>

                            <button type="submit" 
                                    onclick="document.getElementById('form-action').value='download_pdf'"
                                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 btn-interactive text-white font-bold py-2.5 px-4 rounded-xl shadow-md transition-all duration-200 flex items-center justify-center gap-2 text-xs sm:text-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Download Itemized BOQ PDF</span>
                            </button>

                            <button type="submit" 
                                    onclick="document.getElementById('form-action').value='preview_pdf'"
                                    class="w-full bg-slate-800 hover:bg-slate-700 btn-interactive text-slate-200 border border-slate-700 font-semibold py-2.5 px-4 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 text-xs">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Preview BOQ in New Tab</span>
                            </button>

                            <button type="submit" 
                                    onclick="document.getElementById('form-action').value='view'"
                                    class="w-full bg-transparent hover:bg-slate-800 btn-interactive text-slate-300 hover:text-white font-medium py-2 px-4 rounded-xl transition text-xs flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>View Web Summary &amp; Print</span>
                            </button>
                        </div>
                    </div>

                    <!-- Direct Help Box -->
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm text-xs space-y-2 transition-colors">
                        <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>Need Specialized Items or Bidding?</span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                            For specialized lighting items, indent orders, or project bidding, email your requirements to <strong>huenicsindustrialsales@gmail.com</strong> or call <strong>(02) 8561-6836</strong> / <strong>+63 968 8500720</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

@endsection

@push('scripts')
<script>
    // Master database catalog products passed from controller
    const catalogProducts = @json($catalogProducts);

    // State management for builder rows
    let currentItems = [];

    function escapeHtml(string) {
        return String(string || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function initBuilder() {
        currentItems = CartManager.getCart();
        
        // If empty, initialize with the first item from the authentic DB catalog
        if (currentItems.length === 0) {
            if (catalogProducts && catalogProducts.length > 0) {
                const defaultDbItem = catalogProducts[0];
                currentItems.push({
                    product_id: defaultDbItem.id,
                    item_code: defaultDbItem.sku || defaultDbItem.product_code || 'HISI-PROD',
                    description: defaultDbItem.canonical_name,
                    quantity: 10,
                    unit: defaultDbItem.unit_default || 'pcs',
                    unit_price: 0,
                    line_total: 0
                });
                CartManager.saveCart(currentItems);
            }
        } else {
            // Reconcile items to ensure valid product_id and DB unit
            currentItems.forEach(item => {
                let matched = null;
                if (item.product_id) {
                    matched = catalogProducts.find(p => String(p.id) === String(item.product_id));
                } else if (item.description) {
                    matched = catalogProducts.find(p => p.canonical_name === item.description);
                }

                if (matched) {
                    item.product_id = matched.id;
                    item.item_code = matched.sku || matched.product_code || item.item_code;
                    item.description = matched.canonical_name;
                    item.unit = matched.unit_default || 'pcs';
                } else if (catalogProducts && catalogProducts.length > 0 && !item.product_id) {
                    item.product_id = catalogProducts[0].id;
                    item.item_code = catalogProducts[0].sku || catalogProducts[0].product_code || 'HISI-PROD';
                    item.description = catalogProducts[0].canonical_name;
                    item.unit = catalogProducts[0].unit_default || 'pcs';
                }
            });
            CartManager.saveCart(currentItems);
        }

        renderRows();
    }

    function renderRows() {
        const tbody = document.getElementById('items-tbody');
        const emptyState = document.getElementById('empty-cart-state');
        const countEl = document.getElementById('table-row-count');
        const addBtn = document.getElementById('btn-add-product');
        const emptyAddBtn = document.getElementById('btn-empty-add-product');

        if (!tbody) return;

        tbody.innerHTML = '';

        // Determine all currently selected product IDs across all rows
        const allSelectedIds = currentItems
            .filter(it => it.product_id)
            .map(it => String(it.product_id));

        const isCatalogFull = catalogProducts && allSelectedIds.length >= catalogProducts.length;

        [addBtn, emptyAddBtn].forEach(btn => {
            if (!btn) return;
            if (isCatalogFull) {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.title = 'All catalog products have been selected';
            } else {
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                btn.removeAttribute('title');
            }
        });

        if (currentItems.length === 0) {
            emptyState.classList.remove('hidden');
            countEl.textContent = '0 items selected';
            updateSummaryTotals(0);
            return;
        }

        emptyState.classList.add('hidden');
        countEl.textContent = `${currentItems.length} item(s) selected`;

        currentItems.forEach((item, index) => {
            item.unit_price = 0;
            item.line_total = 0;

            // Product IDs selected in other rows (excluding current row)
            const selectedInOtherRows = currentItems
                .filter((it, idx) => idx !== index && it.product_id)
                .map(it => String(it.product_id));

            // Exclude products already selected in other rows
            const availableOptions = (catalogProducts || []).filter(p => 
                !selectedInOtherRows.includes(String(p.id)) || String(p.id) === String(item.product_id)
            );

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 dark:hover:bg-[#161f38]/60 transition border-b border-slate-100 dark:border-slate-800';
            tr.innerHTML = `
                <td class="py-2.5 px-3 text-center text-slate-400 dark:text-slate-500 font-bold">${index + 1}</td>
                <td class="py-2.5 px-3">
                    <input type="hidden" name="items[${index}][product_id]" value="${item.product_id || ''}">
                    <input type="hidden" name="items[${index}][item_code]" value="${escapeHtml(item.item_code || '')}">
                    <input type="hidden" name="items[${index}][description]" value="${escapeHtml(item.description || '')}">
                    <select required onchange="onProductSelect(${index}, this.value)"
                            class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded text-xs font-semibold focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white dark:bg-[#161f38] text-slate-900 dark:text-white">
                        <option value="" disabled ${!item.product_id ? 'selected' : ''}>-- Select Product --</option>
                        ${availableOptions.map(p => `
                            <option value="${p.id}" ${item.product_id == p.id ? 'selected' : ''}>
                                ${escapeHtml(p.canonical_name)} (${escapeHtml(p.sku || p.product_code || 'HISI')})
                            </option>
                        `).join('')}
                    </select>
                </td>
                <td class="py-2.5 px-3 text-center">
                    <input type="hidden" name="items[${index}][unit]" value="${escapeHtml(item.unit || 'pcs')}">
                    <select disabled class="w-24 text-center px-1.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded text-xs bg-slate-100 dark:bg-[#161f38] text-slate-700 dark:text-slate-300 font-bold cursor-not-allowed opacity-90">
                        <option value="${escapeHtml(item.unit || 'pcs')}" selected>${escapeHtml(item.unit || 'pcs')}</option>
                    </select>
                </td>
                <td class="py-2.5 px-3 text-center">
                    <input type="number" name="items[${index}][quantity]" value="${item.quantity}" min="0.01" step="any" required
                           onchange="updateItemField(${index}, 'quantity', this.value)"
                           oninput="updateItemField(${index}, 'quantity', this.value)"
                           class="w-20 text-center px-2 py-1.5 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold font-mono tabular-nums text-slate-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white dark:bg-[#161f38]">
                </td>
                <td class="py-2.5 px-3 text-center">
                    <input type="hidden" name="items[${index}][unit_price]" value="0">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-[#214fe0] dark:text-[#60a5fa] bg-blue-50 dark:bg-blue-950/70 border border-blue-200/80 dark:border-blue-800/60 px-2.5 py-1 rounded-full whitespace-nowrap">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Quote Upon Request
                    </span>
                </td>
                <td class="py-2.5 px-3 text-center">
                    <button type="button" onclick="deleteRow(${index})" class="text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition p-1" title="Remove item">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        updateSummaryTotals(currentItems.length);
        CartManager.saveCart(currentItems);
    }

    function addProductRow() {
        if (!catalogProducts || catalogProducts.length === 0) {
            showToast('Notice', 'No catalog products available.');
            return;
        }

        // Find products already selected in any existing row
        const selectedIds = currentItems
            .filter(it => it.product_id)
            .map(it => String(it.product_id));

        // Find first catalog product that has NOT been selected yet
        const nextAvailableProd = catalogProducts.find(p => !selectedIds.includes(String(p.id)));

        if (!nextAvailableProd) {
            HuenicsModal.alert({
                title: 'All Products Added',
                message: 'All available products from the master catalog have already been included in your quotation request.',
                icon: 'check',
                type: 'primary',
                buttonText: 'Got It'
            });
            return;
        }

        currentItems.push({
            product_id: nextAvailableProd.id,
            item_code: nextAvailableProd.sku || nextAvailableProd.product_code || 'HISI-PROD',
            description: nextAvailableProd.canonical_name,
            quantity: 1,
            unit: nextAvailableProd.unit_default || 'pcs',
            unit_price: 0,
            line_total: 0
        });

        renderRows();
        showToast('Product Added', `Added "${nextAvailableProd.canonical_name}" to quotation.`);
    }

    function onProductSelect(index, productId) {
        const prod = catalogProducts.find(p => String(p.id) === String(productId));
        if (!prod || !currentItems[index]) return;

        currentItems[index].product_id = prod.id;
        currentItems[index].item_code = prod.sku || prod.product_code || 'HISI-PROD';
        currentItems[index].description = prod.canonical_name;
        currentItems[index].unit = prod.unit_default || 'pcs';

        renderRows();
    }

    function updateItemField(index, field, value) {
        if (!currentItems[index]) return;

        if (field === 'quantity') {
            currentItems[index].quantity = parseFloat(value) || 0;
        } else if (field === 'unit_price') {
            currentItems[index].unit_price = 0;
        } else {
            currentItems[index][field] = value;
        }

        renderRows();
    }

    function deleteRow(index) {
        const item = currentItems[index];
        const itemName = item?.description || 'this line item';

        HuenicsModal.confirm({
            title: 'Remove Line Item?',
            message: `Are you sure you want to remove "${itemName}" from this quotation request?`,
            icon: 'trash',
            type: 'danger',
            confirmText: 'Remove Item',
            cancelText: 'Keep Item',
            onConfirm: () => {
                currentItems.splice(index, 1);
                renderRows();
                showToast('Item Removed', `"${itemName}" removed from quotation.`);
            }
        });
    }

    function clearAllItems() {
        if (currentItems.length === 0) return;

        HuenicsModal.confirm({
            title: 'Clear All Line Items?',
            message: 'Are you sure you want to clear all items from this quotation request? This will reset your table.',
            icon: 'trash',
            type: 'danger',
            confirmText: 'Clear All Items',
            cancelText: 'Cancel',
            onConfirm: () => {
                currentItems = [];
                renderRows();
                showToast('Quotation Cleared', 'All line items removed from quotation.');
            }
        });
    }

    function updateSummaryTotals(count) {
        const countEl = document.getElementById('summary-items-count');
        if (countEl) {
            countEl.textContent = `${count} item(s)`;
        }
    }

    function formatMoney(amount) {
        return (parseFloat(amount) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Expose builder functions globally for inline events
    window.addProductRow = addProductRow;
    window.onProductSelect = onProductSelect;
    window.updateItemField = updateItemField;
    window.deleteRow = deleteRow;
    window.clearAllItems = clearAllItems;
    window.initBuilder = initBuilder;

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('items-tbody')) initBuilder();
    });
    document.addEventListener('huenics:page-loaded', () => {
        if (document.getElementById('items-tbody')) initBuilder();
    });
    // Immediate init if injected via SPA
    if (document.readyState !== 'loading' && document.getElementById('items-tbody')) {
        initBuilder();
    }
</script>
@endpush
