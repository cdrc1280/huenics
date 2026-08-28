@extends('layouts.customer')

@section('title', 'About Us - Huenics Industrial Sales Inc.')

@section('content')
<!-- About Header Hero -->
<section class="bg-slate-900 text-white py-16 lg:py-20 relative overflow-hidden">
    <div class="absolute inset-0 opacity-15 bg-[radial-gradient(#3b82f6_1px,transparent_1px)] [background-size:16px_16px]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 bg-blue-500/20 text-blue-300 border border-blue-500/30 px-3.5 py-1 rounded-full text-xs font-semibold mb-4">
                <i class="fa-solid fa-lightbulb"></i> Company Profile &bull; HISI
            </span>
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight">
                Huenics Industrial Sales Inc.
            </h1>
            <p class="text-blue-400 font-bold text-sm sm:text-base tracking-widest uppercase mt-2">
                Colors &bull; Techniques &bull; Technology
            </p>
            <p class="text-slate-300 text-base sm:text-lg mt-4 leading-relaxed font-normal">
                Emerged from the strength in combining technical expertise to meet customers' demands in providing innovative and reliable quality products. Committed to go beyond expectations.
            </p>
        </div>
    </div>
</section>

<!-- About Us Story & Origin (Page 2 & 3) -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-6 space-y-5">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Our Story & Commitment</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    "Our Focus: You First..."
                </h2>
                <div class="p-4 bg-blue-50 border-l-4 border-blue-600 rounded-r-xl">
                    <p class="text-sm font-bold text-blue-950 italic">
                        "Focus on Pursuing Quality & Speed of Delivery."
                    </p>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed">
                    It has grown from a single man to a whole team of qualified employees after we identified the needs of the market. Striving for excellence in all areas of our business, systems, procedures, policies, and practices; utilized to ensure compliance with our legal obligations.
                </p>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Seeking new ideas, techniques, and innovations with energy efficiency and alternative energy generation is paramount to ensuring longevity for the industry.
                </p>

                <div class="pt-2 grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 text-center">
                        <div class="text-xl font-black text-blue-600">Citizen</div>
                        <div class="text-[11px] font-bold text-slate-900 mt-1">Japan C.O.B</div>
                        <div class="text-[10px] text-slate-500">Exacting Color Temp</div>
                    </div>
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 text-center">
                        <div class="text-xl font-black text-blue-600">1–2 Yrs</div>
                        <div class="text-[11px] font-bold text-slate-900 mt-1">Warranty</div>
                        <div class="text-[10px] text-slate-500">Limited Warranty</div>
                    </div>
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 text-center">
                        <div class="text-xl font-black text-blue-600">₱20,000+</div>
                        <div class="text-[11px] font-bold text-slate-900 mt-1">Free Delivery</div>
                        <div class="text-[10px] text-slate-500">Metro Manila</div>
                    </div>
                </div>
            </div>

            <!-- Our Unique Feature, Passion & Pride (Page 4) -->
            <div class="lg:col-span-6 space-y-4">
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 hover:border-blue-300 transition">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-lg font-bold shrink-0">
                            <i class="fa-solid fa-palette"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600">Our Unique Feature</span>
                            <h3 class="text-base font-bold text-slate-900">Right Color</h3>
                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                                In the world of Commercial & Industrial, color is everything. The importance of color and its impact on these industries cannot be overstated. Whether it is for exclusive or simple projects, Huenics LED Technology achieves exacting color temperatures and rendering—allowing customers to get the best possible illumination.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 hover:border-blue-300 transition">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg font-bold shrink-0">
                            <i class="fa-solid fa-gears"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600">Our Passion</span>
                            <h3 class="text-base font-bold text-slate-900">Utilizing Techniques</h3>
                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                                The moment the client speaks, we listen. Huenics products are designed to the customer's greatest advantage—this commitment is further demonstrated by the continuous development of techniques.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 hover:border-blue-300 transition">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg font-bold shrink-0">
                            <i class="fa-solid fa-microchip"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600">Our Pride</span>
                            <h3 class="text-base font-bold text-slate-900">Stand Out Technology</h3>
                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                                There’s more than just cost—there’s also efficiency, which draws in customers and determines whether they return. Offering unmatched quality and efficiency, Huenics is becoming the option of choice for customers interested in value engineering and creating an ideal brightest atmosphere.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Goals & Guiding Principles (Pages 5 & 6) -->
