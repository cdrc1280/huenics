@extends('layouts.customer')

@section('title', 'About Us - Huenics Industrial Sales Inc.')

@section('content')
<!-- About Header Hero -->
<section class="bg-slate-900 text-white py-16 lg:py-20 relative overflow-hidden">
    <div class="absolute inset-0 opacity-15 bg-[radial-gradient(#3b82f6_1px,transparent_1px)] [background-size:16px_16px]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 bg-blue-500/20 text-blue-300 border border-blue-500/30 px-3 py-1 rounded-full text-xs font-semibold mb-4">
                <i class="fa-solid fa-building"></i> Corporate Profile
            </span>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">
                About Huenics Industrial Sales Inc.
            </h1>
            <p class="text-blue-300 font-semibold text-sm tracking-wider uppercase mt-2">
                Colors &bull; Techniques &bull; Technology
            </p>
            <p class="text-slate-300 text-base sm:text-lg mt-4 leading-relaxed font-normal">
                A dependable Philippine engineering and commercial supply partner delivering certified commercial lighting, Citizen Japan C.O.B downlights, industrial electrical, and construction infrastructure materials.
            </p>
        </div>
    </div>
</section>

<!-- Company Overview & Story -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-6 space-y-5">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Our Heritage & Mission</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Precision, Technology, and Certified Standards
                </h2>
                <p class="text-sm text-slate-600 leading-relaxed">
                    <strong>Huenics Industrial Sales Inc. (HISI)</strong> is an established direct importer, wholesale distributor, and technical supplier delivering engineering-grade commercial LED downlights, specialized indent orders, and critical infrastructure hardware across the Philippines.
                </p>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Headquartered in Paco, Manila, we maintain direct manufacturer access and strict testing protocols to guarantee dependable quality, backed by official warranties, comprehensive customer service, and full tax compliance.
                </p>

                <div class="pt-4 grid grid-cols-2 gap-4">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <div class="text-2xl font-black text-blue-600">1–2 Yrs</div>
                        <div class="text-xs font-bold text-slate-900 mt-1">Official Warranty</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Limited warranty w/ replacement policy</div>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <div class="text-2xl font-black text-blue-600">₱20,000+</div>
                        <div class="text-xs font-bold text-slate-900 mt-1">Free Metro Delivery</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Complimentary Metro Manila transport</div>
                    </div>
                </div>
            </div>

            <!-- Core Values / Pillars -->
            <div class="lg:col-span-6 space-y-4">
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 hover:border-blue-300 transition">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg font-bold shrink-0">
                            <i class="fa-solid fa-bullseye"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Our Mission</h3>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                To empower general contractors, plumbing sub-contractors, and property developers with uncompromised material quality, prompt digital quotations, and zero-defect jobsite deliveries.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 hover:border-blue-300 transition">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg font-bold shrink-0">
                            <i class="fa-solid fa-eye"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Our Vision</h3>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                To be the Philippines' premier digitized industrial distributor, combining robust supply-chain networks with instant transparent digital pricing and inventory visibility.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 hover:border-blue-300 transition">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg font-bold shrink-0">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Compliance & Integrity</h3>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                Full compliance with Philippine Bureau of Internal Revenue (BIR) standards, Bureau of Product Standards (BPS), and formal Philippine National Standards (PNS 49).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product Lines Grid -->
<section class="py-16 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Distribution Capabilities</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                Comprehensive Engineering Product Lines
            </h2>
            <p class="text-sm text-slate-500 mt-2">
                We supply complete Bill of Quantities (BOQ) specifications for high-rise, horizontal, and industrial builds.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-faucet-drip"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">Plumbing & Piping</h3>
                <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside">
                    <li>Schedule 40 PVC Pressure Pipes</li>
                    <li>PPR Hot & Cold Water Pipes</li>
                    <li>Cast Iron & GI Drainage Fittings</li>
                    <li>Industrial Ball & Gate Valves</li>
                </ul>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-bars-staggered"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">Structural Steel</h3>
                <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside">
                    <li>Deformed Steel Bars (Grade 40/60)</li>
                    <li>Angle Bars & Flat Bars</li>
                    <li>Wide Flange & I-Beams</li>
                    <li>C-Channels & Square Tubing</li>
                </ul>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-water"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">Pumps & Fluid Systems</h3>
                <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside">
                    <li>Submersible Sewage & Drainage Pumps</li>
                    <li>Multi-stage Booster Pumps</li>
                    <li>Centrifugal Transfer Pumps</li>
                    <li>Variable Speed Pressure Controllers</li>
                </ul>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">Electrical & Infrastructure</h3>
                <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside">
                    <li>HDPE Corrugated Conduits</li>
                    <li>EMT / IMC Rigid Metal Conduits</li>
                    <li>Perforated Cable Trays</li>
                    <li>Industrial Circuit Breakers & Panels</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Corporate Details & Contact Card -->
<section class="py-16 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-slate-900 text-white rounded-2xl p-8 sm:p-12 shadow-xl grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-7 space-y-4">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Direct Inquiries & Technical Support</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Partner With Huenics Industrial Sales Inc.
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Contact our technical sales team for project quotations, indent orders, specifications verification, and volume pricing.
                </p>
                <div class="pt-2 flex flex-col gap-2.5 text-xs">
                    <div class="bg-slate-800 px-3 py-2 rounded-lg border border-slate-700">
                        <span class="text-slate-400 font-semibold">Head Office:</span>
                        <span class="font-medium text-white ml-1">916 Avida Towers Intima, Zulueta St., Brgy. 678, Zone 74, Dist. V, 1007 Paco, Manila</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div class="bg-slate-800 px-3 py-2 rounded-lg border border-slate-700">
                            <span class="text-slate-400 font-semibold">Telefax:</span>
                            <span class="font-medium text-white ml-1">(02) 8561-6836</span>
                        </div>
                        <div class="bg-slate-800 px-3 py-2 rounded-lg border border-slate-700">
                            <span class="text-slate-400 font-semibold">Hotlines:</span>
                            <span class="font-medium text-white ml-1">+63 968 8500720 / +63 965 6287205</span>
                        </div>
                    </div>
                    <div class="bg-slate-800 px-3 py-2 rounded-lg border border-slate-700">
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
