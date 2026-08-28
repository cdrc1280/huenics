<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 text-slate-900 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Huenics Industrial Supply Corp.') - Industrial & Construction Supplies</title>

    <!-- Tailwind CSS (Vite + CDN fallback) -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#0f172a',
                        },
                        amber: {
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
        }
    </style>
</head>
<body class="flex min-h-full flex-col font-sans bg-slate-50">

    <!-- Top Banner -->
    <div class="bg-slate-900 text-slate-300 text-xs py-2 px-3 sm:px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-[11px] sm:text-xs">
                <span><i class="fa-solid fa-phone text-blue-400 mr-1"></i> +63 (2) 8123 4567</span>
                <span class="hidden sm:inline"><i class="fa-solid fa-envelope text-blue-400 mr-1"></i> sales@huenics.com</span>
                <span class="hidden lg:inline"><i class="fa-solid fa-location-dot text-blue-400 mr-1"></i> Mandaluyong City, Metro Manila</span>
            </div>
            <div class="flex items-center gap-3 ml-auto sm:ml-0 text-[11px] sm:text-xs">
                <span class="bg-emerald-500/20 text-emerald-300 px-2 py-0.5 rounded font-medium border border-emerald-500/30 whitespace-nowrap">
                    <i class="fa-solid fa-check mr-1"></i> 12% VAT Compliant
                </span>
                <a href="{{ url('/admin/login') }}" class="text-slate-300 hover:text-white transition flex items-center gap-1 font-medium whitespace-nowrap">
                    <i class="fa-solid fa-lock text-xs"></i> Employee Login
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 sm:h-20 gap-2">
                <!-- Logo -->
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <a href="{{ route('customer.home') }}" class="flex items-center gap-2 sm:gap-3 group min-w-0">
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-tr from-blue-700 via-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold text-lg sm:text-xl shadow-md shadow-blue-500/20 group-hover:scale-105 transition transform shrink-0">
                            <i class="fa-solid fa-cubes"></i>
                        </div>
                        <div class="min-w-0 truncate">
                            <div class="text-base sm:text-xl font-extrabold tracking-tight text-slate-900 leading-none truncate">
                                HUENICS <span class="text-blue-600 font-black">INDUSTRIAL</span>
                            </div>
                            <div class="text-[9px] sm:text-xs font-semibold text-slate-500 tracking-wider uppercase mt-0.5 truncate">
                                Supply & Engineering Distribution
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Desktop Nav Links -->
                <nav class="hidden md:flex items-center gap-6 lg:gap-8 text-sm font-medium text-slate-700">
                    <a href="{{ route('customer.home') }}" class="hover:text-blue-600 transition whitespace-nowrap {{ request()->routeIs('customer.home') ? 'text-blue-600 font-semibold' : '' }}">
                        Home
                    </a>
                    <a href="{{ route('customer.about') }}" class="hover:text-blue-600 transition whitespace-nowrap {{ request()->routeIs('customer.about') ? 'text-blue-600 font-semibold' : '' }}">
                        About Us
                    </a>
                    <a href="{{ route('customer.products') }}" class="hover:text-blue-600 transition whitespace-nowrap {{ request()->routeIs('customer.products') ? 'text-blue-600 font-semibold' : '' }}">
                        Product Catalog
                    </a>
                    <a href="{{ route('customer.quotation-builder') }}" class="hover:text-blue-600 transition whitespace-nowrap {{ request()->routeIs('customer.quotation-builder') ? 'text-blue-600 font-semibold' : '' }}">
                        Quotation Builder
                    </a>
                </nav>

                <!-- Action / Cart Button -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <a href="{{ route('customer.quotation-builder') }}" 
                       class="relative inline-flex items-center gap-1.5 sm:gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm px-2.5 sm:px-4 py-2 sm:py-2.5 rounded-lg shadow-sm hover:shadow transition">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span class="hidden xs:inline sm:inline">Quotation Estimate</span>
                        <span id="nav-cart-count" class="hidden bg-amber-400 text-slate-900 font-black text-[11px] sm:text-xs px-1.5 py-0.2 rounded-full ml-0.5 sm:ml-1">
                            0
                        </span>
                    </a>

                    <!-- Mobile Menu Button -->
                    <button type="button" onclick="toggleMobileMenu()" class="md:hidden p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none" aria-label="Toggle Navigation Menu">
                        <i class="fa-solid fa-bars text-lg sm:text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Nav Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 py-4 space-y-2 shadow-lg">
            <a href="{{ route('customer.home') }}" class="flex items-center px-3 py-2.5 rounded-lg font-medium text-sm text-slate-700 hover:bg-slate-100 {{ request()->routeIs('customer.home') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                <i class="fa-solid fa-house w-6 text-blue-600"></i> Home
            </a>
            <a href="{{ route('customer.about') }}" class="flex items-center px-3 py-2.5 rounded-lg font-medium text-sm text-slate-700 hover:bg-slate-100 {{ request()->routeIs('customer.about') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                <i class="fa-solid fa-building w-6 text-blue-600"></i> About Us
            </a>
            <a href="{{ route('customer.products') }}" class="flex items-center px-3 py-2.5 rounded-lg font-medium text-sm text-slate-700 hover:bg-slate-100 {{ request()->routeIs('customer.products') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                <i class="fa-solid fa-box-open w-6 text-blue-600"></i> Product Catalog
            </a>
            <a href="{{ route('customer.quotation-builder') }}" class="flex items-center px-3 py-2.5 rounded-lg font-medium text-sm text-slate-700 hover:bg-slate-100 {{ request()->routeIs('customer.quotation-builder') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                <i class="fa-solid fa-calculator w-6 text-blue-600"></i> Quotation Builder
            </a>
            <a href="{{ url('/admin/login') }}" class="flex items-center px-3 py-2.5 rounded-lg font-medium text-sm text-slate-500 hover:bg-slate-100 border-t border-slate-100 mt-2 pt-2">
                <i class="fa-solid fa-lock w-6 text-slate-400"></i> Employee Login
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
                        <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-lg">
                            <i class="fa-solid fa-cubes"></i>
                        </div>
                        <span class="text-lg font-bold text-white tracking-tight">HUENICS INDUSTRIAL</span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Huenics Industrial Supply Corp. (HISI) is a leading distributor and direct importer of engineering, electrical, plumbing, piping, structural steel, and hardware supplies in the Philippines.
                    </p>
                    <div class="text-xs text-slate-500">
                        BIR TIN: 009-876-543-000-VAT &bull; SEC Registered
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
                        <li><a href="{{ url('/admin/login') }}" class="hover:text-blue-400 transition">Staff Portal</a></li>
                    </ul>
                </div>

                <!-- Col 3: Product Categories -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-white mb-4">Product Lines</h3>
                    <ul class="space-y-2.5 text-sm text-slate-400">
                        <li><i class="fa-solid fa-angle-right text-xs text-blue-500 mr-1.5"></i> Pipes, Fittings & Valves</li>
                        <li><i class="fa-solid fa-angle-right text-xs text-blue-500 mr-1.5"></i> Deformed Steel & Structural Bars</li>
                        <li><i class="fa-solid fa-angle-right text-xs text-blue-500 mr-1.5"></i> Submersible & Sewage Pumps</li>
                        <li><i class="fa-solid fa-angle-right text-xs text-blue-500 mr-1.5"></i> HDPE & Electrical Conduits</li>
                        <li><i class="fa-solid fa-angle-right text-xs text-blue-500 mr-1.5"></i> Plumbing & Sanitary Fixtures</li>
                    </ul>
                </div>

                <!-- Col 4: Contact & Site Location -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-white mb-4">Head Office</h3>
                    <div class="space-y-3 text-sm text-slate-400">
                        <p class="flex items-start gap-2.5">
                            <i class="fa-solid fa-location-dot text-blue-400 mt-1"></i>
                            <span>2F Starmall EDSA-Shaw, Mandaluyong City, Metro Manila, 1550 Philippines</span>
                        </p>
                        <p class="flex items-center gap-2.5">
                            <i class="fa-solid fa-phone text-blue-400"></i>
                            <span>+63 (2) 8123 4567 / 0906-144-2553</span>
                        </p>
                        <p class="flex items-center gap-2.5">
                            <i class="fa-solid fa-envelope text-blue-400"></i>
                            <span>sales@huenics.com</span>
                        </p>
                        <p class="flex items-center gap-2.5">
                            <i class="fa-solid fa-clock text-blue-400"></i>
                            <span>Mon - Sat: 8:00 AM - 5:00 PM</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-slate-800 text-xs text-slate-500 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p>&copy; {{ date('Y') }} Huenics Industrial Supply Corp. All rights reserved.</p>
                <p>Philippine Standard 12% VAT Applied to All Document Calculations.</p>
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

        // Initialize badge on load
        document.addEventListener('DOMContentLoaded', () => {
            CartManager.updateNavBadge();
        });
    </script>

    @stack('scripts')
</body>
</html>
