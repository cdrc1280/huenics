@extends('layouts.customer')

@section('title', 'Huenics Industrial Sales Inc. - Colors • Techniques • Technology')

@section('content')
<!-- Hero Section (PDF Design: Crisp White, Royal Blue & Geometric Accents) -->
<section class="relative bg-white overflow-hidden py-14 lg:py-20 border-b border-slate-200 his-geometric-accent">
    <!-- PDF Inspired Diagonal Stripes & Corner Geometry -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-blue-600/15 via-blue-500/5 to-transparent pointer-events-none -z-0"></div>
    <div class="absolute -bottom-10 -left-10 w-80 h-80 pointer-events-none opacity-40 -z-0" style="background: repeating-linear-gradient(45deg, rgba(33, 79, 224, 0.07), rgba(33, 79, 224, 0.07) 3px, transparent 3px, transparent 12px);"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <!-- Tagline Badge -->
                <div class="inline-flex items-center gap-2 bg-blue-50 border border-blue-200 text-[#214fe0] px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-[#214fe0] animate-pulse"></span>
                    Direct Importer & Wholesale Engineering Supplier &bull; HISI
                </div>
                
                <!-- Main Title from PDF Cover -->
                <div class="space-y-2">
                    <div class="text-xs sm:text-sm font-extrabold uppercase tracking-widest text-slate-500">
                        Company Profile &bull; Product Catalog
                    </div>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-slate-950 leading-[1.12]">
                        Colors &bull; Techniques <br class="hidden sm:inline">
                        <span class="text-[#214fe0]">
                            & Technology
                        </span>
                    </h1>
                </div>

                <!-- Slogan from Page 3 -->
                <div class="p-3.5 bg-blue-50/80 border-l-4 border-[#214fe0] rounded-r-lg max-w-xl mx-auto lg:mx-0">
                    <p class="text-xs sm:text-sm font-extrabold text-blue-950 italic">
                        "Focus on Pursuing Quality & Speed of Delivery."
                    </p>
                </div>

                <p class="text-sm sm:text-base text-slate-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    Direct importer and wholesale distributor of commercial LED downlights, Citizen Japan C.O.B modules, indent orders, and certified electrical infrastructure materials across the Philippines.
                </p>

                <!-- Actions -->
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5">
                    <a href="{{ route('customer.quotation-builder') }}" 
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold px-6 py-3.5 rounded-xl shadow-lg shadow-blue-600/25 transition transform hover:-translate-y-0.5 text-sm sm:text-base">
                        <i class="fa-solid fa-file-signature"></i>
                        <span>Generate Instant Quotation (PDF)</span>
                    </a>
                    <a href="{{ route('customer.products') }}" 
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white hover:bg-slate-50 text-slate-800 border-2 border-slate-300 hover:border-[#214fe0] font-bold px-6 py-3.5 rounded-xl transition text-sm sm:text-base shadow-sm">
                        <i class="fa-solid fa-boxes-stacked text-[#214fe0]"></i>
                        <span>Browse Product Catalog</span>
                    </a>
                </div>

                <!-- Company Stats -->
                <div class="pt-6 grid grid-cols-2 sm:grid-cols-3 gap-4 border-t border-slate-200">
                    <div class="text-center lg:text-left" title="Total active products in our catalog">
                        <div class="text-2xl sm:text-3xl font-black text-[#214fe0]">{{ number_format($totalProductsCount) }}+</div>
                        <div class="text-xs font-semibold text-slate-600">Active Products</div>
                    </div>
                    <div class="text-center lg:text-left" title="Different categories of supplies we offer">
                        <div class="text-2xl sm:text-3xl font-black text-[#214fe0]">{{ $categories->count() }}</div>
                        <div class="text-xs font-semibold text-slate-600">Product Categories</div>
                    </div>
                    <div class="text-center lg:text-left" title="Years serving the industry">
                        <div class="text-2xl sm:text-3xl font-black text-[#214fe0]">{{ $yearsInBusiness }}</div>
                        <div class="text-xs font-semibold text-slate-600">Years in Business</div>
                    </div>
                </div>
            </div>

            <!-- Hero Quick Quote Card Preview (Clean White with Royal Blue Header) -->
            <div class="lg:col-span-5">
                <div class="bg-white border-2 border-blue-600/20 rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-[#1a42be] text-white p-4 flex items-center justify-between">
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

                    <div class="p-5 space-y-3.5 text-xs text-slate-600">
                        <div class="bg-blue-50/50 p-3 rounded-lg border border-blue-100 space-y-2">
                            <div class="text-blue-900 text-[10px] uppercase font-extrabold tracking-wider">Featured Commercial Line Items:</div>
                            @foreach($featuredProducts->take(2) as $prod)
                            <div class="flex justify-between items-center text-xs font-medium">
                                <span class="truncate pr-2 font-semibold text-slate-800" title="{{ $prod->canonical_name }}">{{ $prod->canonical_name }}</span>
                                <span class="text-[#214fe0] font-black whitespace-nowrap">₱ {{ number_format($prod->selling_price ?: $prod->default_price, 2) }}</span>
                            </div>
                            @endforeach
                        </div>

                        <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200 space-y-1.5">
                            <div class="text-xs text-slate-600">Build your project BOQ estimate and download an immediate formal PDF quotation.</div>
                            <div class="flex justify-between text-slate-900 font-bold text-xs pt-2 border-t border-slate-200">
                                <span>Official Pricing</span>
                                <span class="text-[#214fe0] font-black">12% VAT Included</span>
                            </div>
                        </div>

                        <a href="{{ route('customer.quotation-builder') }}" 
                           class="w-full flex items-center justify-center gap-2 bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold py-3 rounded-lg transition text-xs shadow-sm">
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
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Product Portfolio</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                    Commercial & Architectural Lighting
                </h2>
                <p class="text-sm text-slate-500 mt-1 max-w-xl">
                    Explore our catalog of Citizen Japan C.O.B downlights, highbays, tracklights, architectural linear profiles, and smart digital systems.
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
            <div class="bg-white border-2 border-slate-200 hover:border-[#214fe0] rounded-xl p-5 hover:shadow-lg transition group flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start gap-2 mb-3">
                        <span class="hisi-pill-badge">
                            {{ strtoupper($product->category ?: 'General Supplies') }}
                        </span>
                        <span class="text-xs font-mono text-slate-500 font-bold bg-slate-100 px-2 py-0.5 rounded">
                            {{ $product->sku ?: $product->product_code ?: 'SKU-00' }}
                        </span>
                    </div>

                    <h3 class="text-base font-bold text-slate-900 group-hover:text-[#214fe0] transition mb-2">
                        {{ $product->canonical_name }}
                    </h3>

                    <p class="text-xs text-slate-500 line-clamp-2 mb-4 leading-relaxed">
                        {{ $product->description ?: 'Certified commercial and industrial lighting product from official Huenics catalog.' }}
                    </p>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <div class="flex justify-between items-baseline mb-3">
                        <span class="text-xs text-slate-500 font-medium">Catalog Price:</span>
                        <div class="text-right">
                            <span class="text-base font-black text-[#214fe0]">
                                ₱ {{ number_format($product->selling_price ?: $product->default_price ?: 0, 2) }}
                            </span>
                            <span class="text-[11px] text-slate-500 font-normal"> / {{ $product->unit_default ?: 'pcs' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="number" id="qty-{{ $product->id }}" value="1" min="1" step="1" 
                               class="w-16 text-center text-xs font-bold border border-slate-300 rounded-lg py-2 focus:ring-2 focus:ring-[#214fe0] focus:outline-none">
                        <button type="button" 
                                onclick="addProductToQuote({{ json_encode($product) }}, document.getElementById('qty-{{ $product->id }}').value)"
                                class="flex-1 bg-[#214fe0] hover:bg-[#1a42be] text-white text-xs font-bold py-2.5 px-3 rounded-lg transition flex items-center justify-center gap-1.5 shadow-sm">
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

<!-- Three Core Pillars (Page 4) -->
<section class="py-16 bg-slate-100 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600">The Huenics Advantage</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                Colors &bull; Techniques &bull; Technology
            </h2>
            <p class="text-sm text-slate-600 mt-2 font-medium italic">
                "Focus on Pursuing Quality & Speed of Delivery."
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Right Color -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-palette"></i>
                </div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Our Unique Feature</span>
                <h3 class="text-lg font-bold text-slate-900">Right Color</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    In commercial and industrial illumination, color is everything. Huenics LED Technology achieves exacting color temperatures and high CRI rendering—allowing customers to achieve the best possible illumination.
                </p>
            </div>

            <!-- Utilizing Techniques -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-gears"></i>
                </div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Our Passion</span>
                <h3 class="text-lg font-bold text-slate-900">Utilizing Techniques</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    The moment the client speaks, we listen. Huenics products are designed to the customer's greatest advantage through the continuous development of optical and engineering techniques.
                </p>
            </div>

            <!-- Stand Out Technology -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-microchip"></i>
                </div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Our Pride</span>
                <h3 class="text-lg font-bold text-slate-900">Stand Out Technology</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Efficiency draws in customers and ensures repeat partnership. Becoming the option of choice for customers interested in value engineering and creating an ideal brightest atmosphere.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Lighting Clinic & Value-Added Services (Pages 24-26) -->
<section class="py-16 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-br from-[#1a42be] via-[#153396] to-[#0b1742] text-white rounded-3xl p-8 sm:p-12 shadow-2xl relative overflow-hidden">
            <!-- Diagonal pinstripe overlay from PDF -->
            <div class="absolute inset-0 pointer-events-none opacity-10" style="background: repeating-linear-gradient(-45deg, #ffffff, #ffffff 2px, transparent 2px, transparent 12px);"></div>

            <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-6 space-y-4">
                    <span class="inline-flex items-center gap-1.5 bg-amber-400/20 text-amber-300 border border-amber-400/30 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                        <i class="fa-solid fa-wrench"></i> Value-Added Services &bull; Pages 24-26
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight">
                        LIGHTING CLINIC <span class="text-amber-400">&bull;</span> Enercon
                    </h2>
                    <p class="text-blue-200 text-xs sm:text-sm font-bold uppercase tracking-wider">
                        Energy Conservation &bull; Repair &bull; Upgrade &bull; Retrofit
                    </p>
                    <p class="text-slate-200 text-xs sm:text-sm leading-relaxed">
                        Our Enercon program provides comprehensive energy management and cost analysis in lighting. Extend fixture life, reduce replacement costs, and improve luminous efficacy across your facilities.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('customer.about') }}" class="inline-flex items-center gap-2 text-xs font-extrabold text-amber-300 hover:text-white transition">
                            <span>Learn more about Lighting Clinic</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="bg-white/10 backdrop-blur p-4 rounded-xl border border-white/15 space-y-1.5 text-center sm:text-left">
                        <div class="text-amber-300 font-extrabold text-xs uppercase flex items-center gap-1.5">
                            <i class="fa-solid fa-screwdriver-wrench"></i> We Repair
                        </div>
                        <p class="text-[11px] text-slate-200 leading-snug">
                            Downlights, Tracklights, Highbays, and more as long as major parts exist.
                        </p>
                    </div>
                    <div class="bg-white/10 backdrop-blur p-4 rounded-xl border border-white/15 space-y-1.5 text-center sm:text-left">
                        <div class="text-emerald-300 font-extrabold text-xs uppercase flex items-center gap-1.5">
                            <i class="fa-solid fa-bolt"></i> We Upgrade
                        </div>
                        <p class="text-[11px] text-slate-200 leading-snug">
                            Upgrade existing LED lights into high quality Citizen LED C.O.B chips.
                        </p>
                    </div>
                    <div class="bg-white/10 backdrop-blur p-4 rounded-xl border border-white/15 space-y-1.5 text-center sm:text-left">
                        <div class="text-blue-300 font-extrabold text-xs uppercase flex items-center gap-1.5">
                            <i class="fa-solid fa-arrows-rotate"></i> We Retrofit
                        </div>
                        <p class="text-[11px] text-slate-200 leading-snug">
                            Retrofit old or traditional fixtures to modern high-efficiency LED.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Huenics Section -->
<section class="py-16 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0]">Our Commitments</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                Built for Quality, Speed & Dependability
            </h2>
            <p class="text-sm text-slate-500 mt-2">
                Providing innovative and reliable quality products that go beyond expectations.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#214fe0] flex items-center justify-center text-xl font-bold border border-blue-100">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">VAT Inclusive Invoicing</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    All prices are VAT Inclusive (VAT INC.). Full commercial documentation with Sales Invoices (SI) and official vendor agreements.
                </p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold border border-emerald-100">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Free Metro Manila Delivery (₱20k+)</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Minimum order of Php 20,000.00 and above enjoys Free Delivery within Metro Manila. Outside Metro Manila shipment cost applied.
                </p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold border border-amber-100">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Official Product Warranty</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    1 to 2 Years limited warranty w/o physical damage. 7 days item change policy and 1 mo. outright defective replacement.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner (PDF Royal Blue Theme) -->
<section class="py-14 bg-[#214fe0] text-white text-center relative overflow-hidden">
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
