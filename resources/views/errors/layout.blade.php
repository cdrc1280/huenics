@extends('layouts.customer')

@section('title')
    @yield('title') - Huenics Industrial Sales Inc.
@endsection

@section('content')
<section class="min-h-[70vh] flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8 bg-slate-50 dark:bg-[#070b14] relative overflow-hidden hisi-geometric-accent transition-colors duration-200">
    <!-- Geometric Background Elements matching Brand Guideline -->
    <div class="absolute top-0 right-0 w-80 h-80 bg-gradient-to-bl from-[#214fe0]/15 dark:from-blue-500/10 via-blue-500/5 to-transparent pointer-events-none"></div>
    <div class="absolute -bottom-12 -left-12 w-80 h-80 pointer-events-none opacity-30 dark:opacity-15" style="background: repeating-linear-gradient(45deg, rgba(33, 79, 224, 0.08), rgba(33, 79, 224, 0.08) 3px, transparent 3px, transparent 12px);"></div>

    <div class="max-w-3xl w-full mx-auto relative z-10">
        <div class="bg-white dark:bg-[#111827] border-2 border-slate-200 dark:border-slate-800 rounded-3xl shadow-xl p-8 sm:p-12 text-center transition-colors duration-200">
            
            <!-- Error Code Pill Badge -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider bg-blue-50 dark:bg-blue-950/70 border border-blue-200 dark:border-blue-800/60 text-[#214fe0] dark:text-[#60a5fa] mb-6 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-[#214fe0] dark:bg-[#3b82f6] animate-pulse"></span>
                @yield('badge', 'HTTP Status Notice')
            </div>

            <!-- Big Icon and Code Display -->
            <div class="flex items-center justify-center gap-4 mb-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 dark:from-[#16203d] dark:to-[#0f172a] border border-blue-200 dark:border-blue-800/60 flex items-center justify-center text-3xl sm:text-4xl text-[#214fe0] dark:text-[#60a5fa] shadow-inner">
                    @yield('icon', '<i class="fa-solid fa-triangle-exclamation"></i>')
                </div>
                <div class="text-4xl sm:text-6xl font-black text-slate-900 dark:text-white tracking-tight font-mono">
                    @yield('code', '404')
                </div>
            </div>

            <!-- Headline & Explanation -->
            <h1 class="text-xl sm:text-3xl font-extrabold text-slate-950 dark:text-white tracking-tight mb-3">
                @yield('heading', 'Page Not Found')
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 max-w-xl mx-auto mb-8 leading-relaxed font-normal">
                @yield('message', 'The requested resource could not be found or has been moved to a new destination.')
            </p>

            <!-- Search Bar to Help User Find Products Directly from Fallback -->
            <div class="max-w-md mx-auto mb-8">
                <form action="{{ route('customer.products') }}" method="GET" class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" 
                           name="search" 
                           placeholder="Search products by keyword, code, or description..." 
                           class="w-full pl-10 pr-24 py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-[#214fe0] focus:outline-none bg-slate-50 dark:bg-[#161f38] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 shadow-inner">
                    <button type="submit" 
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 bg-[#214fe0] hover:bg-[#1a42be] text-white text-xs font-bold px-3 py-1.5 rounded-lg transition shadow-sm">
                        Search
                    </button>
                </form>
            </div>

            <!-- Main Navigation Actions -->
            <div class="flex flex-wrap items-center justify-center gap-3 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                <a href="{{ route('customer.home') }}" 
                   class="inline-flex items-center gap-2 bg-[#214fe0] hover:bg-[#1a42be] text-white font-bold text-xs sm:text-sm px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg dark:shadow-[0_0_15px_rgba(33,79,224,0.35)] transition">
                    <i class="fa-solid fa-house"></i>
                    <span>Return to Home</span>
                </a>

                <a href="{{ route('customer.products') }}" 
                   class="inline-flex items-center gap-2 bg-white dark:bg-[#161f38] hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700 hover:border-[#214fe0] dark:hover:border-[#3b82f6] font-bold text-xs sm:text-sm px-5 py-2.5 rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-boxes-stacked text-[#214fe0] dark:text-[#60a5fa]"></i>
                    <span>Browse Products</span>
                </a>

                <a href="{{ route('customer.quotation-builder') }}" 
                   class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs sm:text-sm px-5 py-2.5 rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-calculator"></i>
                    <span>Quotation Builder</span>
                </a>

                <a href="{{ route('customer.about') }}" 
                   class="inline-flex items-center gap-2 bg-slate-100 dark:bg-[#1f293d] hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs sm:text-sm px-4 py-2.5 rounded-xl transition">
                    <i class="fa-solid fa-envelope"></i>
                    <span>Contact Support</span>
                </a>
            </div>

            <!-- Helpful Suggested Shortcuts -->
            <div class="mt-8 pt-4 text-left border-t border-slate-100 dark:border-slate-800/80">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block text-center mb-2">
                    Popular Product Categories
                </span>
                <div class="flex flex-wrap items-center justify-center gap-1.5">
                    <a href="{{ route('customer.products', ['category' => 'Indoor Downlights']) }}" class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-[#161f38] text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-blue-950/60 hover:text-[#214fe0] dark:hover:text-[#60a5fa] transition border border-slate-200 dark:border-slate-700">
                        Indoor Downlights
                    </a>
                    <a href="{{ route('customer.products', ['category' => 'Tracklights & Ceiling Lamps']) }}" class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-[#161f38] text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-blue-950/60 hover:text-[#214fe0] dark:hover:text-[#60a5fa] transition border border-slate-200 dark:border-slate-700">
                        Tracklights
                    </a>
                    <a href="{{ route('customer.products', ['category' => 'Office & Linear Lights']) }}" class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-[#161f38] text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-blue-950/60 hover:text-[#214fe0] dark:hover:text-[#60a5fa] transition border border-slate-200 dark:border-slate-700">
                        Linear Profiles
                    </a>
                    <a href="{{ route('customer.products', ['category' => 'Pipes & Fittings']) }}" class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-[#161f38] text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-blue-950/60 hover:text-[#214fe0] dark:hover:text-[#60a5fa] transition border border-slate-200 dark:border-slate-700">
                        Pipes & Infrastructure
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
