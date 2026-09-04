@extends('layouts.customer')

@section('title', 'About Us - Huenics Industrial Sales Inc.')

@section('content')
<!-- About Header Hero (PDF Design: Crisp White in Light / Sleek Obsidian in Dark) -->
<section class="relative bg-white dark:bg-[#070b14] ambient-mesh-hero py-14 lg:py-18 border-b border-slate-200 dark:border-slate-800/80 overflow-hidden hisi-geometric-accent transition-colors duration-200">
    <!-- Diagonal Stripes Accent -->
    <div class="absolute top-0 right-0 w-72 h-72 bg-gradient-to-bl from-[#214fe0]/15 dark:from-blue-500/10 via-blue-500/5 to-transparent pointer-events-none"></div>
    <div class="absolute -bottom-10 -left-10 w-64 h-64 pointer-events-none opacity-30 dark:opacity-20" style="background: repeating-linear-gradient(45deg, rgba(33, 79, 224, 0.08), rgba(33, 79, 224, 0.08) 3px, transparent 3px, transparent 12px);"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl space-y-3 animate-fade-in-up">
            <div class="inline-flex items-center gap-2 bg-blue-50 dark:bg-blue-950/70 text-[#214fe0] dark:text-[#60a5fa] border border-blue-200 dark:border-blue-800/60 px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">
                <i class="fa-solid fa-lightbulb"></i> Company Profile &bull; HISI Engineering
            </div>
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-slate-950 dark:text-white">
                About Huenics Industrial Sales Inc.
            </h1>
            <p class="text-[#214fe0] dark:text-[#60a5fa] font-black text-sm sm:text-base tracking-widest uppercase">
                Colors &bull; Techniques &bull; Technology
            </p>
            <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base leading-relaxed font-normal pt-1">
                Emerged from the strength in combining technical expertise to meet customers' demands in providing innovative and reliable quality products. Committed to go beyond expectations.
            </p>
        </div>
    </div>
</section>

