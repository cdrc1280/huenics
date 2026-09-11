@extends('layouts.customer')

@section('title', 'Huenics Industrial Sales Inc. - Colors • Techniques • Technology')

@section('content')
    <!-- ==========================================================================
                 SECTION 1: HERO SECTION & 3D SPATIAL CONTROL HUB (Antixor Luxury Architecture)
                 ========================================================================== -->
    <section
        class="ambient-mesh-hero relative overflow-hidden border-b border-slate-200 bg-white py-12 transition-colors duration-200 lg:py-20 dark:border-slate-800/80 dark:bg-[#070b14]">
        <!-- Blueprint / Geometric Micro-Grid Background -->
        <div class="pointer-events-none absolute inset-0 opacity-40 dark:opacity-20"
            style="background-image: radial-gradient(rgba(33, 79, 224, 0.15) 1px, transparent 1px); background-size: 28px 28px;">
        </div>
        <div
            class="pointer-events-none absolute right-0 top-0 h-96 w-96 bg-gradient-to-bl from-blue-600/15 via-blue-500/5 to-transparent dark:from-blue-500/15">
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-12 lg:gap-8">

                <!-- Left Column: Kinetic Typography & Floating Estimator Hub -->
                <div class="space-y-6 text-center lg:col-span-7 lg:text-left">
                    <!-- Tagline Badge: Industrial Datum Tag -->
                    <div
                        class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-slate-100 px-3 py-1 font-mono text-[11px] font-bold uppercase tracking-wider text-slate-800 shadow-sm dark:border-slate-700 dark:bg-slate-800/90 dark:text-slate-200">
                        <span class="h-1.5 w-1.5 rounded-sm bg-[#214fe0] dark:bg-[#3b82f6]"></span>
                        <span>Direct Importer &bull; Wholesale Engineering Supply &bull; HISI</span>
                    </div>

                    <!-- Headline -->
                    <div class="space-y-2">
                        <div
                            class="text-xs font-extrabold uppercase tracking-widest text-slate-500 sm:text-sm dark:text-slate-400">
                            Commercial Optoelectronics &bull; Power Distribution
                        </div>
                        <h1
                            class="text-3xl font-black leading-[1.08] tracking-tight text-slate-950 sm:text-5xl lg:text-6xl dark:text-white">
                            Industrial Lighting <br class="hidden sm:inline">
                            <span class="text-[#214fe0] dark:text-[#3b82f6]">&amp; Power Systems.</span><br>
                            Your Way.
                        </h1>
                    </div>

                    <!-- Brand Narrative -->
                    <p
                        class="mx-auto max-w-2xl text-sm font-normal leading-relaxed text-slate-600 sm:text-base lg:mx-0 dark:text-slate-300">
                        Direct importer and wholesale distributor of Citizen Japan C.O.B downlights, industrial drivers,
                        architectural linear systems, and certified electrical infrastructure materials across the
                        Philippines.
                    </p>

                    <!-- Floating Interactive Estimator & Procurement Hub (Antixor Control Card) -->
                    <div class="card-3d overflow-hidden rounded-2xl border-2 border-blue-600/20 bg-white text-left shadow-xl dark:border-slate-800 dark:bg-[#111827]"
                        data-3d-tilt data-max-tilt="5">
                        <div class="glare-sheen"></div>

                        <!-- Hub Tabs -->
                        <div
                            class="flex items-center gap-1.5 border-b border-slate-200 bg-slate-100/90 p-1.5 dark:border-slate-800 dark:bg-[#0c1220]">
                            <button type="button" id="tab-btn-quote" onclick="switchHeroTab('quote')"
                                class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-white px-3 py-2 text-xs font-bold text-[#214fe0] shadow-sm transition-all duration-200 dark:bg-[#1a233b] dark:text-[#60a5fa]">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Instant Quotation</span>
                            </button>
                            <button type="button" id="tab-btn-fleet" onclick="switchHeroTab('fleet')"
                                class="flex flex-1 items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition-all duration-200 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                <span>Browse Fleet</span>
                            </button>
                            <button type="button" id="tab-btn-indent" onclick="switchHeroTab('indent')"
                                class="flex flex-1 items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition-all duration-200 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <span>Volume Indent</span>
                            </button>
                        </div>

                        <!-- Tab Panel 1: Instant Quotation -->
                        <div id="panel-quote" class="space-y-4 p-4 sm:p-5">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div>
                                    <label
                                        class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        1. Category
                                    </label>
                                    <select id="hero-category-select"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#214fe0] dark:border-slate-700 dark:bg-[#161f38] dark:text-white">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        2. Specification
                                    </label>
                                    <select id="hero-spec-select"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#214fe0] dark:border-slate-700 dark:bg-[#161f38] dark:text-white">
                                        <option value="all">Standard Commercial Spec</option>
                                        <option value="downlight">12W–24W Downlight (Citizen COB)</option>
                                        <option value="highbay">50W–150W Industrial Highbay</option>
                                        <option value="strip">24V Constant Voltage Strip</option>
                                        <option value="track">Tracklights &amp; Spotlights</option>
                                    </select>
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        3. Est. Units
                                    </label>
                                    <div
                                        class="flex items-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-[#161f38]">
                                        <button type="button" onclick="adjustHeroQty(-10)"
                                            class="px-3 py-2 font-bold text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700">-</button>
                                        <input type="number" id="hero-qty-input" value="50" min="1"
                                            step="10"
                                            class="w-full bg-transparent text-center font-mono text-xs font-bold text-slate-900 focus:outline-none dark:text-white">
                                        <button type="button" onclick="adjustHeroQty(10)"
                                            class="px-3 py-2 font-bold text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700">+</button>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 pt-2 sm:flex-row dark:border-slate-800">
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    <span>100% BIR 12% VAT Compliant &bull; Official Serialized SI &amp; DR</span>
                                </div>
                                <button type="button" onclick="launchHeroQuote()"
                                    class="inline-flex w-full transform items-center justify-center gap-2 rounded-xl bg-[#214fe0] px-5 py-2.5 text-xs font-bold text-white shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#1a42be] active:scale-[0.98] sm:w-auto">
                                    <span>Launch Quotation Generator</span>
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Tab Panel 2: Browse Fleet -->
                        <div id="panel-fleet" class="hidden space-y-3 p-4 sm:p-5">
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <input type="text" id="hero-fleet-search"
                                    placeholder="Search catalog items, SKUs, or wattages..."
                                    class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#214fe0] dark:border-slate-700 dark:bg-[#161f38] dark:text-white">
                                <button type="button" onclick="searchHeroFleet()"
                                    class="rounded-xl bg-[#214fe0] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#1a42be]">
                                    Browse Products
                                </button>
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                Over <strong
                                    class="font-mono text-slate-800 dark:text-slate-200">{{ $totalProductsCount }}</strong>
                                verified commercial line items in stock.
                            </div>
                        </div>

                        <!-- Tab Panel 3: Volume Indent -->
                        <div id="panel-indent" class="hidden space-y-3 p-4 sm:p-5">
                            <p class="text-xs text-slate-600 dark:text-slate-300">
                                Submitting a Bill of Quantities (BOQ) for high-rise commercial towers, hotels, or wholesale
                                infrastructure projects? We provide direct indent overseas container volume pricing.
                            </p>
                            <a href="{{ route('customer.quotation-builder') }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-[#214fe0] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#1a42be]">
                                <span>Submit Bill of Quantities (BOQ)</span>
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Company Stats Strip: High-Density Industrial Telemetry Ribbon -->
                    <div class="flex flex-wrap items-center divide-y divide-slate-200 sm:divide-y-0 sm:divide-x rounded-xl border border-slate-200 bg-slate-50/80 p-3.5 font-mono text-xs shadow-sm dark:divide-slate-800 dark:border-slate-800 dark:bg-[#0b101f]">
                        <div class="flex-1 min-w-[120px] pb-2 sm:pb-0 sm:pr-4 text-center lg:text-left">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Catalog Inventory</span>
                            <span class="font-mono text-xl font-black tabular-nums text-slate-900 sm:text-2xl dark:text-white">{{ number_format($totalProductsCount) }}+</span>
                            <span class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400">Verified Line Items</span>
                        </div>
                        <div class="flex-1 min-w-[120px] py-2 sm:py-0 sm:px-4 text-center lg:text-left">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Product Lines</span>
                            <span class="font-mono text-xl font-black tabular-nums text-slate-900 sm:text-2xl dark:text-white">{{ $categories->count() }}</span>
                            <span class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400">Master Categories</span>
                        </div>
                        <div class="flex-1 min-w-[120px] pt-2 sm:pt-0 sm:pl-4 text-center lg:text-left">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Wholesale History</span>
                            <span class="font-mono text-xl font-black tabular-nums text-slate-900 sm:text-2xl dark:text-white">{{ $yearsInBusiness }} Years</span>
                            <span class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400">Direct Importer</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Interactive 3D Photonic Light Bulb Stage (Lighting Engineering Core) -->
                <div id="hero-stage-container"
                    class="relative flex w-full flex-col items-center justify-center px-2 sm:px-0 lg:col-span-5">
                    <!-- Dynamic Backlight Halo (Subtle Photometric Radial Wash, Zero Fuzzy AI Neon Blobs) -->
                    <div class="pointer-events-none absolute -z-0 h-72 w-72 rounded-full opacity-30 transition-all duration-700 sm:h-96 sm:w-96 lg:h-[440px] lg:w-[440px]"
                        style="background: radial-gradient(circle, rgba(245, 158, 11, 0.20) 0%, rgba(37, 99, 235, 0.05) 50%, transparent 70%);">
                    </div>

                    <!-- Clean Mobile & Tablet Datum Strip: Precision Engineering Tags (Replaces Pill Badges) -->
                    <div class="mb-3 flex w-full flex-wrap items-center justify-center gap-1.5 px-2 lg:hidden">
                        <span
                            class="inline-flex items-center gap-1 whitespace-nowrap rounded-md border border-amber-400/40 bg-amber-500 px-2.5 py-0.5 font-mono text-[10px] font-extrabold text-white shadow-sm">
                            <i data-lucide="star" class="h-2.5 w-2.5 text-amber-100"></i>
                            <span>20% OFF Volume</span>
                        </span>
                        <span
                            class="inline-flex items-center gap-1 whitespace-nowrap rounded-md border border-slate-300 bg-white/95 px-2.5 py-0.5 font-mono text-[10px] font-bold text-slate-800 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-[#0c1220]/95 dark:text-white">
                            <i data-lucide="file-check-2" class="h-2.5 w-2.5 text-blue-500"></i>
                            <span>12% BIR VAT</span>
                        </span>
                        <span
                            class="inline-flex items-center gap-1 whitespace-nowrap rounded-md border border-slate-300 bg-white/95 px-2.5 py-0.5 font-mono text-[10px] font-bold text-slate-800 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-[#0c1220]/95 dark:text-white">
                            <i data-lucide="truck" class="h-2.5 w-2.5 text-emerald-500"></i>
                            <span>Free Freight &ge; &#8369;20k</span>
                        </span>
                    </div>

                    <!-- 3D Spatial Perspective Stage -->
                    <div id="hero-3d-stage"
                        class="xs:max-w-[340px] relative flex aspect-square w-full max-w-[300px] select-none items-center justify-center rounded-full border border-slate-700/50 bg-gradient-to-b from-slate-900/60 via-[#0b1120]/80 to-[#060913]/95 shadow-2xl backdrop-blur-md sm:max-w-[420px] dark:border-slate-800/80"
                        style="transform-style: preserve-3d; will-change: transform;">

                        <div class="glare-sheen" style="transition: opacity 0.3s ease;"></div>

                        <!-- Ambient Photometric Stage Wall Wash (Subtle & Minimized to Showcase Subject) -->
                        <div id="luminaire-ambient-halo"
                            class="pointer-events-none absolute inset-0 rounded-full transition-all duration-700"
                            style="opacity: 0.30; background: radial-gradient(circle at 50% 50%, rgba(255, 184, 77, 0.18) 0%, rgba(245, 158, 11, 0.05) 32%, transparent 55%);">
                        </div>

                        <!-- Subtle Studio Vignette Glow -->
                        <div
                            class="bg-radial pointer-events-none absolute inset-0 rounded-full from-transparent via-transparent to-black/40">
                        </div>

                        <!-- Three.js 3D WebGL Light Bulb Canvas Container -->
                        <div class="pointer-events-none relative z-10 flex h-full w-full items-center justify-center">
                            <canvas id="luminaire-3d-canvas"
                                class="pointer-events-auto h-full w-full cursor-grab rounded-full active:cursor-grabbing"
                                style="touch-action: none; width: 100%; height: 100%;"></canvas>

                            <!-- Center Hotspot Click-to-Toggle Overlay (Clean non-shifting hitbox) -->
                            <div id="luminaire-center-toggle"
                                class="pointer-events-auto absolute z-20 flex h-32 w-32 cursor-pointer items-center justify-center rounded-full transition-transform active:scale-95 sm:h-36 sm:w-36"
                                title="Click to Toggle Citizen COB LED ON / OFF">
                                <span class="sr-only">Toggle Citizen COB LED</span>
                            </div>

                            <!-- Technical Specification Tag (Docked Cleanly at Stage Bottom with Zero Overlap) -->
                            <div id="luminaire-tech-tag"
                                class="xs:text-[9px] pointer-events-none absolute inset-x-0 bottom-2.5 z-20 mx-auto flex w-fit max-w-[90%] items-center justify-center gap-1 overflow-hidden text-ellipsis whitespace-nowrap rounded-full border border-blue-500/40 bg-slate-900/90 px-2 py-0.5 font-mono text-[8px] font-black uppercase tracking-normal text-white shadow-md backdrop-blur transition-all duration-300 sm:bottom-3.5 sm:max-w-[85%] sm:gap-1.5 sm:px-3 sm:py-1 sm:text-[10px] sm:tracking-wider dark:bg-[#0c1220]/95">
                                <span id="luminaire-status-dot"
                                    class="h-1.5 w-1.5 shrink-0 animate-pulse rounded-full bg-emerald-400 sm:h-2 sm:w-2"></span>
                                <span id="luminaire-status-text" class="truncate">CITIZEN COB LED • 3000K SOFT WHITE • 24°
                                    SPOT • CRI 80</span>
                            </div>
                        </div>

                        <!-- Floating Badge 1: 20% OFF Contractor Volume (Desktop Only - Top Right) -->
                        <div id="hero-badge-discount"
                            class="pointer-events-auto absolute -right-4 top-2 z-30 hidden items-center gap-1.5 whitespace-nowrap rounded-md border border-amber-400/50 bg-amber-500 px-3 py-1 font-mono text-[11px] font-extrabold text-white shadow-md lg:flex"
                            style="will-change: transform;">
                            <i data-lucide="star" class="h-3.5 w-3.5 text-amber-100"></i>
                            <span>20% OFF Volume</span>
                        </div>

                        <!-- Floating Badge 2: 12% BIR VAT Invoicing (Desktop Only - Mid Left) -->
                        <div id="hero-badge-vat"
                            class="pointer-events-auto absolute -left-6 top-1/3 z-30 hidden items-center gap-1.5 whitespace-nowrap rounded-md border border-slate-300 bg-white/95 px-3 py-1 font-mono text-[11px] font-bold text-slate-800 shadow-md backdrop-blur lg:flex dark:border-slate-700 dark:bg-[#0c1220]/95 dark:text-white"
                            style="will-change: transform;">
                            <i data-lucide="file-check-2" class="h-3.5 w-3.5 text-blue-500"></i>
                            <span>12% BIR VAT Invoicing</span>
                        </div>

                        <!-- Floating Badge 3: Free Freight ≥ ₱20,000 (Desktop Only - Lower Right Quadrant) -->
                        <div id="hero-badge-freight"
                            class="pointer-events-auto absolute -right-6 top-2/3 z-30 hidden items-center gap-1.5 whitespace-nowrap rounded-md border border-slate-300 bg-white/95 px-3 py-1 font-mono text-[11px] font-bold text-slate-800 shadow-md backdrop-blur lg:flex dark:border-slate-700 dark:bg-[#0c1220]/95 dark:text-white"
                            style="will-change: transform;">
                            <i data-lucide="truck" class="h-3.5 w-3.5 text-emerald-500"></i>
                            <span>Free Freight &ge; &#8369; 20,000</span>
                        </div>
                    </div>

                    <!-- Tactile Industrial 3D Lighting Control Deck ("ON / OFF & Citizen COB CCT Selection") -->
                    <div
                        class="xs:max-w-[360px] z-20 mt-4 flex w-full max-w-[320px] flex-col gap-2 rounded-2xl border border-slate-300/60 bg-slate-100/95 p-2 shadow-xl backdrop-blur-md sm:mt-5 sm:max-w-[400px] sm:p-2.5 dark:border-blue-500/30 dark:bg-[#0c1427]/95">

                        <!-- Console Tier 1: Primary Power Engine & Kelvin CCT Selector -->
                        <div class="flex w-full items-center justify-between gap-1.5 sm:gap-2">
                            <!-- Power Switch (ON / OFF) -->
                            <button type="button" id="luminaire-power-btn" onclick="window.toggleLuminairePower()"
                                class="flex shrink-0 cursor-pointer items-center justify-center gap-1 whitespace-nowrap rounded-xl bg-emerald-500 px-2.5 py-1.5 text-[10.5px] font-bold text-white shadow-md shadow-emerald-500/20 transition-all duration-200 hover:bg-emerald-600 active:scale-95 sm:gap-1.5 sm:px-3 sm:text-xs">
                                <i data-lucide="power" class="h-3 w-3 sm:h-3.5 sm:w-3.5"></i>
                                <span id="luminaire-power-label"><span class="xs:inline hidden">COB: </span>ON</span>
                            </button>

                            <!-- Kelvin CCT Selector (3000K / 3500K / 4000K / 5000K) - Rigid 4-Column Grid Guaranteeing Zero Layout Shift -->
                            <div
                                class="grid flex-1 grid-cols-4 gap-0.5 rounded-xl border border-slate-300/60 bg-slate-200 p-0.5 dark:border-slate-700/60 dark:bg-slate-800/90">
                                <button type="button" data-cct="3000K" onclick="window.setLuminaireCCT('3000K', this)"
                                    class="cct-btn xs:text-[10px] w-full cursor-pointer rounded-lg bg-amber-500 py-1 text-center text-[9.5px] font-black text-white shadow-md shadow-amber-500/30 transition-all duration-200 sm:text-[11px]"
                                    title="3000K Soft White Glow (Warm White • 24° Spot • CRI 80)">
                                    3000K
                                </button>
                                <button type="button" data-cct="3500K" onclick="window.setLuminaireCCT('3500K', this)"
                                    class="cct-btn xs:text-[10px] w-full cursor-pointer rounded-lg py-1 text-center text-[9.5px] font-black text-slate-500 transition-all duration-200 hover:text-slate-900 sm:text-[11px] dark:text-slate-400 dark:hover:text-white"
                                    title="3500K Neutral Glow (Warm White • 24° Spot • CRI 80)">
                                    3500K
                                </button>
                                <button type="button" data-cct="4000K" onclick="window.setLuminaireCCT('4000K', this)"
                                    class="cct-btn xs:text-[10px] w-full cursor-pointer rounded-lg py-1 text-center text-[9.5px] font-black text-slate-500 transition-all duration-200 hover:text-slate-900 sm:text-[11px] dark:text-slate-400 dark:hover:text-white"
                                    title="4000K Daylight Glow (Neutral White • 24° Spot • CRI 80)">
                                    4000K
                                </button>
                                <button type="button" data-cct="5000K" onclick="window.setLuminaireCCT('5000K', this)"
                                    class="cct-btn xs:text-[10px] w-full cursor-pointer rounded-lg py-1 text-center text-[9.5px] font-black text-slate-500 transition-all duration-200 hover:text-slate-900 sm:text-[11px] dark:text-slate-400 dark:hover:text-white"
                                    title="5000K Crystal White Glow (Cool White • 24° Spot • CRI 80)">
                                    5000K
                                </button>
                            </div>
                        </div>

                        <!-- Console Tier 2: 24° Optic Spot Reflector & Interactive High-Voltage Surge Ignition -->
                        <div
                            class="flex w-full items-center justify-between gap-1.5 border-t border-slate-800/80 pt-1.5 sm:gap-2">
                            <!-- 24° Architectural Optic Badge -->
                            <div class="xs:text-[10px] flex flex-1 items-center justify-center gap-1.5 whitespace-nowrap rounded-xl border border-slate-700/60 bg-slate-800/90 px-2.5 py-1.5 font-mono text-[9.5px] font-bold text-amber-300 sm:text-[10.5px]"
                                title="Precision 24° Architectural Collimated Optic Spot Reflector • CRI 80">
                                <svg class="h-3 w-3 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707" />
                                </svg>
                                <span>24&deg; Optic Spot</span>
                            </div>

                            <!-- Interactive Surge / Pulse Animation Button -->
                            <button type="button" onclick="window.triggerLuminaireSurge()"
                                class="flex flex-1 cursor-pointer items-center justify-center gap-1 whitespace-nowrap rounded-xl border border-blue-500/30 bg-blue-500/15 px-2.5 py-1.5 text-[10.5px] font-semibold text-blue-300 transition-all hover:bg-blue-500/30 hover:text-white active:scale-95 sm:gap-1.5 sm:px-3 sm:text-xs"
                                title="Trigger High-Voltage Photonic Ignition Surge">
                                <i data-lucide="zap" class="h-3 w-3 shrink-0 text-amber-400 sm:h-3.5 sm:w-3.5"></i>
                                <span>Surge</span>
                            </button>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Hero Bottom Trust Bar: Monolithic Architectural Telemetry Strip (De-cardified) -->
            <div
                class="mt-10 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm sm:mt-14 lg:grid lg:grid-cols-4 dark:border-slate-800 dark:bg-[#0b101f]">
                <div
                    class="flex items-center gap-3 border-b border-slate-200 p-4 sm:border-b-0 sm:border-r dark:border-slate-800">
                    <svg class="h-5 w-5 shrink-0 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <div class="min-w-0">
                        <div class="truncate text-xs font-bold text-slate-900 dark:text-white">Direct Factory Importer</div>
                        <div class="truncate font-mono text-[10px] text-slate-500 dark:text-slate-400">Zero distributor markups</div>
                    </div>
                </div>

                <div
                    class="flex items-center gap-3 border-b border-slate-200 p-4 sm:border-b-0 sm:border-r dark:border-slate-800">
                    <svg class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="min-w-0">
                        <div class="truncate text-xs font-bold text-slate-900 dark:text-white">BIR 12% VAT Invoicing</div>
                        <div class="truncate font-mono text-[10px] text-slate-500 dark:text-slate-400">Official serialized SI &amp; DR</div>
                    </div>
                </div>

                <div
                    class="flex items-center gap-3 border-b border-slate-200 p-4 sm:border-b-0 sm:border-r dark:border-slate-800">
                    <svg class="h-5 w-5 shrink-0 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="min-w-0">
                        <div class="truncate text-xs font-bold text-slate-900 dark:text-white">Instant BOQ Estimation</div>
                        <div class="truncate font-mono text-[10px] text-slate-500 dark:text-slate-400">Exportable itemized PDF</div>
                    </div>
                </div>

                <div
                    class="flex items-center gap-3 p-4">
                    <svg class="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <div class="min-w-0">
                        <div class="truncate text-xs font-bold text-slate-900 dark:text-white">Jobsite Dispatch Fleet</div>
                        <div class="truncate font-mono text-[10px] text-slate-500 dark:text-slate-400">Direct site delivery</div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ==========================================================================
                 SECTION 2: WHY CHOOSE HUENICS? (Antixor 4-Card Feature Row)
                 ========================================================================== -->
    <section
        class="border-b border-slate-200 bg-slate-50 py-16 transition-colors duration-200 dark:border-slate-800/80 dark:bg-[#070b14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <span class="text-xs font-black uppercase tracking-widest text-[#214fe0] dark:text-[#60a5fa]">
                    Engineering Advantage
                </span>
                <h2 class="mt-1.5 text-2xl font-black tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                    Why Top Commercial Contractors Partner with Huenics
                </h2>
                <p class="mt-2 text-xs text-slate-500 sm:text-sm dark:text-slate-400">
                    We combine direct manufacturer pricing with rigorous Japanese optoelectronics and official Philippine
                    corporate tax documentation.
                </p>
            </div>

            <!-- Integrated Monolithic Engineering Ledger (De-cardified continuous plane with 1px internal gridlines) -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-[#0b101f]">
                <div class="grid grid-cols-1 divide-y divide-slate-200 sm:grid-cols-2 sm:divide-y-0 sm:divide-x lg:grid-cols-4 dark:divide-slate-800">
                    <!-- Column 1: Direct Wholesale Volume -->
                    <div class="p-6 transition-colors hover:bg-slate-50/70 dark:hover:bg-slate-800/20">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-[#214fe0] dark:text-[#60a5fa]">01 // SOURCING</span>
                            <svg class="h-4 w-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-sm font-bold text-slate-900 dark:text-white">Direct Wholesale Indent</h3>
                        <p class="mt-1.5 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                            Zero middlemen or secondary broker markups. We import directly from verified manufacturing lines for competitive project margins.
                        </p>
                    </div>

                    <!-- Column 2: Citizen Japan COB -->
                    <div class="p-6 transition-colors hover:bg-slate-50/70 dark:hover:bg-slate-800/20">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-amber-600 dark:text-amber-400">02 // OPTOELECTRONICS</span>
                            <svg class="h-4 w-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-sm font-bold text-slate-900 dark:text-white">Citizen Japan C.O.B Fidelity</h3>
                        <p class="mt-1.5 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                            Powered by genuine Citizen Japan arrays. Ra &ge; 90 high CRI, MacAdam ellipse binning, and 50,000-hour L70 life.
                        </p>
                    </div>

                    <!-- Column 3: Instant Automated Estimation -->
                    <div class="p-6 transition-colors hover:bg-slate-50/70 dark:hover:bg-slate-800/20">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">03 // AUTOMATION</span>
                            <svg class="h-4 w-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-sm font-bold text-slate-900 dark:text-white">Instant 60s Quotations</h3>
                        <p class="mt-1.5 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                            Select specifications, customize volume tiers, and download an official itemized PDF quotation with BIR VAT calculations immediately.
                        </p>
                    </div>

                    <!-- Column 4: Lighting Clinic Support -->
                    <div class="p-6 transition-colors hover:bg-slate-50/70 dark:hover:bg-slate-800/20">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300">04 // CLINIC SUPPORT</span>
                            <svg class="h-4 w-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-sm font-bold text-slate-900 dark:text-white">Lighting Clinic &amp; Lab</h3>
                        <p class="mt-1.5 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                            On-staff electrical engineers for driver diagnostics, custom indent orders, Dialux lux simulations, and fixture retrofitting.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================================================
                 SECTION 3: POPULAR PRODUCTS & COMMERCIAL FLEET SHOWCASE (Antixor Fleet Grid)
                 ========================================================================== -->
    <section id="popular-products-fleet"
        class="border-b border-slate-200 bg-white py-16 transition-colors duration-200 dark:border-slate-800/80 dark:bg-[#0a0e1a]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="mb-8 flex flex-col items-start justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">
                        Commercial Catalog
                    </span>
                    <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                        Popular Products &amp; Lighting Fleet
                    </h2>
                    <p class="mt-1 max-w-xl text-xs text-slate-500 sm:text-sm dark:text-slate-400">
                        High-efficiency commercial downlights, Citizen Japan C.O.B fixtures, linear profiles, and certified
                        drivers ready for jobsite dispatch.
                    </p>
                </div>
                <a href="{{ route('customer.products') }}"
                    class="group inline-flex shrink-0 items-center gap-1.5 text-xs font-bold text-[#214fe0] hover:underline sm:text-sm dark:text-[#60a5fa]">
                    <span>View All Products ({{ $totalProductsCount }})</span>
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>

            <!-- Filter Pill Tabs (Interactive JS Filter) -->
            <div class="no-scrollbar mb-8 flex select-none items-center gap-2 overflow-x-auto pb-3 pr-8 sm:pr-0"
                style="scrollbar-width: none; -ms-overflow-style: none; -webkit-overflow-scrolling: touch;">
                <button type="button" onclick="filterFleetCategory('all', this)"
                    class="fleet-filter-btn whitespace-nowrap rounded-xl bg-[#214fe0] px-4 py-2 text-xs font-bold text-white shadow-md transition-all duration-200">
                    All Products
                </button>
                @foreach ($categories->take(6) as $cat)
                    <button type="button" onclick="filterFleetCategory('{{ $cat }}', this)"
                        class="fleet-filter-btn whitespace-nowrap rounded-xl border border-slate-200/80 bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 transition-all duration-200 hover:bg-slate-200 dark:border-slate-700/60 dark:bg-[#151f38] dark:text-slate-300 dark:hover:bg-slate-700">
                        {{ $cat }}
                    </button>
                @endforeach
            </div>

            <!-- 4-Column Fleet Grid with 3D Tilt & Specular Glare -->
            <div id="fleet-grid-container" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @forelse($featuredProducts as $product)
                    <div class="fleet-product-card card-interactive group flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm hover:border-[#214fe0] hover:shadow-xl dark:border-slate-800 dark:bg-[#111827] dark:hover:border-[#3b82f6] dark:hover:shadow-[0_12px_30px_rgba(33,79,224,0.2)]"
                        data-category="{{ $product->category }}" data-3d-tilt data-max-tilt="10">

                        <div class="glare-sheen"></div>

                        <div>
                            <!-- Product Image Container -->
                            <div
                                class="relative mb-3.5 flex h-44 w-full items-center justify-center overflow-hidden rounded-xl border border-slate-200/70 bg-slate-100/90 transition-colors group-hover:border-blue-300 dark:border-slate-800/80 dark:bg-[#161f38]/90 dark:group-hover:border-blue-600">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->canonical_name }}"
                                        loading="lazy"
                                        class="h-full w-full object-contain p-2 transition-transform duration-300 ease-out group-hover:scale-105">
                                @else
                                    <div
                                        class="relative flex h-full w-full select-none flex-col items-center justify-center p-4 text-slate-400 dark:text-slate-500">
                                        <div
                                            class="relative mb-1.5 flex h-16 w-16 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50/80 shadow-sm transition-all duration-300 group-hover:scale-110 group-hover:bg-blue-100/80 dark:border-blue-900/50 dark:bg-blue-950/40 dark:group-hover:bg-blue-900/60">
                                            <svg class="h-8 w-8 text-[#214fe0] transition-colors group-hover:text-blue-600 dark:text-[#60a5fa] dark:group-hover:text-blue-400"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="1.6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                            </svg>
                                        </div>
                                        <span
                                            class="font-mono text-[10px] font-extrabold uppercase tracking-wider text-slate-500 transition-colors group-hover:text-[#214fe0] dark:text-slate-400 dark:group-hover:text-[#60a5fa]">
                                            {{ $product->category ?: 'Lighting' }}
                                        </span>
                                    </div>
                                @endif
                                <div
                                    class="pointer-events-none absolute inset-x-2.5 top-2.5 z-10 flex items-center justify-between gap-2">
                                    <span
                                        class="hisi-pill-badge pointer-events-auto !inline-flex min-w-0 max-w-[62%] shrink items-center shadow-sm"
                                        title="{{ strtoupper($product->category ?: 'Lighting') }}">
                                        <span class="truncate">{{ strtoupper($product->category ?: 'Lighting') }}</span>
                                    </span>
                                    <span
                                        class="pointer-events-auto max-w-[38%] shrink-0 truncate rounded border border-slate-200/60 bg-white/95 px-2 py-0.5 font-mono text-[10px] font-bold text-slate-600 shadow-sm backdrop-blur dark:border-slate-700/60 dark:bg-[#0c1220]/95 dark:text-slate-300"
                                        title="{{ $product->sku ?: $product->product_code ?: 'SKU-00' }}">
                                        {{ $product->sku ?: $product->product_code ?: 'SKU-00' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Title -->
                            <h3 class="mb-1 line-clamp-1 text-sm font-bold text-slate-900 transition group-hover:text-[#214fe0] dark:text-white dark:group-hover:text-[#60a5fa]"
                                title="{{ $product->canonical_name }}">
                                {{ $product->canonical_name }}
                            </h3>

                            <!-- Specs Chips -->
                            <div class="my-2 flex flex-wrap gap-1.5">
                                <span
                                    class="rounded bg-slate-100 px-2 py-0.5 font-mono text-[10px] font-semibold text-slate-700 dark:bg-[#161f38] dark:text-slate-300">
                                    {{ $product->unit_of_measurement ?: 'PC' }}
                                </span>
                                <span
                                    class="rounded bg-blue-50 px-2 py-0.5 font-mono text-[10px] font-semibold text-[#214fe0] dark:bg-blue-950/60 dark:text-[#60a5fa]">
                                    Citizen Japan C.O.B
                                </span>
                            </div>
                        </div>

                        <!-- Add to Quote Control -->
                        <div class="border-t border-slate-100 pt-3 dark:border-slate-800/80">
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex shrink-0 items-center overflow-hidden rounded-lg border border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-[#161f38]">
                                    <button type="button" onclick="adjustCardQty('fleet-qty-{{ $product->id }}', -1)"
                                        class="px-2 py-1 text-xs font-bold text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700">-</button>
                                    <input type="number" id="fleet-qty-{{ $product->id }}" value="1"
                                        min="1" step="1"
                                        class="w-10 bg-transparent py-1 text-center font-mono text-xs font-bold text-slate-900 focus:outline-none dark:text-white">
                                    <button type="button" onclick="adjustCardQty('fleet-qty-{{ $product->id }}', 1)"
                                        class="px-2 py-1 text-xs font-bold text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700">+</button>
                                </div>

                                <button type="button"
                                    onclick="addProductToQuote({{ json_encode($product) }}, document.getElementById('fleet-qty-{{ $product->id }}').value)"
                                    class="flex flex-1 items-center justify-center gap-1 rounded-lg bg-[#214fe0] px-2.5 py-2 text-xs font-bold text-white shadow-sm transition-all duration-200 hover:bg-[#1a42be] active:scale-95">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>Add to Quote</span>
                                </button>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-slate-500">
                        No featured products found.
                    </div>
                @endforelse
            </div>

            <!-- Empty Filter State Container -->
            <div id="fleet-empty-state"
                class="mt-4 hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-12 text-center dark:border-slate-700 dark:bg-[#111827]">
                <svg class="mx-auto mb-2 h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No products match this category filter.
                </p>
                <button type="button" onclick="filterFleetCategory('all', document.querySelector('.fleet-filter-btn'))"
                    class="mt-3 text-xs font-bold text-[#214fe0] hover:underline dark:text-[#60a5fa]">
                    Reset to All Products
                </button>
            </div>

        </div>
    </section>

    <!-- ==========================================================================
                 SECTION 4: SPECIAL CONTRACTOR DISCOUNT BANNER (Antixor Special Offer Banner)
                 ========================================================================== -->
    <section class="relative overflow-hidden border-y border-slate-800 bg-[#080d1a] py-14 text-white">
        <!-- Precision Grid Accent -->
        <div class="pointer-events-none absolute inset-0 opacity-5"
            style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 32px 32px;">
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-8 lg:flex-row">
                <div class="space-y-3 text-center lg:text-left">
                    <div
                        class="inline-flex items-center gap-2 rounded-md border border-amber-400/40 bg-amber-400/10 px-2.5 py-1 font-mono text-[11px] font-bold uppercase tracking-wider text-amber-300">
                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                        </svg>
                        <span>Contractor Volume Program</span>
                    </div>
                    <h2 class="text-2xl font-black tracking-tight sm:text-4xl text-white">
                        Get Up to 20% OFF Your First Commercial Project BOQ
                    </h2>
                    <p class="max-w-2xl text-xs font-normal leading-relaxed text-slate-300 sm:text-sm">
                        Submitting a Bill of Quantities for commercial towers, hotels, or retail rollouts? Unlock direct
                        indent overseas pricing, dedicated technical account handling, and prioritized jobsite dispatch.
                    </p>
                </div>

                <div class="flex shrink-0 flex-col items-center gap-3.5 sm:flex-row">
                    <div
                        class="rounded-md border border-slate-700 bg-slate-900/90 px-3.5 py-2 font-mono text-xs font-bold text-amber-300">
                        CODE: HUENICS2026
                    </div>
                    <a href="{{ route('customer.quotation-builder') }}"
                        class="flex items-center gap-2 rounded-md bg-amber-400 px-5 py-2.5 text-xs font-bold text-slate-950 transition hover:bg-amber-300 active:scale-95 sm:text-sm">
                        <span>Assemble Project BOQ</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================================================
                 SECTION 5: HOW IT WORKS (Antixor 3-Step Numbered Flow)
                 ========================================================================== -->
    <section
        class="border-b border-slate-200 bg-white py-16 transition-colors duration-200 dark:border-slate-800/80 dark:bg-[#070b14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <span class="text-xs font-black uppercase tracking-widest text-[#214fe0] dark:text-[#60a5fa]">
                    Frictionless Procurement
                </span>
                <h2 class="mt-1.5 text-2xl font-black tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                    How Huenics Streamlines Your Order
                </h2>
                <p class="mt-2 text-xs text-slate-500 sm:text-sm dark:text-slate-400">
                    From initial fixture specification to official BIR delivery receipt at your construction gate in 3
                    straightforward steps.
                </p>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-[#0b101f]">
                <div class="grid grid-cols-1 divide-y divide-slate-200 md:grid-cols-3 md:divide-y-0 md:divide-x dark:divide-slate-800">
                    <!-- Step 1 -->
                    <div class="p-7 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                        <div class="mb-4 flex items-center justify-between">
                            <span class="font-mono text-xs font-black tracking-widest text-[#214fe0] dark:text-[#60a5fa]">PHASE // 01</span>
                            <svg class="h-5 w-5 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-base font-bold text-slate-900 dark:text-white">Select Hardware &amp; Specs</h3>
                        <p class="text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                            Pick your fixture types, Citizen C.O.B wattages, beam angles, and required quantities directly from
                            our live catalog.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div class="p-7 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                        <div class="mb-4 flex items-center justify-between">
                            <span class="font-mono text-xs font-black tracking-widest text-[#214fe0] dark:text-[#60a5fa]">PHASE // 02</span>
                            <svg class="h-5 w-5 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-base font-bold text-slate-900 dark:text-white">Instant PDF Estimation</h3>
                        <p class="text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                            Our quotation engine calculates itemized subtotals and 12% VAT, generating an exportable PDF ready
                            for client sign-off.
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div class="p-7 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                        <div class="mb-4 flex items-center justify-between">
                            <span class="font-mono text-xs font-black tracking-widest text-emerald-600 dark:text-emerald-400">PHASE // 03</span>
                            <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-base font-bold text-slate-900 dark:text-white">Official SI &amp; Jobsite Dispatch</h3>
                        <p class="text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                            Our sales desk issues official serialized BIR Sales Invoices and coordinates delivery straight to
                            your construction site receiver.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================================================
                 SECTION 6: COMMERCIAL ENGINEERING SERVICES (Antixor 3 Visual Cards)
                 ========================================================================== -->
    <section
        class="border-b border-slate-200 bg-slate-50 py-16 transition-colors duration-200 dark:border-slate-800/80 dark:bg-[#0a0e1a]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 flex flex-col items-start justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">
                        Specialized Capabilities
                    </span>
                    <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                        Our Engineering Services
                    </h2>
                    <p class="mt-1 max-w-xl text-xs text-slate-500 sm:text-sm dark:text-slate-400">
                        Beyond wholesale supply: hardware repair diagnostics, custom indent overseas manufacturing, and
                        photometric simulation.
                    </p>
                </div>
                <a href="{{ route('customer.about') }}"
                    class="flex items-center gap-1 text-xs font-bold text-[#214fe0] hover:underline sm:text-sm dark:text-[#60a5fa]">
                    <span>Explore Technical Dept</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <!-- Service 1: Lighting Clinic & Enercon -->
                <div class="card-interactive group flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-[#111827]"
                    data-3d-tilt data-max-tilt="8">
                    <div class="glare-sheen"></div>
                    <div class="p-6">
                        <div
                            class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-[#214fe0] dark:border-blue-900/60 dark:bg-blue-950/70 dark:text-[#60a5fa]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-base font-bold text-slate-900 dark:text-white">Lighting Clinic &amp; Enercon
                        </h3>
                        <p class="mb-4 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                            Component-level driver testing, thermal management diagnostics, and Citizen C.O.B engine
                            retrofitting for legacy architectural luminaires.
                        </p>
                    </div>
                    <div
                        class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-6 py-3.5 dark:border-slate-800 dark:bg-[#0c1220]">
                        <span class="text-[11px] font-bold text-[#214fe0] dark:text-[#60a5fa]">Diagnostics &amp;
                            Repair</span>
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-[#214fe0] transition-transform group-hover:scale-110 dark:bg-blue-900/60 dark:text-[#60a5fa]">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Service 2: Custom Indent Sourcing -->
                <div class="card-interactive group flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-[#111827]"
                    data-3d-tilt data-max-tilt="8">
                    <div class="glare-sheen"></div>
                    <div class="p-6">
                        <div
                            class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600 dark:border-amber-900/60 dark:bg-amber-950/70 dark:text-amber-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-base font-bold text-slate-900 dark:text-white">Custom Indent Sourcing</h3>
                        <p class="mb-4 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                            Custom housing fabrication, tailored CCT/CRI requirements, IP67/IP68 submersible fittings, and
                            non-standard industrial voltage driver specs.
                        </p>
                    </div>
                    <div
                        class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-6 py-3.5 dark:border-slate-800 dark:bg-[#0c1220]">
                        <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400">Factory Indent</span>
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-amber-600 transition-transform group-hover:scale-110 dark:bg-amber-900/60 dark:text-amber-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Service 3: Photometric Simulation & Dialux -->
                <div class="card-interactive group flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-[#111827]"
                    data-3d-tilt data-max-tilt="8">
                    <div class="glare-sheen"></div>
                    <div class="p-6">
                        <div
                            class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-600 dark:border-emerald-900/60 dark:bg-emerald-950/70 dark:text-emerald-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-base font-bold text-slate-900 dark:text-white">Photometric Simulation</h3>
                        <p class="mb-4 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                            Dialux lighting calculations, illuminance distribution verification (Lux), and glare ratings
                            (UGR &lt; 19) for electrical consultancy compliance.
                        </p>
                    </div>
                    <div
                        class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-6 py-3.5 dark:border-slate-800 dark:bg-[#0c1220]">
                        <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">Dialux Lux
                            Modeling</span>
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 transition-transform group-hover:scale-110 dark:bg-emerald-900/60 dark:text-emerald-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================================================
                 SECTION 8: COMMERCIAL INSTALLATIONS GALLERY (Antixor Fleet Gallery Bento)
                 ========================================================================== -->
    <section
        class="border-b border-slate-200 bg-white py-16 transition-colors duration-200 dark:border-slate-800/80 dark:bg-[#070b14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <span class="text-xs font-black uppercase tracking-widest text-[#214fe0] dark:text-[#60a5fa]">
                    Deployments
                </span>
                <h2 class="mt-1.5 text-2xl font-black tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                    Commercial Installations &amp; Engineering References
                </h2>
                <p class="mt-2 text-xs text-slate-500 sm:text-sm dark:text-slate-400">
                    Trusted across commercial high-rises, flagship retail stores, corporate atriums, and logistics centers
                    across Luzon.
                </p>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-[#0b101f]">
                <div class="grid grid-cols-1 divide-y divide-slate-200 sm:grid-cols-2 sm:divide-y-0 sm:divide-x lg:grid-cols-4 dark:divide-slate-800">
                    <!-- Project 1: High Rise -->
                    <div class="p-6 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                        <div class="mb-1 font-mono text-[11px] font-bold text-[#214fe0] dark:text-[#60a5fa]">Taguig City &bull; BGC</div>
                        <h3 class="mb-2 text-base font-bold text-slate-900 dark:text-white">Commercial Office Tower</h3>
                        <p class="mb-4 text-xs text-slate-600 dark:text-slate-400">
                            1,200+ Citizen C.O.B downlights, low-glare darklight reflectors, and DALI-2 dimmable driver arrays
                            across 28 storeys.
                        </p>
                        <div class="flex justify-between border-t border-slate-100 pt-3 font-mono text-[11px] text-slate-500 dark:border-slate-800/80 dark:text-slate-400">
                            <span>Downlights &amp; Linear</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">100% On-Time</span>
                        </div>
                    </div>

                    <!-- Project 2: Retail Boutique -->
                    <div class="p-6 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                        <div class="mb-1 font-mono text-[11px] font-bold text-[#214fe0] dark:text-[#60a5fa]">Makati City</div>
                        <h3 class="mb-2 text-base font-bold text-slate-900 dark:text-white">Luxury Fashion Boutique</h3>
                        <p class="mb-4 text-xs text-slate-600 dark:text-slate-400">
                            High-CRI Ra &ge; 95 tracklight spotlights, 3000K warm CCT, and precision honeycomb louvers for exact
                            fabric fidelity.
                        </p>
                        <div class="flex justify-between border-t border-slate-100 pt-3 font-mono text-[11px] text-slate-500 dark:border-slate-800/80 dark:text-slate-400">
                            <span>Ra &ge; 95 Spotlight</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">Completed</span>
                        </div>
                    </div>

                    <!-- Project 3: Industrial Logistics -->
                    <div class="p-6 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                        <div class="mb-1 font-mono text-[11px] font-bold text-[#214fe0] dark:text-[#60a5fa]">Laguna Technopark</div>
                        <h3 class="mb-2 text-base font-bold text-slate-900 dark:text-white">Cold Storage &amp; Warehouse</h3>
                        <p class="mb-4 text-xs text-slate-600 dark:text-slate-400">
                            150W IP65 highbay luminaires, industrial surge-protected drivers (6kV), and high-bay microwave
                            occupancy sensors.
                        </p>
                        <div class="flex justify-between border-t border-slate-100 pt-3 font-mono text-[11px] text-slate-500 dark:border-slate-800/80 dark:text-slate-400">
                            <span>150W Highbay</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">Active</span>
                        </div>
                    </div>

                    <!-- Project 4: Hospitality Lobby -->
                    <div class="p-6 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                        <div class="mb-1 font-mono text-[11px] font-bold text-[#214fe0] dark:text-[#60a5fa]">Pasay City</div>
                        <h3 class="mb-2 text-base font-bold text-slate-900 dark:text-white">Hospitality Atrium &amp; Lounge</h3>
                        <p class="mb-4 text-xs text-slate-600 dark:text-slate-400">
                            Architectural recessed cove linear profiles, 24V constant voltage flicker-free dimming, and seamless
                            corner joiners.
                        </p>
                        <div class="flex justify-between border-t border-slate-100 pt-3 font-mono text-[11px] text-slate-500 dark:border-slate-800/80 dark:text-slate-400">
                            <span>24V Linear Cove</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">Completed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================================================
                 SECTION 9: DIGITAL QUOTATION APP SHOWCASE (Antixor Mobile Mockup Section)
                 ========================================================================== -->
    <section
        class="border-b border-slate-200 bg-slate-50 py-16 transition-colors duration-200 dark:border-slate-800/80 dark:bg-[#070b14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-12">

                <div class="space-y-6 lg:col-span-6">
                    <div
                        class="inline-flex items-center gap-2 rounded-md border border-blue-200 bg-blue-50/80 px-2.5 py-1 font-mono text-[11px] font-bold uppercase tracking-wider text-[#214fe0] dark:border-blue-800/60 dark:bg-blue-950/40 dark:text-[#60a5fa]">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span>On-Site Procurement Power</span>
                    </div>

                    <h2
                        class="text-2xl font-black leading-tight tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                        Generate Commercial Quotations Anywhere, Right from the Jobsite.
                    </h2>

                    <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        No waiting for paper estimates or delayed email exchanges. Project engineers can select fixtures,
                        verify stock availability, and produce an official-ready PDF proposal with 12% VAT calculations on
                        their smartphone or laptop in real time.
                    </p>

                    <div class="space-y-3 pt-2">
                        <div class="flex items-center gap-3">
                            <svg class="h-4 w-4 shrink-0 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-xs font-semibold text-slate-800 sm:text-sm dark:text-slate-200">Instant PDF
                                download with serialized reference numbers</span>
                        </div>

                        <div class="flex items-center gap-3">
                            <svg class="h-4 w-4 shrink-0 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-xs font-semibold text-slate-800 sm:text-sm dark:text-slate-200">100% BIR 12%
                                VAT itemized subtotal calculations</span>
                        </div>

                        <div class="flex items-center gap-3">
                            <svg class="h-4 w-4 shrink-0 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-xs font-semibold text-slate-800 sm:text-sm dark:text-slate-200">One-click
                                handover to our sales desk for formal billing &amp; dispatch</span>
                        </div>
                    </div>

                    <div class="pt-4">
                        <a href="{{ route('customer.quotation-builder') }}"
                            class="inline-flex items-center gap-2.5 rounded-xl bg-[#214fe0] px-6 py-3.5 text-xs font-bold text-white shadow-lg transition hover:bg-[#1a42be] sm:text-sm">
                            <span>Open Quotation Builder App</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Stylized Mobile Mockup Frame (Antixor Mobile Frame) -->
                <div class="flex justify-center lg:col-span-6">
                    <div class="card-3d w-full max-w-xs rounded-[2.5rem] border-4 border-slate-700 bg-slate-900 p-3.5 shadow-2xl sm:max-w-sm"
                        data-3d-tilt data-max-tilt="8">
                        <div class="glare-sheen"></div>

                        <!-- Phone Speaker & Camera Notch -->
                        <div class="mx-auto mb-3 h-4 w-28 rounded-full bg-slate-800"></div>

                        <!-- Screen Content -->
                        <div
                            class="space-y-3.5 overflow-hidden rounded-[2rem] bg-white p-4 text-slate-900 dark:bg-[#0c1220] dark:text-white">
                            <div
                                class="flex items-center justify-between border-b border-slate-100 pb-2 dark:border-slate-800">
                                <div class="text-[11px] font-black uppercase text-[#214fe0]">Huenics Instant Quote</div>
                                <span
                                    class="rounded bg-emerald-100 px-2 py-0.5 font-mono text-[10px] font-bold text-emerald-600 dark:bg-emerald-950">ONLINE</span>
                            </div>

                            <div class="space-y-2">
                                <div class="text-[10px] font-bold uppercase text-slate-400">Sample Line Items:</div>
                                <div
                                    class="space-y-1 rounded-xl border border-slate-100 bg-slate-50 p-2.5 text-xs dark:border-slate-800 dark:bg-[#161f38]">
                                    <div class="flex justify-between font-bold">
                                        <span class="truncate pr-1">18W Citizen COB Downlight</span>
                                        <span class="font-mono">x 50</span>
                                    </div>
                                    <div class="flex justify-between text-[11px] text-slate-500">
                                        <span>3000K &bull; Ra 90 &bull; Dimmable</span>
                                        <span class="font-mono">&#8369; 32,500.00</span>
                                    </div>
                                </div>
                                <div
                                    class="space-y-1 rounded-xl border border-slate-100 bg-slate-50 p-2.5 text-xs dark:border-slate-800 dark:bg-[#161f38]">
                                    <div class="flex justify-between font-bold">
                                        <span class="truncate pr-1">24V High-CRI Linear Strip (5m)</span>
                                        <span class="font-mono">x 20</span>
                                    </div>
                                    <div class="flex justify-between text-[11px] text-slate-500">
                                        <span>4000K &bull; IP67 Outdoor</span>
                                        <span class="font-mono">&#8369; 18,000.00</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-1 border-t border-slate-200 pt-2 text-xs dark:border-slate-800">
                                <div class="flex justify-between text-slate-500">
                                    <span>12% BIR VAT:</span>
                                    <span class="font-mono font-bold text-slate-800 dark:text-slate-200">&#8369;
                                        6,060.00</span>
                                </div>
                                <div
                                    class="flex justify-between pt-1 text-sm font-black text-[#214fe0] dark:text-[#60a5fa]">
                                    <span>Est. Total:</span>
                                    <span class="font-mono">&#8369; 56,560.00</span>
                                </div>
                            </div>

                            <div
                                class="w-full rounded-xl bg-[#214fe0] py-2.5 text-center text-xs font-bold text-white shadow-md">
                                Download Official Estimate (PDF)
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ==========================================================================
                 SECTION 10: TECHNICAL RESOURCES & ENGINEERING INSIGHTS (Antixor 3 Editorial Cards)
                 ========================================================================== -->
    <section
        class="border-b border-slate-200 bg-white py-16 transition-colors duration-200 dark:border-slate-800/80 dark:bg-[#0a0e1a]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 flex flex-col items-start justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">
                        Engineering Knowledgebase
                    </span>
                    <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                        Technical Resources &amp; Specifications
                    </h2>
                    <p class="mt-1 max-w-xl text-xs text-slate-500 sm:text-sm dark:text-slate-400">
                        Engineering articles on power factor, thermal dissipation, and Philippine corporate procurement
                        standards.
                    </p>
                </div>
                <a href="{{ route('customer.about') }}"
                    class="flex items-center gap-1 text-xs font-bold text-[#214fe0] hover:underline sm:text-sm dark:text-[#60a5fa]">
                    <span>All Technical Guides</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <!-- Article 1: Power Factor -->
                <article
                    class="card-interactive flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-[#111827]"
                    data-3d-tilt data-max-tilt="6">
                    <div class="glare-sheen"></div>
                    <div>
                        <div class="mb-3 flex items-center justify-between text-xs">
                            <span
                                class="text-[10px] font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">Electrical
                                Engineering</span>
                            <span class="text-[11px] text-slate-400">5 min read</span>
                        </div>
                        <h3 class="mb-2 text-base font-bold leading-snug text-slate-900 dark:text-white">
                            LED Driver Topologies: Reducing THD &amp; Maintaining PF &gt; 0.95 in Commercial Risers
                        </h3>
                        <p class="mb-4 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                            How high harmonic distortion degrades facility transformers and how certified constant-current
                            drivers ensure electrical code compliance.
                        </p>
                    </div>
                    <div
                        class="flex items-center gap-1 border-t border-slate-100 pt-4 text-[11px] font-bold text-[#214fe0] dark:border-slate-800 dark:text-[#60a5fa]">
                        <span>Read Technical Paper</span>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>
                </article>

                <!-- Article 2: Citizen COB Thermal -->
                <article
                    class="card-interactive flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-[#111827]"
                    data-3d-tilt data-max-tilt="6">
                    <div class="glare-sheen"></div>
                    <div>
                        <div class="mb-3 flex items-center justify-between text-xs">
                            <span
                                class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Optoelectronics</span>
                            <span class="text-[11px] text-slate-400">4 min read</span>
                        </div>
                        <h3 class="mb-2 text-base font-bold leading-snug text-slate-900 dark:text-white">
                            Citizen Japan C.O.B Thermal Dissipation: Heatsink Design for 50,000h L70 Lifespan
                        </h3>
                        <p class="mb-4 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                            Why junction temperature (Tj &lt; 85&deg;C) is the single most critical factor in preventing
                            premature lumen degradation and color shift.
                        </p>
                    </div>
                    <div
                        class="flex items-center gap-1 border-t border-slate-100 pt-4 text-[11px] font-bold text-amber-600 dark:border-slate-800 dark:text-amber-400">
                        <span>Read Technical Paper</span>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>
                </article>

                <!-- Article 3: BIR Tax & Invoicing -->
                <article
                    class="card-interactive flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-[#111827]"
                    data-3d-tilt data-max-tilt="6">
                    <div class="glare-sheen"></div>
                    <div>
                        <div class="mb-3 flex items-center justify-between text-xs">
                            <span
                                class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Tax
                                &amp; Procurement</span>
                            <span class="text-[11px] text-slate-400">6 min read</span>
                        </div>
                        <h3 class="mb-2 text-base font-bold leading-snug text-slate-900 dark:text-white">
                            The Contractor's Guide to BIR Sales Invoices, Form 2307 Withholding, and Jobsite DRs
                        </h3>
                        <p class="mb-4 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                            Navigating auditable creditable withholding tax (CWT) and official receipt compliance for
                            corporate and government construction projects.
                        </p>
                    </div>
                    <div
                        class="flex items-center gap-1 border-t border-slate-100 pt-4 text-[11px] font-bold text-emerald-600 dark:border-slate-800 dark:text-emerald-400">
                        <span>Read Technical Paper</span>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- ==========================================================================
                 SECTION 11: FINAL CALL TO ACTION (Antixor High-Impact Closing Banner)
                 ========================================================================== -->
    <section
        class="relative overflow-hidden bg-[#214fe0] py-16 text-center text-white dark:bg-gradient-to-r dark:from-[#152e80] dark:to-[#0d1d52]">
        <div class="pointer-events-none absolute inset-0 opacity-10"
            style="background: repeating-linear-gradient(45deg, #ffffff, #ffffff 3px, transparent 3px, transparent 15px);">
        </div>

        <div class="relative z-10 mx-auto max-w-4xl space-y-6 px-4">
            <div
                class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-blue-100">
                <span>Direct Commercial Sourcing</span>
            </div>

            <h2 class="text-3xl font-black leading-tight tracking-tight sm:text-5xl">
                Ready to Build Your Commercial Quotation?
            </h2>

            <p class="mx-auto max-w-2xl text-sm font-normal leading-relaxed text-blue-100 sm:text-base">
                Assemble your Bill of Quantities (BOQ) online in minutes or talk directly with our Mandaluyong technical
                sales desk for project indent pricing.
            </p>

            <div class="flex flex-col items-center justify-center gap-4 pt-4 sm:flex-row">
                <a href="{{ route('customer.quotation-builder') }}"
                    class="flex w-full transform items-center justify-center gap-2 rounded-xl bg-amber-400 px-8 py-4 text-sm font-bold text-slate-950 shadow-xl transition hover:-translate-y-0.5 hover:bg-amber-300 active:scale-95 sm:w-auto">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Launch Instant Quotation Generator</span>
                </a>

                <a href="{{ route('customer.about') }}"
                    class="flex w-full transform items-center justify-center gap-2 rounded-xl border-2 border-white/30 bg-white/10 px-8 py-4 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-white/20 active:scale-95 sm:w-auto">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Contact Engineering Sales Desk</span>
                </a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <!-- Dedicated High-Performance Three.js Engine (Loaded on Home Page Only) -->
    <script id="threejs-cdn-script" src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js" defer></script>
    <script>
        // Suppress benign DevTools extension warning if external hook inspects THREE
        (function() {
            if (!window.__huenicsWarnFiltered) {
                window.__huenicsWarnFiltered = true;
                const originalWarn = console.warn;
                console.warn = function(...args) {
                    if (typeof args[0] === 'string' && args[0].includes(
                            'Multiple instances of Three.js being imported')) {
                        return;
                    }
                    originalWarn.apply(console, args);
                };
            }
        })();

        // Tab Switcher for Floating Hero Estimator Hub
        window.switchHeroTab = function(tab) {
            const tabs = ['quote', 'fleet', 'indent'];
            tabs.forEach(t => {
                const btn = document.getElementById('tab-btn-' + t);
                const panel = document.getElementById('panel-' + t);
                if (!btn || !panel) return;

                if (t === tab) {
                    btn.className =
                        'flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all duration-200 flex items-center justify-center gap-1.5 bg-white dark:bg-[#1a233b] text-[#214fe0] dark:text-[#60a5fa] shadow-sm';
                    panel.classList.remove('hidden');
                } else {
                    btn.className =
                        'flex-1 py-2 px-3 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all duration-200 flex items-center justify-center gap-1.5';
                    panel.classList.add('hidden');
                }
            });
        };

        window.adjustHeroQty = function(delta) {
            const input = document.getElementById('hero-qty-input');
            if (!input) return;
            let val = parseInt(input.value, 10) || 10;
            val = Math.max(1, val + delta);
            input.value = val;
        };

        window.launchHeroQuote = function() {
            const cat = document.getElementById('hero-category-select')?.value || '';
            const spec = document.getElementById('hero-spec-select')?.value || '';
            const qty = document.getElementById('hero-qty-input')?.value || 10;

            let url = '{{ route('customer.quotation-builder') }}';
            const params = new URLSearchParams();
            if (cat) params.append('category', cat);
            if (spec && spec !== 'all') params.append('spec', spec);
            if (qty) params.append('qty', qty);

            const qs = params.toString();
            if (qs) url += '?' + qs;
            window.location.href = url;
        };

        window.searchHeroFleet = function() {
            const q = document.getElementById('hero-fleet-search')?.value.trim() || '';
            let url = '{{ route('customer.products') }}';
            if (q) url += '?search=' + encodeURIComponent(q);
            window.location.href = url;
        };

        // Category Filter for Popular Products Fleet Showcase
        window.filterFleetCategory = function(category, buttonEl) {
            document.querySelectorAll('.fleet-filter-btn').forEach(btn => {
                btn.className =
                    'fleet-filter-btn px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all duration-200 bg-slate-100 dark:bg-[#151f38] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700/60';
            });
            if (buttonEl) {
                buttonEl.className =
                    'fleet-filter-btn px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-200 bg-[#214fe0] text-white shadow-md';
            }

            const cards = document.querySelectorAll('.fleet-product-card');
            let visibleCount = 0;
            cards.forEach(card => {
                const cardCat = card.getAttribute('data-category');
                if (category === 'all' || cardCat === category) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const emptyState = document.getElementById('fleet-empty-state');
            if (emptyState) {
                emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        };

        // Card Quantity Steppers
        window.adjustCardQty = function(inputId, delta) {
            const input = document.getElementById(inputId);
            if (!input) return;
            let val = parseInt(input.value, 10) || 1;
            val = Math.max(1, val + delta);
            input.value = val;
        };

        // Add to Quotation Handler
        window.addProductToQuote = function(product, qty) {
            if (window.CartManager) {
                CartManager.addItem(product, qty);
            }
        };

        // Resilient singleton bootstrapper for 3D Luminaire Stage
        function bootstrapLuminaire() {
            if (typeof window.THREE !== 'undefined') {
                if (typeof window.initHuenicsLuminaire3D === 'function') {
                    window.initHuenicsLuminaire3D();
                }
                return;
            }
            const existingScript = document.getElementById('threejs-cdn-script') || document.querySelector(
                'script[src*="three.min.js"]');
            if (existingScript) {
                existingScript.addEventListener('load', () => {
                    if (typeof window.initHuenicsLuminaire3D === 'function') {
                        window.initHuenicsLuminaire3D();
                    }
                }, {
                    once: true
                });
                return;
            }
            const script = document.createElement('script');
            script.id = 'threejs-cdn-script';
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';
            script.defer = true;
            script.onload = () => {
                if (typeof window.initHuenicsLuminaire3D === 'function') {
                    window.initHuenicsLuminaire3D();
                }
            };
            document.head.appendChild(script);
        }

        // Re-initialize 3D physics and icons when view loads or on SPA page transitions
        document.addEventListener('DOMContentLoaded', () => {
            if (window.Huenics3D) Huenics3D.init();
            if (window.lucide) lucide.createIcons();
            bootstrapLuminaire();
        });

        document.addEventListener('huenics:page-loaded', () => {
            if (window.Huenics3D) Huenics3D.init();
            if (window.lucide) lucide.createIcons();
            bootstrapLuminaire();
        });

        /* =========================================================================
         * HUENICS 3D CITIZEN LED COB ENGINE (Photorealistic Studio WebGL)
         * ========================================================================= */
        (function() {
            let scene, camera, renderer, cobGroup, phosphorMesh, damMesh, ceramicMesh, backplateMesh, colletMesh,
                opticRimMesh;
            let bloomSprite, particleSystem, shockwaveRing, corePointLight, ambientLight, keyLight, rimLight;
            let spotLight, spotLightTarget, beamConeMesh;
            let isPowerOn = true;
            let currentCCT = '3000K';
            let currentRotX = 0.20,
                currentRotY = -0.32;
            let basePitch = 0.20,
                baseYaw = -0.32;
            let isDragging = false;
            let previousMousePosition = {
                x: 0,
                y: 0
            };
            let surgeTime = 0;
            let isSurging = false;
            let isVisible = true;
            let thermalPower = 1.0; // 0.0 (off) to 1.0 (full lumen output)
            let flickerCounter = 0;

            const cctProfiles = {
                '3000K': {
                    hex: 0xffb84d, // Warm Soft White (Golden champagne glow per reference)
                    emissive: 0xffa834, // Warm filament glow
                    beamHex: 0xffc266, // Photonic beam tone - warm soft white radiance
                    targetColor: 0xffb84d,
                    ambientTint: 0x241608, // Warm room bounce
                    halo: 'radial-gradient(circle at 50% 50%, rgba(255, 184, 77, 0.20) 0%, rgba(245, 158, 11, 0.05) 30%, transparent 55%)',
                    badgeText: 'CITIZEN COB • 3000K SOFT WHITE • 24° SPOT • CRI 80',
                    shortBadgeText: '3000K SOFT WHITE • 24° • CRI 80',
                    name: '3000K Soft White Glow (Warm White • CRI 80)',
                    btnClass: 'bg-amber-500 text-white shadow-md shadow-amber-500/30',
                    powerScale: 0.94,
                    lumens: '3,450 lm'
                },
                '3500K': {
                    hex: 0xffe6c4, // Neutral Warm White (Creamy ivory / vanilla white per reference)
                    emissive: 0xffd59e,
                    beamHex: 0xffebcc, // Creamy ivory neutral-warm beam
                    targetColor: 0xffe6c4,
                    ambientTint: 0x1f1912,
                    halo: 'radial-gradient(circle at 50% 50%, rgba(255, 230, 196, 0.18) 0%, rgba(245, 180, 60, 0.05) 30%, transparent 55%)',
                    badgeText: 'CITIZEN COB • 3500K NEUTRAL GLOW • 24° SPOT • CRI 80',
                    shortBadgeText: '3500K NEUTRAL • 24° • CRI 80',
                    name: '3500K Neutral Glow (Warm White • CRI 80)',
                    btnClass: 'bg-amber-400 text-slate-950 shadow-md shadow-amber-400/30',
                    powerScale: 1.00,
                    lumens: '3,680 lm'
                },
                '4000K': {
                    hex: 0xffffff, // Clean Daylight White (Pure crisp balanced white per reference)
                    emissive: 0xf2f6fa,
                    beamHex: 0xffffff, // Pristine pure white daylight beam
                    targetColor: 0xffffff,
                    ambientTint: 0x161a24,
                    halo: 'radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.20) 0%, rgba(226, 232, 240, 0.06) 30%, transparent 55%)',
                    badgeText: 'CITIZEN COB • 4000K DAYLIGHT GLOW • 24° SPOT • CRI 80',
                    shortBadgeText: '4000K DAYLIGHT • 24° • CRI 80',
                    name: '4000K Daylight Glow (Neutral White • CRI 80)',
                    btnClass: 'bg-slate-100 text-slate-950 shadow-md shadow-white/30 border border-slate-300',
                    powerScale: 1.08,
                    lumens: '3,920 lm'
                },
                '5000K': {
                    hex: 0xd4e9ff, // Crystal Cool White (Bright diamond daylight with ice blue tint per reference)
                    emissive: 0xb8dcff,
                    beamHex: 0xcce4ff, // Radiant cool crystal ice beam
                    targetColor: 0xd4e9ff,
                    ambientTint: 0x0c1b30,
                    halo: 'radial-gradient(circle at 50% 50%, rgba(186, 230, 253, 0.20) 0%, rgba(56, 189, 248, 0.06) 30%, transparent 55%)',
                    badgeText: 'CITIZEN COB • 5000K CRYSTAL WHITE • 24° SPOT • CRI 80',
                    shortBadgeText: '5000K CRYSTAL • 24° • CRI 80',
                    name: '5000K Crystal White Glow (Cool White • CRI 80)',
                    btnClass: 'bg-cyan-400 text-slate-950 shadow-md shadow-cyan-400/30',
                    powerScale: 1.18,
                    lumens: '4,150 lm'
                }
            };

            // Procedural Studio HDR Environment Map for Crystal Glass & Brass Specular Reflections
            function createStudioEnvironment() {
                const canvas = document.createElement('canvas');
                canvas.width = 512;
                canvas.height = 256;
                const ctx = canvas.getContext('2d');
                if (!ctx) return null;

                // Dark luxury studio backdrop gradient
                const bgGrad = ctx.createLinearGradient(0, 0, 0, 256);
                bgGrad.addColorStop(0, '#0c1322');
                bgGrad.addColorStop(0.4, '#141e34');
                bgGrad.addColorStop(0.7, '#0f172a');
                bgGrad.addColorStop(1, '#080d1a');
                ctx.fillStyle = bgGrad;
                ctx.fillRect(0, 0, 512, 256);

                // Key Light Softbox Panel (Upper-Right)
                const keyGrad = ctx.createRadialGradient(370, 70, 10, 370, 70, 95);
                keyGrad.addColorStop(0, 'rgba(255, 255, 255, 0.95)');
                keyGrad.addColorStop(0.4, 'rgba(240, 245, 255, 0.70)');
                keyGrad.addColorStop(0.8, 'rgba(200, 220, 255, 0.22)');
                keyGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');
                ctx.fillStyle = keyGrad;
                ctx.beginPath();
                ctx.ellipse(370, 70, 85, 55, 0.15, 0, Math.PI * 2);
                ctx.fill();

                // Fill Light Softbox Panel (Mid-Left)
                const fillGrad = ctx.createRadialGradient(110, 110, 8, 110, 110, 80);
                fillGrad.addColorStop(0, 'rgba(210, 230, 255, 0.55)');
                fillGrad.addColorStop(0.5, 'rgba(160, 195, 245, 0.25)');
                fillGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');
                ctx.fillStyle = fillGrad;
                ctx.beginPath();
                ctx.ellipse(110, 110, 75, 50, -0.15, 0, Math.PI * 2);
                ctx.fill();

                // Overhead Warm Tungsten Rim Strip
                const rimGrad = ctx.createLinearGradient(120, 0, 400, 0);
                rimGrad.addColorStop(0, 'rgba(0, 0, 0, 0)');
                rimGrad.addColorStop(0.2, 'rgba(255, 230, 180, 0.65)');
                rimGrad.addColorStop(0.5, 'rgba(255, 245, 220, 0.85)');
                rimGrad.addColorStop(0.8, 'rgba(255, 230, 180, 0.65)');
                rimGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');
                ctx.fillStyle = rimGrad;
                ctx.fillRect(120, 12, 280, 16);

                // Floor Light Bounce Strip
                const floorGrad = ctx.createLinearGradient(0, 200, 0, 256);
                floorGrad.addColorStop(0, 'rgba(0, 0, 0, 0)');
                floorGrad.addColorStop(1, 'rgba(30, 45, 75, 0.4)');
                ctx.fillStyle = floorGrad;
                ctx.fillRect(0, 200, 512, 56);

                const envTexture = new THREE.CanvasTexture(canvas);
                envTexture.mapping = THREE.EquirectangularReflectionMapping;
                return envTexture;
            }

            // Neutral High-CRI Optical Bloom Texture (True to Active CCT Wavelength)
            function createOpticalBloomTexture() {
                const canvas = document.createElement('canvas');
                canvas.width = 256;
                canvas.height = 256;
                const ctx = canvas.getContext('2d');
                if (!ctx) return null;

                const radGrad = ctx.createRadialGradient(128, 128, 4, 128, 128, 120);
                radGrad.addColorStop(0, 'rgba(255, 255, 255, 1.0)');
                radGrad.addColorStop(0.14, 'rgba(255, 255, 255, 0.88)');
                radGrad.addColorStop(0.35, 'rgba(255, 255, 255, 0.48)');
                radGrad.addColorStop(0.62, 'rgba(255, 255, 255, 0.16)');
                radGrad.addColorStop(0.84, 'rgba(255, 255, 255, 0.04)');
                radGrad.addColorStop(1.0, 'rgba(255, 255, 255, 0.0)');
                ctx.fillStyle = radGrad;
                ctx.fillRect(0, 0, 256, 256);

                return new THREE.CanvasTexture(canvas);
            }

            // Procedural Volumetric 36° Architectural Beam Texture with Collimation Striations
            function createVolumetricBeamTexture() {
                const canvas = document.createElement('canvas');
                canvas.width = 128;
                canvas.height = 512;
                const ctx = canvas.getContext('2d');
                if (!ctx) return null;

                // Longitudinal falloff along the beam (from origin to extent)
                const grad = ctx.createLinearGradient(0, 0, 0, 512);
                grad.addColorStop(0, 'rgba(255, 255, 255, 0.95)');
                grad.addColorStop(0.08, 'rgba(255, 255, 255, 0.72)');
                grad.addColorStop(0.25, 'rgba(255, 255, 255, 0.40)');
                grad.addColorStop(0.55, 'rgba(255, 255, 255, 0.16)');
                grad.addColorStop(0.82, 'rgba(255, 255, 255, 0.04)');
                grad.addColorStop(1.0, 'rgba(255, 255, 255, 0.0)');

                ctx.fillStyle = grad;
                ctx.fillRect(0, 0, 128, 512);

                // Subtle striations to simulate faceted reflector beam collimation
                ctx.fillStyle = 'rgba(255, 255, 255, 0.07)';
                for (let x = 16; x < 128; x += 24) {
                    ctx.fillRect(x, 0, 8, 512);
                }

                const tex = new THREE.CanvasTexture(canvas);
                tex.wrapS = THREE.RepeatWrapping;
                tex.wrapT = THREE.ClampToEdgeWrapping;
                return tex;
            }

            window.initHuenicsLuminaire3D = function() {
                const canvas = document.getElementById('luminaire-3d-canvas');
                if (!canvas || typeof THREE === 'undefined') return;

                // Idempotent: Prevent duplicate initialization loops on the same canvas
                if (canvas.dataset.initialized === 'true') return;
                canvas.dataset.initialized = 'true';

                const container = canvas.parentElement;
                let width = container.clientWidth || 360;
                let height = container.clientHeight || 360;

                // Scene setup
                scene = new THREE.Scene();
                camera = new THREE.PerspectiveCamera(40, width / height, 0.1, 100);
                camera.position.set(0, 0, 7.6);

                renderer = new THREE.WebGLRenderer({
                    canvas: canvas,
                    alpha: true,
                    antialias: true,
                    powerPreference: 'high-performance'
                });
                renderer.setSize(width, height);
                renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.75));
                renderer.toneMapping = THREE.ACESFilmicToneMapping;
                renderer.toneMappingExposure = 1.1;

                // Apply Studio Environment Map for Specular Reflection & Refraction
                const studioEnv = createStudioEnvironment();
                if (studioEnv) {
                    scene.environment = studioEnv;
                }

                cobGroup = new THREE.Group();
                cobGroup.position.set(0, 0, 0);
                scene.add(cobGroup);

                // =========================================================
                // 1. CITIZEN ALUMINA CERAMIC SUBSTRATE BOARD (28mm x 28mm)
                // =========================================================
                // Alumina ceramic substrate plate (High-dielectric off-white ceramic)
                const ceramicGeo = new THREE.BoxGeometry(2.7, 2.7, 0.12);
                const ceramicMat = new THREE.MeshStandardMaterial({
                    color: 0xf8fafc,
                    roughness: 0.35,
                    metalness: 0.05
                });
                ceramicMesh = new THREE.Mesh(ceramicGeo, ceramicMat);
                cobGroup.add(ceramicMesh);

                // Aluminum Heat-Spreader Backing Plate (Thermal base)
                const backplateGeo = new THREE.BoxGeometry(2.76, 2.76, 0.06);
                const backplateMat = new THREE.MeshStandardMaterial({
                    color: 0x94a3b8,
                    metalness: 0.88,
                    roughness: 0.22
                });
                backplateMesh = new THREE.Mesh(backplateGeo, backplateMat);
                backplateMesh.position.set(0, 0, -0.09);
                cobGroup.add(backplateMesh);

                // Extruded Thermal Heatsink Fin Array (Solid rear occlusion & industrial realism)
                const finGeo = new THREE.BoxGeometry(2.5, 0.045, 0.16);
                const finMat = new THREE.MeshStandardMaterial({
                    color: 0x475569,
                    metalness: 0.90,
                    roughness: 0.26
                });
                for (let i = -3; i <= 3; i++) {
                    const finMesh = new THREE.Mesh(finGeo, finMat);
                    finMesh.position.set(0, i * 0.36, -0.17);
                    cobGroup.add(finMesh);
                }

                // Corner Mounting Screw Relief Notches / Holes (4 corners)
                const holeMat = new THREE.MeshStandardMaterial({
                    color: 0x1e293b,
                    metalness: 0.9,
                    roughness: 0.4
                });
                const holePositions = [
                    [-1.08, -1.08],
                    [1.08, -1.08],
                    [-1.08, 1.08],
                    [1.08, 1.08]
                ];
                holePositions.forEach(([hx, hy]) => {
                    const holeGeo = new THREE.CylinderGeometry(0.14, 0.14, 0.14, 16);
                    const holeMesh = new THREE.Mesh(holeGeo, holeMat);
                    holeMesh.rotation.x = Math.PI / 2;
                    holeMesh.position.set(hx, hy, 0);
                    cobGroup.add(holeMesh);

                    // Metallic screw ring rim
                    const ringGeo = new THREE.RingGeometry(0.14, 0.22, 16);
                    const ringMat = new THREE.MeshStandardMaterial({
                        color: 0xc4b5fd,
                        metalness: 0.8,
                        roughness: 0.3
                    });
                    const ringMesh = new THREE.Mesh(ringGeo, ringMat);
                    ringMesh.position.set(hx, hy, 0.062);
                    cobGroup.add(ringMesh);
                });

                // Citizen Polarity Notch (Chamfered Corner at Top-Left)
                const notchGeo = new THREE.BoxGeometry(0.35, 0.35, 0.14);
                const notchMat = new THREE.MeshStandardMaterial({
                    color: 0x0f172a,
                    roughness: 0.5
                });
                const notchMesh = new THREE.Mesh(notchGeo, notchMat);
                notchMesh.position.set(-1.25, 1.25, 0);
                notchMesh.rotation.z = Math.PI / 4;
                cobGroup.add(notchMesh);

                // =========================================================
                // 2. GOLD BONDING PADS & ELECTRICAL POLARITY TERMINALS
                // =========================================================
                const goldMat = new THREE.MeshStandardMaterial({
                    color: 0xeab308,
                    metalness: 0.94,
                    roughness: 0.18
                });

                // Positive Anode (+) Pad at Top-Right
                const padPlusGeo = new THREE.BoxGeometry(0.42, 0.28, 0.02);
                const padPlus = new THREE.Mesh(padPlusGeo, goldMat);
                padPlus.position.set(0.95, 0.95, 0.065);
                cobGroup.add(padPlus);

                // Negative Cathode (-) Pad at Bottom-Left
                const padMinusGeo = new THREE.BoxGeometry(0.42, 0.28, 0.02);
                const padMinus = new THREE.Mesh(padMinusGeo, goldMat);
                padMinus.position.set(-0.95, -0.95, 0.065);
                cobGroup.add(padMinus);

                // Polarity Markings (+) and (-)
                const plusMarkGeo = new THREE.BoxGeometry(0.18, 0.04, 0.02);
                const plusMark1 = new THREE.Mesh(plusMarkGeo, new THREE.MeshBasicMaterial({
                    color: 0x991b1b
                }));
                plusMark1.position.set(0.95, 0.72, 0.066);
                cobGroup.add(plusMark1);
                const plusMark2 = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.18, 0.02), new THREE
                    .MeshBasicMaterial({
                        color: 0x991b1b
                    }));
                plusMark2.position.set(0.95, 0.72, 0.066);
                cobGroup.add(plusMark2);

                const minusMark = new THREE.Mesh(plusMarkGeo, new THREE.MeshBasicMaterial({
                    color: 0x1e3a8a
                }));
                minusMark.position.set(-0.95, -0.72, 0.066);
                cobGroup.add(minusMark);

                // Laser Marking Strip: "CITIZEN CLU048"
                const labelGeo = new THREE.PlaneGeometry(1.6, 0.22);
                const labelCanvas = document.createElement('canvas');
                labelCanvas.width = 256;
                labelCanvas.height = 36;
                const lCtx = labelCanvas.getContext('2d');
                if (lCtx) {
                    lCtx.fillStyle = 'rgba(240, 240, 240, 0)';
                    lCtx.fillRect(0, 0, 256, 36);
                    lCtx.fillStyle = '#475569';
                    lCtx.font = 'bold 18px monospace';
                    lCtx.fillText('CITIZEN CLU048', 18, 24);
                }
                const labelTex = new THREE.CanvasTexture(labelCanvas);
                const labelMat = new THREE.MeshBasicMaterial({
                    map: labelTex,
                    transparent: true
                });
                const labelMesh = new THREE.Mesh(labelGeo, labelMat);
                labelMesh.position.set(0, 1.15, 0.065);
                cobGroup.add(labelMesh);

                // =========================================================
                // 3. CIRCULAR PHOSPHOR LIGHT EMITTING SURFACE (LES) & DAM RING
                // =========================================================
                // Silicone Retention Dam Ring (White resin boundary)
                const damGeo = new THREE.TorusGeometry(0.88, 0.065, 16, 64);
                const damMat = new THREE.MeshStandardMaterial({
                    color: 0xffffff,
                    roughness: 0.25,
                    metalness: 0.1
                });
                damMesh = new THREE.Mesh(damGeo, damMat);
                damMesh.position.set(0, 0, 0.08);
                cobGroup.add(damMesh);

                // Central Circular Phosphor LES Disk
                const phosphorGeo = new THREE.CylinderGeometry(0.86, 0.88, 0.08, 64);
                const phosphorMat = new THREE.MeshStandardMaterial({
                    color: 0xffffff,
                    emissive: cctProfiles[currentCCT].emissive,
                    emissiveIntensity: 6.5,
                    roughness: 0.2,
                    metalness: 0.05
                });
                phosphorMesh = new THREE.Mesh(phosphorGeo, phosphorMat);
                phosphorMesh.rotation.x = Math.PI / 2;
                phosphorMesh.position.set(0, 0, 0.07);
                cobGroup.add(phosphorMesh);

                // Opaque Rear Light Barrier Disc (Blocks any internal photonic leakage to the rear)
                const rearBarrierGeo = new THREE.CircleGeometry(0.92, 32);
                const rearBarrierMat = new THREE.MeshStandardMaterial({
                    color: 0x0f172a,
                    roughness: 0.8,
                    metalness: 0.1,
                    side: THREE.FrontSide
                });
                const rearBarrierMesh = new THREE.Mesh(rearBarrierGeo, rearBarrierMat);
                rearBarrierMesh.position.set(0, 0, 0.035);
                rearBarrierMesh.rotation.y = Math
                    .PI; // Normals face backward towards negative Z to block rear viewing
                cobGroup.add(rearBarrierMesh);

                // Precision Architectural 24° Faceted Reflector Collet (Mounting Bezel)
                const colletGeo = new THREE.CylinderGeometry(1.18, 0.94, 0.18, 32, 1, true);
                colletGeo.rotateX(Math.PI / 2);
                const colletMat = new THREE.MeshStandardMaterial({
                    color: 0xe2e8f0,
                    metalness: 0.94,
                    roughness: 0.16,
                    side: THREE.DoubleSide
                });
                colletMesh = new THREE.Mesh(colletGeo, colletMat);
                colletMesh.position.set(0, 0, 0.14);
                cobGroup.add(colletMesh);

                // Optic Specular Ring Bezel
                const opticRimGeo = new THREE.RingGeometry(1.16, 1.28, 48);
                const opticRimMat = new THREE.MeshStandardMaterial({
                    color: 0x94a3b8,
                    metalness: 0.88,
                    roughness: 0.25,
                    side: THREE.DoubleSide
                });
                opticRimMesh = new THREE.Mesh(opticRimGeo, opticRimMat);
                opticRimMesh.position.set(0, 0, 0.22);
                cobGroup.add(opticRimMesh);

                // =========================================================
                // 4. PRECISION 24° VOLUMETRIC ARCHITECTURAL BEAM CONE
                // =========================================================
                // Calibrated 24° Spot Optic Cone (Half-angle 12°: tan(12°) = 0.21255)
                // Emits exclusively from optic bezel aperture forward into +Z space with zero occlusion
                const beamLength = 4.4;
                const radiusOrigin = 1.16;
                const radiusTerminal = radiusOrigin + beamLength * Math.tan(12 * Math.PI / 180); // ~2.095
                const beamGeo = new THREE.CylinderGeometry(radiusOrigin, radiusTerminal, beamLength, 36, 1, true);
                beamGeo.translate(0, -beamLength / 2, 0);
                beamGeo.rotateX(-Math.PI / 2); // Forward +Z projection from z = 0 to z = +4.4
                const beamTexture = createVolumetricBeamTexture();
                const beamMat = new THREE.MeshBasicMaterial({
                    map: beamTexture,
                    color: cctProfiles[currentCCT].beamHex,
                    transparent: true,
                    opacity: 0.36,
                    side: THREE.DoubleSide,
                    blending: THREE.AdditiveBlending,
                    depthWrite: false,
                    depthTest: false
                });
                beamConeMesh = new THREE.Mesh(beamGeo, beamMat);
                beamConeMesh.position.set(0, 0, 0.22);
                beamConeMesh.renderOrder = 10;
                cobGroup.add(beamConeMesh);

                // =========================================================
                // 5. OPTICAL BLOOM SPRITE (FOCUSED PHOTON GLOW)
                // =========================================================
                const bloomTexture = createOpticalBloomTexture();
                const bloomMat = new THREE.SpriteMaterial({
                    map: bloomTexture,
                    blending: THREE.AdditiveBlending,
                    color: cctProfiles[currentCCT].hex,
                    transparent: true,
                    opacity: 0.52,
                    depthWrite: false,
                    depthTest: false
                });
                bloomSprite = new THREE.Sprite(bloomMat);
                bloomSprite.position.set(0, 0, 0.24);
                bloomSprite.scale.set(2.2, 2.2, 1.0);
                bloomSprite.renderOrder = 11;
                cobGroup.add(bloomSprite);

                // =========================================================
                // 6. ATMOSPHERIC DUST PARTICLES & SURGE SHOCKWAVE
                // =========================================================
                const particleCount = 14;
                const particleGeo = new THREE.BufferGeometry();
                const particlePos = new Float32Array(particleCount * 3);
                for (let i = 0; i < particleCount; i++) {
                    const r = 0.15 + Math.random() * 0.55;
                    const theta = Math.random() * Math.PI * 2;
                    particlePos[i * 3] = r * Math.cos(theta);
                    particlePos[i * 3 + 1] = r * Math.sin(theta);
                    particlePos[i * 3 + 2] = 0.35 + Math.random() * 1.5;
                }
                particleGeo.setAttribute('position', new THREE.BufferAttribute(particlePos, 3));
                const particleMat = new THREE.PointsMaterial({
                    color: cctProfiles[currentCCT].hex,
                    size: 0.038,
                    transparent: true,
                    opacity: 0.50,
                    blending: THREE.AdditiveBlending
                });
                particleSystem = new THREE.Points(particleGeo, particleMat);
                cobGroup.add(particleSystem);

                // Expanding Shockwave Surge Ring
                const ringGeo = new THREE.RingGeometry(0.85, 1.05, 64);
                const ringMat = new THREE.MeshBasicMaterial({
                    color: 0xffffff,
                    transparent: true,
                    opacity: 0,
                    side: THREE.DoubleSide,
                    blending: THREE.AdditiveBlending
                });
                shockwaveRing = new THREE.Mesh(ringGeo, ringMat);
                shockwaveRing.position.set(0, 0, 0.15);
                cobGroup.add(shockwaveRing);

                // =========================================================
                // 7. PHYSICS-BASED DYNAMIC LIGHTING (24° SPOTLIGHT & AMBIENT)
                // =========================================================
                // Core Point Light for High CRI Local Glow (Focused exclusively on forward phosphor plane)
                corePointLight = new THREE.PointLight(cctProfiles[currentCCT].hex, 4.2, 8, 2.0);
                corePointLight.position.set(0, 0, 0.35);
                cobGroup.add(corePointLight);

                // Precision 24° Architectural Spotlight (Collimated Optical Cone)
                // Half-angle = 12 degrees = 0.20944 rad; penumbra = 0.32 for crisp architectural punch
                spotLight = new THREE.SpotLight(cctProfiles[currentCCT].hex, 9.5, 26, 12 * Math.PI / 180, 0.32,
                    1.6);
                spotLight.position.set(0, 0, 0.15);
                spotLightTarget = new THREE.Object3D();
                spotLightTarget.position.set(0, 0, 15);
                cobGroup.add(spotLightTarget);
                spotLight.target = spotLightTarget;
                cobGroup.add(spotLight);

                // Studio Ambiance: Dimmed to allow COB & 24° beam to take dramatic center stage
                ambientLight = new THREE.AmbientLight(0x181c28, 0.22);
                scene.add(ambientLight);

                keyLight = new THREE.DirectionalLight(0xffffff, 0.42);
                keyLight.position.set(5, 7, 6);
                scene.add(keyLight);

                rimLight = new THREE.DirectionalLight(0x93c5fd, 0.35);
                rimLight.position.set(-4, 3, -3);
                scene.add(rimLight);

                // Initial 3D Isometric View
                cobGroup.rotation.x = 0.20;
                cobGroup.rotation.y = -0.32;
                cobGroup.rotation.z = -0.04;

                // =========================================================
                // 8. UNIFIED SILKY DAMPED MOUSE & TOUCH PHYSICS ENGINE
                // =========================================================
                const stageEl = document.getElementById('hero-3d-stage') || container;
                const stageContainer = document.getElementById('hero-stage-container') || (stageEl ? stageEl
                    .parentElement : container);
                const glareSheen = stageEl ? stageEl.querySelector('.glare-sheen') : null;

                const badgeDiscount = document.getElementById('hero-badge-discount');
                const badgeVat = document.getElementById('hero-badge-vat');
                const badgeFreight = document.getElementById('hero-badge-freight');

                // Interactive Damping State Variables
                let targetMouseX = 0,
                    targetMouseY = 0;
                let currentMouseX = 0,
                    currentMouseY = 0;
                let mouseInfluence = 0.0;
                let isHoveringStage = false;
                let stageRotX = 0,
                    stageRotY = 0;

                // Calculate normalized mouse coordinates [-1.0, 1.0] relative to stage center
                function getStageNormalizedCoords(clientX, clientY) {
                    if (!stageEl) return {
                        x: 0,
                        y: 0,
                        isDirect: false
                    };
                    const rect = stageEl.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;
                    const halfW = rect.width / 2;
                    const halfH = rect.height / 2;
                    const x = Math.max(-1.0, Math.min(1.0, (clientX - centerX) / halfW));
                    const y = Math.max(-1.0, Math.min(1.0, (clientY - centerY) / halfH));
                    const distFromCenter = Math.sqrt(x * x + y * y);
                    // Hover threshold encompassing the circular stage and floating badges
                    const isDirect = distFromCenter <= 1.25;
                    return {
                        x,
                        y,
                        isDirect
                    };
                }

                if (stageContainer) {
                    stageContainer.addEventListener('mousedown', (e) => {
                        if (e.target.closest('#luminaire-center-toggle') || e.target.closest(
                                '#luminaire-3d-canvas') || e.target.closest('#hero-3d-stage')) {
                            isDragging = true;
                            previousMousePosition = {
                                x: e.clientX,
                                y: e.clientY
                            };
                        }
                    });

                    window.addEventListener('mouseup', () => {
                        isDragging = false;
                    });

                    // 360-Degree Continuous Viewing & Freedom Tilting Physics
                    stageContainer.addEventListener('mousemove', (e) => {
                        const coords = getStageNormalizedCoords(e.clientX, e.clientY);
                        if (isDragging) {
                            const deltaX = e.clientX - previousMousePosition.x;
                            const deltaY = e.clientY - previousMousePosition.y;
                            baseYaw = (baseYaw + deltaX * 0.015) % (Math.PI * 2);
                            basePitch = Math.max(-Math.PI * 0.95, Math.min(Math.PI * 0.95, basePitch + deltaY * 0.015));
                            currentRotX = basePitch;
                            currentRotY = baseYaw;
                            previousMousePosition = {
                                x: e.clientX,
                                y: e.clientY
                            };
                            isHoveringStage = true;
                        } else {
                            targetMouseX = coords.x;
                            targetMouseY = coords.y;
                            isHoveringStage = coords.isDirect;
                        }
                    });

                    stageContainer.addEventListener('mouseleave', () => {
                        isHoveringStage = false;
                        isDragging = false;
                    });

                    // Touch physics for mobile/tablets with 360-degree freedom
                    stageContainer.addEventListener('touchstart', (e) => {
                        if (e.touches.length > 0) {
                            isDragging = true;
                            previousMousePosition = {
                                x: e.touches[0].clientX,
                                y: e.touches[0].clientY
                            };
                        }
                    }, {
                        passive: true
                    });

                    stageContainer.addEventListener('touchmove', (e) => {
                        if (e.touches.length > 0 && isDragging) {
                            const deltaX = e.touches[0].clientX - previousMousePosition.x;
                            const deltaY = e.touches[0].clientY - previousMousePosition.y;
                            baseYaw = (baseYaw + deltaX * 0.016) % (Math.PI * 2);
                            basePitch = Math.max(-Math.PI * 0.95, Math.min(Math.PI * 0.95, basePitch + deltaY * 0.016));
                            currentRotX = basePitch;
                            currentRotY = baseYaw;
                            previousMousePosition = {
                                x: e.touches[0].clientX,
                                y: e.touches[0].clientY
                            };
                            isHoveringStage = true;
                        }
                    }, {
                        passive: true
                    });

                    stageContainer.addEventListener('touchend', () => {
                        isDragging = false;
                        isHoveringStage = false;
                    });
                }

                // Click Center Hotspot to Toggle Power
                const centerToggle = document.getElementById('luminaire-center-toggle');
                if (centerToggle) {
                    centerToggle.addEventListener('click', (e) => {
                        e.stopPropagation();
                        window.toggleLuminairePower();
                    });
                }

                // High-Precision Viewport & Orientation Resize Handler
                const handleResize = () => {
                    if (!container) return;
                    const newW = container.clientWidth;
                    const newH = container.clientHeight;
                    if (newW > 0 && newH > 0) {
                        camera.aspect = newW / newH;
                        camera.updateProjectionMatrix();
                        renderer.setSize(newW, newH);
                    }
                };
                window.addEventListener('resize', handleResize);
                window.addEventListener('orientationchange', () => setTimeout(handleResize, 150));

                // Pause WebGL Render Loop When Offscreen (0% Idle CPU Impact)
                const obs = new IntersectionObserver((entries) => {
                    isVisible = entries[0].isIntersecting;
                }, {
                    threshold: 0.1
                });
                obs.observe(canvas);

                // =========================================================
                // 9. ANIMATION LOOP WITH LIQUID-SMOOTH INERTIAL PHYSICS
                // =========================================================
                let clock = new THREE.Clock();

                function animate() {
                    requestAnimationFrame(animate);
                    if (!isVisible) return;

                    const delta = Math.min(clock.getDelta(), 0.1);
                    const time = clock.getElapsedTime();

                    // -----------------------------------------------------
                    // Continuous Exponential Smoothing (Zero Snapping / Jerk)
                    // -----------------------------------------------------
                    if (isHoveringStage && !isDragging) {
                        mouseInfluence += (1.0 - mouseInfluence) * 0.08;
                    } else if (!isDragging) {
                        mouseInfluence += (0.0 - mouseInfluence) * 0.035;
                    }

                    currentMouseX += (targetMouseX - currentMouseX) * 0.07;
                    currentMouseY += (targetMouseY - currentMouseY) * 0.07;

                    // -----------------------------------------------------
                    // 1. Citizen LED COB Model 3D Rotation Physics (Full 360° Viewing)
                    // -----------------------------------------------------
                    if (!isDragging) {
                        // Ambient showcase rotation: 360-degree continuous slow turn when not hovering
                        if (!isHoveringStage) {
                            baseYaw = (baseYaw + 0.0035) % (Math.PI * 2);
                        }

                        // Gyroscopic micro-breathing & mouse-guided dynamic parallax tilt
                        const hoverTiltX = (-currentMouseY * 0.35) * mouseInfluence;
                        const hoverTiltY = (currentMouseX * 0.45) * mouseInfluence;
                        const targetX = basePitch + hoverTiltX + Math.sin(time * 0.5) * 0.02;
                        const targetY = baseYaw + hoverTiltY;

                        currentRotX += (targetX - currentRotX) * 0.075;
                        currentRotY += (targetY - currentRotY) * 0.075;
                    }

                    cobGroup.rotation.x = currentRotX;
                    cobGroup.rotation.y = currentRotY;
                    cobGroup.rotation.z = -0.04 + Math.sin(time * 0.25) * 0.015;

                    // -----------------------------------------------------
                    // 2. Silky Damped Circular Stage Micro-Tilt
                    // -----------------------------------------------------
                    if (stageEl) {
                        const targetStageX = (-currentMouseY * 3.5) * mouseInfluence;
                        const targetStageY = (currentMouseX * 4.5) * mouseInfluence;
                        stageRotX += (targetStageX - stageRotX) * 0.065;
                        stageRotY += (targetStageY - stageRotY) * 0.065;

                        stageEl.style.transform =
                            `perspective(1000px) rotateX(${stageRotX.toFixed(2)}deg) rotateY(${stageRotY.toFixed(2)}deg)`;
                    }

                    // -----------------------------------------------------
                    // 3. Dynamic Specular Glare Tracking
                    // -----------------------------------------------------
                    if (glareSheen) {
                        const glareX = 50 + currentMouseX * 35;
                        const glareY = 50 + currentMouseY * 35;
                        glareSheen.style.opacity = (0.2 + mouseInfluence * 0.45).toFixed(2);
                        glareSheen.style.background =
                            `radial-gradient(circle at ${glareX.toFixed(1)}% ${glareY.toFixed(1)}%, rgba(255,255,255,0.22) 0%, transparent 60%)`;
                    }

                    // -----------------------------------------------------
                    // 4. Floating Depth Badges Damped Parallax
                    // -----------------------------------------------------
                    if (badgeDiscount) {
                        const px = (currentMouseX * 18 * mouseInfluence).toFixed(1);
                        const py = (currentMouseY * 18 * mouseInfluence).toFixed(1);
                        badgeDiscount.style.transform = `translate3d(${px}px, ${py}px, 32px)`;
                    }
                    if (badgeVat) {
                        const px = (currentMouseX * 14 * mouseInfluence).toFixed(1);
                        const py = (currentMouseY * 14 * mouseInfluence).toFixed(1);
                        badgeVat.style.transform = `translate3d(${px}px, ${py}px, 24px)`;
                    }
                    if (badgeFreight) {
                        const px = (currentMouseX * 16 * mouseInfluence).toFixed(1);
                        const py = (currentMouseY * 16 * mouseInfluence).toFixed(1);
                        badgeFreight.style.transform = `translate3d(${px}px, ${py}px, 28px)`;
                    }

                    // LED Phosphor Electronic Response (Instant warm-up, phosphor decay curve)
                    if (isPowerOn) {
                        thermalPower += (1.0 - thermalPower) * 0.16;
                    } else {
                        thermalPower += (0.0 - thermalPower) * 0.08;
                    }

                    // Dynamic Phosphor Color Emission & 36° Collimated Optic Tuning
                    const profile = cctProfiles[currentCCT];
                    const coldPhosphor = new THREE.Color(0xf59e0b); // Unexcited yellow-amber phosphor
                    const targetColor = new THREE.Color(profile.targetColor);
                    const emissiveColor = new THREE.Color(profile.emissive);

                    let displayColor = new THREE.Color();
                    displayColor.lerpColors(coldPhosphor, targetColor, Math.min(1.0, thermalPower * 1.25));

                    let displayEmissive = new THREE.Color();
                    displayEmissive.lerpColors(new THREE.Color(0x332200), emissiveColor, thermalPower);

                    // Update Central Phosphor LES Disc
                    if (phosphorMesh) {
                        phosphorMesh.material.emissive.copy(displayEmissive);
                        const surgeMult = isSurging ? 1.6 : 1.0;
                        phosphorMesh.material.emissiveIntensity = thermalPower * 7.5 * surgeMult * profile
                            .powerScale;
                        phosphorMesh.material.color.lerpColors(coldPhosphor, targetColor, thermalPower);
                    }

                    // Update Optical Bloom Sprite (Focused tightly on phosphor LES core)
                    if (bloomSprite) {
                        const breathe = 1.0 + Math.sin(time * 2.4) * 0.02;
                        const surgeScale = isSurging ? 1.25 : 1.0;
                        const sW = 2.2 * (0.35 + 0.65 * thermalPower) * breathe * surgeScale;
                        const sH = 2.2 * (0.35 + 0.65 * thermalPower) * breathe * surgeScale;
                        bloomSprite.scale.set(sW, sH, 1.0);
                        bloomSprite.material.opacity = Math.pow(thermalPower, 1.4) * (isSurging ? 0.85 : 0.52);
                        bloomSprite.material.color.copy(displayColor);
                    }

                    // Update 24° Architectural Collimated Spotlight
                    if (spotLight) {
                        spotLight.intensity = Math.pow(thermalPower, 1.8) * (isSurging ? 15.0 : 9.5) * profile
                            .powerScale;
                        spotLight.color.copy(displayColor);
                    }

                    // Update 24° Volumetric Architectural Spot Beam Cone
                    if (beamConeMesh) {
                        beamConeMesh.material.color.copy(new THREE.Color(profile.beamHex));
                        beamConeMesh.material.opacity = Math.pow(thermalPower, 1.4) * (isSurging ? 0.70 : 0.36);
                    }

                    // Update Core Point Light with CRI 80 Local Glow
                    if (corePointLight) {
                        corePointLight.intensity = Math.pow(thermalPower, 1.8) * (isSurging ? 6.0 : 3.2);
                        corePointLight.color.copy(displayColor);
                    }

                    // Dynamic Room Ambient Tinting (Reflects active CCT warmth or cool daylight)
                    if (ambientLight) {
                        const baseAmb = new THREE.Color(0x181c28);
                        const tintAmb = new THREE.Color(profile.ambientTint).multiplyScalar(thermalPower * 0.5);
                        ambientLight.color.copy(baseAmb.add(tintAmb));
                    }

                    // Floating Dust Motes Drift in Atmospheric Light Beam (Zero opacity when OFF)
                    if (particleSystem) {
                        const positions = particleSystem.geometry.attributes.position.array;
                        for (let i = 0; i < particleCount; i++) {
                            positions[i * 3 + 2] += (thermalPower > 0.1 ? 0.010 : 0.002);
                            if (positions[i * 3 + 2] > 2.2) {
                                positions[i * 3 + 2] = 0.35;
                            }
                        }
                        particleSystem.geometry.attributes.position.needsUpdate = true;
                        particleSystem.material.opacity = thermalPower * 0.50;
                        particleSystem.material.color.copy(displayColor);
                    }

                    // Photonic Surge Shockwave Ring Animation
                    if (isSurging) {
                        surgeTime += delta * 3.2;
                        flickerCounter++;
                        // Micro-arc pulse flicker on initial frames
                        if (flickerCounter % 2 === 0 && surgeTime < 0.25 && corePointLight) {
                            corePointLight.intensity *= 0.65;
                        }
                        const ringScale = 1.0 + surgeTime * 3.6;
                        shockwaveRing.scale.set(ringScale, ringScale, 1);
                        shockwaveRing.material.opacity = Math.max(0, 0.95 - surgeTime);

                        if (surgeTime > 1.0) {
                            isSurging = false;
                            shockwaveRing.material.opacity = 0;
                            flickerCounter = 0;
                        }
                    }

                    renderer.render(scene, camera);
                }

                animate();
                updateLuminaireStatusText();
            };

            function updateLuminaireStatusText() {
                const text = document.getElementById('luminaire-status-text');
                if (!text) return;
                if (!isPowerOn) {
                    text.innerText = window.innerWidth < 640 ? 'COB STANDBY' : 'CITIZEN COB LED • STANDBY MODE';
                    return;
                }
                const profile = cctProfiles[currentCCT];
                if (!profile) return;
                text.innerText = window.innerWidth < 640 ? (profile.shortBadgeText || profile.badgeText) : profile
                    .badgeText;
            }

            // Industrial Power Switch (ON / OFF) with Incandescent Warm-up & Phosphor Decay
            window.toggleLuminairePower = function() {
                isPowerOn = !isPowerOn;
                const btn = document.getElementById('luminaire-power-btn');
                const label = document.getElementById('luminaire-power-label');
                const dot = document.getElementById('luminaire-status-dot');
                const halo = document.getElementById('luminaire-ambient-halo');

                if (isPowerOn) {
                    if (btn) {
                        btn.className =
                            'flex items-center justify-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl text-[10.5px] sm:text-xs font-bold transition-all duration-200 bg-emerald-500 text-white hover:bg-emerald-600 shadow-md shadow-emerald-500/20 active:scale-95 cursor-pointer shrink-0 whitespace-nowrap';
                    }
                    if (label) label.innerHTML = '<span class="hidden sm:inline">COB: </span>ON';
                    if (dot) dot.className =
                        'w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-400 animate-pulse shrink-0';
                    updateLuminaireStatusText();
                    if (halo) {
                        halo.style.opacity = '0.30';
                        halo.style.background = cctProfiles[currentCCT].halo;
                    }
                    window.triggerLuminaireSurge();
                } else {
                    if (btn) {
                        btn.className =
                            'flex items-center justify-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl text-[10.5px] sm:text-xs font-bold transition-all duration-200 bg-slate-700 text-slate-300 hover:bg-slate-600 active:scale-95 cursor-pointer shrink-0 whitespace-nowrap';
                    }
                    if (label) label.innerHTML = '<span class="hidden sm:inline">COB: </span>OFF';
                    if (dot) dot.className = 'w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-slate-500 shrink-0';
                    updateLuminaireStatusText();
                    if (halo) {
                        halo.style.opacity = '0';
                    }
                }
            };

            // Kelvin Color Temperature Selector (3000K / 3500K / 4000K / 5000K)
            window.setLuminaireCCT = function(cct, btnEl) {
                if (!cctProfiles[cct]) return;
                currentCCT = cct;
                const profile = cctProfiles[cct];

                // Update CCT Buttons with High-Contrast Active State (Rigid Grid Cells: Zero Shift or Collapse)
                document.querySelectorAll('.cct-btn').forEach(b => {
                    const isSelected = b.getAttribute('data-cct') === cct || b === btnEl;
                    if (isSelected) {
                        b.className =
                            'cct-btn w-full py-1 rounded-lg text-[9.5px] xs:text-[10px] sm:text-[11px] font-black transition-all duration-200 cursor-pointer text-center ' +
                            profile.btnClass;
                    } else {
                        b.className =
                            'cct-btn w-full py-1 rounded-lg text-[9.5px] xs:text-[10px] sm:text-[11px] font-black transition-all duration-200 cursor-pointer text-center text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white';
                    }
                });

                // Update Ambient Halo & Tech Badge (Clean and minimized)
                const halo = document.getElementById('luminaire-ambient-halo');
                if (halo) {
                    halo.style.background = profile.halo;
                    halo.style.opacity = isPowerOn ? '0.30' : '0';
                }
                updateLuminaireStatusText();

                // Trigger brief photonic surge to dramatize the CCT transition if powered on
                if (isPowerOn) {
                    window.triggerLuminaireSurge();
                }
            };

            window.addEventListener('resize', updateLuminaireStatusText);

            // Photonic Ignition Surge Animation
            window.triggerLuminaireSurge = function() {
                if (!isPowerOn) return;
                isSurging = true;
                surgeTime = 0;
                flickerCounter = 0;
                if (shockwaveRing) {
                    shockwaveRing.scale.set(1, 1, 1);
                    shockwaveRing.material.opacity = 0.95;
                }
            };
        })();
    </script>
@endpush
