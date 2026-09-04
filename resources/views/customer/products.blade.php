@extends('layouts.customer')

@section('title', 'Product Catalog - Huenics Industrial Sales Inc.')

@section('content')
<!-- Header Banner (PDF Crisp White in Light / Sleek Obsidian in Dark) -->
<section class="bg-white dark:bg-[#070b14] ambient-mesh-hero py-12 border-b border-slate-200 dark:border-slate-800/80 relative overflow-hidden hisi-geometric-accent transition-colors duration-200 animate-fade-in-up">
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
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Open Quotation Builder</span>
            </a>
        </div>
    </div>
</section>

<!-- Filter & Search Bar -->
<section class="bg-white/95 dark:bg-[#0b0f19]/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800/80 py-4 sm:py-5 sticky top-16 sm:top-20 z-30 shadow-sm transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-3.5">
        <!-- Row 1: Prominent Search Input + Catalog Status & Clear Action -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('customer.products') }}" class="relative w-full sm:max-w-lg">
                @if(!empty($selectedCategory) && $selectedCategory !== 'all')
                    <input type="hidden" name="category" value="{{ $selectedCategory }}">
                @endif
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" 
                       id="product-search-input"
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Search products by SKU, name, or keywords..."
                       class="w-full pl-10 pr-10 py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-700/80 rounded-xl focus:ring-2 focus:ring-[#214fe0] focus:border-[#214fe0] focus:outline-none bg-slate-50 dark:bg-[#12192b] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all shadow-inner">
                @if(!empty($search))
                    <a href="{{ route('customer.products', ['category' => $selectedCategory]) }}" 
                       title="Clear search keyword" 
                       class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-white transition p-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </form>

            <div class="flex items-center justify-between sm:justify-end gap-3 text-xs text-slate-500 dark:text-slate-400 shrink-0">
                <span class="inline-flex items-center gap-1.5 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $products->total() ?? count($products) }}</span>
                    <span>products found</span>
                </span>

                @if(!empty($search) || (!empty($selectedCategory) && $selectedCategory !== 'all'))
                    <span class="text-slate-300 dark:text-slate-700">|</span>
                    <a href="{{ route('customer.products') }}" 
                       class="text-xs font-bold text-[#214fe0] dark:text-[#60a5fa] hover:underline inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Reset All</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Row 2: Fluid Category Navigation Rail (No Ugly Scrollbar, Smooth Mousewheel & Arrows) -->
        <div class="relative flex items-center group/cat-rail">
            <!-- Left Chevron for Desktop Scroll -->
            <button type="button" 
                    id="cat-scroll-left"
                    aria-label="Scroll categories left"
                    class="hidden md:flex absolute -left-2 z-20 w-7 h-7 rounded-full bg-white dark:bg-[#161f38] shadow-md border border-slate-200 dark:border-slate-700 items-center justify-center text-slate-600 dark:text-slate-300 hover:text-[#214fe0] dark:hover:text-white transition-opacity opacity-0 group-hover/cat-rail:opacity-100 disabled:opacity-0 focus:opacity-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Categories Scroll Container -->
            <div id="category-pills-rail" 
                 class="flex items-center gap-2 overflow-x-auto scroll-smooth py-1 w-full no-scrollbar select-none"
                 style="scrollbar-width: none; -ms-overflow-style: none;">
                <a href="{{ route('customer.products', ['search' => $search]) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-150 shrink-0 {{ empty($selectedCategory) || $selectedCategory === 'all' ? 'bg-[#214fe0] text-white shadow-md shadow-blue-500/25' : 'bg-slate-100 dark:bg-[#141d33] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700/80 border border-slate-200/80 dark:border-slate-700/60' }}">
                    All Categories
                </a>
                @foreach($categories as $category)
                    <a href="{{ route('customer.products', ['category' => $category, 'search' => $search]) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all duration-150 shrink-0 border {{ $selectedCategory === $category ? 'bg-[#214fe0] text-white border-[#214fe0] font-bold shadow-md shadow-blue-500/25' : 'bg-slate-50 dark:bg-[#141d33] text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700/70 hover:bg-blue-50 dark:hover:bg-slate-800 hover:border-blue-300 dark:hover:border-blue-500/50 hover:text-[#214fe0] dark:hover:text-white font-medium' }}">
                        {{ $category }}
                    </a>
                @endforeach
            </div>

            <!-- Right Chevron for Desktop Scroll -->
            <button type="button" 
                    id="cat-scroll-right"
                    aria-label="Scroll categories right"
                    class="hidden md:flex absolute -right-2 z-20 w-7 h-7 rounded-full bg-white dark:bg-[#161f38] shadow-md border border-slate-200 dark:border-slate-700 items-center justify-center text-slate-600 dark:text-slate-300 hover:text-[#214fe0] dark:hover:text-white transition-opacity opacity-0 group-hover/cat-rail:opacity-100 focus:opacity-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
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
            <a href="{{ route('customer.products') }}" class="ml-auto text-[#214fe0] dark:text-[#60a5fa] hover:underline font-bold inline-flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>Clear Filters</span>
            </a>
        </div>
        @endif

        <!-- Bento Grid Pulse Loading Skeleton for Dynamic Filter & Search -->
        <div id="products-bento-skeleton" class="hidden">
            <x-bento-skeleton variant="catalog" :count="8" />
        </div>

        <div id="products-real-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($products as $product)
            <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 hover:border-[#214fe0] dark:hover:border-[#3b82f6] rounded-2xl p-4 shadow-sm hover:shadow-xl dark:hover:shadow-[0_12px_30px_rgba(33,79,224,0.18)] card-interactive flex flex-col justify-between group">
                <div>
                    <!-- Product Image Container -->
                    <div class="relative w-full h-44 bg-slate-100/90 dark:bg-[#161f38]/90 rounded-xl overflow-hidden mb-3 flex items-center justify-center border border-slate-200/70 dark:border-slate-800/80 group-hover:border-blue-300 dark:group-hover:border-blue-600 transition-colors">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->canonical_name }}" loading="lazy" class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-300 ease-out">
                        @else
                            <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                                <svg class="w-10 h-10 opacity-40 mb-1.5 group-hover:text-[#214fe0] dark:group-hover:text-[#60a5fa] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
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

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center border border-slate-300 dark:border-slate-700 rounded-lg overflow-hidden bg-slate-50 dark:bg-[#161f38] shrink-0">
                            <button type="button" 
                                    onclick="adjustQty('qty-{{ $product->id }}', -1)"
                                    class="px-2.5 py-1.5 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-95 transition">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
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
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>

                        <button type="button" 
                                onclick="addProductToQuote({{ json_encode($product) }}, document.getElementById('qty-{{ $product->id }}').value)"
                                class="flex-1 bg-[#214fe0] hover:bg-[#1a42be] btn-interactive text-white text-xs font-bold py-2 px-3 rounded-lg transition-all duration-200 flex items-center justify-center gap-1.5 shadow-sm dark:shadow-[0_0_12px_rgba(33,79,224,0.3)]">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>Add to Quote</span>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-16 bg-white dark:bg-[#111827] rounded-2xl border border-dashed border-slate-300 dark:border-slate-700">
                <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-3 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
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
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="text-xs font-bold text-white flex items-center gap-1.5">
                    <span id="floating-cart-count" class="font-mono tabular-nums">0</span> items in Quotation
                </div>
                <div class="text-[11px] text-slate-400">
                    Bill of Quantities / Project Inquiry
                </div>
            </div>
        </div>

        <a href="{{ route('customer.quotation-builder') }}" 
           class="bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold text-xs px-4 py-2.5 rounded-xl btn-interactive transition flex items-center gap-2 shadow-sm">
            <span>Review &amp; Request Quote</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.adjustQty = function(inputId, delta) {
        const input = document.getElementById(inputId);
        if (!input) return;
        let val = parseInt(input.value) || 1;
        val = Math.max(1, val + delta);
        input.value = val;
    };

    window.addProductToQuote = function(product, qty) {
        CartManager.addItem(product, qty);
        window.updateFloatingCartBar();
    };

    window.updateFloatingCartBar = function() {
        const cart = CartManager.getCart();
        const bar = document.getElementById('floating-cart-bar');
        const countEl = document.getElementById('floating-cart-count');

        if (!bar) return;

        if (cart.length > 0) {
            const totalQty = cart.reduce((sum, item) => sum + (parseFloat(item.quantity) || 0), 0);
            if (countEl) countEl.textContent = totalQty;
            if (bar.classList.contains('hidden')) {
                bar.classList.remove('hidden');
                bar.classList.add('animate-pop-in');
            }
        } else {
            bar.classList.add('hidden');
            bar.classList.remove('animate-pop-in');
        }
    };

    window.showProductsSkeleton = function() {
        const skeleton = document.getElementById('products-bento-skeleton');
        const grid = document.getElementById('products-real-grid');
        if (skeleton && grid) {
            grid.classList.add('hidden');
            skeleton.classList.remove('hidden');
        }
    };

    window.hideProductsSkeleton = function() {
        const skeleton = document.getElementById('products-bento-skeleton');
        const grid = document.getElementById('products-real-grid');
        if (skeleton && grid) {
            skeleton.classList.add('hidden');
            grid.classList.remove('hidden');
        }
    };

    document.addEventListener('click', function(e) {
        if (!e.target || typeof e.target.closest !== 'function') return;
        const catLink = e.target.closest('a[href*="/products"]');
        if (catLink && !catLink.getAttribute('href').startsWith('#')) {
            window.showProductsSkeleton();
        }
    });

    const searchForm = document.querySelector('form[action*="/products"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function() {
            window.showProductsSkeleton();
        });
    }

    // Fluid Category Navigation Rail (Mousewheel + Chevron Controls)
    function initCategoryRail() {
        const rail = document.getElementById('category-pills-rail');
        const btnLeft = document.getElementById('cat-scroll-left');
        const btnRight = document.getElementById('cat-scroll-right');

        if (!rail) return;

        // Horizontal mousewheel scrolling
        rail.addEventListener('wheel', function(e) {
            if (e.deltaY !== 0) {
                e.preventDefault();
                rail.scrollLeft += e.deltaY;
            }
        }, { passive: false });

        // Chevron click controls
        if (btnLeft) {
            btnLeft.addEventListener('click', function() {
                rail.scrollBy({ left: -280, behavior: 'smooth' });
            });
        }
        if (btnRight) {
            btnRight.addEventListener('click', function() {
                rail.scrollBy({ left: 280, behavior: 'smooth' });
            });
        }

        // Auto-center active category pill
        const activePill = rail.querySelector('.bg-\\[\\#214fe0\\]');
        if (activePill && typeof activePill.scrollIntoView === 'function') {
            activePill.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        window.updateFloatingCartBar();
        window.hideProductsSkeleton();
        initCategoryRail();
    });
    document.addEventListener('huenics:page-loaded', function() {
        window.updateFloatingCartBar();
        window.hideProductsSkeleton();
        initCategoryRail();
    });
    // Immediate invocation if dynamically loaded
    if (document.readyState !== 'loading') {
        window.updateFloatingCartBar();
        window.hideProductsSkeleton();
        initCategoryRail();
    }
</script>
@endpush
