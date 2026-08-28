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
        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
        }
        .dark .glass-panel {
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(8px);
        }
        /* Bulletproof single-icon display per theme */
        html.dark #theme-icon-sun { display: inline-block !important; }
        html.dark #theme-icon-moon { display: none !important; }
        html:not(.dark) #theme-icon-sun { display: none !important; }
        html:not(.dark) #theme-icon-moon { display: inline-block !important; }
    </style>
</head>
<body class="flex min-h-full flex-col font-sans bg-slate-50 dark:bg-[#070b14] text-slate-900 dark:text-slate-100 transition-colors duration-200">

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
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <a href="{{ route('customer.home') }}" class="flex items-center gap-2 sm:gap-3 group min-w-0" title="Huenics Industrial Sales Inc.">
                        <div class="border-l-[3px] border-r-[3px] border-[#214fe0] dark:border-[#3b82f6] px-2 py-0.5 text-center shrink-0 bg-blue-50/50 dark:bg-blue-950/60 rounded-sm transition-colors">
                            <div class="text-lg sm:text-2xl font-black tracking-widest text-[#214fe0] dark:text-[#60a5fa] leading-none">HISI</div>
                            <div class="text-[7.5px] sm:text-[9px] font-bold text-blue-900 dark:text-blue-200 tracking-tight whitespace-nowrap mt-0.5">Colors &bull; Techniques &bull; Technology</div>
                        </div>
                        <div class="border-l border-slate-200 dark:border-slate-800 pl-2.5 sm:pl-3 min-w-0 truncate transition-colors">
                            <div class="text-xs sm:text-sm font-black tracking-tight text-slate-900 dark:text-white uppercase leading-none truncate">
                                HUENICS
                            </div>
                            <div class="text-[10px] sm:text-xs font-extrabold tracking-tight text-[#214fe0] dark:text-[#60a5fa] uppercase leading-none mt-0.5 truncate">
                                INDUSTRIAL SALES INC.
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Desktop Nav Links -->
                <nav class="hidden md:flex items-center gap-6 lg:gap-8 text-sm font-medium text-slate-700 dark:text-slate-300">
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
                            onclick="toggleDarkMode()" 
                            class="p-2 rounded-xl text-slate-500 dark:text-amber-300 hover:text-slate-900 dark:hover:text-amber-200 hover:bg-slate-100 dark:hover:bg-slate-800/80 transition focus:outline-none border border-transparent dark:border-slate-800 flex items-center justify-center w-9 h-9" 
                            title="Toggle Light / Dark Theme"
                            aria-label="Toggle Theme">
                        <i id="theme-icon-sun" class="fa-solid fa-sun text-amber-400 text-sm"></i>
                        <i id="theme-icon-moon" class="fa-solid fa-moon text-slate-500 text-sm"></i>
                    </button>

                    <a href="{{ route('customer.quotation-builder') }}" 
                       class="relative inline-flex items-center gap-1.5 sm:gap-2 bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold text-xs sm:text-sm px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl shadow-md hover:shadow-lg transition dark:shadow-[0_0_15px_rgba(33,79,224,0.35)]">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span class="hidden xs:inline sm:inline">Quotation Estimate</span>
                        <span id="nav-cart-count" class="hidden bg-amber-400 text-slate-950 font-black text-[11px] sm:text-xs px-1.5 py-0.2 rounded-full ml-0.5 sm:ml-1">
                            0
                        </span>
                    </a>

                    <!-- Mobile Menu Button -->
                    <button type="button" onclick="toggleMobileMenu()" class="md:hidden p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none" aria-label="Toggle Navigation Menu">
                        <i class="fa-solid fa-bars text-lg sm:text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Nav Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0b0f19] px-4 py-4 space-y-2 shadow-lg transition-colors">
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

    <!-- Main Content Area -->
    <main class="flex-grow">
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

            <!-- Global Support Network Partners (Page 7) -->
            <div class="mt-10 pt-6 border-t border-slate-800/80">
                <div class="text-center sm:text-left mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400">
                        Global Support Network & Technology Partners
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

        function toggleDarkMode() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('huenics_theme', isDark ? 'dark' : 'light');
            updateThemeIcons();
        }

        function updateThemeIcons() {
            // Strict CSS rules (html.dark #theme-icon-sun / html:not(.dark) #theme-icon-moon) guarantee
            // exactly one icon is rendered at all times per theme with zero delay or flicker.
        }

        // Initialize badge and theme icon on load
        document.addEventListener('DOMContentLoaded', () => {
            CartManager.updateNavBadge();
            updateThemeIcons();
        });
    </script>

    @stack('scripts')
</body>
</html>
