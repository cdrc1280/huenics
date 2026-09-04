<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Huenics Industrial Sales Inc.') - Colors • Techniques • Technology</title>

    <!-- Instant Dark Mode Init to Prevent Flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('huenics_theme');
            if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <!-- Compiled Production Tailwind CSS & Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Production Vector Icons (Lucide Icons) -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        [x-cloak] { display: none !important; }
        
        /* -------------------------------------------------------------
         * 1. Theme Crossfade (Surface Containers Only, Zero Twinkling)
         * ------------------------------------------------------------- */
        html.theme-transitioning body,
        html.theme-transitioning header,
        html.theme-transitioning #page-content,
        html.theme-transitioning footer,
        html.theme-transitioning nav,
        html.theme-transitioning aside {
            transition: background-color 260ms cubic-bezier(0.2, 0.8, 0.2, 1), 
                        border-color 260ms cubic-bezier(0.2, 0.8, 0.2, 1) !important;
        }

        /* -------------------------------------------------------------
         * 2. High-Performance 3D Spatial Stage & Instant Morph
         * ------------------------------------------------------------- */
        #page-stage {
            perspective: 1200px;
            perspective-origin: 50% 20%;
            transform-style: preserve-3d;
            backface-visibility: hidden;
        }

        .page-3d-in {
            animation: page3DEnter 200ms cubic-bezier(0.16, 1, 0.3, 1) both;
            will-change: transform, opacity;
        }

        .page-3d-out {
            animation: page3DExit 90ms cubic-bezier(0.25, 1, 0.5, 1) both;
            will-change: transform, opacity;
        }

        @keyframes page3DEnter {
            0% {
                opacity: 0;
                transform: translate3d(0, 10px, -15px) scale(0.993);
            }
            100% {
                opacity: 1;
                transform: translate3d(0, 0, 0) scale(1);
            }
        }

        @keyframes page3DExit {
            0% {
                opacity: 1;
                transform: translate3d(0, 0, 0) scale(1);
            }
            100% {
                opacity: 0;
                transform: translate3d(0, -6px, -10px) scale(0.995);
            }
        }

        /* -------------------------------------------------------------
         * 3. Tactile 3D Card Physics & Ambient Depth Surfaces
         * ------------------------------------------------------------- */
        .card-interactive,
        .bento-surface,
        .product-card,
        .catalog-card {
            transform-style: preserve-3d;
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.28s cubic-bezier(0.16, 1, 0.3, 1), 
                        border-color 0.28s ease;
            will-change: transform, box-shadow;
        }
        .card-interactive:hover {
            transform: translate3d(0, -4px, 12px);
        }

        /* Spring & Inertia Micro-Interactions */
        * {
            scroll-behavior: smooth;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes popIn {
            0% { transform: scale(0.92); opacity: 0; }
            70% { transform: scale(1.03); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes badgeBounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.3); }
        }
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* Uniform Staggered Entrance Animations */
        .page-transition-wrapper {
            animation: uniformPageEnter 280ms cubic-bezier(0.16, 1, 0.3, 1) both;
            will-change: opacity, transform;
        }
        .page-transition-exit {
            opacity: 0;
            transform: translateY(-4px);
            transition: opacity 120ms ease-out, transform 120ms ease-out;
        }
        @keyframes uniformPageEnter {
            0% {
                opacity: 0;
                transform: translateY(8px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stagger-1 { animation: fadeInUp 0.38s cubic-bezier(0.16, 1, 0.3, 1) 0.03s both; }
        .stagger-2 { animation: fadeInUp 0.42s cubic-bezier(0.16, 1, 0.3, 1) 0.08s both; }
        .stagger-3 { animation: fadeInUp 0.46s cubic-bezier(0.16, 1, 0.3, 1) 0.14s both; }
        .stagger-4 { animation: fadeInUp 0.50s cubic-bezier(0.16, 1, 0.3, 1) 0.20s both; }

        .animate-fade-in-up {
            animation: fadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out both;
        }
        .animate-pop-in {
            animation: popIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .badge-bounce {
            animation: badgeBounce 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Dribbble-Grade Ambient Radial Light Meshes */
        .ambient-mesh-hero {
            background-image: 
                radial-gradient(at 15% 15%, rgba(33, 79, 224, 0.07) 0px, transparent 55%),
                radial-gradient(at 85% 25%, rgba(96, 165, 250, 0.05) 0px, transparent 50%),
                radial-gradient(at 50% 80%, rgba(33, 79, 224, 0.03) 0px, transparent 60%);
        }
        .dark .ambient-mesh-hero {
            background-image: 
                radial-gradient(at 20% 20%, rgba(33, 79, 224, 0.16) 0px, transparent 60%),
                radial-gradient(at 80% 20%, rgba(96, 165, 250, 0.10) 0px, transparent 55%),
                radial-gradient(at 50% 90%, rgba(14, 165, 233, 0.06) 0px, transparent 60%);
        }

        /* Dribbble-Grade Card & Button Physics */
        .card-interactive {
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.28s cubic-bezier(0.16, 1, 0.3, 1), 
                        border-color 0.28s ease;
            will-change: transform, box-shadow;
        }
        .card-interactive:hover {
            transform: translateY(-4px);
        }

        .btn-interactive {
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-interactive:active {
            transform: scale(0.97);
        }

        .transition-spring {
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .hover-lift {
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .hover-lift:hover {
            transform: translateY(-3px);
        }

        /* High-Precision Tabular Numbers for Pricing & Specs */
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }

        /* Tactile Bento Surface */
        .bento-surface {
            background-color: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.04);
        }
        .dark .bento-surface {
            background-color: #111827;
            border: 1px solid rgba(30, 41, 59, 0.9);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.5);
        }

        /* Skeleton Shimmer States */
        .skeleton-shimmer {
            background: linear-gradient(90deg, rgba(226,232,240,0.6) 25%, rgba(241,245,249,0.9) 50%, rgba(226,232,240,0.6) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        .dark .skeleton-shimmer {
            background: linear-gradient(90deg, rgba(30,41,59,0.6) 25%, rgba(51,65,85,0.9) 50%, rgba(30,41,59,0.6) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        /* Glassmorphism Panels */
        .glass-panel {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .dark .glass-panel {
            background: rgba(11, 15, 25, 0.88);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Accessibility: Strict prefers-reduced-motion support */
        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }

        /* Bulletproof single-icon display per theme with smooth rotation */
        #theme-toggle {
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s, border-color 0.2s;
        }
        #theme-toggle:active {
            transform: scale(0.92) rotate(15deg);
        }
        html.dark #theme-icon-sun { display: inline-block !important; }
        html.dark #theme-icon-moon { display: none !important; }
        html:not(.dark) #theme-icon-sun { display: none !important; }
        html:not(.dark) #theme-icon-moon { display: inline-block !important; }
    </style>
</head>
<body class="flex min-h-full flex-col font-sans bg-slate-50 dark:bg-[#070b14] text-slate-900 dark:text-slate-100 transition-colors duration-200">

    <!-- Top SPA Progress Loading Bar -->
    <div id="page-progress-bar" class="fixed top-0 left-0 right-0 h-[2.5px] bg-gradient-to-r from-[#214fe0] via-[#60a5fa] to-[#3b82f6] shadow-[0_0_10px_rgba(33,79,224,0.7)] z-50 pointer-events-none transition-all duration-200 ease-out opacity-0 -translate-x-full"></div>

    <!-- Top Banner (PDF Royal Blue in Light / Deep Obsidian Navy in Dark) -->
    <div class="bg-[#1a42be] dark:bg-[#091129] text-white text-xs py-2 px-3 sm:px-4 border-b border-blue-900/40 dark:border-blue-950 transition-colors duration-200">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-[11px] sm:text-xs">
                <span class="inline-flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg> Tel. #8561 6836</span>
                <span class="hidden sm:inline-flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg> CS: +63 968 8500720</span>
                <span class="hidden md:inline-flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg> huenicsindustrialsales@gmail.com</span>
            </div>
            <div class="flex items-center gap-2.5 ml-auto sm:ml-0 text-[11px] sm:text-xs">
                <span class="text-blue-100 font-semibold tracking-wider uppercase text-[10px] hidden lg:inline">
                    Colors &bull; Techniques &bull; Technology
                </span>
                <span class="inline-flex items-center gap-1.5 bg-white/15 dark:bg-blue-950/60 text-white px-2 py-0.5 rounded font-medium border border-white/20 dark:border-blue-800/50 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg> Free Delivery ₱20k+ (Metro Manila)
                </span>
                <span class="bg-emerald-400 dark:bg-emerald-500 text-slate-950 font-bold px-2 py-0.5 rounded whitespace-nowrap text-[10px] uppercase tracking-wide shadow-sm">
                    VAT INC.
                </span>
            </div>
        </div>
    </div>

    <!-- Main Navigation (Clean White in Light / Sleek Dark Glass in Dark) -->
    <header class="sticky top-0 z-40 bg-white/95 dark:bg-[#0b0f19]/95 backdrop-blur border-b border-slate-200 dark:border-slate-800/90 shadow-sm transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 sm:h-20 gap-2">
                <!-- Authentic HISI Logo from PDF Header -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <a href="{{ route('customer.home') }}" class="flex items-center gap-2 sm:gap-3 group shrink-0" title="Huenics Industrial Sales Inc.">
                        <div class="border-l-[3px] border-r-[3px] border-[#214fe0] dark:border-[#3b82f6] px-2 py-0.5 text-center shrink-0 bg-blue-50/50 dark:bg-blue-950/60 rounded-sm transition-colors">
                            <div class="text-lg sm:text-2xl font-black tracking-widest text-[#214fe0] dark:text-[#60a5fa] leading-none">HISI</div>
                            <div class="text-[7.5px] sm:text-[9px] font-bold text-blue-900 dark:text-blue-200 tracking-tight whitespace-nowrap mt-0.5">Colors &bull; Techniques &bull; Technology</div>
                        </div>
                        <div class="border-l border-slate-200 dark:border-slate-800 pl-2.5 sm:pl-3 hidden sm:block shrink-0 transition-colors">
                            <div class="text-xs sm:text-sm font-black tracking-tight text-slate-900 dark:text-white uppercase leading-none">
                                HUENICS
                            </div>
                            <div class="text-[10px] sm:text-xs font-extrabold tracking-tight text-[#214fe0] dark:text-[#60a5fa] uppercase leading-none mt-0.5">
                                INDUSTRIAL SALES INC.
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Desktop Nav Links (Visible on Laptop / Desktop >= 1024px) -->
                <nav class="hidden lg:flex items-center gap-4 xl:gap-7 text-xs xl:text-sm font-medium text-slate-700 dark:text-slate-300 shrink-0">
                    <a href="{{ route('customer.home') }}" class="hover:text-[#214fe0] dark:hover:text-[#60a5fa] transition whitespace-nowrap {{ request()->routeIs('customer.home') ? 'text-[#214fe0] dark:text-[#60a5fa] font-bold border-b-2 border-[#214fe0] dark:border-[#3b82f6] pb-1' : '' }}">
                        Home
                    </a>
                    <a href="{{ route('customer.about') }}" class="hover:text-[#214fe0] dark:hover:text-[#60a5fa] transition whitespace-nowrap {{ request()->routeIs('customer.about') ? 'text-[#214fe0] dark:text-[#60a5fa] font-bold border-b-2 border-[#214fe0] dark:border-[#3b82f6] pb-1' : '' }}">
                        About Us
                    </a>
                    <a href="{{ route('customer.products') }}" class="hover:text-[#214fe0] dark:hover:text-[#60a5fa] transition whitespace-nowrap {{ request()->routeIs('customer.products') ? 'text-[#214fe0] dark:text-[#60a5fa] font-bold border-b-2 border-[#214fe0] dark:border-[#3b82f6] pb-1' : '' }}">
                        Product Catalog
                    </a>
                    <a href="{{ route('customer.quotation-builder') }}" class="hover:text-[#214fe0] dark:hover:text-[#60a5fa] transition whitespace-nowrap {{ request()->routeIs('customer.quotation-builder') ? 'text-[#214fe0] dark:text-[#60a5fa] font-bold border-b-2 border-[#214fe0] dark:border-[#3b82f6] pb-1' : '' }}">
                        Quotation Builder
                    </a>
                </nav>

                <!-- Action / Cart Button & Theme Toggle -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <!-- Theme Toggle Button -->
                    <button type="button" 
                            id="theme-toggle"
                            onclick="toggleDarkMode(event)" 
                            class="p-2 rounded-xl text-slate-600 dark:text-amber-300 hover:text-slate-900 dark:hover:text-amber-200 bg-white dark:bg-[#161f38] hover:bg-slate-100 dark:hover:bg-slate-800 transition focus:outline-none border border-slate-300 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-600 shadow-sm flex items-center justify-center w-9 h-9" 
                            title="Toggle Light / Dark Theme"
                            aria-label="Toggle Theme">
                        <span id="theme-icon-sun" class="flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-400 transition-transform duration-300 hover:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="4"></circle>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41m14.14-14.14l-1.41 1.41" />
                            </svg>
                        </span>
                        <span id="theme-icon-moon" class="flex items-center justify-center">
                            <svg class="w-4 h-4 text-slate-600 dark:text-slate-300 transition-transform duration-300 hover:-rotate-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                            </svg>
                        </span>
                    </button>

                    <a href="{{ route('customer.quotation-builder') }}" 
                       class="relative inline-flex items-center gap-1.5 sm:gap-2 bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold text-xs sm:text-sm px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl shadow-md hover:shadow-lg transition-transform duration-200 hover:-translate-y-0.5 active:scale-95 dark:shadow-[0_0_15px_rgba(33,79,224,0.35)] shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="hidden sm:inline">Quotation</span><span class="hidden xl:inline"> Estimate</span>
                        <span id="nav-cart-count" class="hidden bg-amber-400 text-slate-950 font-black text-[11px] sm:text-xs px-1.5 py-0.2 rounded-full ml-0.5 sm:ml-1">
                            0
                        </span>
                    </a>

                    <!-- Mobile / Tablet Hamburger Button (Visible on Mobile & Tablet < 1024px) -->
                    <button type="button" onclick="toggleMobileMenu()" class="lg:hidden p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none" aria-label="Toggle Navigation Menu">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile & Tablet Nav Menu Drawer -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b0f19] px-4 py-4 space-y-2 shadow-lg transition-colors">
            <a href="{{ route('customer.home') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg font-medium text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 {{ request()->routeIs('customer.home') ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-[#60a5fa] font-semibold' : '' }}">
                <svg class="w-4 h-4 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg> Home
            </a>
            <a href="{{ route('customer.about') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg font-medium text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 {{ request()->routeIs('customer.about') ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-[#60a5fa] font-semibold' : '' }}">
                <svg class="w-4 h-4 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg> About Us
            </a>
            <a href="{{ route('customer.products') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg font-medium text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 {{ request()->routeIs('customer.products') ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-[#60a5fa] font-semibold' : '' }}">
                <svg class="w-4 h-4 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg> Product Catalog
            </a>
            <a href="{{ route('customer.quotation-builder') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg font-medium text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 {{ request()->routeIs('customer.quotation-builder') ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-[#60a5fa] font-semibold' : '' }}">
                <svg class="w-4 h-4 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg> Quotation Builder
            </a>
        </div>
    </header>

    <!-- Main Content Area with 3D Spatial Stage -->
    <div id="page-stage" class="flex-grow flex flex-col relative">
        <main id="page-content" class="flex-grow page-3d-in">
            @yield('content')
        </main>
        <div id="page-scripts-container" class="hidden">
            @stack('scripts')
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-4 right-4 sm:bottom-5 sm:right-5 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none max-w-[calc(100vw-2rem)] sm:max-w-sm">
        <div class="bg-slate-900 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 border border-slate-700">
            <div id="toast-icon" class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="flex-1">
                <p id="toast-title" class="font-bold text-xs text-white">Item Added</p>
                <p id="toast-message" class="text-xs text-slate-300">Added to quotation cart.</p>
            </div>
        </div>
    </div>

    <!-- Global Uniform Customer Modal (Confirmation & Alerts) -->
    <div id="huenics-modal" 
         class="fixed inset-0 z-50 bg-slate-950/60 dark:bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4 transition-opacity duration-200"
         role="dialog" 
         aria-modal="true" 
         aria-labelledby="huenics-modal-title">
        <div id="huenics-modal-card" 
             class="bg-white dark:bg-[#111827] rounded-2xl shadow-2xl max-w-md w-full border border-slate-200 dark:border-slate-800 overflow-hidden transform scale-95 opacity-0 transition-all duration-200">
            <!-- Top Geometric Accent Bar -->
            <div class="h-1.5 w-full bg-gradient-to-r from-[#214fe0] via-blue-500 to-[#1a42be]"></div>

            <div class="p-6">
                <div class="flex items-start gap-4">
                    <!-- Dynamic Icon Container -->
                    <div id="huenics-modal-icon-container" class="w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0">
                        <span id="huenics-modal-icon">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 id="huenics-modal-title" class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight">
                            Confirmation
                        </h3>
                        <p id="huenics-modal-message" class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                            Are you sure you want to proceed?
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" 
                            id="huenics-modal-cancel-btn"
                            class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#161f38] hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs sm:text-sm transition focus:outline-none focus:ring-2 focus:ring-slate-400">
                        Cancel
                    </button>
                    <button type="button" 
                            id="huenics-modal-confirm-btn"
                            class="px-5 py-2.5 rounded-xl text-white font-bold text-xs sm:text-sm shadow-md transition focus:outline-none focus:ring-2 flex items-center gap-1.5">
                        <span>Confirm</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 border-t border-slate-800 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                <!-- Col 1: Brand Info -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center text-white font-black text-xs tracking-wider">
                            HISI
                        </div>
                        <span class="text-lg font-bold text-white tracking-tight">HUENICS INDUSTRIAL</span>
                    </div>
                    <p class="text-xs text-blue-400 font-semibold uppercase tracking-wider">
                        Colors &bull; Techniques &bull; Technology
                    </p>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Huenics Industrial Sales Inc. (HISI) is a trusted supplier and direct importer of commercial lighting, C.O.B downlights, industrial electrical, and construction supplies in the Philippines.
                    </p>
                    <div class="text-xs text-slate-500">
                        All Prices are VAT Inclusive (VAT INC.)
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-white mb-4">Quick Links</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('customer.home') }}" class="hover:text-blue-400 transition">Home</a></li>
                        <li><a href="{{ route('customer.about') }}" class="hover:text-blue-400 transition">About Company</a></li>
                        <li><a href="{{ route('customer.products') }}" class="hover:text-blue-400 transition">Product Catalog</a></li>
                        <li><a href="{{ route('customer.quotation-builder') }}" class="hover:text-blue-400 transition">Online Quotation Builder</a></li>
                    </ul>
                </div>

                <!-- Col 3: Product Categories & Policies -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-white mb-4">Customer Policies</h3>
                    <ul class="space-y-2.5 text-xs text-slate-400">
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg> <span><strong>Free Delivery:</strong> Orders ₱20,000.00+ within Metro Manila</span></li>
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> <span><strong>Return & Exchange:</strong> Within 7 days upon delivery</span></li>
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg> <span><strong>Warranty:</strong> 1–2 Years limited warranty w/o physical damage</span></li>
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> <span><strong>Replacement:</strong> 1 mo. outright replacement for defective units</span></li>
                    </ul>
                </div>

                <!-- Col 4: Contact & Site Location -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-white mb-4">Head Office</h3>
                    <div class="space-y-3 text-sm text-slate-400">
                        <p class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-blue-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-xs leading-relaxed">Unit 916 Avida Towers Intima, 497 Pres. Quirino Ave. Ext. Cor. Zulueta St., Paco, Manila</span>
                        </p>
                        <p class="flex items-center gap-2.5 text-xs">
                            <svg class="w-3.5 h-3.5 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>Tel. #8561 6836</span>
                        </p>
                        <p class="flex items-center gap-2.5 text-xs">
                            <svg class="w-3.5 h-3.5 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg>
                            <span>CS: +63 968 8500720 / Tech: +63 965 6287205</span>
                        </p>
                        <p class="flex items-center gap-2.5 text-xs">
                            <svg class="w-3.5 h-3.5 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>huenicsindustrialsales@gmail.com</span>
                        </p>
                        <p class="flex items-center gap-2.5 text-xs">
                            <svg class="w-3.5 h-3.5 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"/></svg>
                            <span>crm.huenics777@gmail.com</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Global Component & Technology Alliances -->
            <div class="mt-10 pt-6 border-t border-slate-800/80">
                <div class="text-center sm:text-left mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400">
                        Global Component & Technology Alliances
                    </span>
                </div>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 text-xs text-slate-400">
                    <span class="bg-slate-800/90 px-2.5 py-1 rounded border border-slate-700 font-semibold text-slate-300">Citizen</span>
                    <span class="bg-slate-800/90 px-2.5 py-1 rounded border border-slate-700 font-semibold text-slate-300">Osram</span>
                    <span class="bg-slate-800/90 px-2.5 py-1 rounded border border-slate-700 font-semibold text-slate-300">Lumileds</span>
                    <span class="bg-slate-800/90 px-2.5 py-1 rounded border border-slate-700 font-semibold text-slate-300">Philips LED Driver</span>
                    <span class="bg-slate-800/90 px-2.5 py-1 rounded border border-slate-700 font-semibold text-slate-300">Inventronics</span>
                    <span class="bg-slate-800/90 px-2.5 py-1 rounded border border-slate-700 font-semibold text-slate-300">Hyperion</span>
                    <span class="bg-slate-800/90 px-2.5 py-1 rounded border border-slate-700 font-semibold text-slate-300">MechaTronix</span>
                    <span class="bg-slate-800/90 px-2.5 py-1 rounded border border-slate-700 font-semibold text-slate-300">Khatod</span>
                    <span class="bg-slate-800/90 px-2.5 py-1 rounded border border-slate-700 font-semibold text-slate-300">Molex</span>
                    <span class="bg-slate-800/90 px-2.5 py-1 rounded border border-slate-700 font-semibold text-slate-300">BJB</span>
                    <span class="bg-slate-800/90 px-2.5 py-1 rounded border border-slate-700 font-semibold text-slate-300">Darkoo</span>
                    <span class="bg-slate-800/90 px-2.5 py-1 rounded border border-slate-700 font-semibold text-slate-300">DONE</span>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-800 text-xs text-slate-500 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p>&copy; {{ date('Y') }} Huenics Industrial Sales Inc. (HISI). All rights reserved.</p>
                <p>Prices are subject to change without prior notice. (VAT INC.)</p>
            </div>
        </div>
    </footer>

    <!-- Global Cart Script for Quotation Builder -->
    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        // Shared Cart Helpers using localStorage
        const CartManager = {
            storageKey: 'huenics_quotation_cart',

            getCart() {
                try {
                    const data = localStorage.getItem(this.storageKey);
                    return data ? JSON.parse(data) : [];
                } catch(e) {
                    return [];
                }
            },

            saveCart(cart) {
                try {
                    localStorage.setItem(this.storageKey, JSON.stringify(cart));
                    this.updateNavBadge();
                } catch(e) {}
            },

            addItem(product, quantity = 1) {
                let cart = this.getCart();
                quantity = parseFloat(quantity) || 1;
                if (quantity <= 0) quantity = 1;

                const existingIndex = cart.findIndex(item => item.item_code === (product.sku || product.product_code || product.canonical_name));

                if (existingIndex > -1) {
                    cart[existingIndex].quantity += quantity;
                    cart[existingIndex].line_total = parseFloat((cart[existingIndex].quantity * cart[existingIndex].unit_price).toFixed(2));
                } else {
                    const price = parseFloat(product.selling_price || product.default_price || 0);
                    cart.push({
                        id: product.id || null,
                        item_code: product.sku || product.product_code || ('ITM-' + Date.now().toString().slice(-4)),
                        description: product.canonical_name || product.description || 'Custom Item',
                        quantity: quantity,
                        unit: product.unit_default || 'pcs',
                        unit_price: price,
                        line_total: parseFloat((quantity * price).toFixed(2))
                    });
                }

                this.saveCart(cart);
                showToast('Added to Quotation', `"${product.canonical_name || 'Item'}" (${quantity} ${product.unit_default || 'pcs'}) added.`);
            },

            updateNavBadge() {
                const cart = this.getCart();
                const totalItems = cart.reduce((sum, item) => sum + (parseFloat(item.quantity) || 0), 0);
                const badge = document.getElementById('nav-cart-count');
                if (badge) {
                    if (totalItems > 0) {
                        badge.textContent = totalItems;
                        badge.classList.remove('hidden');
                        badge.classList.remove('badge-bounce');
                        void badge.offsetWidth;
                        badge.classList.add('badge-bounce');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            },

            clearCart() {
                localStorage.removeItem(this.storageKey);
                this.updateNavBadge();
            }
        };

        function showToast(title, message, isSuccess = true) {
            const toast = document.getElementById('toast');
            const titleEl = document.getElementById('toast-title');
            const msgEl = document.getElementById('toast-message');
            const iconEl = document.getElementById('toast-icon');

            if (!toast) return;

            titleEl.textContent = title;
            msgEl.textContent = message;

            if (isSuccess) {
                iconEl.className = 'w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold';
                iconEl.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
            } else {
                iconEl.className = 'w-8 h-8 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold';
                iconEl.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
            }

            toast.classList.remove('translate-y-20', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');

            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }

        function toggleDarkMode(event) {
            const isDark = document.documentElement.classList.contains('dark');
            const nextTheme = isDark ? 'light' : 'dark';

            const applyThemeChange = () => {
                document.documentElement.classList.toggle('dark', nextTheme === 'dark');
                localStorage.setItem('huenics_theme', nextTheme);
                updateThemeIcons();
                if (window.lucide) lucide.createIcons();
            };

            if (document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.documentElement.classList.add('theme-transitioning');
                const transition = document.startViewTransition(() => {
                    applyThemeChange();
                });
                transition.finished.finally(() => {
                    document.documentElement.classList.remove('theme-transitioning');
                });
            } else {
                document.documentElement.classList.add('theme-transitioning');
                applyThemeChange();
                setTimeout(() => {
                    document.documentElement.classList.remove('theme-transitioning');
                }, 280);
            }
        }

        function updateThemeIcons() {
            // Strict CSS rules (html.dark #theme-icon-sun / html:not(.dark) #theme-icon-moon) guarantee
            // exactly one icon is rendered at all times per theme with zero delay or flicker.
        }

        // =========================================================================
        // High-Speed Client-Side SPA Engine (0ms Instant Transitions & Preload)
        // =========================================================================
        window.HuenicsSPA = {
            cache: new Map(), // url -> { title, contentHtml, scriptsHtml, timestamp }
            scrollHistory: new Map(), // url -> scrollY
            isNavigating: false,
            progressBar: null,
            progressTimer: null,

            init() {
                this.progressBar = document.getElementById('page-progress-bar');

                // 1. Cache current initial page immediately
                this.cacheCurrentPage();

                // 2. Intercept internal link clicks
                document.addEventListener('click', (e) => {
                    const link = e.target.closest('a');
                    if (!link) return;

                    const href = link.getAttribute('href');
                    if (!href) return;

                    // Exclude special / non-SPA anchors
                    if (
                        href.startsWith('#') ||
                        href.startsWith('javascript:') ||
                        href.startsWith('mailto:') ||
                        href.startsWith('tel:') ||
                        link.hasAttribute('download') ||
                        link.getAttribute('target') === '_blank' ||
                        link.getAttribute('data-no-spa') !== null ||
                        e.ctrlKey || e.metaKey || e.shiftKey || e.altKey ||
                        e.button !== 0
                    ) {
                        return;
                    }

                    try {
                        const targetUrl = new URL(link.href, window.location.origin);
                        if (targetUrl.origin !== window.location.origin) return;

                        // Same URL navigation
                        if (targetUrl.pathname === window.location.pathname && targetUrl.search === window.location.search) {
                            if (targetUrl.hash) return;
                            e.preventDefault();
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            return;
                        }

                        e.preventDefault();
                        this.navigate(targetUrl.href, true);
                    } catch (err) {
                        // Fallback
                    }
                });

                // 3. Hover / Touch Prefetching for 0ms Instant Click Response
                const prefetchHandler = (e) => {
                    const link = e.target.closest('a');
                    if (!link) return;
                    const href = link.getAttribute('href');
                    if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:') || link.getAttribute('target') === '_blank') return;
                    try {
                        const targetUrl = new URL(link.href, window.location.origin);
                        if (targetUrl.origin === window.location.origin) {
                            this.prefetch(targetUrl.href);
                        }
                    } catch (_) {}
                };
                document.addEventListener('pointerenter', prefetchHandler, { capture: true, passive: true });
                document.addEventListener('touchstart', prefetchHandler, { capture: true, passive: true });

                // 4. Intercept GET Search & Filter Forms
                document.addEventListener('submit', (e) => {
                    const form = e.target;
                    if (form.method && form.method.toUpperCase() === 'GET') {
                        const action = form.getAttribute('action') || window.location.href;
                        try {
                            const targetUrl = new URL(action, window.location.origin);
                            if (targetUrl.origin === window.location.origin && form.getAttribute('target') !== '_blank') {
                                e.preventDefault();
                                const formData = new FormData(form);
                                const params = new URLSearchParams(formData);
                                targetUrl.search = params.toString();
                                this.navigate(targetUrl.href, true);
                            }
                        } catch (_) {}
                    }
                });

                // 5. Handle Back/Forward History with Instant Cache Restoration
                window.addEventListener('popstate', () => {
                    this.navigate(window.location.href, false);
                });

                // 6. Warm-up prefetch for primary navigation pages on idle
                if ('requestIdleCallback' in window) {
                    window.requestIdleCallback(() => this.warmup());
                } else {
                    setTimeout(() => this.warmup(), 300);
                }
            },

            warmup() {
                const primaryRoutes = [
                    '{{ route('customer.home') }}',
                    '{{ route('customer.about') }}',
                    '{{ route('customer.products') }}',
                    '{{ route('customer.quotation-builder') }}'
                ];
                primaryRoutes.forEach(url => {
                    if (url !== window.location.href) {
                        this.prefetch(url);
                    }
                });
            },

            cacheCurrentPage() {
                const contentEl = document.getElementById('page-content');
                const scriptsEl = document.getElementById('page-scripts-container');
                if (contentEl) {
                    this.cache.set(this.normalizeUrl(window.location.href), {
                        title: document.title,
                        contentHtml: contentEl.innerHTML,
                        scriptsHtml: scriptsEl ? scriptsEl.innerHTML : '',
                        timestamp: Date.now()
                    });
                }
            },

            async prefetch(url) {
                const normalized = this.normalizeUrl(url);
                if (this.cache.has(normalized)) return;

                try {
                    const res = await fetch(normalized, {
                        headers: { 'X-Requested-With': 'Huenics-SPA', 'Accept': 'text/html' }
                    });
                    if (!res.ok) return;
                    const html = await res.text();
                    this.storeInCache(normalized, html);
                } catch (_) {}
            },

            normalizeUrl(url) {
                const u = new URL(url, window.location.origin);
                u.hash = '';
                return u.href;
            },

            storeInCache(url, html) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const contentEl = doc.getElementById('page-content');
                const scriptsEl = doc.getElementById('page-scripts-container');
                const title = doc.querySelector('title')?.textContent || document.title;

                if (contentEl) {
                    this.cache.set(url, {
                        title: title,
                        contentHtml: contentEl.innerHTML,
                        scriptsHtml: scriptsEl ? scriptsEl.innerHTML : '',
                        timestamp: Date.now()
                    });
                }
            },

            async navigate(url, pushState = true) {
                if (this.isNavigating) return;
                this.isNavigating = true;

                const normalizedUrl = this.normalizeUrl(url);
                const currentUrl = this.normalizeUrl(window.location.href);

                // Save current scroll position
                this.scrollHistory.set(currentUrl, window.scrollY);

                // Optimistic instant active navbar link update
                const targetPath = new URL(normalizedUrl).pathname;
                this.updateActiveNavLinks(targetPath);

                // Close mobile drawer if open
                const mobileMenu = document.getElementById('mobile-menu');
                if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }

                const currentContent = document.getElementById('page-content');
                const currentScripts = document.getElementById('page-scripts-container');

                // Check in-memory cache
                let pageData = this.cache.get(normalizedUrl);

                if (!pageData) {
                    this.startProgress();
                    try {
                        const response = await fetch(normalizedUrl, {
                            headers: { 'X-Requested-With': 'Huenics-SPA', 'Accept': 'text/html' }
                        });
                        if (!response.ok) {
                            window.location.href = url;
                            return;
                        }
                        const html = await response.text();
                        this.storeInCache(normalizedUrl, html);
                        pageData = this.cache.get(normalizedUrl);
                    } catch (err) {
                        window.location.href = url;
                        return;
                    } finally {
                        this.finishProgress();
                    }
                }

                if (!pageData || !currentContent) {
                    window.location.href = url;
                    return;
                }

                // Smooth 3D spatial exit
                currentContent.classList.remove('page-3d-in');
                currentContent.classList.add('page-3d-out');

                await new Promise(resolve => setTimeout(resolve, 80));

                // Swap DOM & Title
                document.title = pageData.title;
                currentContent.innerHTML = pageData.contentHtml;
                if (currentScripts && pageData.scriptsHtml) {
                    currentScripts.innerHTML = pageData.scriptsHtml;
                }

                if (pushState) {
                    window.history.pushState({}, '', url);
                }

                // Restore scroll or scroll top
                const savedScroll = !pushState ? (this.scrollHistory.get(normalizedUrl) || 0) : 0;
                window.scrollTo({ top: savedScroll, behavior: 'instant' });

                // Enter 3D animation
                currentContent.classList.remove('page-3d-out');
                void currentContent.offsetWidth; // Force reflow
                currentContent.classList.add('page-3d-in');

                // Execute scripts
                this.executeScripts(currentContent);
                if (currentScripts) {
                    this.executeScripts(currentScripts);
                }

                // Re-bind interactive system utilities
                if (window.CartManager) {
                    window.CartManager.updateNavBadge();
                }
                if (window.Huenics3D) {
                    window.Huenics3D.init();
                }
                if (window.lucide) {
                    lucide.createIcons();
                }

                // Dispatch page-loaded event
                document.dispatchEvent(new CustomEvent('huenics:page-loaded', { detail: { url: normalizedUrl } }));

                this.isNavigating = false;
            },

            executeScripts(container) {
                if (!container) return;
                const scripts = container.querySelectorAll('script');
                scripts.forEach(oldScript => {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    newScript.textContent = oldScript.textContent;
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });
            },

            startProgress() {
                if (!this.progressBar) return;
                clearTimeout(this.progressTimer);
                this.progressBar.style.transition = 'none';
                this.progressBar.style.transform = 'translateX(-100%)';
                this.progressBar.style.opacity = '1';
                void this.progressBar.offsetWidth;
                this.progressBar.style.transition = 'transform 200ms ease-out, opacity 100ms';
                this.progressBar.style.transform = 'translateX(-30%)';
            },

            finishProgress() {
                if (!this.progressBar) return;
                this.progressBar.style.transform = 'translateX(0%)';
                this.progressTimer = setTimeout(() => {
                    this.progressBar.style.opacity = '0';
                    setTimeout(() => {
                        this.progressBar.style.transform = 'translateX(-100%)';
                    }, 150);
                }, 100);
            },

            updateActiveNavLinks(currentPath) {
                // Desktop navbar links
                const desktopLinks = document.querySelectorAll('header nav a');
                desktopLinks.forEach(link => {
                    const linkPath = new URL(link.href, window.location.origin).pathname;
                    const isActive = linkPath === currentPath;

                    if (isActive) {
                        link.className = 'hover:text-[#214fe0] dark:hover:text-[#60a5fa] transition whitespace-nowrap text-[#214fe0] dark:text-[#60a5fa] font-bold border-b-2 border-[#214fe0] dark:border-[#3b82f6] pb-1';
                    } else {
                        link.className = 'hover:text-[#214fe0] dark:hover:text-[#60a5fa] transition whitespace-nowrap';
                    }
                });

                // Mobile drawer links
                const mobileLinks = document.querySelectorAll('#mobile-menu a');
                mobileLinks.forEach(link => {
                    const linkPath = new URL(link.href, window.location.origin).pathname;
                    const isActive = linkPath === currentPath;

                    if (isActive) {
                        link.className = 'flex items-center px-3 py-2.5 rounded-lg font-semibold text-sm bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-[#60a5fa]';
                    } else {
                        link.className = 'flex items-center px-3 py-2.5 rounded-lg font-medium text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800';
                    }
                });
            }
        };

        // Backwards-compatible alias for any existing code calling HuenicsNavigator
        window.HuenicsNavigator = window.HuenicsSPA;

        // Global Uniform Modal Controller for Customer Portal
        window.HuenicsModal = {
            modalEl: null,
            cardEl: null,
            titleEl: null,
            messageEl: null,
            iconContainerEl: null,
            iconEl: null,
            confirmBtnEl: null,
            cancelBtnEl: null,
            confirmCallback: null,
            cancelCallback: null,

            init() {
                this.modalEl = document.getElementById('huenics-modal');
                this.cardEl = document.getElementById('huenics-modal-card');
                this.titleEl = document.getElementById('huenics-modal-title');
                this.messageEl = document.getElementById('huenics-modal-message');
                this.iconContainerEl = document.getElementById('huenics-modal-icon-container');
                this.iconEl = document.getElementById('huenics-modal-icon');
                this.confirmBtnEl = document.getElementById('huenics-modal-confirm-btn');
                this.cancelBtnEl = document.getElementById('huenics-modal-cancel-btn');

                if (!this.modalEl) return;

                // Close on cancel button
                this.cancelBtnEl?.addEventListener('click', () => {
                    const cb = this.cancelCallback;
                    this.close();
                    if (cb) cb();
                });

                // Confirm on confirm button
                this.confirmBtnEl?.addEventListener('click', () => {
                    const cb = this.confirmCallback;
                    this.close();
                    if (cb) cb();
                });

                // Close on clicking backdrop
                this.modalEl.addEventListener('click', (e) => {
                    if (e.target === this.modalEl) {
                        const cb = this.cancelCallback;
                        this.close();
                        if (cb) cb();
                    }
                });

                // Close on Escape key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !this.modalEl.classList.contains('hidden')) {
                        const cb = this.cancelCallback;
                        this.close();
                        if (cb) cb();
                    }
                });
            },

            confirm({
                title = 'Confirmation',
                message = 'Are you sure you want to proceed?',
                icon = 'warning',
                type = 'danger', // 'danger' | 'warning' | 'primary' | 'success'
                confirmText = 'Confirm',
                cancelText = 'Cancel',
                onConfirm = () => {},
                onCancel = () => {}
            } = {}) {
                if (!this.modalEl) this.init();

                this.titleEl.textContent = title;
                this.messageEl.textContent = message;
                this.confirmCallback = onConfirm;
                this.cancelCallback = onCancel;

                this.applyTheme(type, icon, confirmText, cancelText, false);
                this.open();
            },

            alert({
                title = 'Notice',
                message = '',
                icon = 'info',
                type = 'primary',
                buttonText = 'Got It',
                onClose = () => {}
            } = {}) {
                if (!this.modalEl) this.init();

                this.titleEl.textContent = title;
                this.messageEl.textContent = message;
                this.confirmCallback = onClose;
                this.cancelCallback = onClose;

                this.applyTheme(type, icon, buttonText, '', true);
                this.open();
            },

            applyTheme(type, icon, confirmText, cancelText, isAlert = false) {
                if (icon && icon.includes('<svg')) {
                    this.iconContainerEl.innerHTML = icon;
                } else if (icon && (icon.includes('trash') || icon.includes('delete'))) {
                    this.iconContainerEl.innerHTML = '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
                } else if (icon && (icon.includes('check') || icon.includes('success'))) {
                    this.iconContainerEl.innerHTML = '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
                } else if (icon && (icon.includes('info') || icon.includes('circle-info'))) {
                    this.iconContainerEl.innerHTML = '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                } else {
                    this.iconContainerEl.innerHTML = '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
                }

                const styles = {
                    danger: {
                        iconBg: 'bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/60',
                        btnClass: 'bg-red-600 hover:bg-red-700 focus:ring-red-500 text-white shadow-red-500/20',
                    },
                    warning: {
                        iconBg: 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60',
                        btnClass: 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500 text-white shadow-amber-500/20',
                    },
                    success: {
                        iconBg: 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60',
                        btnClass: 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500 text-white shadow-emerald-500/20',
                    },
                    primary: {
                        iconBg: 'bg-blue-50 dark:bg-blue-950/60 text-[#214fe0] dark:text-[#60a5fa] border border-blue-200 dark:border-blue-800/60',
                        btnClass: 'bg-[#214fe0] hover:bg-[#1a42be] focus:ring-blue-500 text-white shadow-blue-500/20',
                    },
                };

                const theme = styles[type] || styles.primary;
                this.iconContainerEl.className = `w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0 ${theme.iconBg}`;
                this.confirmBtnEl.className = `px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm shadow-md transition focus:outline-none focus:ring-2 flex items-center gap-1.5 ${theme.btnClass}`;
                this.confirmBtnEl.querySelector('span').textContent = confirmText;

                if (isAlert) {
                    this.cancelBtnEl.classList.add('hidden');
                } else {
                    this.cancelBtnEl.classList.remove('hidden');
                    this.cancelBtnEl.textContent = cancelText || 'Cancel';
                }
            },

            open() {
                this.modalEl.classList.remove('hidden');
                this.modalEl.classList.add('flex');

                requestAnimationFrame(() => {
                    this.cardEl.classList.remove('scale-95', 'opacity-0');
                    this.cardEl.classList.add('scale-100', 'opacity-100');
                    this.confirmBtnEl.focus();
                });
            },

            close() {
                this.cardEl.classList.remove('scale-100', 'opacity-100');
                this.cardEl.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    this.modalEl.classList.remove('flex');
                    this.modalEl.classList.add('hidden');
                }, 150);
            }
        };

        // Tactile 3D Card Tilt & Spatial Depth Engine
        window.Huenics3D = {
            init() {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                const elements = document.querySelectorAll('.card-interactive, .bento-surface, .product-card, .catalog-card');
                elements.forEach(el => {
                    if (el.dataset.tiltActive) return;
                    el.dataset.tiltActive = 'true';

                    let rect;
                    const onMouseMove = (e) => {
                        if (!rect) rect = el.getBoundingClientRect();
                        const x = e.clientX - rect.left;
                        const y = e.clientY - rect.top;
                        const xPct = (x / rect.width) - 0.5;
                        const yPct = (y / rect.height) - 0.5;
                        const maxTilt = 4.0;
                        const tiltX = -(yPct * maxTilt).toFixed(2);
                        const tiltY = (xPct * maxTilt).toFixed(2);
                        el.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) translate3d(0, -4px, 10px)`;
                    };

                    const onMouseEnter = () => {
                        rect = el.getBoundingClientRect();
                        el.style.transition = 'transform 0.1s ease-out, box-shadow 0.25s ease';
                    };

                    const onMouseLeave = () => {
                        el.style.transition = 'transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s ease';
                        el.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translate3d(0, 0, 0)';
                        rect = null;
                    };

                    el.addEventListener('mouseenter', onMouseEnter);
                    el.addEventListener('mousemove', onMouseMove);
                    el.addEventListener('mouseleave', onMouseLeave);
                });
            }
        };

        // Initialize badge, theme icon, modal, 3D physics, and SPA navigator on load
        document.addEventListener('DOMContentLoaded', () => {
            CartManager.updateNavBadge();
            updateThemeIcons();
            HuenicsModal.init();
            HuenicsSPA.init();
            if (window.Huenics3D) Huenics3D.init();
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>
