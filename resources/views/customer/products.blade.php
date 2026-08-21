@extends('layouts.customer')

@section('title', 'Product Catalog & Showcase - Huenics Industrial Supply')

@section('content')
<!-- Header Banner -->
<section class="bg-slate-900 text-white py-12 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Engineering Material Showcase</span>
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight mt-1">
                    Industrial & Construction Product Catalog
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl font-normal">
                    Select items and quantities to assemble your Bill of Quantities (BOQ). Click "Add to Quotation" to automatically build your preliminary estimate.
                </p>
            </div>
            <a href="{{ route('customer.quotation-builder') }}" 
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-bold px-4 py-2.5 rounded-xl text-xs sm:text-sm shadow-md transition shrink-0">
                <i class="fa-solid fa-file-signature"></i>
                <span>Open Quotation Builder</span>
            </a>
        </div>
    </div>
</section>

<!-- Filter & Search Bar -->
<section class="bg-white border-b border-slate-200 py-6 sticky top-16 sm:top-20 z-30 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('customer.products') }}" class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <!-- Search Input -->
            <div class="relative w-full md:max-w-md">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Search products by SKU, name, or keywords..."
                       class="w-full pl-10 pr-4 py-2 text-xs sm:text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-slate-50">
            </div>

            <!-- Category Filter -->
            <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
                <a href="{{ route('customer.products', ['search' => $search]) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ empty($selectedCategory) || $selectedCategory === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    All Categories
                </a>
                @foreach($categories as $category)
                    <a href="{{ route('customer.products', ['category' => $category, 'search' => $search]) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition border border-slate-200 {{ $selectedCategory === $category ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">
                        {{ $category }}
                    </a>
                @endforeach
            </div>
        </form>
    </div>
</section>

<!-- Products Grid -->
<section class="py-12 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Active Filter Indicator -->
        @if(!empty($search) || !empty($selectedCategory))
        <div class="mb-6 flex items-center gap-2 text-xs text-slate-600 bg-white p-3 rounded-lg border border-slate-200">
            <span class="font-bold text-slate-800">Filtered by:</span>
            @if(!empty($selectedCategory))
                <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-semibold">Category: {{ $selectedCategory }}</span>
            @endif
            @if(!empty($search))
                <span class="bg-slate-100 text-slate-800 px-2 py-0.5 rounded font-semibold">Keyword: "{{ $search }}"</span>
            @endif
            <a href="{{ route('customer.products') }}" class="ml-auto text-blue-600 hover:underline font-bold">
                <i class="fa-solid fa-xmark mr-1"></i> Clear Filters
            </a>
        </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($products as $product)
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                <div>
                    <!-- Category & SKU Tag -->
                    <div class="flex justify-between items-center mb-2">
                        <span class="bg-slate-100 text-slate-700 text-[10px] font-bold px-2 py-0.5 rounded">
                            {{ $product->category ?: 'General' }}
                        </span>
                        <span class="text-[11px] font-mono text-slate-400 font-semibold">
                            {{ $product->sku ?: $product->product_code ?: 'SKU-GEN' }}
                        </span>
                    </div>

                    <!-- Title -->
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition line-clamp-2 mb-1.5">
                        {{ $product->canonical_name }}
                    </h3>

                    <!-- Description -->
                    <p class="text-xs text-slate-500 line-clamp-3 mb-4 leading-relaxed">
                        {{ $product->description ?: 'Standard Philippine commercial grade material. Tested for compliance with DPWH & ASTM standards.' }}
                    </p>
                </div>

                <div class="pt-3 border-t border-slate-100 space-y-3">
                    <div class="flex justify-between items-baseline">
                        <span class="text-[11px] text-slate-500 font-medium">Standard Rate:</span>
                        <div class="text-right">
                            <span class="text-base font-extrabold text-slate-900">
                                ₱ {{ number_format($product->selling_price ?: $product->default_price ?: 0, 2) }}
                            </span>
                            <span class="text-[11px] text-slate-500 font-normal"> / {{ $product->unit_default ?: 'pcs' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="flex items-center border border-slate-300 rounded-lg overflow-hidden bg-slate-50">
                            <button type="button" 
                                    onclick="adjustQty('qty-{{ $product->id }}', -1)"
                                    class="px-2 py-1.5 text-xs text-slate-600 hover:bg-slate-200">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <input type="number" 
                                   id="qty-{{ $product->id }}" 
                                   value="1" 
                                   min="1" 
                                   step="1" 
                                   class="w-12 text-center text-xs font-bold py-1.5 border-x border-slate-300 focus:outline-none bg-white">
                            <button type="button" 
                                    onclick="adjustQty('qty-{{ $product->id }}', 1)"
                                    class="px-2 py-1.5 text-xs text-slate-600 hover:bg-slate-200">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>

                        <button type="button" 
                                onclick="addProductToQuote({{ json_encode($product) }}, document.getElementById('qty-{{ $product->id }}').value)"
                                class="flex-1 bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold py-2 px-3 rounded-lg transition flex items-center justify-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-cart-plus text-xs"></i>
                            <span>Add to Quote</span>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-16 bg-white rounded-2xl border border-dashed border-slate-300">
                <i class="fa-solid fa-boxes-packing text-4xl text-slate-300 mb-3"></i>
                <h3 class="text-base font-bold text-slate-800">No Products Found</h3>
                <p class="text-xs text-slate-500 mt-1">Try adjusting your search criteria or category filter.</p>
                <div class="mt-4">
                    <a href="{{ route('customer.products') }}" class="inline-block bg-blue-600 text-white font-semibold text-xs px-4 py-2 rounded-lg">
                        Reset All Filters
                    </a>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-10">
            {{ $products->links() }}
        </div>
    </div>
</section>

<!-- Floating Cart Action Bar -->
<div id="floating-cart-bar" class="fixed bottom-4 left-1/2 -translate-x-1/2 z-40 w-full max-w-xl px-4 hidden">
    <div class="bg-slate-900/95 backdrop-blur text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-slate-700 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold">
                <i class="fa-solid fa-file-invoice"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-white flex items-center gap-1.5">
                    <span id="floating-cart-count">0</span> items in Quotation
                </div>
                <div class="text-[11px] text-slate-400">
                    Est. Subtotal: <span id="floating-cart-subtotal" class="text-amber-400 font-bold">₱ 0.00</span>
                </div>
            </div>
        </div>

        <a href="{{ route('customer.quotation-builder') }}" 
           class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center gap-2 shadow-sm">
            <span>Review & Generate PDF</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function adjustQty(inputId, delta) {
        const input = document.getElementById(inputId);
        if (!input) return;
        let val = parseInt(input.value) || 1;
        val = Math.max(1, val + delta);
        input.value = val;
    }

    function addProductToQuote(product, qty) {
        CartManager.addItem(product, qty);
        updateFloatingCartBar();
    }

    function updateFloatingCartBar() {
        const cart = CartManager.getCart();
        const bar = document.getElementById('floating-cart-bar');
        const countEl = document.getElementById('floating-cart-count');
        const subtotalEl = document.getElementById('floating-cart-subtotal');

        if (!bar) return;

        if (cart.length > 0) {
            const totalQty = cart.reduce((sum, item) => sum + (parseFloat(item.quantity) || 0), 0);
            const subtotal = cart.reduce((sum, item) => sum + (parseFloat(item.line_total) || 0), 0);
            countEl.textContent = totalQty;
            subtotalEl.textContent = '₱ ' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            bar.classList.remove('hidden');
        } else {
            bar.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateFloatingCartBar();
    });
</script>
@endpush