<section class="py-16 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <!-- Goals (Page 5) -->
            <div class="lg:col-span-5 space-y-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600">What Drives Us</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                        Our GOALS!
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Five bedrock metrics that guide our service execution.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center font-black text-sm">Q</div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">Quality</div>
                            <div class="text-[11px] text-slate-500">Unmatched precision</div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-black text-sm">S</div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">Speed</div>
                            <div class="text-[11px] text-slate-500">Prompt fulfillment</div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-black text-sm">D</div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">Dependable</div>
                            <div class="text-[11px] text-slate-500">Consistent execution</div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-500 text-white flex items-center justify-center font-black text-sm">F</div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">Flexibility</div>
                            <div class="text-[11px] text-slate-500">Custom indent orders</div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3 sm:col-span-2">
                        <div class="w-10 h-10 rounded-lg bg-slate-900 text-white flex items-center justify-center font-black text-sm">C</div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">Cost</div>
                            <div class="text-[11px] text-slate-500">Value engineering and wholesale pricing</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guiding Principles (Page 6) -->
            <div class="lg:col-span-7 space-y-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Company Culture</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                        Guiding Principles
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">The foundational pillars our team lives by every day.</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    @php
                        $principles = [
                            ['icon' => 'fa-handshake', 'title' => 'Partnership'],
                            ['icon' => 'fa-sun', 'title' => 'Make a Difference Every Day'],
                            ['icon' => 'fa-gem', 'title' => 'Uniqueness'],
                            ['icon' => 'fa-book-open-reader', 'title' => 'Continuous Learning'],
                            ['icon' => 'fa-telescope', 'title' => 'Long-term Vision'],
                            ['icon' => 'fa-scale-balanced', 'title' => 'Act with Integrity'],
                            ['icon' => 'fa-fire', 'title' => 'Passion'],
                            ['icon' => 'fa-award', 'title' => 'Setting Industry Standards'],
                            ['icon' => 'fa-lightbulb', 'title' => 'Innovation'],
                            ['icon' => 'fa-heart', 'title' => 'Courageous Heart'],
                            ['icon' => 'fa-arrow-trend-up', 'title' => 'Be the Next'],
                            ['icon' => 'fa-shield-halved', 'title' => 'Build a Foundation of Trust'],
                        ];
                    @endphp
                    @foreach($principles as $p)
                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex items-center gap-2.5">
                        <i class="fa-solid {{ $p['icon'] }} text-blue-600 text-xs shrink-0"></i>
                        <span class="text-xs font-semibold text-slate-800 leading-snug">{{ $p['title'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Lighting Clinic (Pages 24, 25, 26) -->
<section class="py-16 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-blue-950 text-white rounded-3xl p-8 sm:p-12 shadow-2xl overflow-hidden relative">
            <div class="max-w-3xl space-y-4 relative z-10">
                <span class="inline-flex items-center gap-1.5 bg-amber-400/20 text-amber-300 border border-amber-400/30 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                    <i class="fa-solid fa-wrench"></i> Value-Added Services
                </span>
                <h2 class="text-2xl sm:text-4xl font-black tracking-tight">
                    LIGHTING CLINIC <span class="text-amber-400">&bull;</span> Enercon
                </h2>
                <p class="text-blue-300 text-xs sm:text-sm font-semibold uppercase tracking-wider">
                    Repair &bull; Upgrade &bull; Retrofit
                </p>
                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                    <strong>Enercon (Energy Conservation)</strong> is our dedicated energy management and lighting cost analysis program. We provide specialized technical services to extend the lifespan of your fixtures and maximize energy efficiency:
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                    <div class="bg-slate-800/80 p-5 rounded-2xl border border-slate-700 space-y-2">
                        <div class="text-amber-400 font-extrabold text-sm uppercase flex items-center gap-1.5">
                            <i class="fa-solid fa-screwdriver-wrench"></i> We Repair
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            We repair different kinds of fixtures whether it is Downlights, Tracklights, Highbays, etc. as long as it has its own major parts.
                        </p>
                    </div>

                    <div class="bg-slate-800/80 p-5 rounded-2xl border border-slate-700 space-y-2">
                        <div class="text-emerald-400 font-extrabold text-sm uppercase flex items-center gap-1.5">
                            <i class="fa-solid fa-bolt"></i> We Upgrade
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            We upgrade existing LED lights into high-quality, high-efficiency LED C.O.B Chips with improved thermal dissipation.
                        </p>
                    </div>

                    <div class="bg-slate-800/80 p-5 rounded-2xl border border-slate-700 space-y-2">
                        <div class="text-blue-400 font-extrabold text-sm uppercase flex items-center gap-1.5">
                            <i class="fa-solid fa-arrows-rotate"></i> We Retrofit
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            We upgrade and retrofit old fixtures or traditional lighting systems and convert them into high-performance LED C.O.B systems.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Global Support Network (Page 7) -->
<section class="py-16 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Technology Ecosystem</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                Global Support Network
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                We partner with tier-1 international component manufacturers to engineer commercial fixtures that endure.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-blue-600">LED Solutions</div>
                <div class="space-y-1 text-sm font-bold text-slate-800">
                    <div>Citizen <span class="text-[11px] font-normal text-slate-500">(Micro HumanTech Japan)</span></div>
                    <div>OSRAM</div>
                    <div>Lumileds</div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-emerald-600">Thermal & Optical</div>
                <div class="space-y-1 text-sm font-bold text-slate-800">
                    <div>Khatod Optical Solutions</div>
                    <div>MechaTronix</div>
                    <div>Darkoo</div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-indigo-600">Driver Solutions</div>
                <div class="space-y-1 text-sm font-bold text-slate-800">
                    <div>Philips LED Driver</div>
                    <div>Inventronics</div>
                    <div>Hyperion Technology</div>
                    <div>DONE</div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-amber-600">Holders & Connectors</div>
                <div class="space-y-1 text-sm font-bold text-slate-800">
                    <div>Molex</div>
                    <div>BJB <span class="text-[11px] font-normal text-slate-500">(Technology for Light)</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Corporate Details & Contact Card (Page 7) -->
<section class="py-16 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-slate-900 text-white rounded-2xl p-8 sm:p-12 shadow-xl grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-7 space-y-4">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Head Office & Inquiries</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Partner With Huenics Industrial Sales Inc.
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Reach out for commercial lighting layout consulting, custom indent orders, sample demonstrations, or competitive project pricing.
                </p>
                <div class="pt-2 flex flex-col gap-2.5 text-xs">
                    <div class="bg-slate-800 px-3.5 py-2.5 rounded-lg border border-slate-700">
                        <span class="text-slate-400 font-semibold">Office Address:</span>
                        <span class="font-medium text-white ml-1">Unit 916 Avida Towers Intima, 497 Pres. Quirino Ave. Ext. Cor. Zulueta St., Paco, Manila</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div class="bg-slate-800 px-3.5 py-2.5 rounded-lg border border-slate-700">
                            <span class="text-slate-400 font-semibold">Contact No:</span>
                            <span class="font-medium text-white ml-1">Tel. #8561 6836</span>
                        </div>
                        <div class="bg-slate-800 px-3.5 py-2.5 rounded-lg border border-slate-700">
                            <span class="text-slate-400 font-semibold">Hotlines:</span>
                            <span class="font-medium text-white ml-1">+63 968 8500720 / +63 965 6287205</span>
                        </div>
                    </div>
                    <div class="bg-slate-800 px-3.5 py-2.5 rounded-lg border border-slate-700">
                        <span class="text-slate-400 font-semibold">Official Email:</span>
                        <span class="font-medium text-white ml-1">huenicsindustrialsales@gmail.com &bull; crm.huenics777@gmail.com</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 flex flex-col sm:flex-row lg:flex-col gap-3 justify-center">
                <a href="{{ route('customer.quotation-builder') }}" 
                   class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-bold py-3.5 px-6 rounded-xl transition text-sm text-center">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Generate Instant Unofficial Estimate</span>
                </a>
                <a href="{{ route('customer.products') }}" 
                   class="inline-flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold py-3.5 px-6 rounded-xl transition text-sm text-center">
                    <i class="fa-solid fa-box-open"></i>
                    <span>Explore Products Catalog</span>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