<!-- About Us Story & Heritage -->
<section class="py-16 bg-white dark:bg-[#0a0e1a] transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-6 space-y-5">
                <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">Our Story & Commitment</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    "Our Focus: You First..."
                </h2>
                <div class="p-4 bg-blue-50 dark:bg-[#111827] border-l-4 border-blue-600 dark:border-[#3b82f6] rounded-r-xl shadow-sm">
                    <p class="text-sm font-bold text-blue-950 dark:text-blue-200 italic">
                        "Focus on Pursuing Quality & Speed of Delivery."
                    </p>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    It has grown from a single man to a whole team of qualified employees after we identified the needs of the market. Striving for excellence in all areas of our business, systems, procedures, policies, and practices; utilized to ensure compliance with our legal obligations.
                </p>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Seeking new ideas, techniques, and innovations with energy efficiency and alternative energy generation is paramount to ensuring longevity for the industry.
                </p>

                <div class="pt-2 grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="bg-slate-50 dark:bg-[#111827] p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 text-center">
                        <div class="text-xl font-black text-[#214fe0] dark:text-[#60a5fa]">Citizen</div>
                        <div class="text-[11px] font-bold text-slate-900 dark:text-slate-200 mt-1">Japan C.O.B</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400">Exacting Color Temp</div>
                    </div>
                    <div class="bg-slate-50 dark:bg-[#111827] p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 text-center">
                        <div class="text-xl font-black text-[#214fe0] dark:text-[#60a5fa] font-mono tabular-nums">1–2 Yrs</div>
                        <div class="text-[11px] font-bold text-slate-900 dark:text-slate-200 mt-1">Warranty</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400">Limited Warranty</div>
                    </div>
                    <div class="bg-slate-50 dark:bg-[#111827] p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 text-center">
                        <div class="text-xl font-black text-[#214fe0] dark:text-[#60a5fa] font-mono tabular-nums">₱ 20,000+</div>
                        <div class="text-[11px] font-bold text-slate-900 dark:text-slate-200 mt-1">Free Delivery</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400">Metro Manila</div>
                    </div>
                </div>
            </div>

            <!-- Core Capabilities: Color, Techniques, Technology -->
            <div class="lg:col-span-6 space-y-4">
                <div class="bg-slate-50 dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 card-interactive hover:border-blue-300 dark:hover:border-[#3b82f6] shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg font-bold shrink-0">
                            <i class="fa-solid fa-palette"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Our Unique Feature</span>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Right Color</h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">
                                In the world of Commercial & Industrial, color is everything. The importance of color and its impact on these industries cannot be overstated. Whether it is for exclusive or simple projects, Huenics LED Technology achieves exacting color temperatures and rendering—allowing customers to get the best possible illumination.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 card-interactive hover:border-blue-300 dark:hover:border-[#3b82f6] shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-[#214fe0] dark:text-[#60a5fa] flex items-center justify-center text-lg font-bold shrink-0">
                            <i class="fa-solid fa-gears"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">Our Passion</span>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Utilizing Techniques</h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">
                                The moment the client speaks, we listen. Huenics products are designed to the customer's greatest advantage—this commitment is further demonstrated by the continuous development of techniques.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 card-interactive hover:border-blue-300 dark:hover:border-[#3b82f6] shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg font-bold shrink-0">
                            <i class="fa-solid fa-microchip"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Our Pride</span>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Stand Out Technology</h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">
                                There’s more than just cost—there’s also efficiency, which draws in customers and determines whether they return. Offering unmatched quality and efficiency, Huenics is becoming the option of choice for customers interested in value engineering and creating an ideal brightest atmosphere.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Goals & Guiding Principles -->
<section class="py-16 bg-slate-50 dark:bg-[#070b14] border-t border-slate-200 dark:border-slate-800 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <!-- Strategic Performance Goals -->
            <div class="lg:col-span-5 space-y-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">What Drives Us</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                        Our Strategic Goals
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Five bedrock metrics that guide our service execution.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-white dark:bg-[#111827] p-4 rounded-xl border border-slate-200 dark:border-slate-800 card-interactive shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-600 dark:bg-blue-500 text-white flex items-center justify-center font-black text-sm">Q</div>
                        <div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white">Quality</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">Unmatched precision</div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#111827] p-4 rounded-xl border border-slate-200 dark:border-slate-800 card-interactive shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-black text-sm">S</div>
                        <div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white">Speed</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">Prompt fulfillment</div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#111827] p-4 rounded-xl border border-slate-200 dark:border-slate-800 card-interactive shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-black text-sm">D</div>
                        <div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white">Dependable</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">Consistent execution</div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#111827] p-4 rounded-xl border border-slate-200 dark:border-slate-800 card-interactive shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-500 text-white flex items-center justify-center font-black text-sm">F</div>
                        <div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white">Flexibility</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">Custom indent orders</div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#111827] p-4 rounded-xl border border-slate-200 dark:border-slate-800 card-interactive shadow-sm flex items-center gap-3 sm:col-span-2">
                        <div class="w-10 h-10 rounded-lg bg-slate-900 dark:bg-blue-600 text-white flex items-center justify-center font-black text-sm">C</div>
                        <div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white">Cost</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">Value engineering & wholesale pricing</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guiding Principles -->
            <div class="lg:col-span-7 space-y-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">Company Culture</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                        Guiding Principles
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">The foundational pillars our team lives by every day.</p>
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
                    <div class="bg-white dark:bg-[#111827] p-3 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-2.5">
                        <i class="fa-solid {{ $p['icon'] }} text-[#214fe0] dark:text-[#60a5fa] text-xs shrink-0"></i>
                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 leading-snug">{{ $p['title'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Lighting Clinic & Technical Maintenance -->
<section class="py-16 bg-white dark:bg-[#0a0e1a] border-t border-slate-200 dark:border-slate-800 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-blue-950 dark:from-[#0c1636] dark:via-[#091129] dark:to-[#050b1d] text-white rounded-3xl p-8 sm:p-12 shadow-2xl overflow-hidden relative border border-transparent dark:border-blue-900/40">
            <div class="max-w-3xl space-y-4 relative z-10">
                <span class="inline-flex items-center gap-1.5 bg-amber-400/20 text-amber-300 border border-amber-400/30 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                    <i class="fa-solid fa-wrench"></i> Specialized Engineering Services
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
                    <div class="bg-slate-800/80 dark:bg-[#111c3a]/90 p-5 rounded-2xl border border-slate-700 dark:border-blue-800/40 space-y-2 card-interactive">
                        <div class="text-amber-400 font-extrabold text-sm uppercase flex items-center gap-1.5">
                            <i class="fa-solid fa-screwdriver-wrench"></i> We Repair
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            We repair different kinds of fixtures whether it is Downlights, Tracklights, Highbays, etc. as long as it has its own major parts.
                        </p>
                    </div>

                    <div class="bg-slate-800/80 dark:bg-[#111c3a]/90 p-5 rounded-2xl border border-slate-700 dark:border-blue-800/40 space-y-2 card-interactive">
                        <div class="text-emerald-400 font-extrabold text-sm uppercase flex items-center gap-1.5">
                            <i class="fa-solid fa-bolt"></i> We Upgrade
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            We upgrade existing LED lights into high-quality, high-efficiency LED C.O.B Chips with improved thermal dissipation.
                        </p>
                    </div>

                    <div class="bg-slate-800/80 dark:bg-[#111c3a]/90 p-5 rounded-2xl border border-slate-700 dark:border-blue-800/40 space-y-2 card-interactive">
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

<!-- Global Support Network -->
<section class="py-16 bg-slate-50 dark:bg-[#070b14] border-t border-slate-200 dark:border-slate-800 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">Technology Ecosystem</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                Global Support Network
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                We partner with tier-1 international component manufacturers to engineer commercial fixtures that endure.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-[#111827] p-5 rounded-2xl border border-slate-200 dark:border-slate-800 card-interactive shadow-sm space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">LED Solutions</div>
                <div class="space-y-1 text-sm font-bold text-slate-800 dark:text-slate-200">
                    <div>Citizen <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400">(Micro HumanTech Japan)</span></div>
                    <div>OSRAM</div>
                    <div>Lumileds</div>
                </div>
            </div>

            <div class="bg-white dark:bg-[#111827] p-5 rounded-2xl border border-slate-200 dark:border-slate-800 card-interactive shadow-sm space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Thermal & Optical</div>
                <div class="space-y-1 text-sm font-bold text-slate-800 dark:text-slate-200">
                    <div>Khatod Optical Solutions</div>
                    <div>MechaTronix</div>
                    <div>Darkoo</div>
                </div>
            </div>

            <div class="bg-white dark:bg-[#111827] p-5 rounded-2xl border border-slate-200 dark:border-slate-800 card-interactive shadow-sm space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Driver Solutions</div>
                <div class="space-y-1 text-sm font-bold text-slate-800 dark:text-slate-200">
                    <div>Philips LED Driver</div>
                    <div>Inventronics</div>
                    <div>Hyperion Technology</div>
                    <div>DONE</div>
                </div>
            </div>

            <div class="bg-white dark:bg-[#111827] p-5 rounded-2xl border border-slate-200 dark:border-slate-800 card-interactive shadow-sm space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Holders & Connectors</div>
                <div class="space-y-1 text-sm font-bold text-slate-800 dark:text-slate-200">
                    <div>Molex</div>
                    <div>BJB <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400">(Technology for Light)</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Corporate Details & Contact Card -->
<section class="py-16 bg-white dark:bg-[#0a0e1a] border-t border-slate-200 dark:border-slate-800 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-br from-[#1a42be] via-[#153396] to-[#0b1742] dark:from-[#0f216b] dark:via-[#0b1742] dark:to-[#070e24] text-white rounded-3xl p-8 sm:p-12 shadow-2xl relative overflow-hidden grid grid-cols-1 lg:grid-cols-12 gap-8 items-center border border-transparent dark:border-blue-900/50">
            <!-- Diagonal pinstripes from brand styling -->
            <div class="absolute inset-0 pointer-events-none opacity-10" style="background: repeating-linear-gradient(-45deg, #ffffff, #ffffff 2px, transparent 2px, transparent 12px);"></div>

            <div class="lg:col-span-7 space-y-4 relative z-10">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-200">Head Office & Commercial Desk</span>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight">
                    Partner With Huenics Industrial Sales Inc.
                </h2>
                <p class="text-xs sm:text-sm text-slate-200 leading-relaxed">
                    Reach out for commercial lighting layout consulting, custom indent orders, sample demonstrations, or competitive project pricing.
                </p>
                <div class="pt-2 flex flex-col gap-2.5 text-xs">
                    <div class="bg-white/10 dark:bg-black/20 backdrop-blur px-3.5 py-2.5 rounded-lg border border-white/15 dark:border-white/10">
                        <span class="text-blue-200 font-semibold">Office Address:</span>
                        <span class="font-medium text-white ml-1">Unit 916 Avida Towers Intima, 497 Pres. Quirino Ave. Ext. Cor. Zulueta St., Paco, Manila</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div class="bg-white/10 dark:bg-black/20 backdrop-blur px-3.5 py-2.5 rounded-lg border border-white/15 dark:border-white/10">
                            <span class="text-blue-200 font-semibold">Contact No:</span>
                            <span class="font-medium text-white ml-1">Tel. #8561 6836</span>
                        </div>
                        <div class="bg-white/10 dark:bg-black/20 backdrop-blur px-3.5 py-2.5 rounded-lg border border-white/15 dark:border-white/10">
                            <span class="text-blue-200 font-semibold">Hotlines:</span>
                            <span class="font-medium text-white ml-1">+63 968 8500720 / +63 965 6287205</span>
                        </div>
                    </div>
                    <div class="bg-white/10 dark:bg-black/20 backdrop-blur px-3.5 py-2.5 rounded-lg border border-white/15 dark:border-white/10">
                        <span class="text-blue-200 font-semibold">Official Email:</span>
                        <span class="font-medium text-white ml-1">huenicsindustrialsales@gmail.com &bull; crm.huenics777@gmail.com</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 flex flex-col sm:flex-row lg:flex-col gap-3 justify-center relative z-10">
                <a href="{{ route('customer.quotation-builder') }}" 
                   class="inline-flex items-center justify-center gap-2 bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold py-3.5 px-6 rounded-xl transition text-sm text-center shadow-lg active:scale-[0.98]">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Generate Instant Quotation Estimate</span>
                </a>
                <a href="{{ route('customer.products') }}" 
                   class="inline-flex items-center justify-center gap-2 bg-white/15 hover:bg-white/25 text-white border-2 border-white/30 font-bold py-3.5 px-6 rounded-xl transition text-sm text-center active:scale-[0.98]">
                    <i class="fa-solid fa-box-open"></i>
                    <span>Explore Products Catalog</span>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

