@extends('layouts.customer')

@section('title', 'Huenics Industrial Sales Inc. - Colors • Techniques • Technology')

@section('content')
<!-- Hero Section (PDF Design: Crisp White in Light / Sleek Obsidian in Dark) -->
<section class="relative bg-white dark:bg-[#070b14] ambient-mesh-hero overflow-hidden py-14 lg:py-20 border-b border-slate-200 dark:border-slate-800/80 hisi-geometric-accent transition-colors duration-200">
    <!-- PDF Inspired Diagonal Stripes & Corner Geometry -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-blue-600/15 dark:from-blue-500/10 via-blue-500/5 to-transparent pointer-events-none -z-0"></div>
    <div class="absolute -bottom-10 -left-10 w-80 h-80 pointer-events-none opacity-40 dark:opacity-20 -z-0" style="background: repeating-linear-gradient(45deg, rgba(33, 79, 224, 0.07), rgba(33, 79, 224, 0.07) 3px, transparent 3px, transparent 12px);"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left animate-fade-in-up">
                <!-- Tagline Badge -->
                <div class="inline-flex items-center gap-2 bg-blue-50 dark:bg-blue-950/70 border border-blue-200 dark:border-blue-800/60 text-[#214fe0] dark:text-[#60a5fa] px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-[#214fe0] dark:bg-[#3b82f6] animate-pulse"></span>
                    Direct Importer & Wholesale Engineering Supplier &bull; HISI
                </div>
                
                <!-- Main Title -->
                <div class="space-y-2">
                    <div class="text-xs sm:text-sm font-extrabold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        Commercial Engineering &bull; Industrial Lighting
                    </div>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-slate-950 dark:text-white leading-[1.12]">
                        Colors &bull; Techniques <br class="hidden sm:inline">
                        <span class="text-[#214fe0] dark:text-[#60a5fa]">
                            & Technology
                        </span>
                    </h1>
                </div>

                <!-- Brand Motto -->
                <div class="p-3.5 bg-blue-50/80 dark:bg-[#111827]/90 border-l-4 border-[#214fe0] dark:border-[#3b82f6] rounded-r-lg max-w-xl mx-auto lg:mx-0 shadow-sm">
                    <p class="text-xs sm:text-sm font-extrabold text-blue-950 dark:text-blue-200 italic">
                        "Focus on Pursuing Quality & Speed of Delivery."
                    </p>
                </div>

                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    Direct importer and wholesale distributor of commercial LED downlights, Citizen Japan C.O.B modules, indent orders, and certified electrical infrastructure materials across the Philippines.
                </p>

                <!-- Actions -->
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5">
                    <a href="{{ route('customer.quotation-builder') }}" 
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold px-6 py-3.5 rounded-xl shadow-lg shadow-blue-600/25 dark:shadow-[0_0_20px_rgba(33,79,224,0.35)] transition-all duration-200 transform hover:-translate-y-0.5 active:scale-[0.98] text-sm sm:text-base">
                        <i class="fa-solid fa-file-signature"></i>
                        <span>Generate Instant Quotation (PDF)</span>
                    </a>
                    <a href="{{ route('customer.products') }}" 
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white dark:bg-[#111827] hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 border-2 border-slate-300 dark:border-slate-700 hover:border-[#214fe0] dark:hover:border-[#3b82f6] font-bold px-6 py-3.5 rounded-xl transition-all duration-200 transform hover:-translate-y-0.5 active:scale-[0.98] text-sm sm:text-base shadow-sm">
                        <i class="fa-solid fa-boxes-stacked text-[#214fe0] dark:text-[#60a5fa]"></i>
                        <span>Browse Product Catalog</span>
                    </a>
                </div>

                <!-- Company Stats -->
                <div class="pt-6 grid grid-cols-2 sm:grid-cols-3 gap-4 border-t border-slate-200 dark:border-slate-800">
                    <div class="text-center lg:text-left" title="Total active products in our catalog">
                        <div class="text-2xl sm:text-3xl font-black text-[#214fe0] dark:text-[#60a5fa] font-mono tabular-nums">{{ number_format($totalProductsCount) }}+</div>
                        <div class="text-xs font-semibold text-slate-600 dark:text-slate-400">Active Products</div>
                    </div>
                    <div class="text-center lg:text-left" title="Different categories of supplies we offer">
                        <div class="text-2xl sm:text-3xl font-black text-[#214fe0] dark:text-[#60a5fa] font-mono tabular-nums">{{ $categories->count() }}</div>
                        <div class="text-xs font-semibold text-slate-600 dark:text-slate-400">Product Categories</div>
                    </div>
                    <div class="text-center lg:text-left" title="Years serving the industry">
                        <div class="text-2xl sm:text-3xl font-black text-[#214fe0] dark:text-[#60a5fa] font-mono tabular-nums">{{ $yearsInBusiness }}</div>
                        <div class="text-xs font-semibold text-slate-600 dark:text-slate-400">Years in Business</div>
                    </div>
                </div>
            </div>

            <!-- Hero Quick Quote Card Preview (Clean White in Light / Sleek Dark Card in Dark) -->
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-[#111827] border-2 border-blue-600/20 dark:border-slate-800 rounded-2xl shadow-xl overflow-hidden transition-colors">
                    <div class="bg-[#1a42be] dark:bg-[#153396] text-white p-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span class="text-xs font-bold uppercase tracking-wider ml-1">Quick Quotation Estimator</span>
                        </div>
                        <span class="text-[10px] font-extrabold bg-white/20 text-white border border-white/30 px-2 py-0.5 rounded">
                            PDF Ready
                        </span>
                    </div>

                    <div class="p-5 space-y-3.5 text-xs text-slate-600 dark:text-slate-300">
                        <div class="bg-blue-50/50 dark:bg-[#161f38] p-3 rounded-lg border border-blue-100 dark:border-slate-700/60 space-y-2">
                            <div class="text-blue-900 dark:text-blue-300 text-[10px] uppercase font-extrabold tracking-wider">Featured Commercial Line Items:</div>
                            @foreach($featuredProducts->take(2) as $prod)
                            <div class="flex justify-between items-center text-xs font-medium">
                                <span class="truncate pr-2 font-semibold text-slate-800 dark:text-slate-200" title="{{ $prod->canonical_name }}">{{ $prod->canonical_name }}</span>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#214fe0] dark:text-[#60a5fa] bg-blue-100/60 dark:bg-blue-950/70 border border-blue-200/80 dark:border-blue-800/60 px-2 py-0.5 rounded-full whitespace-nowrap">
                                    <i class="fa-solid fa-file-invoice text-[9px]"></i> Quote Upon Request
                                </span>
                            </div>
                            @endforeach
                        </div>

                        <div class="bg-slate-50 dark:bg-[#161f38] p-3.5 rounded-lg border border-slate-200 dark:border-slate-700/60 space-y-1.5">
                            <div class="text-xs text-slate-600 dark:text-slate-300">Assemble your project BOQ and submit for formal pricing or export an itemized request.</div>
                            <div class="flex justify-between text-slate-900 dark:text-slate-200 font-bold text-xs pt-2 border-t border-slate-200 dark:border-slate-700">
                                <span>Commercial Terms:</span>
                                <span class="text-[#214fe0] dark:text-[#60a5fa] font-black">Quote Upon Inquiry</span>
                            </div>
                        </div>

                        <a href="{{ route('customer.quotation-builder') }}" 
                           class="w-full flex items-center justify-center gap-2 bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold py-3 rounded-lg transition text-xs shadow-sm dark:shadow-[0_0_12px_rgba(33,79,224,0.3)]">
                            <span>Launch Quotation Generator</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product Categories & Showcase -->
<section class="py-16 bg-white dark:bg-[#0a0e1a] transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">Product Portfolio</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                    Commercial & Architectural Lighting
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                    Explore our catalog of Citizen Japan C.O.B downlights, highbays, tracklights, architectural linear profiles, and smart digital systems.
                </p>
            </div>
            <a href="{{ route('customer.products') }}" class="inline-flex items-center gap-1.5 text-[#214fe0] dark:text-[#60a5fa] hover:text-blue-800 dark:hover:text-blue-300 font-semibold text-sm group">
                <span>View Full Catalog ({{ $totalProductsCount }} items)</span>
                <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition transform"></i>
            </a>
        </div>

        <!-- Category Pills -->
        <div class="flex flex-wrap gap-2 mb-8">
            <a href="{{ route('customer.products') }}" class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-slate-900 dark:bg-[#214fe0] text-white hover:bg-slate-800 dark:hover:bg-[#1a42be] transition shadow-sm">
                All Products
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('customer.products', ['category' => $cat]) }}" class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-[#151f38] text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-[#214fe0] dark:hover:text-white transition border border-slate-200 dark:border-slate-700/80">
                    {{ $cat }}
                </a>
            @endforeach
        </div>

        <!-- Featured Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($featuredProducts as $product)
            <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 hover:border-[#214fe0] dark:hover:border-[#3b82f6] rounded-2xl p-4.5 hover:shadow-xl dark:hover:shadow-[0_12px_30px_rgba(33,79,224,0.18)] hover:-translate-y-1.5 transition-all duration-300 ease-out group flex flex-col justify-between">
                <div>
                    <!-- Product Image Container -->
                    <div class="relative w-full h-44 bg-slate-100/90 dark:bg-[#161f38]/90 rounded-xl overflow-hidden mb-3.5 flex items-center justify-center border border-slate-200/70 dark:border-slate-800/80 group-hover:border-blue-300 dark:group-hover:border-blue-600 transition-colors">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->canonical_name }}" loading="lazy" class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-300 ease-out">
                        @else
                            <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                                <i class="fa-solid fa-lightbulb text-3xl opacity-50 mb-1.5 group-hover:text-[#214fe0] dark:group-hover:text-[#60a5fa] transition-colors"></i>
                                <span class="text-[10px] font-bold tracking-wider uppercase font-mono">{{ $product->category ?: 'Lighting' }}</span>
                            </div>
                        @endif
                        <span class="absolute top-2.5 left-2.5 hisi-pill-badge shadow-sm">
                            {{ strtoupper($product->category ?: 'General Supplies') }}
                        </span>
                        <span class="absolute top-2.5 right-2.5 text-[10px] font-mono text-slate-600 dark:text-slate-300 font-bold bg-white/95 dark:bg-[#0c1220]/95 backdrop-blur px-2 py-0.5 rounded shadow-sm border border-slate-200/60 dark:border-slate-700/60">
                            {{ $product->sku ?: $product->product_code ?: 'SKU-00' }}
                        </span>
                    </div>

                    <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-[#214fe0] dark:group-hover:text-[#60a5fa] transition mb-1.5">
                        {{ $product->canonical_name }}
                    </h3>

                    <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mb-4 leading-relaxed">
                        {{ $product->description ?: 'Certified commercial and industrial lighting product from official Huenics catalog.' }}
                    </p>
                </div>

                <div class="pt-3.5 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Pricing:</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-[#214fe0] dark:text-[#60a5fa] bg-blue-50 dark:bg-blue-950/70 border border-blue-200/80 dark:border-blue-800/60 px-2.5 py-0.5 rounded-full shadow-xs">
                            <i class="fa-solid fa-file-invoice-dollar text-[10px]"></i> Quote Upon Request
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="number" id="qty-{{ $product->id }}" value="1" min="1" step="1" 
                               class="w-16 text-center text-xs font-bold font-mono tabular-nums border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#161f38] text-slate-900 dark:text-white rounded-lg py-2 focus:ring-2 focus:ring-[#214fe0] focus:outline-none">
                        <button type="button" 
                                onclick="addProductToQuote({{ json_encode($product) }}, document.getElementById('qty-{{ $product->id }}').value)"
                                class="flex-1 bg-[#214fe0] hover:bg-[#1a42be] active:scale-[0.98] text-white text-xs font-bold py-2.5 px-3 rounded-lg transition-all duration-200 flex items-center justify-center gap-1.5 shadow-sm dark:shadow-[0_0_12px_rgba(33,79,224,0.3)]">
                            <i class="fa-solid fa-cart-plus text-xs"></i>
                            <span>Add to Quote</span>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-12 bg-slate-50 dark:bg-[#111827] rounded-xl border border-dashed border-slate-300 dark:border-slate-700">
                <i class="fa-solid fa-boxes-stacked text-3xl text-slate-400 mb-2"></i>
                <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No featured products available at this moment.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- System-Engineered Illumination & Procurement Bento Grid -->
<section class="py-16 bg-slate-50 dark:bg-[#070b14] border-t border-slate-200 dark:border-slate-800 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mb-12">
            <span class="inline-flex items-center gap-2 text-xs font-black tracking-widest text-[#214fe0] dark:text-[#60a5fa] uppercase">
                <span class="w-2 h-2 rounded-full bg-[#214fe0] dark:bg-[#60a5fa]"></span>
                System-Engineered Architecture
            </span>
            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight mt-2">
                Optical Engineering Rigor. Direct Commercial Assurance.
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-2.5 font-normal leading-relaxed">
                Huenics bridges Japanese precision optoelectronics with auditable commercial procurement: official BIR documentation, dedicated site logistics, and factory-level hardware guarantees.
            </p>
        </div>

        <!-- Asymmetrical Bento Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Cell 1: Photometric Accuracy & Citizen Japan C.O.B (7 cols) -->
            <div class="lg:col-span-7 bento-surface rounded-3xl p-7 sm:p-8 flex flex-col justify-between relative overflow-hidden group">
                <div class="relative z-10">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-50 dark:bg-blue-950/60 text-[#214fe0] dark:text-[#60a5fa] border border-blue-200 dark:border-blue-900/60">
                            <i class="fa-solid fa-microchip"></i> Optical Engineering Standard
                        </span>
                        <span class="text-[11px] font-mono tabular-nums text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                            Citizen Japan C.O.B
                        </span>
                    </div>

                    <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Color Consistency &amp; Photometric Accuracy
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-2.5 leading-relaxed">
                        In commercial facilities and retail architecture, color rendering is mission-critical. Huenics fixtures leverage genuine Citizen Japan Chip-on-Board LED engines to ensure tight MacAdam ellipse color binning, high CRI spectrum fidelity, and long-term lumen maintenance without chromatic drift.
                    </p>
                </div>

                <!-- Contextual Technical Data Rail -->
                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-100 dark:border-slate-800">
                        <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Color Fidelity</div>
                        <div class="text-lg font-black font-mono tabular-nums text-[#214fe0] dark:text-[#60a5fa] mt-0.5">Ra &ge; 90</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">High CRI spectrum</div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-100 dark:border-slate-800">
                        <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Efficacy</div>
                        <div class="text-lg font-black font-mono tabular-nums text-[#214fe0] dark:text-[#60a5fa] mt-0.5">120 lm/W</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Luminous output</div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-100 dark:border-slate-800">
                        <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Driver Efficiency</div>
                        <div class="text-lg font-black font-mono tabular-nums text-[#214fe0] dark:text-[#60a5fa] mt-0.5">PF &gt; 0.95</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Power factor rated</div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-100 dark:border-slate-800">
                        <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Lifespan</div>
                        <div class="text-lg font-black font-mono tabular-nums text-[#214fe0] dark:text-[#60a5fa] mt-0.5">50,000h</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">L70 lumen rating</div>
                    </div>
                </div>
            </div>

            <!-- Cell 2: Lighting Clinic & Enercon Lab (5 cols) -->
            <div class="lg:col-span-5 bg-gradient-to-br from-[#1a42be] via-[#153396] to-[#0b1742] dark:from-[#0d1b4a] dark:via-[#0a1334] dark:to-[#050b1d] text-white rounded-3xl p-7 sm:p-8 flex flex-col justify-between relative overflow-hidden border border-blue-600/30 dark:border-blue-900/40 shadow-xl">
                <div class="absolute inset-0 pointer-events-none opacity-10" style="background: repeating-linear-gradient(-45deg, #ffffff, #ffffff 2px, transparent 2px, transparent 12px);"></div>

                <div class="relative z-10">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-400/20 text-amber-300 border border-amber-400/30">
                            <i class="fa-solid fa-wrench"></i> Lighting Clinic &bull; Enercon
                        </span>
                        <a href="{{ route('customer.about') }}" class="text-xs font-bold text-amber-300 hover:text-white transition-colors flex items-center gap-1">
                            <span>Diagnostic Lab</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>

                    <h3 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                        Component Repair &amp; Retrofit Lab
                    </h3>
                    <p class="text-xs text-blue-100 mt-2.5 leading-relaxed">
                        Dedicated hardware engineering program designed to maximize asset lifecycle, cut operational expenses, and minimize electronic waste.
                    </p>
                </div>

                <div class="relative z-10 mt-6 space-y-2.5">
                    <div class="bg-white/10 dark:bg-[#111c38]/90 backdrop-blur px-4 py-2.5 rounded-xl border border-white/15 dark:border-blue-800/40 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-screwdriver-wrench text-amber-300 text-xs"></i>
                            <span class="text-xs font-bold text-white">Component Repair</span>
                        </div>
                        <span class="text-[10px] text-blue-200 font-medium">Downlights, Tracklights, Highbays</span>
                    </div>
                    <div class="bg-white/10 dark:bg-[#111c38]/90 backdrop-blur px-4 py-2.5 rounded-xl border border-white/15 dark:border-blue-800/40 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-bolt text-emerald-300 text-xs"></i>
                            <span class="text-xs font-bold text-white">C.O.B Engine Upgrade</span>
                        </div>
                        <span class="text-[10px] text-blue-200 font-medium">Citizen LED array conversions</span>
                    </div>
                    <div class="bg-white/10 dark:bg-[#111c38]/90 backdrop-blur px-4 py-2.5 rounded-xl border border-white/15 dark:border-blue-800/40 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-arrows-rotate text-blue-300 text-xs"></i>
                            <span class="text-xs font-bold text-white">Fixture Retrofitting</span>
                        </div>
                        <span class="text-[10px] text-blue-200 font-medium">Legacy to high-efficacy LED</span>
                    </div>
                </div>
            </div>

            <!-- Cell 3: 12% BIR Official VAT Invoicing (4 cols) -->
            <div class="lg:col-span-4 bento-surface rounded-3xl p-6 sm:p-7 flex flex-col justify-between">
                <div>
                    <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-[#214fe0] dark:text-[#60a5fa] flex items-center justify-center text-lg font-bold border border-blue-100 dark:border-blue-900/60 mb-4">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <h4 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
                        12% BIR Official VAT Invoicing
                    </h4>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
                        Full corporate accounting compliance with serialized Bureau of Internal Revenue (BIR) Sales Invoices (SI), Official Receipts, and itemized delivery documentation for straightforward auditable creditable withholding.
                    </p>
                </div>
                <div class="mt-5 pt-3.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px]">
                    <span class="font-semibold text-slate-500 dark:text-slate-400">Pricing Basis</span>
                    <span class="font-mono tabular-nums font-bold text-[#214fe0] dark:text-[#60a5fa]">100% VAT Inclusive</span>
                </div>
            </div>

            <!-- Cell 4: Metro Manila Free Delivery (4 cols) -->
            <div class="lg:col-span-4 bento-surface rounded-3xl p-6 sm:p-7 flex flex-col justify-between">
                <div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg font-bold border border-emerald-100 dark:border-emerald-900/60 mb-4">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                    <h4 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
                        Metro Manila Jobsite Logistics
                    </h4>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
                        Complimentary dedicated site delivery across Metro Manila for wholesale and project purchase orders valued at <strong class="font-mono tabular-nums text-slate-900 dark:text-white">₱ 20,000.00</strong> and above. Coordinated directly with your site engineers.
                    </p>
                </div>
                <div class="mt-5 pt-3.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px]">
                    <span class="font-semibold text-slate-500 dark:text-slate-400">Free Freight Threshold</span>
                    <span class="font-mono tabular-nums font-bold text-emerald-600 dark:text-emerald-400">&ge; ₱ 20,000.00</span>
                </div>
            </div>

            <!-- Cell 5: Hardware Assurance & Warranty (4 cols) -->
            <div class="lg:col-span-4 bento-surface rounded-3xl p-6 sm:p-7 flex flex-col justify-between">
                <div>
                    <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg font-bold border border-amber-100 dark:border-amber-900/60 mb-4">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h4 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
                        Hardware Assurance &amp; Swap Window
                    </h4>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
                        1 to 2 Years manufacturer hardware warranty without physical alteration. Standard 7-day model swap window for project spec changes and 30-day outright replacement for verified factory defects.
                    </p>
                </div>
                <div class="mt-5 pt-3.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px]">
                    <span class="font-semibold text-slate-500 dark:text-slate-400">Standard Coverage</span>
                    <span class="font-mono tabular-nums font-bold text-amber-600 dark:text-amber-400">1 – 2 Years Active</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner (PDF Royal Blue Theme) -->
<section class="py-14 bg-[#214fe0] dark:bg-gradient-to-r dark:from-[#152e80] dark:to-[#0d1d52] text-white text-center relative overflow-hidden border-t dark:border-blue-900/50">
    <div class="absolute inset-0 pointer-events-none opacity-10" style="background: repeating-linear-gradient(45deg, #ffffff, #ffffff 3px, transparent 3px, transparent 15px);"></div>
    <div class="max-w-4xl mx-auto px-4 relative z-10">
        <h2 class="text-2xl sm:text-4xl font-black tracking-tight mb-4">
            Need a Formal Commercial Quotation for Your Project?
        </h2>
        <p class="text-sm sm:text-base text-blue-100 mb-8 max-w-2xl mx-auto font-normal">
            Build your estimate online or contact our sales and technical department directly for project-specific pricing.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('customer.quotation-builder') }}" 
               class="bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold px-7 py-3.5 rounded-xl shadow-lg transition text-sm">
                <i class="fa-solid fa-calculator mr-2"></i> Start Quotation Builder
            </a>
            <a href="{{ route('customer.about') }}" 
               class="bg-white/15 hover:bg-white/25 border-2 border-white/30 text-white font-bold px-7 py-3.5 rounded-xl transition text-sm">
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
