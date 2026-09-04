@extends('layouts.customer')

@section('title', 'Instant Quotation Builder - Huenics Industrial Sales Inc.')

@section('content')
<!-- Header Banner (PDF Crisp White in Light / Sleek Obsidian in Dark) -->
<section class="bg-white dark:bg-[#070b14] py-10 border-b border-slate-200 dark:border-slate-800/80 relative overflow-hidden hisi-geometric-accent transition-colors duration-200 animate-fade-in-up">
    <!-- Diagonal Stripes Accent -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-[#214fe0]/15 dark:from-blue-500/10 via-blue-500/5 to-transparent pointer-events-none"></div>
    <div class="absolute -bottom-10 -left-10 w-64 h-64 pointer-events-none opacity-25 dark:opacity-15" style="background: repeating-linear-gradient(45deg, rgba(33, 79, 224, 0.08), rgba(33, 79, 224, 0.08) 3px, transparent 3px, transparent 12px);"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 bg-blue-50 dark:bg-blue-950/70 text-[#214fe0] dark:text-[#60a5fa] border border-blue-200 dark:border-blue-800/60 px-3 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">
                    <i class="fa-solid fa-file-invoice"></i> Unofficial Customer Estimate Generator
                </span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-950 dark:text-white mt-2">
                    Commercial Quotation & BOQ Builder
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 max-w-2xl font-normal">
                    Assemble line items, specify project details, calculate automated 12% Philippine VAT, and export a ready-to-print or downloadable PDF estimate.
                </p>
            </div>
            <a href="{{ route('customer.products') }}" 
               class="inline-flex items-center gap-2 bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl shadow-sm dark:shadow-[0_0_15px_rgba(33,79,224,0.3)] btn-interactive transition">
                <i class="fa-solid fa-plus"></i>
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
                                    <i class="fa-solid fa-list-check text-[#214fe0] dark:text-[#60a5fa]"></i>
                                    <span>Selected Line Items</span>
                                </h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Edit quantities or unit prices directly in the table.</p>
                            </div>

                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <button type="button" 
                                        onclick="openAddCatalogModal()" 
                                        class="flex-1 sm:flex-initial bg-blue-50 dark:bg-blue-950/70 text-[#214fe0] dark:text-[#60a5fa] hover:bg-blue-100 dark:hover:bg-blue-900/80 border border-blue-200 dark:border-blue-800/60 text-xs font-bold px-3 py-2 rounded-lg btn-interactive transition flex items-center justify-center gap-1.5 shadow-sm">
                                    <i class="fa-solid fa-cart-plus"></i>
                                    <span>Add from Catalog</span>
                                </button>
                                <button type="button" 
                                        onclick="addCustomRow()" 
                                        class="flex-1 sm:flex-initial bg-slate-100 dark:bg-[#1a2440] text-slate-800 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-xs font-bold px-3 py-2 rounded-lg btn-interactive transition flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-plus"></i>
                                    <span>Add Custom Item</span>
                                </button>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 dark:bg-[#161f38] text-slate-700 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800">
                                        <th class="py-3 px-3 w-10 text-center">#</th>
                                        <th class="py-3 px-3 min-w-[200px]">Product / Description</th>
                                        <th class="py-3 px-3 w-20 text-center">Unit</th>
                                        <th class="py-3 px-3 w-24 text-center">Qty</th>
                                        <th class="py-3 px-3 w-28 text-right">Unit Price (₱)</th>
                                        <th class="py-3 px-3 w-28 text-right">Line Total (₱)</th>
                                        <th class="py-3 px-3 w-10 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody id="items-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
                                    <!-- Dynamic rows populated via JavaScript from CartManager -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State Container -->
                        <div id="empty-cart-state" class="hidden text-center py-12 px-4">
                            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-[#161f38] text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                <i class="fa-solid fa-folder-open"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Your quotation estimate is currently empty</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                                Add products from our catalog or enter custom line items to compute your project totals.
                            </p>
                            <div class="mt-4 flex justify-center gap-3">
                                <button type="button" onclick="openAddCatalogModal()" class="bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold text-xs px-4 py-2 rounded-lg transition shadow-sm">
                                    <i class="fa-solid fa-boxes-stacked mr-1"></i> Browse Catalog
                                </button>
                                <button type="button" onclick="addCustomRow()" class="bg-slate-100 dark:bg-[#1a2440] hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs px-4 py-2 rounded-lg transition border border-slate-300 dark:border-slate-700">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Add Custom Row
                                </button>
                            </div>
                        </div>

                        <!-- Clear / Reset Table Footer -->
                        <div class="p-3 bg-slate-50 dark:bg-[#161f38]/40 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center text-xs">
                            <span class="text-slate-500 dark:text-slate-400" id="table-row-count">0 items selected</span>
                            <button type="button" onclick="clearAllItems()" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-semibold hover:underline">
                                <i class="fa-solid fa-trash-can mr-1"></i> Clear All Items
                            </button>
                        </div>
                    </div>

                    <!-- Customer & Project Information Card -->
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4 transition-colors">
                        <div class="border-b border-slate-200 dark:border-slate-800 pb-3">
                            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="fa-solid fa-address-card text-[#214fe0] dark:text-[#60a5fa]"></i>
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
                    
                    <!-- Calculation Card -->
                    <div class="bg-slate-900 dark:bg-[#0c1220] text-white border border-slate-800 rounded-2xl p-6 shadow-xl space-y-5">
                        <div class="border-b border-slate-800 pb-3 flex justify-between items-center">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300">
                                Quotation Summary
                            </h3>
                            <span class="text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30 px-2 py-0.5 rounded">
                                Philippine VAT 12%
                            </span>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between text-slate-300">
                                <span>Net Vatable Subtotal:</span>
                                <span id="summary-subtotal" class="font-bold text-white text-sm">₱ 0.00</span>
                            </div>

                            <div class="flex justify-between text-slate-300">
                                <span>12% Value Added Tax (VAT):</span>
                                <span id="summary-vat" class="font-bold text-amber-400 text-sm">₱ 0.00</span>
                            </div>

                            <div class="pt-3 border-t border-slate-800 flex justify-between items-baseline">
                                <div>
                                    <div class="text-xs uppercase font-extrabold text-slate-400">Estimated Total:</div>
                                    <div class="text-[10px] text-slate-500">(VAT-Inclusive)</div>
                                </div>
                                <span id="summary-grand-total" class="font-black text-xl text-[#60a5fa]">₱ 0.00</span>
                            </div>
                        </div>

                        <!-- Notice Box -->
                        <div class="bg-slate-800/80 dark:bg-[#161f38]/80 rounded-xl p-3.5 border border-slate-700 text-[11px] text-slate-400 leading-relaxed">
                            <div class="flex items-center gap-1.5 text-amber-400 font-bold mb-1">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span>Unofficial Estimation</span>
                            </div>
                            This preliminary estimate is generated based on current list pricing. Formal project discounts and delivery commitments will be finalized by Huenics sales upon official PO conversion.
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-2.5 pt-2">
                            <!-- Primary Official Request Button -->
                            <button type="submit" 
                                    onclick="document.getElementById('form-action').value='request_quotation'"
                                    class="w-full bg-emerald-600 hover:bg-emerald-500 btn-interactive text-white font-bold py-3 px-4 rounded-xl shadow-lg hover:shadow-emerald-500/25 transition-all duration-200 flex items-center justify-center gap-2 text-xs sm:text-sm">
                                <i class="fa-solid fa-paper-plane text-base"></i>
                                <span>Request Official Quotation (Encode)</span>
                            </button>

                            <button type="submit" 
                                    onclick="document.getElementById('form-action').value='download_pdf'"
                                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 btn-interactive text-white font-bold py-2.5 px-4 rounded-xl shadow-md transition-all duration-200 flex items-center justify-center gap-2 text-xs sm:text-sm">
                                <i class="fa-solid fa-file-pdf text-base"></i>
                                <span>Download Estimate PDF</span>
                            </button>

                            <button type="submit" 
                                    onclick="document.getElementById('form-action').value='preview_pdf'"
                                    class="w-full bg-slate-800 hover:bg-slate-700 btn-interactive text-slate-200 border border-slate-700 font-semibold py-2.5 px-4 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 text-xs">
                                <i class="fa-solid fa-eye"></i>
                                <span>Preview PDF in New Tab</span>
                            </button>

                            <button type="submit" 
                                    onclick="document.getElementById('form-action').value='view'"
                                    class="w-full bg-transparent hover:bg-slate-800 btn-interactive text-slate-300 hover:text-white font-medium py-2 px-4 rounded-xl transition text-xs flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-desktop"></i>
                                <span>View Web Summary & Print</span>
                            </button>
                        </div>
                    </div>

                    <!-- Direct Help Box -->
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm text-xs space-y-2 transition-colors">
                        <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                            <i class="fa-solid fa-headset text-[#214fe0] dark:text-[#60a5fa]"></i>
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

<!-- Modal: Add from Catalog -->
<div id="catalog-modal" class="fixed inset-0 z-50 bg-slate-950/60 dark:bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4 transition-opacity duration-200">
    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col border border-slate-200 dark:border-slate-800 overflow-hidden">
        <!-- Top Geometric Accent Bar -->
        <div class="h-1.5 w-full bg-gradient-to-r from-[#214fe0] via-blue-500 to-[#1a42be]"></div>

        <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-[#161f38]">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Select Item From Catalog</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pick any product from the Huenics master catalog.</p>
            </div>
            <button type="button" onclick="closeAddCatalogModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <input type="text" id="catalog-search-input" onkeyup="filterCatalogModal()" placeholder="Search by name, SKU, or category..." 
                   class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white dark:bg-[#161f38] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
        </div>

        <div class="overflow-y-auto p-4 space-y-2 divide-y divide-slate-100 dark:divide-slate-800 flex-grow" id="catalog-list">
            @forelse($catalogProducts as $catProd)
            <div class="catalog-item pt-2 pb-2 flex items-center justify-between gap-4" 
                 data-search="{{ strtolower($catProd->canonical_name . ' ' . $catProd->sku . ' ' . $catProd->category) }}">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-xs text-slate-900 dark:text-white">{{ $catProd->canonical_name }}</span>
                        <span class="bg-slate-100 dark:bg-[#161f38] text-slate-600 dark:text-slate-400 border border-transparent dark:border-slate-700 text-[10px] px-2 py-0.5 rounded font-mono">{{ $catProd->sku ?: 'SKU-00' }}</span>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                        Category: {{ $catProd->category ?: 'General' }} &bull; Unit: {{ $catProd->unit_default ?: 'pcs' }}
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <div class="text-right">
                        <div class="text-xs font-bold text-[#214fe0] dark:text-[#60a5fa]">₱ {{ number_format($catProd->display_price ?? ($catProd->selling_price ?: $catProd->default_price ?: 0), 2) }}</div>
                    </div>
                    <button type="button" 
                            onclick="insertCatalogItem({{ json_encode($catProd) }})"
                            class="bg-[#214fe0] hover:bg-[#1a42be] text-white text-xs font-bold px-3 py-1.5 rounded-lg transition shadow-sm">
                        Select
                    </button>
                </div>
            </div>
            @empty
            <div class="text-center py-6 text-xs text-slate-400 dark:text-slate-500">
                No catalog items currently available.
            </div>
            @endforelse
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#161f38] flex justify-end">
            <button type="button" onclick="closeAddCatalogModal()" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-bold text-xs px-4 py-2 rounded-lg transition">
                Close
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // State management for builder rows
    let currentItems = [];

    function initBuilder() {
        currentItems = CartManager.getCart();
        
        // If empty, initialize with the first authentic item from the database catalog
        if (currentItems.length === 0) {
            const defaultDbItem = @json($catalogProducts->first());
            if (defaultDbItem) {
                const unitPrice = parseFloat(defaultDbItem.display_price || defaultDbItem.selling_price || defaultDbItem.default_price || 0);
                const initQty = 10;
                currentItems.push({
                    id: defaultDbItem.id || null,
                    item_code: defaultDbItem.sku || defaultDbItem.product_code || 'HISI-PROD',
                    description: defaultDbItem.canonical_name,
                    quantity: initQty,
                    unit: defaultDbItem.unit_default || 'pcs',
                    unit_price: unitPrice,
                    line_total: parseFloat((initQty * unitPrice).toFixed(2))
                });
                CartManager.saveCart(currentItems);
            }
        }

        renderRows();
    }

    function renderRows() {
        const tbody = document.getElementById('items-tbody');
        const emptyState = document.getElementById('empty-cart-state');
        const countEl = document.getElementById('table-row-count');

        if (!tbody) return;

        tbody.innerHTML = '';

        if (currentItems.length === 0) {
            emptyState.classList.remove('hidden');
            countEl.textContent = '0 items selected';
            updateSummaryTotals(0);
            return;
        }

        emptyState.classList.add('hidden');
        countEl.textContent = `${currentItems.length} item(s) selected`;

        let subtotal = 0;

        currentItems.forEach((item, index) => {
            const lineTotal = parseFloat((parseFloat(item.quantity || 1) * parseFloat(item.unit_price || 0)).toFixed(2));
            item.line_total = lineTotal;
            subtotal += lineTotal;

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 dark:hover:bg-[#161f38]/60 transition border-b border-slate-100 dark:border-slate-800';
            tr.innerHTML = `
                <td class="py-2.5 px-3 text-center text-slate-400 dark:text-slate-500 font-bold">${index + 1}</td>
                <td class="py-2.5 px-3">
                    <input type="hidden" name="items[${index}][item_code]" value="${escapeHtml(item.item_code || '')}">
                    <input type="text" name="items[${index}][description]" value="${escapeHtml(item.description || '')}" required
                           onchange="updateItemField(${index}, 'description', this.value)"
                           placeholder="Item description"
                           class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded text-xs font-semibold focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white dark:bg-[#161f38] text-slate-900 dark:text-white">
                </td>
                <td class="py-2.5 px-3 text-center">
                    <input type="text" name="items[${index}][unit]" value="${escapeHtml(item.unit || 'pcs')}"
                           onchange="updateItemField(${index}, 'unit', this.value)"
                           class="w-16 text-center px-1.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white dark:bg-[#161f38] text-slate-900 dark:text-white">
                </td>
                <td class="py-2.5 px-3 text-center">
                    <input type="number" name="items[${index}][quantity]" value="${item.quantity}" min="0.01" step="any" required
                           onchange="updateItemField(${index}, 'quantity', this.value)"
                           oninput="updateItemField(${index}, 'quantity', this.value)"
                           class="w-20 text-center px-2 py-1.5 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white dark:bg-[#161f38]">
                </td>
                <td class="py-2.5 px-3 text-right">
                    <input type="number" name="items[${index}][unit_price]" value="${item.unit_price}" min="0" step="0.01" required
                           onchange="updateItemField(${index}, 'unit_price', this.value)"
                           oninput="updateItemField(${index}, 'unit_price', this.value)"
                           class="w-24 text-right px-2 py-1.5 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white dark:bg-[#161f38]">
                </td>
                <td class="py-2.5 px-3 text-right font-bold text-slate-900 dark:text-white">
                    ₱ ${formatMoney(lineTotal)}
                </td>
                <td class="py-2.5 px-3 text-center">
                    <button type="button" onclick="deleteRow(${index})" class="text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition p-1" title="Remove item">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        updateSummaryTotals(subtotal);
        CartManager.saveCart(currentItems);
    }

    function updateItemField(index, field, value) {
        if (!currentItems[index]) return;

        if (field === 'quantity') {
            currentItems[index].quantity = parseFloat(value) || 0;
        } else if (field === 'unit_price') {
            currentItems[index].unit_price = parseFloat(value) || 0;
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
            message: `Are you sure you want to remove "${itemName}" from this quotation estimate?`,
            icon: 'fa-solid fa-trash-can',
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
            message: 'Are you sure you want to clear all items from this quotation? This will reset your table and recalculate estimated project totals to zero.',
            icon: 'fa-solid fa-trash-can',
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

    function addCustomRow() {
        currentItems.push({
            item_code: 'CUSTOM-' + Date.now().toString().slice(-4),
            description: 'Custom Material Line Item / Fee',
            quantity: 1,
            unit: 'lot',
            unit_price: 1000.00,
            line_total: 1000.00
        });
        renderRows();
    }

    function insertCatalogItem(product) {
        currentItems.push({
            id: product.id,
            item_code: product.sku || product.product_code || ('ITM-' + Date.now().toString().slice(-4)),
            description: product.canonical_name,
            quantity: 1,
            unit: product.unit_default || 'pcs',
            unit_price: parseFloat(product.display_price || product.selling_price || product.default_price || 0),
            line_total: parseFloat(product.display_price || product.selling_price || product.default_price || 0)
        });
        closeAddCatalogModal();
        renderRows();
        showToast('Catalog Item Added', `"${product.canonical_name}" added to quotation.`);
    }

    function updateSummaryTotals(subtotal) {
        const vat = subtotal * 0.12;
        const grandTotal = subtotal + vat;

        document.getElementById('summary-subtotal').textContent = '₱ ' + formatMoney(subtotal);
        document.getElementById('summary-vat').textContent = '₱ ' + formatMoney(vat);
        document.getElementById('summary-grand-total').textContent = '₱ ' + formatMoney(grandTotal);
    }

    function formatMoney(amount) {
        return (parseFloat(amount) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escapeHtml(string) {
        return String(string).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Modal helpers
    function openAddCatalogModal() {
        const modal = document.getElementById('catalog-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('catalog-search-input').focus();
    }

    function closeAddCatalogModal() {
        const modal = document.getElementById('catalog-modal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    function filterCatalogModal() {
        const query = document.getElementById('catalog-search-input').value.toLowerCase();
        const items = document.querySelectorAll('.catalog-item');
        items.forEach(item => {
            const text = item.getAttribute('data-search') || '';
            if (text.includes(query)) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initBuilder();
    });
</script>
@endpush
