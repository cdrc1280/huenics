@extends('layouts.customer')

@section('title', 'Huenics Industrial Sales Inc. - Colors • Techniques • Technology')

@section('content')
<!-- ==========================================================================
     SECTION 1: HERO SECTION & 3D SPATIAL CONTROL HUB (Antixor Luxury Architecture)
     ========================================================================== -->
<section class="relative bg-white dark:bg-[#070b14] ambient-mesh-hero overflow-hidden py-12 lg:py-20 border-b border-slate-200 dark:border-slate-800/80 transition-colors duration-200">
    <!-- Blueprint / Geometric Micro-Grid Background -->
    <div class="absolute inset-0 pointer-events-none opacity-40 dark:opacity-20" 
         style="background-image: radial-gradient(rgba(33, 79, 224, 0.15) 1px, transparent 1px); background-size: 28px 28px;"></div>
    <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-bl from-blue-600/15 dark:from-blue-500/15 via-blue-500/5 to-transparent pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-8 items-center">
            
            <!-- Left Column: Kinetic Typography & Floating Estimator Hub -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <!-- Tagline Badge -->
                <div class="inline-flex items-center gap-2 bg-blue-50 dark:bg-blue-950/70 border border-blue-200 dark:border-blue-800/70 text-[#214fe0] dark:text-[#60a5fa] px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-[#214fe0] dark:bg-[#3b82f6] animate-pulse"></span>
                    Direct Importer &bull; Wholesale Engineering Supply &bull; HISI
                </div>

                <!-- Headline -->
                <div class="space-y-2">
                    <div class="text-xs sm:text-sm font-extrabold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        Commercial Optoelectronics &bull; Power Distribution
                    </div>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-slate-950 dark:text-white leading-[1.08]">
                        Industrial Lighting <br class="hidden sm:inline">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#214fe0] via-blue-500 to-indigo-400">
                            &amp; Power Systems.
                        </span><br>
                        Your Way.
                    </h1>
                </div>

                <!-- Brand Narrative -->
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    Direct importer and wholesale distributor of Citizen Japan C.O.B downlights, industrial drivers, architectural linear systems, and certified electrical infrastructure materials across the Philippines.
                </p>

                <!-- Floating Interactive Estimator & Procurement Hub (Antixor Control Card) -->
                <div class="bg-white dark:bg-[#111827] border-2 border-blue-600/20 dark:border-slate-800 rounded-2xl shadow-xl overflow-hidden card-3d text-left"
                     data-3d-tilt data-max-tilt="5">
                    <div class="glare-sheen"></div>
                    
                    <!-- Hub Tabs -->
                    <div class="bg-slate-100/90 dark:bg-[#0c1220] p-1.5 border-b border-slate-200 dark:border-slate-800 flex items-center gap-1.5">
                        <button type="button" 
                                id="tab-btn-quote"
                                onclick="switchHeroTab('quote')"
                                class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all duration-200 flex items-center justify-center gap-1.5 bg-white dark:bg-[#1a233b] text-[#214fe0] dark:text-[#60a5fa] shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Instant Quotation</span>
                        </button>
                        <button type="button" 
                                id="tab-btn-fleet"
                                onclick="switchHeroTab('fleet')"
                                class="flex-1 py-2 px-3 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all duration-200 flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            <span>Browse Fleet</span>
                        </button>
                        <button type="button" 
                                id="tab-btn-indent"
                                onclick="switchHeroTab('indent')"
                                class="flex-1 py-2 px-3 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all duration-200 flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Volume Indent</span>
                        </button>
                    </div>

                    <!-- Tab Panel 1: Instant Quotation -->
                    <div id="panel-quote" class="p-4 sm:p-5 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                                    1. Category
                                </label>
                                <select id="hero-category-select" 
                                        class="w-full bg-slate-50 dark:bg-[#161f38] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-[#214fe0] focus:outline-none">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                                    2. Specification
                                </label>
                                <select id="hero-spec-select"
                                        class="w-full bg-slate-50 dark:bg-[#161f38] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-[#214fe0] focus:outline-none">
                                    <option value="all">Standard Commercial Spec</option>
                                    <option value="downlight">12W–24W Downlight (Citizen COB)</option>
                                    <option value="highbay">50W–150W Industrial Highbay</option>
                                    <option value="strip">24V Constant Voltage Strip</option>
                                    <option value="track">Tracklights &amp; Spotlights</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                                    3. Est. Units
                                </label>
                                <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-slate-50 dark:bg-[#161f38]">
                                    <button type="button" onclick="adjustHeroQty(-10)" class="px-3 py-2 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold">-</button>
                                    <input type="number" id="hero-qty-input" value="50" min="1" step="10" 
                                           class="w-full text-center text-xs font-mono font-bold bg-transparent text-slate-900 dark:text-white focus:outline-none">
                                    <button type="button" onclick="adjustHeroQty(10)" class="px-3 py-2 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold">+</button>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>100% BIR 12% VAT Compliant &bull; Official Serialized SI &amp; DR</span>
                            </div>
                            <button type="button" 
                                    onclick="launchHeroQuote()"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold px-5 py-2.5 rounded-xl text-xs shadow-md transition-all duration-200 transform hover:-translate-y-0.5 active:scale-[0.98]">
                                <span>Launch Quotation Generator</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Tab Panel 2: Browse Fleet -->
                    <div id="panel-fleet" class="p-4 sm:p-5 space-y-3 hidden">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input type="text" id="hero-fleet-search" placeholder="Search catalog items, SKUs, or wattages..."
                                   class="flex-1 bg-slate-50 dark:bg-[#161f38] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-[#214fe0] focus:outline-none">
                            <button type="button" onclick="searchHeroFleet()"
                                    class="bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold px-4 py-2 rounded-xl text-xs transition">
                                Browse Products
                            </button>
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            Over <strong class="font-mono text-slate-800 dark:text-slate-200">{{ $totalProductsCount }}</strong> verified commercial line items in stock.
                        </div>
                    </div>

                    <!-- Tab Panel 3: Volume Indent -->
                    <div id="panel-indent" class="p-4 sm:p-5 space-y-3 hidden">
                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            Submitting a Bill of Quantities (BOQ) for high-rise commercial towers, hotels, or wholesale infrastructure projects? We provide direct indent overseas container volume pricing.
                        </p>
                        <a href="{{ route('customer.quotation-builder') }}" 
                           class="inline-flex items-center gap-2 bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold px-4 py-2 rounded-xl text-xs transition">
                            <span>Submit Bill of Quantities (BOQ)</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Company Stats Strip -->
                <div class="pt-2 grid grid-cols-3 gap-4 border-t border-slate-200 dark:border-slate-800">
                    <div class="text-center lg:text-left">
                        <div class="text-xl sm:text-2xl font-black text-[#214fe0] dark:text-[#60a5fa] font-mono tabular-nums">{{ number_format($totalProductsCount) }}+</div>
                        <div class="text-[11px] font-semibold text-slate-600 dark:text-slate-400">Active Products</div>
                    </div>
                    <div class="text-center lg:text-left">
                        <div class="text-xl sm:text-2xl font-black text-[#214fe0] dark:text-[#60a5fa] font-mono tabular-nums">{{ $categories->count() }}</div>
                        <div class="text-[11px] font-semibold text-slate-600 dark:text-slate-400">Categories</div>
                    </div>
                    <div class="text-center lg:text-left">
                        <div class="text-xl sm:text-2xl font-black text-[#214fe0] dark:text-[#60a5fa] font-mono tabular-nums">{{ $yearsInBusiness }} Years</div>
                        <div class="text-[11px] font-semibold text-slate-600 dark:text-slate-400">Industry Direct</div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Interactive 3D Photonic Light Bulb Stage (Lighting Engineering Core) -->
            <div id="hero-stage-container" class="lg:col-span-5 relative flex flex-col items-center justify-center w-full px-2 sm:px-0">
                <!-- Dynamic Backlight Halo (Reacts to Kelvin CCT and ON/OFF Power) -->
                <div id="luminaire-ambient-halo" class="w-72 h-72 sm:w-96 sm:h-96 lg:w-[440px] lg:h-[440px] rounded-full absolute pointer-events-none -z-0 blur-3xl transition-all duration-700 opacity-80"
                     style="background: radial-gradient(circle, rgba(245, 158, 11, 0.45) 0%, rgba(37, 99, 235, 0.15) 50%, transparent 75%);"></div>

                <!-- Clean Mobile Chip Strip: eliminates collisions and overlaps on small viewports (<640px) -->
                <div class="flex sm:hidden items-center justify-center flex-wrap gap-1.5 mb-3 w-full px-2">
                    <span class="inline-flex items-center gap-1 bg-gradient-to-r from-amber-500 to-amber-600 text-white font-extrabold text-[10px] px-2.5 py-0.5 rounded-full shadow-sm border border-amber-300/30 whitespace-nowrap">
                        <i data-lucide="star" class="w-2.5 h-2.5 text-amber-100"></i>
                        <span>20% OFF Volume</span>
                    </span>
                    <span class="inline-flex items-center gap-1 bg-white/95 dark:bg-[#0c1220]/95 backdrop-blur border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white px-2.5 py-0.5 rounded-full shadow-sm text-[10px] font-bold whitespace-nowrap">
                        <i data-lucide="file-check-2" class="w-2.5 h-2.5 text-blue-500"></i>
                        <span>12% BIR VAT</span>
                    </span>
                    <span class="inline-flex items-center gap-1 bg-white/95 dark:bg-[#0c1220]/95 backdrop-blur border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white px-2.5 py-0.5 rounded-full shadow-sm text-[10px] font-bold whitespace-nowrap">
                        <i data-lucide="truck" class="w-2.5 h-2.5 text-emerald-500"></i>
                        <span>Free Freight &ge; &#8369;20k</span>
                    </span>
                </div>

                <!-- 3D Spatial Perspective Stage -->
                <div id="hero-3d-stage" 
                     class="relative w-full max-w-[300px] xs:max-w-[340px] sm:max-w-[420px] aspect-square rounded-full border border-slate-700/50 dark:border-slate-800/80 flex items-center justify-center bg-gradient-to-b from-slate-900/60 via-[#0b1120]/80 to-[#060913]/95 backdrop-blur-md shadow-2xl select-none"
                     style="transform-style: preserve-3d; will-change: transform;">
                    
                    <div class="glare-sheen" style="transition: opacity 0.3s ease;"></div>

                    <!-- Subtle Studio Vignette Glow -->
                    <div class="absolute inset-0 rounded-full bg-radial from-blue-500/5 via-transparent to-black/40 pointer-events-none"></div>

                    <!-- Three.js 3D WebGL Light Bulb Canvas Container -->
                    <div class="relative z-10 w-full h-full flex items-center justify-center pointer-events-none">
                        <canvas id="luminaire-3d-canvas" class="w-full h-full cursor-grab active:cursor-grabbing rounded-full pointer-events-auto" style="touch-action: none; width: 100%; height: 100%;"></canvas>
                        
                        <!-- Center Hotspot Click-to-Toggle Overlay (Clean non-shifting hitbox) -->
                        <div id="luminaire-center-toggle" 
                             class="absolute w-32 h-32 sm:w-36 sm:h-36 rounded-full cursor-pointer z-20 flex items-center justify-center pointer-events-auto active:scale-95 transition-transform"
                             title="Click to Toggle Citizen COB LED ON / OFF">
                            <span class="sr-only">Toggle Citizen COB LED</span>
                        </div>

                        <!-- Technical Specification Tag (Docked Cleanly at Stage Bottom with Zero Overlap) -->
                        <div id="luminaire-tech-tag" class="absolute bottom-2.5 sm:bottom-4 inset-x-0 mx-auto w-fit bg-slate-900/90 dark:bg-[#0c1220]/95 text-white font-mono text-[8.5px] sm:text-[10px] font-black uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-blue-500/40 backdrop-blur shadow-md flex items-center gap-1.5 transition-all duration-300 pointer-events-none z-20">
                            <span id="luminaire-status-dot" class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span id="luminaire-status-text">CITIZEN COB LED • CLU048 • 3000K • CRI 97+</span>
                        </div>
                    </div>

                    <!-- Floating Badge 1: 20% OFF Contractor Volume (Desktop Only) -->
                    <div id="hero-badge-discount"
                         class="hidden sm:flex absolute -top-4 right-4 bg-gradient-to-r from-amber-500 to-amber-600 text-white font-extrabold text-xs px-3.5 py-1.5 rounded-full shadow-xl border-2 border-amber-300/40 items-center gap-1.5 z-30 pointer-events-auto whitespace-nowrap transition-shadow duration-300 hover:shadow-amber-500/30"
                         style="will-change: transform;">
                        <i data-lucide="star" class="w-3.5 h-3.5 text-amber-100"></i>
                        <span>20% OFF Contractor Volume</span>
                    </div>

                    <!-- Floating Badge 2: 12% BIR VAT Invoicing (Desktop Only) -->
                    <div id="hero-badge-vat"
                         class="hidden sm:flex absolute -left-4 top-1/4 bg-white/95 dark:bg-[#0c1220]/95 backdrop-blur border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white px-3 py-1.5 rounded-xl shadow-lg text-[11px] font-bold items-center gap-1.5 z-30 pointer-events-auto whitespace-nowrap transition-shadow duration-300 hover:shadow-blue-500/20"
                         style="will-change: transform;">
                        <i data-lucide="file-check-2" class="w-3.5 h-3.5 text-blue-500"></i>
                        <span>12% BIR VAT Invoicing</span>
                    </div>

                    <!-- Floating Badge 3: Free Freight ≥ ₱20,000 (Desktop Only) -->
                    <div id="hero-badge-freight"
                         class="hidden sm:flex absolute -bottom-3 right-2 bg-white/95 dark:bg-[#0c1220]/95 backdrop-blur border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white px-3.5 py-1.5 rounded-full shadow-lg text-[11px] font-bold items-center gap-1.5 z-30 pointer-events-auto whitespace-nowrap transition-shadow duration-300 hover:shadow-emerald-500/20"
                         style="will-change: transform;">
                        <i data-lucide="truck" class="w-3.5 h-3.5 text-emerald-500"></i>
                        <span>Free Freight &ge; &#8369; 20,000</span>
                    </div>
                </div>

                <!-- Tactile Industrial 3D Lighting Control Deck ("ON / OFF & Citizen COB CCT Selection") -->
                <div class="mt-4 sm:mt-6 w-full max-w-[320px] xs:max-w-[360px] sm:max-w-[440px] bg-slate-900/90 dark:bg-[#0c1427]/95 border border-blue-500/30 rounded-2xl p-1.5 sm:p-2.5 shadow-xl backdrop-blur-md flex items-center justify-between gap-1 sm:gap-2 z-20">
                    <!-- Power Switch (ON / OFF) -->
                    <button type="button" id="luminaire-power-btn" onclick="window.toggleLuminairePower()"
                            class="flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-1.5 rounded-xl text-[10px] sm:text-xs font-bold transition-all duration-200 bg-emerald-500 text-white hover:bg-emerald-600 shadow-md shadow-emerald-500/20 active:scale-95 cursor-pointer shrink-0 whitespace-nowrap">
                        <i data-lucide="power" class="w-3 sm:w-3.5 h-3 sm:h-3.5"></i>
                        <span id="luminaire-power-label"><span class="hidden sm:inline">COB: </span>ON</span>
                    </button>

                    <!-- Kelvin CCT Selector (3000K / 3500K / 4000K / 5000K) -->
                    <div class="flex items-center bg-slate-800/90 rounded-xl p-0.5 border border-slate-700/60 shrink-0">
                        <button type="button" onclick="window.setLuminaireCCT('3000K', this)" 
                                class="cct-btn px-1.5 sm:px-2 py-1 rounded-lg text-[9px] sm:text-[11px] font-black transition-all duration-200 bg-amber-500 text-white shadow-sm cursor-pointer"
                                title="3000K Warm White (Architectural Amber)">
                            3000K
                        </button>
                        <button type="button" onclick="window.setLuminaireCCT('3500K', this)" 
                                class="cct-btn px-1.5 sm:px-2 py-1 rounded-lg text-[9px] sm:text-[11px] font-black transition-all duration-200 text-slate-300 hover:text-white cursor-pointer"
                                title="3500K Neutral Warm (Hospitality Sunset)">
                            3500K
                        </button>
                        <button type="button" onclick="window.setLuminaireCCT('4000K', this)" 
                                class="cct-btn px-1.5 sm:px-2 py-1 rounded-lg text-[9px] sm:text-[11px] font-black transition-all duration-200 text-slate-300 hover:text-white cursor-pointer"
                                title="4000K Natural White (Commercial Crisp)">
                            4000K
                        </button>
                        <button type="button" onclick="window.setLuminaireCCT('5000K', this)" 
                                class="cct-btn px-1.5 sm:px-2 py-1 rounded-lg text-[9px] sm:text-[11px] font-black transition-all duration-200 text-slate-300 hover:text-white cursor-pointer"
                                title="5000K Cool White (Industrial Daylight)">
                            5000K
                        </button>
                    </div>

                    <!-- Cool Interactive Surge / Pulse Animation Button -->
                    <button type="button" onclick="window.triggerLuminaireSurge()"
                            class="flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-1.5 rounded-xl text-[10px] sm:text-xs font-semibold text-blue-300 hover:text-white bg-blue-500/15 hover:bg-blue-500/30 border border-blue-500/30 transition-all active:scale-95 cursor-pointer shrink-0 whitespace-nowrap"
                            title="Trigger High-Voltage Photonic Ignition Animation">
                        <i data-lucide="zap" class="w-3 sm:w-3.5 h-3 sm:h-3.5 text-amber-400"></i>
                        <span>Surge</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- Hero Bottom Trust Bar (Antixor 4-Point Icon Strip) -->
        <div class="mt-10 sm:mt-14 pt-6 sm:pt-8 border-t border-slate-200 dark:border-slate-800/80 grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 text-slate-700 dark:text-slate-300">
            <div class="flex items-center gap-2.5 sm:gap-3 p-2 sm:p-0 rounded-xl bg-slate-50/70 dark:bg-slate-900/50 sm:bg-transparent dark:sm:bg-transparent border border-slate-200/50 dark:border-slate-800/50 sm:border-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 dark:bg-blue-950/70 text-[#214fe0] dark:text-[#60a5fa] flex items-center justify-center shrink-0 border border-blue-100 dark:border-blue-900/60">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Direct Factory Importer</div>
                    <div class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 truncate">Zero distributor markups</div>
                </div>
            </div>

            <div class="flex items-center gap-2.5 sm:gap-3 p-2 sm:p-0 rounded-xl bg-slate-50/70 dark:bg-slate-900/50 sm:bg-transparent dark:sm:bg-transparent border border-slate-200/50 dark:border-slate-800/50 sm:border-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-100 dark:border-emerald-900/60">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate">BIR 12% VAT Compliant</div>
                    <div class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 truncate">Official serialized SI &amp; DR</div>
                </div>
            </div>

            <div class="flex items-center gap-2.5 sm:gap-3 p-2 sm:p-0 rounded-xl bg-slate-50/70 dark:bg-slate-900/50 sm:bg-transparent dark:sm:bg-transparent border border-slate-200/50 dark:border-slate-800/50 sm:border-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-purple-50 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 border border-purple-100 dark:border-purple-900/60">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate">24/7 Digital Estimation</div>
                    <div class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 truncate">Instant PDF download</div>
                </div>
            </div>

            <div class="flex items-center gap-2.5 sm:gap-3 p-2 sm:p-0 rounded-xl bg-slate-50/70 dark:bg-slate-900/50 sm:bg-transparent dark:sm:bg-transparent border border-slate-200/50 dark:border-slate-800/50 sm:border-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-amber-50 dark:bg-amber-950/70 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-100 dark:border-amber-900/60">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Jobsite Delivery Fleet</div>
                    <div class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 truncate">Coordinated site dispatch</div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- ==========================================================================
     SECTION 2: WHY CHOOSE HUENICS? (Antixor 4-Card Feature Row)
     ========================================================================== -->
<section class="py-16 bg-slate-50 dark:bg-[#070b14] border-b border-slate-200 dark:border-slate-800/80 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="text-xs font-black uppercase tracking-widest text-[#214fe0] dark:text-[#60a5fa]">
                Engineering Advantage
            </span>
            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight mt-1.5">
                Why Top Commercial Contractors Partner with Huenics
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-2">
                We combine direct manufacturer pricing with rigorous Japanese optoelectronics and official Philippine corporate tax documentation.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 stagger-cards">
            <!-- Card 1: Direct Wholesale Volume -->
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 card-interactive"
                 data-3d-tilt data-max-tilt="8">
                <div class="glare-sheen"></div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/70 text-[#214fe0] dark:text-[#60a5fa] flex items-center justify-center mb-5 border border-blue-100 dark:border-blue-900/60">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Direct Wholesale Pricing</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Zero middlemen or secondary broker markups. We import directly from verified manufacturing lines to give contractors competitive BOQ margins.
                </p>
            </div>

            <!-- Card 2: Citizen Japan COB -->
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 card-interactive"
                 data-3d-tilt data-max-tilt="8">
                <div class="glare-sheen"></div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/70 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-5 border border-amber-100 dark:border-amber-900/60">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Citizen Japan C.O.B Fidelity</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Powered by genuine Citizen Japan optoelectronic arrays. Ra &ge; 90 high color rendering index, MacAdam ellipse binning, and 50,000-hour L70 life.
                </p>
            </div>

            <!-- Card 3: Instant Automated Estimation -->
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 card-interactive"
                 data-3d-tilt data-max-tilt="8">
                <div class="glare-sheen"></div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-5 border border-emerald-100 dark:border-emerald-900/60">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Instant 60s Quotations</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Select specifications, customize volume tiers, and download an official itemized PDF quotation complete with BIR VAT calculations immediately.
                </p>
            </div>

            <!-- Card 4: Lighting Clinic & Technical Lab -->
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 card-interactive"
                 data-3d-tilt data-max-tilt="8">
                <div class="glare-sheen"></div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-5 border border-indigo-100 dark:border-indigo-900/60">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Lighting Clinic Support</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    On-staff electrical engineers for component repair, custom indent orders, Dialux photometric lux planning, and legacy fixture retrofitting.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     SECTION 3: POPULAR PRODUCTS & COMMERCIAL FLEET SHOWCASE (Antixor Fleet Grid)
     ========================================================================== -->
<section id="popular-products-fleet" class="py-16 bg-white dark:bg-[#0a0e1a] border-b border-slate-200 dark:border-slate-800/80 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-8">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">
                    Commercial Catalog
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                    Popular Products &amp; Lighting Fleet
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                    High-efficiency commercial downlights, Citizen Japan C.O.B fixtures, linear profiles, and certified drivers ready for jobsite dispatch.
                </p>
            </div>
            <a href="{{ route('customer.products') }}" 
               class="inline-flex items-center gap-1.5 text-[#214fe0] dark:text-[#60a5fa] hover:underline font-bold text-xs sm:text-sm group shrink-0">
                <span>View All Products ({{ $totalProductsCount }})</span>
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        <!-- Filter Pill Tabs (Interactive JS Filter) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-3 mb-8 no-scrollbar pr-8 sm:pr-0 select-none" style="scrollbar-width: none; -ms-overflow-style: none; -webkit-overflow-scrolling: touch;">
            <button type="button" 
                    onclick="filterFleetCategory('all', this)"
                    class="fleet-filter-btn px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-200 bg-[#214fe0] text-white shadow-md">
                All Products
            </button>
            @foreach($categories->take(6) as $cat)
                <button type="button" 
                        onclick="filterFleetCategory('{{ $cat }}', this)"
                        class="fleet-filter-btn px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all duration-200 bg-slate-100 dark:bg-[#151f38] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700/60">
                    {{ $cat }}
                </button>
            @endforeach
        </div>

        <!-- 4-Column Fleet Grid with 3D Tilt & Specular Glare -->
        <div id="fleet-grid-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($featuredProducts as $product)
            <div class="fleet-product-card bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 hover:border-[#214fe0] dark:hover:border-[#3b82f6] rounded-2xl p-4 shadow-sm hover:shadow-xl dark:hover:shadow-[0_12px_30px_rgba(33,79,224,0.2)] card-interactive flex flex-col justify-between group"
                 data-category="{{ $product->category }}"
                 data-3d-tilt data-max-tilt="10">
                
                <div class="glare-sheen"></div>

                <div>
                    <!-- Product Image Container -->
                    <div class="relative w-full h-44 bg-slate-100/90 dark:bg-[#161f38]/90 rounded-xl overflow-hidden mb-3.5 flex items-center justify-center border border-slate-200/70 dark:border-slate-800/80 group-hover:border-blue-300 dark:group-hover:border-blue-600 transition-colors">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->canonical_name }}" loading="lazy" class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-300 ease-out">
                        @else
                            <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 w-full h-full p-4 relative select-none">
                                <div class="relative w-16 h-16 rounded-2xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/50 flex items-center justify-center mb-1.5 group-hover:scale-110 group-hover:bg-blue-100/80 dark:group-hover:bg-blue-900/60 transition-all duration-300 shadow-sm">
                                    <svg class="w-8 h-8 text-[#214fe0] dark:text-[#60a5fa] group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                                <span class="text-[10px] font-extrabold tracking-wider uppercase font-mono text-slate-500 dark:text-slate-400 group-hover:text-[#214fe0] dark:group-hover:text-[#60a5fa] transition-colors">
                                    {{ $product->category ?: 'Lighting' }}
                                </span>
                            </div>
                        @endif
                        <div class="absolute top-2.5 inset-x-2.5 flex items-center justify-between gap-2 z-10 pointer-events-none">
                            <span class="hisi-pill-badge shadow-sm shrink min-w-0 max-w-[62%] !inline-flex items-center pointer-events-auto" title="{{ strtoupper($product->category ?: 'Lighting') }}">
                                <span class="truncate">{{ strtoupper($product->category ?: 'Lighting') }}</span>
                            </span>
                            <span class="text-[10px] font-mono text-slate-600 dark:text-slate-300 font-bold bg-white/95 dark:bg-[#0c1220]/95 backdrop-blur px-2 py-0.5 rounded shadow-sm border border-slate-200/60 dark:border-slate-700/60 shrink-0 max-w-[38%] truncate pointer-events-auto" title="{{ $product->sku ?: $product->product_code ?: 'SKU-00' }}">
                                {{ $product->sku ?: $product->product_code ?: 'SKU-00' }}
                            </span>
                        </div>
                    </div>

                    <!-- Title -->
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#214fe0] dark:group-hover:text-[#60a5fa] transition line-clamp-1 mb-1"
                        title="{{ $product->canonical_name }}">
                        {{ $product->canonical_name }}
                    </h3>

                    <!-- Specs Chips -->
                    <div class="flex flex-wrap gap-1.5 my-2">
                        <span class="text-[10px] font-mono font-semibold bg-slate-100 dark:bg-[#161f38] text-slate-700 dark:text-slate-300 px-2 py-0.5 rounded">
                            {{ $product->unit_of_measurement ?: 'PC' }}
                        </span>
                        <span class="text-[10px] font-mono font-semibold bg-blue-50 dark:bg-blue-950/60 text-[#214fe0] dark:text-[#60a5fa] px-2 py-0.5 rounded">
                            Citizen Japan C.O.B
                        </span>
                    </div>
                </div>

                <!-- Add to Quote Control -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center border border-slate-300 dark:border-slate-700 rounded-lg overflow-hidden bg-slate-50 dark:bg-[#161f38] shrink-0">
                            <button type="button" 
                                    onclick="adjustCardQty('fleet-qty-{{ $product->id }}', -1)"
                                    class="px-2 py-1 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold">-</button>
                            <input type="number" 
                                   id="fleet-qty-{{ $product->id }}" 
                                   value="1" 
                                   min="1" 
                                   step="1" 
                                   class="w-10 text-center text-xs font-mono font-bold py-1 bg-transparent text-slate-900 dark:text-white focus:outline-none">
                            <button type="button" 
                                    onclick="adjustCardQty('fleet-qty-{{ $product->id }}', 1)"
                                    class="px-2 py-1 text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold">+</button>
                        </div>

                        <button type="button" 
                                onclick="addProductToQuote({{ json_encode($product) }}, document.getElementById('fleet-qty-{{ $product->id }}').value)"
                                class="flex-1 bg-[#214fe0] hover:bg-[#1a42be] text-white text-xs font-bold py-2 px-2.5 rounded-lg transition-all duration-200 flex items-center justify-center gap-1 shadow-sm active:scale-95">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span>Add to Quote</span>
                        </button>
                    </div>
                </div>

            </div>
            @empty
            <div class="col-span-full text-center py-12 text-slate-500">
                No featured products found.
            </div>
            @endforelse
        </div>

        <!-- Empty Filter State Container -->
        <div id="fleet-empty-state" class="hidden text-center py-12 bg-slate-50 dark:bg-[#111827] rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 mt-4">
            <svg class="w-10 h-10 text-slate-400 mb-2 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No products match this category filter.</p>
            <button type="button" onclick="filterFleetCategory('all', document.querySelector('.fleet-filter-btn'))" class="mt-3 text-xs font-bold text-[#214fe0] dark:text-[#60a5fa] hover:underline">
                Reset to All Products
            </button>
        </div>

    </div>
</section>

<!-- ==========================================================================
     SECTION 4: SPECIAL CONTRACTOR DISCOUNT BANNER (Antixor Special Offer Banner)
     ========================================================================== -->
<section class="py-14 bg-gradient-to-r from-[#152e80] via-[#1a42be] to-[#0d1d52] text-white relative overflow-hidden">
    <!-- Diagonal Geometric Accents -->
    <div class="absolute inset-0 pointer-events-none opacity-10" 
         style="background: repeating-linear-gradient(45deg, #ffffff, #ffffff 3px, transparent 3px, transparent 15px);"></div>
    <div class="absolute -right-16 -top-16 w-80 h-80 bg-blue-400/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
            <div class="space-y-3 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 bg-amber-400/20 border border-amber-300/30 text-amber-300 px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <span>Contractor Volume Program</span>
                </div>
                <h2 class="text-2xl sm:text-4xl font-black tracking-tight">
                    Get Up to 20% OFF Your First Commercial Project BOQ
                </h2>
                <p class="text-xs sm:text-sm text-blue-100 max-w-2xl font-normal leading-relaxed">
                    Submitting a Bill of Quantities for commercial towers, hotels, or retail rollouts? Unlock direct indent overseas pricing, dedicated technical account handling, and prioritized jobsite dispatch.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3.5 shrink-0">
                <div class="bg-white/10 backdrop-blur border border-white/20 px-4 py-2.5 rounded-xl font-mono text-xs font-bold text-amber-300">
                    CODE: HUENICS2026
                </div>
                <a href="{{ route('customer.quotation-builder') }}" 
                   class="bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold px-6 py-3 rounded-xl shadow-lg transition text-xs sm:text-sm flex items-center gap-2 transform hover:-translate-y-0.5 active:scale-95">
                    <span>Assemble Project BOQ</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     SECTION 5: HOW IT WORKS (Antixor 3-Step Numbered Flow)
     ========================================================================== -->
<section class="py-16 bg-white dark:bg-[#070b14] border-b border-slate-200 dark:border-slate-800/80 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="text-xs font-black uppercase tracking-widest text-[#214fe0] dark:text-[#60a5fa]">
                Frictionless Procurement
            </span>
            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight mt-1.5">
                How Huenics Streamlines Your Order
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-2">
                From initial fixture specification to official BIR delivery receipt at your construction gate in 3 straightforward steps.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <!-- Step 1 -->
            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-7 relative card-interactive"
                 data-3d-tilt data-max-tilt="6">
                <div class="glare-sheen"></div>
                <div class="text-4xl font-black text-blue-200 dark:text-slate-800 font-mono mb-4">01</div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/70 text-[#214fe0] dark:text-[#60a5fa] flex items-center justify-center mb-4 border border-blue-100 dark:border-blue-900/60">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Select Hardware &amp; Specs</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Pick your fixture types, Citizen C.O.B wattages, beam angles, and required quantities directly from our live catalog.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-7 relative card-interactive"
                 data-3d-tilt data-max-tilt="6">
                <div class="glare-sheen"></div>
                <div class="text-4xl font-black text-blue-200 dark:text-slate-800 font-mono mb-4">02</div>
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-4 border border-purple-100 dark:border-purple-900/60">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Instant PDF Estimation</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Our quotation engine calculates itemized subtotals and 12% VAT, generating an exportable PDF ready for client sign-off.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-7 relative card-interactive"
                 data-3d-tilt data-max-tilt="6">
                <div class="glare-sheen"></div>
                <div class="text-4xl font-black text-blue-200 dark:text-slate-800 font-mono mb-4">03</div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4 border border-emerald-100 dark:border-emerald-900/60">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Official SI &amp; Jobsite Dispatch</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Our sales desk issues official serialized BIR Sales Invoices and coordinates delivery straight to your construction site receiver.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     SECTION 6: COMMERCIAL ENGINEERING SERVICES (Antixor 3 Visual Cards)
     ========================================================================== -->
<section class="py-16 bg-slate-50 dark:bg-[#0a0e1a] border-b border-slate-200 dark:border-slate-800/80 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">
                    Specialized Capabilities
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                    Our Engineering Services
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                    Beyond wholesale supply: hardware repair diagnostics, custom indent overseas manufacturing, and photometric simulation.
                </p>
            </div>
            <a href="{{ route('customer.about') }}" class="text-xs sm:text-sm font-bold text-[#214fe0] dark:text-[#60a5fa] hover:underline flex items-center gap-1">
                <span>Explore Technical Dept</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Service 1: Lighting Clinic & Enercon -->
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden card-interactive group flex flex-col justify-between"
                 data-3d-tilt data-max-tilt="8">
                <div class="glare-sheen"></div>
                <div class="p-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/70 text-[#214fe0] dark:text-[#60a5fa] flex items-center justify-center mb-4 border border-blue-100 dark:border-blue-900/60">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Lighting Clinic &amp; Enercon</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-4">
                        Component-level driver testing, thermal management diagnostics, and Citizen C.O.B engine retrofitting for legacy architectural luminaires.
                    </p>
                </div>
                <div class="px-6 py-3.5 bg-slate-50 dark:bg-[#0c1220] border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-[11px] font-bold text-[#214fe0] dark:text-[#60a5fa]">Diagnostics &amp; Repair</span>
                    <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/60 text-[#214fe0] dark:text-[#60a5fa] flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </div>
                </div>
            </div>

            <!-- Service 2: Custom Indent Sourcing -->
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden card-interactive group flex flex-col justify-between"
                 data-3d-tilt data-max-tilt="8">
                <div class="glare-sheen"></div>
                <div class="p-6">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/70 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-4 border border-amber-100 dark:border-amber-900/60">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Custom Indent Sourcing</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-4">
                        Custom housing fabrication, tailored CCT/CRI requirements, IP67/IP68 submersible fittings, and non-standard industrial voltage driver specs.
                    </p>
                </div>
                <div class="px-6 py-3.5 bg-slate-50 dark:bg-[#0c1220] border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400">Factory Indent</span>
                    <div class="w-7 h-7 rounded-full bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </div>
                </div>
            </div>

            <!-- Service 3: Photometric Simulation & Dialux -->
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden card-interactive group flex flex-col justify-between"
                 data-3d-tilt data-max-tilt="8">
                <div class="glare-sheen"></div>
                <div class="p-6">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4 border border-emerald-100 dark:border-emerald-900/60">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Photometric Simulation</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-4">
                        Dialux lighting calculations, illuminance distribution verification (Lux), and glare ratings (UGR &lt; 19) for electrical consultancy compliance.
                    </p>
                </div>
                <div class="px-6 py-3.5 bg-slate-50 dark:bg-[#0c1220] border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">Dialux Lux Modeling</span>
                    <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     SECTION 8: COMMERCIAL INSTALLATIONS GALLERY (Antixor Fleet Gallery Bento)
     ========================================================================== -->
<section class="py-16 bg-white dark:bg-[#070b14] border-b border-slate-200 dark:border-slate-800/80 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="text-xs font-black uppercase tracking-widest text-[#214fe0] dark:text-[#60a5fa]">
                Deployments
            </span>
            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight mt-1.5">
                Commercial Installations &amp; Engineering References
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-2">
                Trusted across commercial high-rises, flagship retail stores, corporate atriums, and logistics centers across Luzon.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Project 1: High Rise -->
            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 card-interactive"
                 data-3d-tilt data-max-tilt="8">
                <div class="glare-sheen"></div>
                <div class="text-xs font-mono font-bold text-[#214fe0] dark:text-[#60a5fa] mb-1">Taguig City &bull; BGC</div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Commercial Office Tower</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                    1,200+ Citizen C.O.B downlights, low-glare darklight reflectors, and DALI-2 dimmable driver arrays across 28 storeys.
                </p>
                <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-between text-[11px] font-mono text-slate-600 dark:text-slate-400">
                    <span>Downlights &amp; Linear</span>
                    <span class="font-bold text-emerald-500">100% On-Time</span>
                </div>
            </div>

            <!-- Project 2: Retail Boutique -->
            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 card-interactive"
                 data-3d-tilt data-max-tilt="8">
                <div class="glare-sheen"></div>
                <div class="text-xs font-mono font-bold text-[#214fe0] dark:text-[#60a5fa] mb-1">Makati City</div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Luxury Fashion Boutique</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                    High-CRI Ra &ge; 95 tracklight spotlights, 3000K warm CCT, and precision honeycomb louvers for exact fabric fidelity.
                </p>
                <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-between text-[11px] font-mono text-slate-600 dark:text-slate-400">
                    <span>Ra &ge; 95 Spotlight</span>
                    <span class="font-bold text-emerald-500">Completed</span>
                </div>
            </div>

            <!-- Project 3: Industrial Logistics -->
            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 card-interactive"
                 data-3d-tilt data-max-tilt="8">
                <div class="glare-sheen"></div>
                <div class="text-xs font-mono font-bold text-[#214fe0] dark:text-[#60a5fa] mb-1">Laguna Technopark</div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Cold Storage &amp; Warehouse</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                    150W IP65 highbay luminaires, industrial surge-protected drivers (6kV), and high-bay microwave occupancy sensors.
                </p>
                <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-between text-[11px] font-mono text-slate-600 dark:text-slate-400">
                    <span>150W Highbay</span>
                    <span class="font-bold text-emerald-500">Active</span>
                </div>
            </div>

            <!-- Project 4: Hospitality Lobby -->
            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 card-interactive"
                 data-3d-tilt data-max-tilt="8">
                <div class="glare-sheen"></div>
                <div class="text-xs font-mono font-bold text-[#214fe0] dark:text-[#60a5fa] mb-1">Pasay City</div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Hospitality Atrium &amp; Lounge</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                    Architectural recessed cove linear profiles, 24V constant voltage flicker-free dimming, and seamless corner joiners.
                </p>
                <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-between text-[11px] font-mono text-slate-600 dark:text-slate-400">
                    <span>24V Linear Cove</span>
                    <span class="font-bold text-emerald-500">Completed</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     SECTION 9: DIGITAL QUOTATION APP SHOWCASE (Antixor Mobile Mockup Section)
     ========================================================================== -->
<section class="py-16 bg-slate-50 dark:bg-[#070b14] border-b border-slate-200 dark:border-slate-800/80 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center gap-2 bg-blue-50 dark:bg-blue-950/70 border border-blue-200 dark:border-blue-800/60 text-[#214fe0] dark:text-[#60a5fa] px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>On-Site Procurement Power</span>
                </div>

                <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                    Generate Commercial Quotations Anywhere, Right from the Jobsite.
                </h2>

                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    No waiting for paper estimates or delayed email exchanges. Project engineers can select fixtures, verify stock availability, and produce an official-ready PDF proposal with 12% VAT calculations on their smartphone or laptop in real time.
                </p>

                <div class="space-y-3 pt-2">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/60 text-[#214fe0] dark:text-[#60a5fa] flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200">Instant PDF download with serialized reference numbers</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/60 text-[#214fe0] dark:text-[#60a5fa] flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200">100% BIR 12% VAT itemized subtotal calculations</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/60 text-[#214fe0] dark:text-[#60a5fa] flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200">One-click handover to our sales desk for formal billing &amp; dispatch</span>
                    </div>
                </div>

                <div class="pt-4">
                    <a href="{{ route('customer.quotation-builder') }}" 
                       class="inline-flex items-center gap-2.5 bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold px-6 py-3.5 rounded-xl shadow-lg transition text-xs sm:text-sm">
                        <span>Open Quotation Builder App</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

            <!-- Stylized Mobile Mockup Frame (Antixor Mobile Frame) -->
            <div class="lg:col-span-6 flex justify-center">
                <div class="w-full max-w-xs sm:max-w-sm bg-slate-900 rounded-[2.5rem] p-3.5 border-4 border-slate-700 shadow-2xl card-3d"
                     data-3d-tilt data-max-tilt="8">
                    <div class="glare-sheen"></div>
                    
                    <!-- Phone Speaker & Camera Notch -->
                    <div class="w-28 h-4 bg-slate-800 rounded-full mx-auto mb-3"></div>

                    <!-- Screen Content -->
                    <div class="bg-white dark:bg-[#0c1220] rounded-[2rem] p-4 text-slate-900 dark:text-white space-y-3.5 overflow-hidden">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                            <div class="text-[11px] font-black uppercase text-[#214fe0]">Huenics Instant Quote</div>
                            <span class="text-[10px] font-mono bg-emerald-100 dark:bg-emerald-950 text-emerald-600 px-2 py-0.5 rounded font-bold">ONLINE</span>
                        </div>

                        <div class="space-y-2">
                            <div class="text-[10px] uppercase font-bold text-slate-400">Sample Line Items:</div>
                            <div class="bg-slate-50 dark:bg-[#161f38] p-2.5 rounded-xl text-xs space-y-1 border border-slate-100 dark:border-slate-800">
                                <div class="flex justify-between font-bold">
                                    <span class="truncate pr-1">18W Citizen COB Downlight</span>
                                    <span class="font-mono">x 50</span>
                                </div>
                                <div class="flex justify-between text-[11px] text-slate-500">
                                    <span>3000K &bull; Ra 90 &bull; Dimmable</span>
                                    <span class="font-mono">&#8369; 32,500.00</span>
                                </div>
                            </div>
                            <div class="bg-slate-50 dark:bg-[#161f38] p-2.5 rounded-xl text-xs space-y-1 border border-slate-100 dark:border-slate-800">
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

                        <div class="pt-2 border-t border-slate-200 dark:border-slate-800 space-y-1 text-xs">
                            <div class="flex justify-between text-slate-500">
                                <span>12% BIR VAT:</span>
                                <span class="font-mono font-bold text-slate-800 dark:text-slate-200">&#8369; 6,060.00</span>
                            </div>
                            <div class="flex justify-between font-black text-sm text-[#214fe0] dark:text-[#60a5fa] pt-1">
                                <span>Est. Total:</span>
                                <span class="font-mono">&#8369; 56,560.00</span>
                            </div>
                        </div>

                        <div class="w-full bg-[#214fe0] text-white text-center py-2.5 rounded-xl font-bold text-xs shadow-md">
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
<section class="py-16 bg-white dark:bg-[#0a0e1a] border-b border-slate-200 dark:border-slate-800/80 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">
                    Engineering Knowledgebase
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                    Technical Resources &amp; Specifications
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                    Engineering articles on power factor, thermal dissipation, and Philippine corporate procurement standards.
                </p>
            </div>
            <a href="{{ route('customer.about') }}" class="text-xs sm:text-sm font-bold text-[#214fe0] dark:text-[#60a5fa] hover:underline flex items-center gap-1">
                <span>All Technical Guides</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Article 1: Power Factor -->
            <article class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 card-interactive flex flex-col justify-between"
                     data-3d-tilt data-max-tilt="6">
                <div class="glare-sheen"></div>
                <div>
                    <div class="flex items-center justify-between text-xs mb-3">
                        <span class="font-bold text-[#214fe0] dark:text-[#60a5fa] uppercase tracking-wider text-[10px]">Electrical Engineering</span>
                        <span class="text-slate-400 text-[11px]">5 min read</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                        LED Driver Topologies: Reducing THD &amp; Maintaining PF &gt; 0.95 in Commercial Risers
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-4">
                        How high harmonic distortion degrades facility transformers and how certified constant-current drivers ensure electrical code compliance.
                    </p>
                </div>
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-[11px] font-bold text-[#214fe0] dark:text-[#60a5fa] flex items-center gap-1">
                    <span>Read Technical Paper</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </div>
            </article>

            <!-- Article 2: Citizen COB Thermal -->
            <article class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 card-interactive flex flex-col justify-between"
                     data-3d-tilt data-max-tilt="6">
                <div class="glare-sheen"></div>
                <div>
                    <div class="flex items-center justify-between text-xs mb-3">
                        <span class="font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider text-[10px]">Optoelectronics</span>
                        <span class="text-slate-400 text-[11px]">4 min read</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                        Citizen Japan C.O.B Thermal Dissipation: Heatsink Design for 50,000h L70 Lifespan
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-4">
                        Why junction temperature (Tj &lt; 85&deg;C) is the single most critical factor in preventing premature lumen degradation and color shift.
                    </p>
                </div>
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-[11px] font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1">
                    <span>Read Technical Paper</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </div>
            </article>

            <!-- Article 3: BIR Tax & Invoicing -->
            <article class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 card-interactive flex flex-col justify-between"
                     data-3d-tilt data-max-tilt="6">
                <div class="glare-sheen"></div>
                <div>
                    <div class="flex items-center justify-between text-xs mb-3">
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider text-[10px]">Tax &amp; Procurement</span>
                        <span class="text-slate-400 text-[11px]">6 min read</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                        The Contractor's Guide to BIR Sales Invoices, Form 2307 Withholding, and Jobsite DRs
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-4">
                        Navigating auditable creditable withholding tax (CWT) and official receipt compliance for corporate and government construction projects.
                    </p>
                </div>
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                    <span>Read Technical Paper</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- ==========================================================================
     SECTION 11: FINAL CALL TO ACTION (Antixor High-Impact Closing Banner)
     ========================================================================== -->
<section class="py-16 bg-[#214fe0] dark:bg-gradient-to-r dark:from-[#152e80] dark:to-[#0d1d52] text-white text-center relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none opacity-10" 
         style="background: repeating-linear-gradient(45deg, #ffffff, #ffffff 3px, transparent 3px, transparent 15px);"></div>
    
    <div class="max-w-4xl mx-auto px-4 relative z-10 space-y-6">
        <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider text-blue-100">
            <span>Direct Commercial Sourcing</span>
        </div>

        <h2 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
            Ready to Build Your Commercial Quotation?
        </h2>

        <p class="text-sm sm:text-base text-blue-100 max-w-2xl mx-auto font-normal leading-relaxed">
            Assemble your Bill of Quantities (BOQ) online in minutes or talk directly with our Mandaluyong technical sales desk for project indent pricing.
        </p>

        <div class="pt-4 flex flex-col sm:flex-row justify-center items-center gap-4">
            <a href="{{ route('customer.quotation-builder') }}" 
               class="w-full sm:w-auto bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold px-8 py-4 rounded-xl shadow-xl transition text-sm flex items-center justify-center gap-2 transform hover:-translate-y-0.5 active:scale-95">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Launch Instant Quotation Generator</span>
            </a>

            <a href="{{ route('customer.about') }}" 
               class="w-full sm:w-auto bg-white/10 hover:bg-white/20 border-2 border-white/30 text-white font-bold px-8 py-4 rounded-xl transition text-sm flex items-center justify-center gap-2 transform hover:-translate-y-0.5 active:scale-95">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Contact Engineering Sales Desk</span>
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Tab Switcher for Floating Hero Estimator Hub
    window.switchHeroTab = function(tab) {
        const tabs = ['quote', 'fleet', 'indent'];
        tabs.forEach(t => {
            const btn = document.getElementById('tab-btn-' + t);
            const panel = document.getElementById('panel-' + t);
            if (!btn || !panel) return;

            if (t === tab) {
                btn.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all duration-200 flex items-center justify-center gap-1.5 bg-white dark:bg-[#1a233b] text-[#214fe0] dark:text-[#60a5fa] shadow-sm';
                panel.classList.remove('hidden');
            } else {
                btn.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all duration-200 flex items-center justify-center gap-1.5';
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
        
        let url = '{{ route("customer.quotation-builder") }}';
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
        let url = '{{ route("customer.products") }}';
        if (q) url += '?search=' + encodeURIComponent(q);
        window.location.href = url;
    };

    // Category Filter for Popular Products Fleet Showcase
    window.filterFleetCategory = function(category, buttonEl) {
        document.querySelectorAll('.fleet-filter-btn').forEach(btn => {
            btn.className = 'fleet-filter-btn px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all duration-200 bg-slate-100 dark:bg-[#151f38] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700/60';
        });
        if (buttonEl) {
            buttonEl.className = 'fleet-filter-btn px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-200 bg-[#214fe0] text-white shadow-md';
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

    // Re-initialize 3D physics and icons when view loads
    document.addEventListener('DOMContentLoaded', () => {
        if (window.Huenics3D) Huenics3D.init();
        if (window.lucide) lucide.createIcons();
        if (window.initHuenicsLuminaire3D) initHuenicsLuminaire3D();
    });

    /* =========================================================================
     * HUENICS 3D CITIZEN LED COB ENGINE (Photorealistic Studio WebGL)
     * ========================================================================= */
    (function() {
        let scene, camera, renderer, cobGroup, phosphorMesh, damMesh, ceramicMesh, backplateMesh;
        let bloomSprite, particleSystem, shockwaveRing, corePointLight, ambientLight, keyLight, rimLight;
        let isPowerOn = true;
        let currentCCT = '3000K';
        let currentRotX = 0.20, currentRotY = -0.32;
        let isDragging = false;
        let previousMousePosition = { x: 0, y: 0 };
        let surgeTime = 0;
        let isSurging = false;
        let isVisible = true;
        let thermalPower = 1.0; // 0.0 (off) to 1.0 (full lumen output)
        let flickerCounter = 0;

        const cctProfiles = {
            '3000K': {
                hex: 0xff9e3b,
                emissive: 0xff8c1a,
                halo: 'radial-gradient(circle, rgba(245, 158, 11, 0.45) 0%, rgba(217, 119, 6, 0.18) 45%, rgba(15, 23, 42, 0.05) 75%, transparent 85%)',
                badgeText: 'CITIZEN COB LED • CLU048 • 3000K • CRI 97+',
                name: '3000K Warm White (Architectural Amber)'
            },
            '3500K': {
                hex: 0xffb56c,
                emissive: 0xffa550,
                halo: 'radial-gradient(circle, rgba(251, 146, 60, 0.44) 0%, rgba(234, 88, 12, 0.17) 45%, rgba(15, 23, 42, 0.05) 75%, transparent 85%)',
                badgeText: 'CITIZEN COB LED • CLU048 • 3500K • CRI 97+',
                name: '3500K Neutral Warm (Hospitality Sunset)'
            },
            '4000K': {
                hex: 0xffcaa2,
                emissive: 0xffbf90,
                halo: 'radial-gradient(circle, rgba(254, 215, 170, 0.42) 0%, rgba(245, 158, 11, 0.16) 45%, rgba(15, 23, 42, 0.05) 75%, transparent 85%)',
                badgeText: 'CITIZEN COB LED • CLU048 • 4000K • CRI 95+',
                name: '4000K Natural White (Commercial Crisp)'
            },
            '5000K': {
                hex: 0xfdf5f0,
                emissive: 0xf0e6dc,
                halo: 'radial-gradient(circle, rgba(224, 242, 254, 0.45) 0%, rgba(186, 230, 253, 0.18) 45%, rgba(15, 23, 42, 0.05) 75%, transparent 85%)',
                badgeText: 'CITIZEN COB LED • CLU048 • 5000K • CRI 90+',
                name: '5000K Cool White (Industrial Daylight)'
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

        // Procedural Optical Incandescent Bloom Sprite (Replaces Muddy Brown Ball)
        function createOpticalBloomTexture() {
            const canvas = document.createElement('canvas');
            canvas.width = 128;
            canvas.height = 128;
            const ctx = canvas.getContext('2d');
            if (!ctx) return null;

            const radGrad = ctx.createRadialGradient(64, 64, 2, 64, 64, 60);
            radGrad.addColorStop(0, 'rgba(255, 255, 255, 1.0)');
            radGrad.addColorStop(0.12, 'rgba(255, 250, 230, 0.92)');
            radGrad.addColorStop(0.32, 'rgba(251, 191, 36, 0.60)');
            radGrad.addColorStop(0.55, 'rgba(245, 158, 11, 0.22)');
            radGrad.addColorStop(0.78, 'rgba(217, 119, 6, 0.07)');
            radGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');
            ctx.fillStyle = radGrad;
            ctx.fillRect(0, 0, 128, 128);

            return new THREE.CanvasTexture(canvas);
        }

        window.initHuenicsLuminaire3D = function() {
            const canvas = document.getElementById('luminaire-3d-canvas');
            if (!canvas || typeof THREE === 'undefined') return;

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
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
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
                const ringMat = new THREE.MeshStandardMaterial({ color: 0xc4b5fd, metalness: 0.8, roughness: 0.3 });
                const ringMesh = new THREE.Mesh(ringGeo, ringMat);
                ringMesh.position.set(hx, hy, 0.062);
                cobGroup.add(ringMesh);
            });

            // Citizen Polarity Notch (Chamfered Corner at Top-Left)
            const notchGeo = new THREE.BoxGeometry(0.35, 0.35, 0.14);
            const notchMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.5 });
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
            const plusMark1 = new THREE.Mesh(plusMarkGeo, new THREE.MeshBasicMaterial({ color: 0x991b1b }));
            plusMark1.position.set(0.95, 0.72, 0.066);
            cobGroup.add(plusMark1);
            const plusMark2 = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.18, 0.02), new THREE.MeshBasicMaterial({ color: 0x991b1b }));
            plusMark2.position.set(0.95, 0.72, 0.066);
            cobGroup.add(plusMark2);

            const minusMark = new THREE.Mesh(plusMarkGeo, new THREE.MeshBasicMaterial({ color: 0x1e3a8a }));
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
            const labelMat = new THREE.MeshBasicMaterial({ map: labelTex, transparent: true });
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
                emissiveIntensity: 5.5,
                roughness: 0.2,
                metalness: 0.05
            });
            phosphorMesh = new THREE.Mesh(phosphorGeo, phosphorMat);
            phosphorMesh.rotation.x = Math.PI / 2;
            phosphorMesh.position.set(0, 0, 0.07);
            cobGroup.add(phosphorMesh);

            // =========================================================
            // 4. OPTICAL BLOOM SPRITE (FOCUSED PHOTON GLOW)
            // =========================================================
            const bloomTexture = createOpticalBloomTexture();
            const bloomMat = new THREE.SpriteMaterial({
                map: bloomTexture,
                blending: THREE.AdditiveBlending,
                color: cctProfiles[currentCCT].hex,
                transparent: true,
                opacity: 0.88,
                depthWrite: false
            });
            bloomSprite = new THREE.Sprite(bloomMat);
            bloomSprite.position.set(0, 0, 0.25);
            bloomSprite.scale.set(3.6, 3.6, 1.0);
            cobGroup.add(bloomSprite);

            // =========================================================
            // 5. ATMOSPHERIC DUST PARTICLES & SURGE SHOCKWAVE
            // =========================================================
            const particleCount = 42;
            const particleGeo = new THREE.BufferGeometry();
            const particlePos = new Float32Array(particleCount * 3);
            for (let i = 0; i < particleCount; i++) {
                const r = 0.4 + Math.random() * 1.6;
                const theta = Math.random() * Math.PI * 2;
                particlePos[i * 3] = r * Math.cos(theta);
                particlePos[i * 3 + 1] = r * Math.sin(theta);
                particlePos[i * 3 + 2] = 0.2 + Math.random() * 1.5;
            }
            particleGeo.setAttribute('position', new THREE.BufferAttribute(particlePos, 3));
            const particleMat = new THREE.PointsMaterial({
                color: cctProfiles[currentCCT].hex,
                size: 0.055,
                transparent: true,
                opacity: 0.75,
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
            // 6. PHYSICS-BASED DYNAMIC LIGHTING
            // =========================================================
            corePointLight = new THREE.PointLight(cctProfiles[currentCCT].hex, 6.0, 16, 2.0);
            corePointLight.position.set(0, 0, 0.85);
            cobGroup.add(corePointLight);

            ambientLight = new THREE.AmbientLight(0xffffff, 0.45);
            scene.add(ambientLight);

            keyLight = new THREE.DirectionalLight(0xffffff, 0.95);
            keyLight.position.set(5, 7, 6);
            scene.add(keyLight);

            rimLight = new THREE.DirectionalLight(0x93c5fd, 0.65);
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
            const stageContainer = document.getElementById('hero-stage-container') || (stageEl ? stageEl.parentElement : container);
            const glareSheen = stageEl ? stageEl.querySelector('.glare-sheen') : null;

            const badgeDiscount = document.getElementById('hero-badge-discount');
            const badgeVat = document.getElementById('hero-badge-vat');
            const badgeFreight = document.getElementById('hero-badge-freight');

            // Interactive Damping State Variables
            let targetMouseX = 0, targetMouseY = 0;
            let currentMouseX = 0, currentMouseY = 0;
            let mouseInfluence = 0.0;
            let isHoveringStage = false;
            let stageRotX = 0, stageRotY = 0;

            // Calculate normalized mouse coordinates [-1.0, 1.0] relative to stage center
            function getStageNormalizedCoords(clientX, clientY) {
                if (!stageEl) return { x: 0, y: 0, isDirect: false };
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
                return { x, y, isDirect };
            }

            if (stageContainer) {
                stageContainer.addEventListener('mousedown', (e) => {
                    if (e.target.closest('#luminaire-center-toggle') || e.target.closest('#luminaire-3d-canvas') || e.target.closest('#hero-3d-stage')) {
                        isDragging = true;
                        previousMousePosition = { x: e.clientX, y: e.clientY };
                    }
                });

                window.addEventListener('mouseup', () => {
                    isDragging = false;
                });

                stageContainer.addEventListener('mousemove', (e) => {
                    const coords = getStageNormalizedCoords(e.clientX, e.clientY);
                    if (isDragging) {
                        const deltaX = e.clientX - previousMousePosition.x;
                        const deltaY = e.clientY - previousMousePosition.y;
                        currentRotY += deltaX * 0.012;
                        currentRotX += deltaY * 0.012;
                        previousMousePosition = { x: e.clientX, y: e.clientY };
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

                // Touch physics for mobile/tablets
                stageContainer.addEventListener('touchstart', (e) => {
                    if (e.touches.length > 0) {
                        isDragging = true;
                        previousMousePosition = { x: e.touches[0].clientX, y: e.touches[0].clientY };
                    }
                }, { passive: true });

                stageContainer.addEventListener('touchmove', (e) => {
                    if (e.touches.length > 0 && isDragging) {
                        const deltaX = e.touches[0].clientX - previousMousePosition.x;
                        const deltaY = e.touches[0].clientY - previousMousePosition.y;
                        currentRotY += deltaX * 0.014;
                        currentRotX += deltaY * 0.014;
                        previousMousePosition = { x: e.touches[0].clientX, y: e.touches[0].clientY };
                        isHoveringStage = true;
                    }
                }, { passive: true });

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
            }, { threshold: 0.1 });
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
                // 1. Citizen LED COB Model 3D Rotation Physics
                // -----------------------------------------------------
                if (!isDragging) {
                    // Base resting perspective with elegant gentle gyroscopic breathing
                    const idleRotX = 0.20 + Math.sin(time * 0.5) * 0.05;
                    const idleRotY = -0.32 + Math.sin(time * 0.35) * 0.10;

                    // Mouse-guided 3D tilt target (clean, ergonomic responsiveness)
                    const hoverRotX = 0.20 + (-currentMouseY * 0.35);
                    const hoverRotY = -0.32 + (currentMouseX * 0.45);

                    // Continuous spring blend: eliminates boundary vibration
                    const targetX = idleRotX * (1.0 - mouseInfluence) + hoverRotX * mouseInfluence;
                    const targetY = idleRotY * (1.0 - mouseInfluence) + hoverRotY * mouseInfluence;

                    currentRotX += (targetX - currentRotX) * 0.065;
                    currentRotY += (targetY - currentRotY) * 0.065;
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

                    stageEl.style.transform = `perspective(1000px) rotateX(${stageRotX.toFixed(2)}deg) rotateY(${stageRotY.toFixed(2)}deg)`;
                }

                // -----------------------------------------------------
                // 3. Dynamic Specular Glare Tracking
                // -----------------------------------------------------
                if (glareSheen) {
                    const glareX = 50 + currentMouseX * 35;
                    const glareY = 50 + currentMouseY * 35;
                    glareSheen.style.opacity = (0.2 + mouseInfluence * 0.45).toFixed(2);
                    glareSheen.style.background = `radial-gradient(circle at ${glareX.toFixed(1)}% ${glareY.toFixed(1)}%, rgba(255,255,255,0.22) 0%, transparent 60%)`;
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

                // Dynamic Phosphor Color Emission
                const coldPhosphor = new THREE.Color(0xf59e0b); // Unexcited yellow-amber phosphor
                const targetColor = new THREE.Color(cctProfiles[currentCCT].hex);

                let displayColor = new THREE.Color();
                displayColor.lerpColors(coldPhosphor, targetColor, Math.min(1.0, thermalPower * 1.2));

                // Update Central Phosphor LES Disc
                if (phosphorMesh) {
                    phosphorMesh.material.emissive.copy(displayColor);
                    const surgeMult = isSurging ? 1.5 : 1.0;
                    phosphorMesh.material.emissiveIntensity = thermalPower * 6.5 * surgeMult;
                    phosphorMesh.material.color.lerpColors(coldPhosphor, new THREE.Color(0xffffff), thermalPower);
                }

                // Update Optical Bloom Sprite (Smooth Breathing & Expansion)
                if (bloomSprite) {
                    const breathe = 1.0 + Math.sin(time * 2.4) * 0.03;
                    const surgeScale = isSurging ? 1.4 : 1.0;
                    const sW = 3.6 * (0.35 + 0.65 * thermalPower) * breathe * surgeScale;
                    const sH = 3.6 * (0.35 + 0.65 * thermalPower) * breathe * surgeScale;
                    bloomSprite.scale.set(sW, sH, 1.0);
                    bloomSprite.material.opacity = Math.pow(thermalPower, 1.4) * (isSurging ? 0.98 : 0.88);
                    bloomSprite.material.color.copy(displayColor);
                }

                // Update Core Point Light with High CRI Intensity
                if (corePointLight) {
                    corePointLight.intensity = Math.pow(thermalPower, 1.8) * (isSurging ? 9.5 : 6.0);
                    corePointLight.color.copy(displayColor);
                }

                // Floating Dust Motes Drift in Atmospheric Light Beam
                if (particleSystem) {
                    const positions = particleSystem.geometry.attributes.position.array;
                    for (let i = 0; i < particleCount; i++) {
                        positions[i * 3 + 2] += (thermalPower > 0.1 ? 0.012 : 0.003);
                        if (positions[i * 3 + 2] > 2.2) {
                            positions[i * 3 + 2] = 0.2;
                        }
                    }
                    particleSystem.geometry.attributes.position.needsUpdate = true;
                    particleSystem.material.opacity = 0.2 + (thermalPower * 0.65);
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
        };

        // Industrial Power Switch (ON / OFF) with Incandescent Warm-up & Phosphor Decay
        window.toggleLuminairePower = function() {
            isPowerOn = !isPowerOn;
            const btn = document.getElementById('luminaire-power-btn');
            const label = document.getElementById('luminaire-power-label');
            const dot = document.getElementById('luminaire-status-dot');
            const text = document.getElementById('luminaire-status-text');
            const halo = document.getElementById('luminaire-ambient-halo');

            if (isPowerOn) {
                if (btn) {
                    btn.className = 'flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-1.5 rounded-xl text-[10px] sm:text-xs font-bold transition-all duration-200 bg-emerald-500 text-white hover:bg-emerald-600 shadow-md shadow-emerald-500/20 active:scale-95 cursor-pointer whitespace-nowrap';
                }
                if (label) label.innerText = 'COB: ON';
                if (dot) dot.className = 'w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-400 animate-pulse';
                if (text) text.innerText = cctProfiles[currentCCT].badgeText;
                if (halo) {
                    halo.style.opacity = '0.85';
                    halo.style.background = cctProfiles[currentCCT].halo;
                }
                window.triggerLuminaireSurge();
            } else {
                if (btn) {
                    btn.className = 'flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-1.5 rounded-xl text-[10px] sm:text-xs font-bold transition-all duration-200 bg-slate-700 text-slate-300 hover:bg-slate-600 active:scale-95 cursor-pointer whitespace-nowrap';
                }
                if (label) label.innerText = 'COB: OFF';
                if (dot) dot.className = 'w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-slate-500';
                if (text) text.innerText = 'CITIZEN COB LED • STANDBY MODE';
                if (halo) {
                    halo.style.opacity = '0.12';
                }
            }
        };

        // Kelvin Color Temperature Selector (3000K / 3500K / 4000K / 5000K)
        window.setLuminaireCCT = function(cct, btnEl) {
            if (!cctProfiles[cct]) return;
            currentCCT = cct;
            const profile = cctProfiles[cct];

            // Update CCT Buttons
            document.querySelectorAll('.cct-btn').forEach(b => {
                b.className = 'cct-btn px-1.5 sm:px-2 py-1 rounded-lg text-[9px] sm:text-[11px] font-black transition-all duration-200 text-slate-300 hover:text-white cursor-pointer';
            });
            if (btnEl) {
                const activeBg = cct === '3000K' 
                    ? 'bg-amber-500 text-white shadow-sm' 
                    : (cct === '3500K' 
                        ? 'bg-orange-500 text-white shadow-sm' 
                        : (cct === '4000K' 
                            ? 'bg-yellow-400 text-slate-950 shadow-sm' 
                            : 'bg-blue-400 text-slate-950 shadow-sm'));
                btnEl.className = 'cct-btn px-1.5 sm:px-2 py-1 rounded-lg text-[9px] sm:text-[11px] font-black transition-all duration-200 ' + activeBg + ' cursor-pointer';
            }

            // Update Ambient Halo & Tech Badge
            const halo = document.getElementById('luminaire-ambient-halo');
            if (halo && isPowerOn) {
                halo.style.background = profile.halo;
            }
            const text = document.getElementById('luminaire-status-text');
            if (text && isPowerOn) {
                text.innerText = profile.badgeText;
            }

            // Trigger brief photonic surge
            window.triggerLuminaireSurge();
        };

        // Photonic Ignition Surge Animation
        window.triggerLuminaireSurge = function() {
            if (!isPowerOn) {
                window.toggleLuminairePower();
                return;
            }
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
