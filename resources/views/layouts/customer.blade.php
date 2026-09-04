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
    <style>
        [x-cloak] { display: none !important; }
        
        /* View Transitions API (Modern SPA & Theme Waves) */
        @view-transition {
            navigation: auto;
        }
        ::view-transition-old(root) {
            animation: 160ms cubic-bezier(0.16, 1, 0.3, 1) both pageFadeOut;
        }
        ::view-transition-new(root) {
            animation: 260ms cubic-bezier(0.16, 1, 0.3, 1) both pageSlideIn;
        }
        @keyframes pageFadeOut {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.99); }
        }
        @keyframes pageSlideIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Circular Reveal Transition for Theme Toggle */
        html.theme-transitioning::view-transition-old(root),
        html.theme-transitioning::view-transition-new(root) {
            animation: none !important;
            mix-blend-mode: normal !important;
        }
        html.theme-transitioning::view-transition-new(root) {
            z-index: 9999 !important;
        }
        html.theme-transitioning::view-transition-old(root) {
            z-index: 1 !important;
        }

        /* Fallback Smooth Theme Transition */
        html.theme-transition,
        html.theme-transition *,
        html.theme-transition *::before,
        html.theme-transition *::after {
            transition: background-color 320ms cubic-bezier(0.16, 1, 0.3, 1),
                        border-color 320ms cubic-bezier(0.16, 1, 0.3, 1),
                        color 320ms cubic-bezier(0.16, 1, 0.3, 1),
                        box-shadow 320ms cubic-bezier(0.16, 1, 0.3, 1) !important;
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
                <span><i class="fa-solid fa-phone text-blue-200 mr-1"></i> Tel. #8561 6836</span>
                <span class="hidden sm:inline"><i class="fa-solid fa-headset text-blue-200 mr-1"></i> CS: +63 968 8500720</span>
                <span class="hidden md:inline"><i class="fa-solid fa-envelope text-blue-200 mr-1"></i> huenicsindustrialsales@gmail.com</span>
            </div>
            <div class="flex items-center gap-2.5 ml-auto sm:ml-0 text-[11px] sm:text-xs">
                <span class="text-blue-100 font-semibold tracking-wider uppercase text-[10px] hidden lg:inline">
                    Colors &bull; Techniques &bull; Technology
                </span>
                <span class="bg-white/15 dark:bg-blue-950/60 text-white px-2 py-0.5 rounded font-medium border border-white/20 dark:border-blue-800/50 whitespace-nowrap">
                    <i class="fa-solid fa-truck-fast mr-1"></i> Free Delivery ₱20k+ (Metro Manila)
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
                        <i id="theme-icon-sun" class="fa-solid fa-sun text-amber-400 text-sm"></i>
                        <i id="theme-icon-moon" class="fa-solid fa-moon text-slate-600 dark:text-slate-400 text-sm"></i>
                    </button>

                    <a href="{{ route('customer.quotation-builder') }}" 
                       class="relative inline-flex items-center gap-1.5 sm:gap-2 bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold text-xs sm:text-sm px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl shadow-md hover:shadow-lg transition dark:shadow-[0_0_15px_rgba(33,79,224,0.35)] shrink-0">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span class="hidden sm:inline">Quotation</span><span class="hidden xl:inline"> Estimate</span>
                        <span id="nav-cart-count" class="hidden bg-amber-400 text-slate-950 font-black text-[11px] sm:text-xs px-1.5 py-0.2 rounded-full ml-0.5 sm:ml-1">
                            0
                        </span>
                    </a>

                    <!-- Mobile / Tablet Hamburger Button (Visible on Mobile & Tablet < 1024px) -->
                    <button type="button" onclick="toggleMobileMenu()" class="lg:hidden p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none" aria-label="Toggle Navigation Menu">
                        <i class="fa-solid fa-bars text-lg sm:text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile & Tablet Nav Menu Drawer -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b0f19] px-4 py-4 space-y-2 shadow-lg transition-colors">
            <a href="{{ route('customer.home') }}" class="flex items-center px-3 py-2.5 rounded-lg font-medium text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 {{ request()->routeIs('customer.home') ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-[#60a5fa] font-semibold' : '' }}">
                <i class="fa-solid fa-house w-6 text-[#214fe0] dark:text-[#60a5fa]"></i> Home
            </a>
            <a href="{{ route('customer.about') }}" class="flex items-center px-3 py-2.5 rounded-lg font-medium text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 {{ request()->routeIs('customer.about') ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-[#60a5fa] font-semibold' : '' }}">
                <i class="fa-solid fa-building w-6 text-[#214fe0] dark:text-[#60a5fa]"></i> About Us
            </a>
            <a href="{{ route('customer.products') }}" class="flex items-center px-3 py-2.5 rounded-lg font-medium text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 {{ request()->routeIs('customer.products') ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-[#60a5fa] font-semibold' : '' }}">
                <i class="fa-solid fa-box-open w-6 text-[#214fe0] dark:text-[#60a5fa]"></i> Product Catalog
            </a>
            <a href="{{ route('customer.quotation-builder') }}" class="flex items-center px-3 py-2.5 rounded-lg font-medium text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 {{ request()->routeIs('customer.quotation-builder') ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-[#60a5fa] font-semibold' : '' }}">
                <i class="fa-solid fa-calculator w-6 text-[#214fe0] dark:text-[#60a5fa]"></i> Quotation Builder
            </a>
        </div>
    </header>

    <!-- Main Content Area with Uniform Page Transition -->
    <main id="page-content" class="flex-grow page-transition-wrapper">
        @yield('content')
    </main>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-4 right-4 sm:bottom-5 sm:right-5 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none max-w-[calc(100vw-2rem)] sm:max-w-sm">
        <div class="bg-slate-900 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 border border-slate-700">
            <div id="toast-icon" class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                <i class="fa-solid fa-check"></i>
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
                        <i id="huenics-modal-icon" class="fa-solid fa-triangle-exclamation"></i>
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
                        <li><i class="fa-solid fa-truck text-xs text-blue-400 mr-1.5"></i> <strong>Free Delivery:</strong> Orders ₱20,000.00+ within Metro Manila</li>
                        <li><i class="fa-solid fa-rotate-left text-xs text-blue-400 mr-1.5"></i> <strong>Return & Exchange:</strong> Within 7 days upon delivery</li>
                        <li><i class="fa-solid fa-shield-check text-xs text-blue-400 mr-1.5"></i> <strong>Warranty:</strong> 1–2 Years limited warranty w/o physical damage</li>
                        <li><i class="fa-solid fa-bolt text-xs text-blue-400 mr-1.5"></i> <strong>Replacement:</strong> 1 mo. outright replacement for defective units</li>
                    </ul>
                </div>

                <!-- Col 4: Contact & Site Location -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-white mb-4">Head Office</h3>
                    <div class="space-y-3 text-sm text-slate-400">
                        <p class="flex items-start gap-2.5">
                            <i class="fa-solid fa-location-dot text-blue-400 mt-1"></i>
                            <span class="text-xs leading-relaxed">Unit 916 Avida Towers Intima, 497 Pres. Quirino Ave. Ext. Cor. Zulueta St., Paco, Manila</span>
                        </p>
                        <p class="flex items-center gap-2.5 text-xs">
                            <i class="fa-solid fa-phone text-blue-400"></i>
                            <span>Tel. #8561 6836</span>
                        </p>
                        <p class="flex items-center gap-2.5 text-xs">
                            <i class="fa-solid fa-headset text-blue-400"></i>
                            <span>CS: +63 968 8500720 / Tech: +63 965 6287205</span>
                        </p>
                        <p class="flex items-center gap-2.5 text-xs">
                            <i class="fa-solid fa-envelope text-blue-400"></i>
                            <span>huenicsindustrialsales@gmail.com</span>
                        </p>
                        <p class="flex items-center gap-2.5 text-xs">
                            <i class="fa-solid fa-envelope-open-text text-blue-400"></i>
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
                iconEl.innerHTML = '<i class="fa-solid fa-check"></i>';
            } else {
                iconEl.className = 'w-8 h-8 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold';
                iconEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
            }

            toast.classList.remove('translate-y-20', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');

            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }

        function toggleDarkMode(event) {
            const hasViewTransition = document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (!hasViewTransition) {
                document.documentElement.classList.add('theme-transition');
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('huenics_theme', isDark ? 'dark' : 'light');
                updateThemeIcons();
                setTimeout(() => {
                    document.documentElement.classList.remove('theme-transition');
                }, 350);
                return;
            }

            document.documentElement.classList.add('theme-transitioning');

            const x = event && typeof event.clientX === 'number' ? event.clientX : window.innerWidth / 2;
            const y = event && typeof event.clientY === 'number' ? event.clientY : 40;
            const maxRadius = Math.hypot(
                Math.max(x, window.innerWidth - x),
                Math.max(y, window.innerHeight - y)
            );

            const transition = document.startViewTransition(() => {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('huenics_theme', isDark ? 'dark' : 'light');
                updateThemeIcons();
            });

            transition.ready.then(() => {
                const animation = document.documentElement.animate(
                    {
                        clipPath: [
                            `circle(0px at ${x}px ${y}px)`,
                            `circle(${maxRadius}px at ${x}px ${y}px)`
                        ]
                    },
                    {
                        duration: 420,
                        easing: 'cubic-bezier(0.16, 1, 0.3, 1)',
                        pseudoElement: '::view-transition-new(root)'
                    }
                );

                animation.finished.finally(() => {
                    document.documentElement.classList.remove('theme-transitioning');
                });
            }).catch(() => {
                document.documentElement.classList.remove('theme-transitioning');
            });
        }

        function updateThemeIcons() {
            // Strict CSS rules (html.dark #theme-icon-sun / html:not(.dark) #theme-icon-moon) guarantee
            // exactly one icon is rendered at all times per theme with zero delay or flicker.
        }

        // Client-Side Seamless SPA Navigator (Removes Reload, Smooth Transition)
        window.HuenicsNavigator = {
            progressBar: null,
            isNavigating: false,

            init() {
                this.progressBar = document.getElementById('page-progress-bar');

                // Intercept internal link clicks
                document.addEventListener('click', (e) => {
                    const link = e.target.closest('a');
                    if (!link) return;

                    const href = link.getAttribute('href');
                    if (!href) return;

                    // Exclude non-navigation or external targets
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
                        // Fallback to normal navigation
                    }
                });

                // Handle Back/Forward history buttons
                window.addEventListener('popstate', () => {
                    this.navigate(window.location.href, false);
                });
            },

            async navigate(url, pushState = true) {
                if (this.isNavigating) return;
                this.isNavigating = true;

                this.startProgress();

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'Huenics-SPA',
                            'Accept': 'text/html'
                        }
                    });

                    if (!response.ok) {
                        window.location.href = url;
                        return;
                    }

                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const newContent = doc.getElementById('page-content');
                    const newTitle = doc.querySelector('title')?.textContent || document.title;

                    if (!newContent) {
                        window.location.href = url;
                        return;
                    }

                    const currentContent = document.getElementById('page-content');

                    const swapDom = () => {
                        document.title = newTitle;

                        if (currentContent) {
                            currentContent.innerHTML = newContent.innerHTML;
                            currentContent.classList.remove('page-transition-wrapper', 'page-transition-exit');
                            void currentContent.offsetWidth; // Force reflow
                            currentContent.classList.add('page-transition-wrapper');
                        }

                        if (pushState) {
                            window.history.pushState({}, '', url);
                        }

                        // Update active desktop & mobile navbar links
                        const targetPath = new URL(url, window.location.origin).pathname;
                        this.updateActiveNavLinks(targetPath);

                        // Re-evaluate page scripts
                        this.reexecuteScripts(currentContent);

                        // Re-initialize cart badge
                        if (window.CartManager) {
                            window.CartManager.updateNavBadge();
                        }

                        // Close mobile drawer if open
                        const mobileMenu = document.getElementById('mobile-menu');
                        if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                            mobileMenu.classList.add('hidden');
                        }

                        // Smooth scroll to top
                        window.scrollTo({ top: 0, behavior: 'instant' });

                        // Dispatch page-loaded event for any interactive widgets
                        document.dispatchEvent(new CustomEvent('huenics:page-loaded', { detail: { url } }));
                    };

                    if (document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        document.startViewTransition(() => swapDom());
                    } else {
                        if (currentContent) {
                            currentContent.classList.add('page-transition-exit');
                            setTimeout(() => {
                                swapDom();
                            }, 100);
                        } else {
                            swapDom();
                        }
                    }
                } catch (err) {
                    console.error('SPA Navigation error:', err);
                    window.location.href = url;
                } finally {
                    this.finishProgress();
                    this.isNavigating = false;
                }
            },

            startProgress() {
                if (!this.progressBar) return;
                this.progressBar.style.transition = 'none';
                this.progressBar.style.transform = 'translateX(-100%)';
                this.progressBar.style.opacity = '1';
                void this.progressBar.offsetWidth;
                this.progressBar.style.transition = 'transform 300ms cubic-bezier(0.16, 1, 0.3, 1), opacity 150ms';
                this.progressBar.style.transform = 'translateX(-25%)';
            },

            finishProgress() {
                if (!this.progressBar) return;
                this.progressBar.style.transform = 'translateX(0%)';
                setTimeout(() => {
                    this.progressBar.style.opacity = '0';
                    setTimeout(() => {
                        this.progressBar.style.transform = 'translateX(-100%)';
                    }, 200);
                }, 140);
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
            },

            reexecuteScripts(container) {
                if (!container) return;
                const scripts = container.querySelectorAll('script');
                scripts.forEach(oldScript => {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    newScript.textContent = oldScript.textContent;
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });
            }
        };

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
                icon = 'fa-solid fa-triangle-exclamation',
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
                icon = 'fa-solid fa-circle-info',
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
                this.iconEl.className = icon;

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

        // Initialize badge, theme icon, modal, and SPA navigator on load
        document.addEventListener('DOMContentLoaded', () => {
            CartManager.updateNavBadge();
            updateThemeIcons();
            HuenicsModal.init();
            HuenicsNavigator.init();
        });
    </script>

    @stack('scripts')
</body>
</html>
