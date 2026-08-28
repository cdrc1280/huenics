@extends('layouts.customer')

@section('title', 'Huenics Industrial Supply - Direct Engineering & Construction Materials')

@section('content')
<!-- Hero Section -->
<section class="relative bg-slate-900 text-white overflow-hidden py-16 lg:py-24">
    <!-- Gradient Glow & Pattern -->
    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#3b82f6_1px,transparent_1px)] [background-size:16px_16px]"></div>
    <div class="absolute top-1/4 -right-20 w-96 h-96 bg-blue-600/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 bg-blue-500/10 text-blue-300 border border-blue-500/20 px-3.5 py-1.5 rounded-full text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                    Direct Importer & Wholesale Construction Supply
                </div>
                
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.15]">
                    Engineering-Grade <br class="hidden sm:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-indigo-300 to-sky-300">
                        Industrial Supplies
                    </span>
                </h1>

                <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    Supplying top general contractors and developers with certified plumbing, structural steel, sewage pumps, HDPE conduits, and electrical infrastructure materials across the Philippines.
                </p>

                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a href="{{ route('customer.quotation-builder') }}" 
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold px-6 py-3.5 rounded-xl shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-0.5 text-sm sm:text-base">
                        <i class="fa-solid fa-file-signature"></i>
                        <span>Generate Instant Quotation (PDF)</span>
                    </a>
                    <a href="{{ route('customer.products') }}" 
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-slate-800/80 hover:bg-slate-800 text-slate-200 border border-slate-700 font-semibold px-6 py-3.5 rounded-xl transition text-sm sm:text-base">
                        <i class="fa-solid fa-boxes-stacked text-blue-400"></i>
                        <span>Browse Product Catalog</span>
                    </a>
                </div>

                <!-- Company Stats -->
                <div class="pt-6 grid grid-cols-2 sm:grid-cols-3 gap-4 border-t border-slate-800/80">
                    <div class="text-center lg:text-left" title="Total active products in our catalog">
                        <div class="text-xl sm:text-2xl font-bold text-white">{{ number_format($totalProductsCount) }}+</div>
                        <div class="text-xs text-slate-400">Industrial Products</div>
                    </div>
                    <div class="text-center lg:text-left" title="Different categories of supplies we offer">
                        <div class="text-xl sm:text-2xl font-bold text-white">{{ $categories->count() }}</div>
                        <div class="text-xs text-slate-400">Product Categories</div>
                    </div>
                    <div class="text-center lg:text-left" title="Years serving the industry">
                        <div class="text-xl sm:text-2xl font-bold text-white">{{ $yearsInBusiness }}</div>
                        <div class="text-xs text-slate-400">Years in Business</div>
                    </div>
                </div>
            </div>

            <!-- Hero Quick Quote Card Preview -->
            <div class="lg:col-span-5">
                <div class="bg-slate-800/90 border border-slate-700 rounded-2xl p-6 shadow-2xl backdrop-blur relative overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-700 pb-4 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-500/80"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-500/80"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-500/80"></span>
                            <span class="text-xs font-mono text-slate-400 ml-2">Quick Quotation Estimator</span>
                        </div>
                        <span class="text-[11px] font-semibold bg-amber-400/10 text-amber-300 border border-amber-400/20 px-2 py-0.5 rounded">
                            Unofficial PDF Ready
                        </span>
                    </div>

                    <div class="space-y-3 text-xs text-slate-300">
                        <div class="bg-slate-900/60 p-3 rounded-lg border border-slate-700/60 space-y-1.5">
                            <div class="text-slate-400 text-[10px] uppercase font-bold tracking-wider" title="Real-time pricing for our top items">Featured Catalog Items:</div>
                            @foreach($featuredProducts->take(2) as $prod)
                            <div class="flex justify-between font-medium">
                                <span class="truncate pr-2" title="{{ $prod->canonical_name }}">{{ $prod->canonical_name }} (1 {{ $prod->unit_default ?: 'pc' }})</span>
                                <span class="text-white font-semibold whitespace-nowrap">₱ {{ number_format($prod->selling_price ?: $prod->default_price, 2) }}</span>
                            </div>
                            @endforeach
                        </div>

                        <div class="bg-slate-900/90 p-3.5 rounded-lg border border-slate-700/80 space-y-1.5">
                            <div class="text-xs text-slate-400 mb-2">Build your own quotation instantly with our portal.</div>
                            <div class="flex justify-between text-white font-bold text-sm pt-1 border-t border-slate-700">
                                <span>Real-time Pricing</span>
                                <span class="text-blue-400">VAT Included</span>
                            </div>
                        </div>

                        <a href="{{ route('customer.quotation-builder') }}" 
                           class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-2.5 rounded-lg transition text-xs">
                            <span>Launch Quotation Generator</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product Categories & Showcase -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Product Portfolio</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                    Featured Industrial Product Lines
                </h2>
                <p class="text-sm text-slate-500 mt-1 max-w-xl">
                    Explore our inventory of plumbing, electrical, structural, and pumping systems. Add products directly to your preliminary quotation cart.
                </p>
            </div>
            <a href="{{ route('customer.products') }}" class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-semibold text-sm group">
                <span>View Full Catalog ({{ $totalProductsCount }} items)</span>
                <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition transform"></i>
            </a>
        </div>

        <!-- Category Pills -->
        <div class="flex flex-wrap gap-2 mb-8">
            <a href="{{ route('customer.products') }}" class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-slate-900 text-white hover:bg-slate-800 transition">
                All Products
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('customer.products', ['category' => $cat]) }}" class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition border border-slate-200">
                    {{ $cat }}
                </a>
            @endforeach
        </div>

        <!-- Featured Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($featuredProducts as $product)
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 hover:shadow-lg transition group flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start mb-3">
                        <span class="bg-blue-100 text-blue-800 text-[11px] font-bold px-2.5 py-0.5 rounded-md">
                            {{ $product->category ?: 'General Supplies' }}
                        </span>
                        <span class="text-xs font-mono text-slate-500 font-medium">
                            {{ $product->sku ?: $product->product_code ?: 'SKU-00' }}
                        </span>
                    </div>

                    <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition mb-2">
                        {{ $product->canonical_name }}
                    </h3>

                    <p class="text-xs text-slate-500 line-clamp-2 mb-4">
                        {{ $product->description ?: 'Standard Philippine engineering grade industrial item with full manufacturer warranty and compliance specifications.' }}
                    </p>
                </div>

                <div class="pt-4 border-t border-slate-200">
                    <div class="flex justify-between items-baseline mb-3">
                        <span class="text-xs text-slate-500 font-medium">Catalog Rate:</span>
                        <div class="text-right">
                            <span class="text-base font-extrabold text-slate-900">
                                ₱ {{ number_format($product->selling_price ?: $product->default_price ?: 0, 2) }}
                            </span>
                            <span class="text-[11px] text-slate-500 font-normal"> / {{ $product->unit_default ?: 'pcs' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="number" id="qty-{{ $product->id }}" value="1" min="1" step="1" 
                               class="w-16 text-center text-xs font-bold border border-slate-300 rounded-lg py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <button type="button" 
                                onclick="addProductToQuote({{ json_encode($product) }}, document.getElementById('qty-{{ $product->id }}').value)"
                                class="flex-1 bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold py-2 px-3 rounded-lg transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Add to Quote</span>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-12 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                <i class="fa-solid fa-boxes-stacked text-3xl text-slate-400 mb-2"></i>
                <p class="text-sm font-medium text-slate-600">No featured products available at this moment.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Why Choose Huenics Section -->
<section class="py-16 bg-slate-100 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Our Capabilities</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                Built for Contractors, Developers & Engineers
            </h2>
            <p class="text-sm text-slate-500 mt-2">
                We bridge high-volume manufacturer supply directly with jobsite procurement schedules.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Strict 12% Philippine VAT Invoicing</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Complete tax documentation with Sales Invoices (SI) and Official Receipts (OR) compliant with BIR rules. Zero hidden discrepancies.
                </p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Reliable Jobsite Delivery Fleet</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Fleet of boom trucks, dropside trailers, and express delivery vans to dispatch heavy steel, pipes, and bulk machinery across Mega Manila & Luzon.
                </p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-file-circle-check"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Immediate Digital Quotations</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Generate instant preliminary estimates directly on our web portal, review VAT math in real-time, and download formatted PDF estimates.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-14 bg-gradient-to-r from-blue-700 via-blue-800 to-slate-900 text-white text-center">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight mb-4">
            Need a Formal Commercial Quotation for Your Project?
        </h2>
        <p class="text-sm sm:text-base text-blue-100 mb-8 max-w-2xl mx-auto font-normal">
            Build your estimate online or contact our engineering sales department directly for project-specific contractor discounts.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('customer.quotation-builder') }}" 
               class="bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold px-7 py-3.5 rounded-xl shadow-lg transition text-sm">
                <i class="fa-solid fa-calculator mr-2"></i> Start Quotation Builder
            </a>
            <a href="{{ route('customer.about') }}" 
               class="bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold px-7 py-3.5 rounded-xl transition text-sm">
                <i class="fa-solid fa-envelope mr-2"></i> Contact Sales Desk
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    function addProductToQuote(product, qty) {
        CartManager.addItem(product, qty);
    }
</script>
@endpush
