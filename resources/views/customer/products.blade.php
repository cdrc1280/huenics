@extends('layouts.customer')

@section('title', 'Product Catalog - Huenics Industrial Sales Inc.')

@section('content')
<!-- Header Banner (PDF Crisp White in Light / Sleek Obsidian in Dark) -->
<section class="bg-white dark:bg-[#070b14] py-12 border-b border-slate-200 dark:border-slate-800/80 relative overflow-hidden hisi-geometric-accent transition-colors duration-200 animate-fade-in-up">
    <!-- Diagonal Stripes Accent -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-[#214fe0]/15 dark:from-blue-500/10 via-blue-500/5 to-transparent pointer-events-none"></div>
    <div class="absolute -bottom-10 -left-10 w-64 h-64 pointer-events-none opacity-25 dark:opacity-15" style="background: repeating-linear-gradient(45deg, rgba(33, 79, 224, 0.08), rgba(33, 79, 224, 0.08) 3px, transparent 3px, transparent 12px);"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="text-xs font-black uppercase tracking-widest text-[#214fe0] dark:text-[#60a5fa]">Colors &bull; Techniques &bull; Technology</span>
                <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-slate-950 dark:text-white mt-1">
                    Commercial & Architectural Product Catalog
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 max-w-2xl font-normal">
                    Select items and quantities to assemble your Bill of Quantities (BOQ). Click "Add to Quote" to automatically build your preliminary estimate.
                </p>
            </div>
            <a href="{{ route('customer.quotation-builder') }}" 
               class="inline-flex items-center gap-2 bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold px-4 py-2.5 rounded-xl text-xs sm:text-sm shadow-md dark:shadow-[0_0_15px_rgba(33,79,224,0.3)] btn-interactive shrink-0">
                <i class="fa-solid fa-file-signature"></i>
                <span>Open Quotation Builder</span>
            </a>
        </div>
    </div>
</section>

<!-- Filter & Search Bar -->
<section class="bg-white dark:bg-[#0b0f19] border-b border-slate-200 dark:border-slate-800 py-6 sticky top-16 sm:top-20 z-30 shadow-sm transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('customer.products') }}" class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <!-- Search Input -->
            <div class="relative w-full md:max-w-md">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Search products by SKU, name, or keywords..."
                       class="w-full pl-10 pr-4 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-[#214fe0] focus:outline-none bg-slate-50 dark:bg-[#161f38] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
            </div>

            <!-- Category Filter -->
            <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
                <a href="{{ route('customer.products', ['search' => $search]) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition {{ empty($selectedCategory) || $selectedCategory === 'all' ? 'bg-[#214fe0] text-white shadow-sm' : 'bg-slate-100 dark:bg-[#161f38] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    All Categories
                </a>
                @foreach($categories as $category)
                    <a href="{{ route('customer.products', ['category' => $category, 'search' => $search]) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition border border-slate-200 dark:border-slate-700 {{ $selectedCategory === $category ? 'bg-[#214fe0] text-white border-[#214fe0] shadow-sm' : 'bg-slate-50 dark:bg-[#161f38] text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-[#214fe0] dark:hover:text-white' }}">
                        {{ $category }}
                    </a>
                @endforeach
            </div>
        </form>
    </div>
</section>

<!-- Products Grid -->
<section class="py-12 bg-slate-50 dark:bg-[#070b14] transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Active Filter Indicator -->
        @if(!empty($search) || !empty($selectedCategory))
        <div class="mb-6 flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300 bg-white dark:bg-[#111827] p-3 rounded-lg border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="font-bold text-slate-800 dark:text-white">Filtered by:</span>
            @if(!empty($selectedCategory))
                <span class="bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 px-2 py-0.5 rounded font-semibold">Category: {{ $selectedCategory }}</span>
            @endif
            @if(!empty($search))
                <span class="bg-slate-100 dark:bg-[#161f38] text-slate-800 dark:text-slate-200 px-2 py-0.5 rounded font-semibold">Keyword: "{{ $search }}"</span>
            @endif
            <a href="{{ route('customer.products') }}" class="ml-auto text-[#214fe0] dark:text-[#60a5fa] hover:underline font-bold">
                <i class="fa-solid fa-xmark mr-1"></i> Clear Filters
            </a>
        </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($products as $product)
            <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 hover:border-[#214fe0] dark:hover:border-[#3b82f6] rounded-2xl p-4 shadow-sm hover:shadow-xl dark:hover:shadow-[0_12px_30px_rgba(33,79,224,0.18)] card-interactive flex flex-col justify-between group">
                <div>
                    <!-- Product Image Container -->
                    <div class="relative w-full h-44 bg-slate-100/90 dark:bg-[#161f38]/90 rounded-xl overflow-hidden mb-3 flex items-center justify-center border border-slate-200/70 dark:border-slate-800/80 group-hover:border-blue-300 dark:group-hover:border-blue-600 transition-colors">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->canonical_name }}" loading="lazy" class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-300 ease-out">
                        @else
                            <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                                <i class="fa-solid fa-lightbulb text-3xl opacity-50 mb-1.5 group-hover:text-[#214fe0] dark:group-hover:text-[#60a5fa] transition-colors"></i>
                                <span class="text-[10px] font-bold tracking-wider uppercase font-mono">{{ $product->category ?: 'Lighting' }}</span>
                            </div>
                        @endif
                        <span class="absolute top-2.5 left-2.5 hisi-pill-badge shadow-sm">
                            {{ strtoupper($product->category ?: 'Lighting') }}
                        </span>
                        <span class="absolute top-2.5 right-2.5 text-[10px] font-mono text-slate-600 dark:text-slate-300 font-bold bg-white/95 dark:bg-[#0c1220]/95 backdrop-blur px-2 py-0.5 rounded shadow-sm border border-slate-200/60 dark:border-slate-700/60">
                            {{ $product->sku ?: $product->product_code ?: 'SKU-GEN' }}
                        </span>
                    </div>

                    <!-- Title -->
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#214fe0] dark:group-hover:text-[#60a5fa] transition line-clamp-2 mb-1.5">
                        {{ $product->canonical_name }}
                    </h3>

                    <!-- Description -->
                    <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mb-3.5 leading-relaxed">
                        {{ $product->description ?: 'Certified commercial and architectural product from official Huenics industrial catalog.' }}
                    </p>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 space-y-3">
                    <!-- Price Hidden -> Quote on Request -->
                    <div class="flex justify-between items-center py-0.5">
                        <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Pricing:</span>
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-[#214fe0] dark:text-[#60a5fa] bg-blue-50 dark:bg-blue-950/70 border border-blue-200/80 dark:border-blue-800/60 px-2.5 py-0.5 rounded-full shadow-xs">
                            <i class="fa-solid fa-file-invoice-dollar text-[10px]"></i> Quote Upon Request
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="flex items-center border border-slate-300 dark:border-slate-700 rounded-lg overflow-hidden bg-slate-50 dark:bg-[#161f38] shrink-0">
                            <button type="button" 
                                    onclick="adjustQty('qty-{{ $product->id }}', -1)"
                                    class="px-2.5 py-1.5 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-95 transition">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <input type="number" 
                                   id="qty-{{ $product->id }}" 
                                   value="1" 
                                   min="1" 
                                   step="1" 
                                   class="w-12 text-center text-xs font-bold font-mono tabular-nums py-1.5 border-x border-slate-300 dark:border-slate-700 focus:outline-none bg-white dark:bg-[#161f38] text-slate-900 dark:text-white">
                            <button type="button" 
                                    onclick="adjustQty('qty-{{ $product->id }}', 1)"
                                    class="px-2.5 py-1.5 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-95 transition">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>

                        <button type="button" 
                                onclick="addProductToQuote({{ json_encode($product) }}, document.getElementById('qty-{{ $product->id }}').value)"
                                class="flex-1 bg-[#214fe0] hover:bg-[#1a42be] btn-interactive text-white text-xs font-bold py-2 px-3 rounded-lg transition-all duration-200 flex items-center justify-center gap-1.5 shadow-sm dark:shadow-[0_0_12px_rgba(33,79,224,0.3)]">
                            <i class="fa-solid fa-cart-plus text-xs"></i>
                            <span>Add to Quote</span>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-16 bg-white dark:bg-[#111827] rounded-2xl border border-dashed border-slate-300 dark:border-slate-700">
                <i class="fa-solid fa-boxes-packing text-4xl text-slate-300 dark:text-slate-600 mb-3"></i>
                <h3 class="text-base font-bold text-slate-800 dark:text-white">No Products Found</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Try adjusting your search criteria or category filter.</p>
                <div class="mt-4">
                    <a href="{{ route('customer.products') }}" class="inline-block bg-[#214fe0] text-white font-semibold text-xs px-4 py-2 rounded-lg">
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
    <div class="bg-slate-900/95 dark:bg-[#0c1322]/95 backdrop-blur text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-slate-700 dark:border-slate-800 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-[#214fe0] text-white flex items-center justify-center font-bold">
                <i class="fa-solid fa-file-invoice"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-white flex items-center gap-1.5">
                    <span id="floating-cart-count" class="font-mono tabular-nums">0</span> items in Quotation
                </div>
                <div class="text-[11px] text-slate-400">
                    Est. Subtotal: <span id="floating-cart-subtotal" class="text-amber-400 font-bold font-mono tabular-nums">₱ 0.00</span>
                </div>
            </div>
        </div>

        <a href="{{ route('customer.quotation-builder') }}" 
           class="bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold text-xs px-4 py-2.5 rounded-xl btn-interactive transition flex items-center gap-2 shadow-sm">
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
            if (bar.classList.contains('hidden')) {
                bar.classList.remove('hidden');
                bar.classList.add('animate-pop-in');
            }
        } else {
            bar.classList.add('hidden');
            bar.classList.remove('animate-pop-in');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateFloatingCartBar();
    });
</script>
@endpush
