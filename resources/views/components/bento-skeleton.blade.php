@props([
    'variant' => 'full', // 'full' (or 'page'), 'catalog', 'quotation', 'card'
    'count' => 6,
])

{{-- 
    Bento Grid Pulse Skeleton Component
    Atheros-Grade Asymmetrical Bento Grid with Multi-Tier Pulse & Cascading Shimmer
    Supports: Dark/Light Mode Parity, Zero-Jank GPU Transforms, prefers-reduced-motion
--}}

@if($variant === 'card')
    <div {{ $attributes->merge(['class' => 'bento-skeleton-card bento-pulse relative overflow-hidden rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 bg-white/90 dark:bg-[#111827]/90 shadow-sm transition-colors']) }}>
        <div class="bento-shimmer-wave absolute inset-0 pointer-events-none"></div>
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="h-4 w-28 rounded-md bg-slate-200/80 dark:bg-slate-700/70"></div>
            <div class="h-5 w-16 rounded-full bg-blue-100/60 dark:bg-blue-900/40"></div>
        </div>
        <div class="h-7 w-3/4 rounded-lg bg-slate-200/80 dark:bg-slate-700/70 mb-3"></div>
        <div class="h-4 w-full rounded bg-slate-200/60 dark:bg-slate-800/80 mb-2"></div>
        <div class="h-4 w-4/5 rounded bg-slate-200/60 dark:bg-slate-800/80 mb-4"></div>
        <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
            <div class="h-4 w-20 rounded bg-slate-200/70 dark:bg-slate-800/80"></div>
            <div class="h-8 w-24 rounded-lg bg-blue-100/80 dark:bg-blue-950/80"></div>
        </div>
    </div>

@elseif($variant === 'catalog')
    <div {{ $attributes->merge(['class' => 'bento-skeleton-container space-y-6 w-full animate-fade-in']) }}>
        <!-- Top Bento Spotlight Header -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <!-- Featured Tile (7 cols) -->
            <div class="lg:col-span-7 bento-skeleton-card bento-pulse relative overflow-hidden rounded-2xl p-6 border border-slate-200/80 dark:border-slate-800 bg-white/90 dark:bg-[#111827]/90 shadow-sm" style="animation-delay: 0s;">
                <div class="bento-shimmer-wave absolute inset-0 pointer-events-none"></div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="h-5 w-24 rounded-full bg-blue-100 dark:bg-blue-950/70"></div>
                    <div class="h-5 w-20 rounded-full bg-slate-200/70 dark:bg-slate-800/80"></div>
                </div>
                <div class="h-7 w-2/3 rounded-lg bg-slate-200/90 dark:bg-slate-700/80 mb-3"></div>
                <div class="h-4 w-4/5 rounded bg-slate-200/60 dark:bg-slate-800/80 mb-6"></div>
                <div class="grid grid-cols-3 gap-3 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="h-10 rounded-xl bg-slate-200/60 dark:bg-slate-800/70"></div>
                    <div class="h-10 rounded-xl bg-slate-200/60 dark:bg-slate-800/70"></div>
                    <div class="h-10 rounded-xl bg-blue-100/70 dark:bg-blue-900/40"></div>
                </div>
            </div>

            <!-- Quick Filter & Specs Tile (5 cols) -->
            <div class="lg:col-span-5 bento-skeleton-card bento-pulse relative overflow-hidden rounded-2xl p-6 border border-slate-200/80 dark:border-slate-800 bg-white/90 dark:bg-[#111827]/90 shadow-sm flex flex-col justify-between" style="animation-delay: 0.15s;">
                <div class="bento-shimmer-wave absolute inset-0 pointer-events-none"></div>
                <div>
                    <div class="h-4 w-32 rounded bg-slate-200/80 dark:bg-slate-700/70 mb-3"></div>
                    <div class="h-9 w-full rounded-xl bg-slate-200/60 dark:bg-slate-800/70 mb-3"></div>
                    <div class="flex flex-wrap gap-2">
                        <div class="h-7 w-16 rounded-lg bg-slate-200/70 dark:bg-slate-800/80"></div>
                        <div class="h-7 w-20 rounded-lg bg-slate-200/70 dark:bg-slate-800/80"></div>
                        <div class="h-7 w-14 rounded-lg bg-slate-200/70 dark:bg-slate-800/80"></div>
                        <div class="h-7 w-24 rounded-lg bg-slate-200/70 dark:bg-slate-800/80"></div>
                    </div>
                </div>
                <div class="h-4 w-40 rounded bg-slate-200/50 dark:bg-slate-800/50 mt-4"></div>
            </div>
        </div>

        <!-- Catalog Bento Product Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @for($i = 0; $i < $count; $i++)
                <div class="bento-skeleton-card bento-pulse relative overflow-hidden rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 bg-white/90 dark:bg-[#111827]/90 shadow-sm flex flex-col justify-between" style="animation-delay: {{ ($i * 0.08) }}s;">
                    <div class="bento-shimmer-wave absolute inset-0 pointer-events-none"></div>
                    <div>
                        <!-- Image Container Placeholder -->
                        <div class="w-full h-44 bg-slate-200/70 dark:bg-[#161f38]/90 rounded-xl mb-3 flex items-center justify-center relative overflow-hidden">
                            <div class="w-10 h-10 rounded-xl bg-slate-300/60 dark:bg-slate-700/50"></div>
                            <div class="absolute top-2.5 left-2.5 h-4 w-16 rounded-full bg-slate-300/80 dark:bg-slate-700/80"></div>
                            <div class="absolute top-2.5 right-2.5 h-4 w-14 rounded bg-slate-300/80 dark:bg-slate-700/80"></div>
                        </div>
                        <!-- Title Placeholder -->
                        <div class="h-4.5 w-3/4 rounded bg-slate-200/90 dark:bg-slate-700/80 mb-2"></div>
                        <div class="h-4 w-1/2 rounded bg-slate-200/70 dark:bg-slate-700/60 mb-3"></div>
                        <!-- Description Lines -->
                        <div class="h-3 w-full rounded bg-slate-200/50 dark:bg-slate-800/70 mb-1.5"></div>
                        <div class="h-3 w-4/5 rounded bg-slate-200/50 dark:bg-slate-800/70 mb-3.5"></div>
                    </div>
                    <!-- Footer Actions -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 space-y-3">
                        <div class="flex justify-between items-center">
                            <div class="h-3.5 w-14 rounded bg-slate-200/60 dark:bg-slate-800/70"></div>
                            <div class="h-5 w-28 rounded-full bg-blue-100/70 dark:bg-blue-950/70"></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-8 w-24 rounded-lg bg-slate-200/70 dark:bg-slate-800/80 shrink-0"></div>
                            <div class="h-8 flex-1 rounded-lg bg-blue-600/70 dark:bg-blue-600/60"></div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>

@elseif($variant === 'quotation')
    <div {{ $attributes->merge(['class' => 'bento-skeleton-container w-full animate-fade-in']) }}>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left 8-Cols: Line Items Bento Panel -->
            <div class="lg:col-span-8 space-y-6">
                <div class="bento-skeleton-card bento-pulse relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white/90 dark:bg-[#111827]/90 shadow-sm" style="animation-delay: 0s;">
                    <div class="bento-shimmer-wave absolute inset-0 pointer-events-none"></div>
                    <!-- Header -->
                    <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-[#161f38]/60">
                        <div class="space-y-1.5">
                            <div class="h-5 w-44 rounded-lg bg-slate-200/90 dark:bg-slate-700/80"></div>
                            <div class="h-3 w-64 rounded bg-slate-200/60 dark:bg-slate-800/70"></div>
                        </div>
                        <div class="h-8 w-28 rounded-lg bg-blue-600/70 dark:bg-blue-600/60"></div>
                    </div>
                    <!-- Table Header Placeholder -->
                    <div class="p-3 bg-slate-100/70 dark:bg-[#161f38]/80 border-b border-slate-200 dark:border-slate-800 flex gap-4">
                        <div class="h-4 w-8 rounded bg-slate-200/80 dark:bg-slate-700/80"></div>
                        <div class="h-4 w-1/3 rounded bg-slate-200/80 dark:bg-slate-700/80"></div>
                        <div class="h-4 w-16 rounded bg-slate-200/80 dark:bg-slate-700/80 ml-auto"></div>
                        <div class="h-4 w-16 rounded bg-slate-200/80 dark:bg-slate-700/80"></div>
                        <div class="h-4 w-28 rounded bg-slate-200/80 dark:bg-slate-700/80"></div>
                    </div>
                    <!-- Rows -->
                    <div class="divide-y divide-slate-100 dark:divide-slate-800/80 p-2 space-y-2">
                        @for($r = 0; $r < 4; $r++)
                            <div class="p-3 flex items-center gap-4">
                                <div class="h-4 w-6 rounded bg-slate-200/60 dark:bg-slate-800/70"></div>
                                <div class="flex-1 space-y-1">
                                    <div class="h-4 w-{{ [ '3/4', '2/3', '4/5', '1/2' ][$r] }} rounded bg-slate-200/80 dark:bg-slate-700/80"></div>
                                    <div class="h-3 w-1/3 rounded bg-slate-200/50 dark:bg-slate-800/60"></div>
                                </div>
                                <div class="h-6 w-14 rounded bg-slate-200/60 dark:bg-slate-800/70"></div>
                                <div class="h-8 w-16 rounded-lg bg-slate-200/70 dark:bg-slate-800/80"></div>
                                <div class="h-6 w-24 rounded-full bg-blue-100/60 dark:bg-blue-950/70"></div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Right 4-Cols: Specs & Commercial Summary Bento Panel -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Customer Spec Bento Card -->
                <div class="bento-skeleton-card bento-pulse relative overflow-hidden rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 bg-white/90 dark:bg-[#111827]/90 shadow-sm" style="animation-delay: 0.2s;">
                    <div class="bento-shimmer-wave absolute inset-0 pointer-events-none"></div>
                    <div class="h-4.5 w-36 rounded-lg bg-slate-200/90 dark:bg-slate-700/80 mb-4"></div>
                    <div class="space-y-3">
                        <div class="h-8 w-full rounded-lg bg-slate-200/60 dark:bg-slate-800/70"></div>
                        <div class="h-8 w-full rounded-lg bg-slate-200/60 dark:bg-slate-800/70"></div>
                        <div class="h-8 w-full rounded-lg bg-slate-200/60 dark:bg-slate-800/70"></div>
                    </div>
                </div>

                <!-- Commercial Total Bento Card -->
                <div class="bento-skeleton-card bento-pulse relative overflow-hidden rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 bg-white/90 dark:bg-[#111827]/90 shadow-sm space-y-4" style="animation-delay: 0.35s;">
                    <div class="bento-shimmer-wave absolute inset-0 pointer-events-none"></div>
                    <div class="flex justify-between">
                        <div class="h-4 w-24 rounded bg-slate-200/70 dark:bg-slate-800/70"></div>
                        <div class="h-4 w-20 rounded bg-slate-200/70 dark:bg-slate-800/70"></div>
                    </div>
                    <div class="flex justify-between">
                        <div class="h-4 w-20 rounded bg-slate-200/70 dark:bg-slate-800/70"></div>
                        <div class="h-4 w-16 rounded bg-slate-200/70 dark:bg-slate-800/70"></div>
                    </div>
                    <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center">
                        <div class="h-5 w-28 rounded bg-slate-200/90 dark:bg-slate-700/80"></div>
                        <div class="h-7 w-32 rounded-lg bg-blue-100 dark:bg-blue-950/70"></div>
                    </div>
                    <div class="h-10 w-full rounded-xl bg-blue-600/70 dark:bg-blue-600/60 mt-4"></div>
                </div>
            </div>
        </div>
    </div>

@else
    {{-- Full / Page Master Asymmetrical Bento Grid Pulse Skeleton (Atheros Spec) --}}
    <div {{ $attributes->merge(['class' => 'bento-skeleton-container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full animate-fade-in']) }}>
        
        <!-- Top Context Header Skeleton -->
        <div class="mb-8 space-y-2.5">
            <div class="h-4 w-40 rounded-full bg-blue-100/70 dark:bg-blue-950/70 bento-pulse"></div>
            <div class="h-8 sm:h-10 w-3/4 max-w-xl rounded-xl bg-slate-200/90 dark:bg-slate-700/80 bento-pulse"></div>
            <div class="h-4 w-2/3 max-w-lg rounded-md bg-slate-200/60 dark:bg-slate-800/70 bento-pulse"></div>
        </div>

        <!-- Asymmetric Bento Grid (12-Columns with Staggered Visual Weighting) -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-stretch">
            
            <!-- Cell 1: Primary Showcase / Hero Preview (7 cols) -->
            <div class="md:col-span-12 lg:col-span-7 bento-skeleton-card bento-pulse relative overflow-hidden rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 bg-white/95 dark:bg-[#0e1526]/95 shadow-sm" style="animation-delay: 0s;">
                <div class="bento-shimmer-wave absolute inset-0 pointer-events-none"></div>
                
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-blue-500/80 animate-ping"></div>
                        <div class="h-4 w-24 rounded-full bg-blue-100 dark:bg-blue-950/80"></div>
                    </div>
                    <div class="h-5 w-20 rounded-md bg-slate-200/70 dark:bg-slate-800/80"></div>
                </div>

                <div class="h-8 w-4/5 rounded-xl bg-slate-200/90 dark:bg-slate-700/80 mb-3"></div>
                <div class="h-4 w-3/5 rounded bg-slate-200/60 dark:bg-slate-800/70 mb-6"></div>

                <!-- Preview Canvas Geometric Placeholder -->
                <div class="w-full aspect-[16/9] rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200/70 dark:from-[#161f38] dark:to-[#0c1220] border border-slate-200/70 dark:border-slate-800/80 p-4 flex flex-col justify-between mb-6 relative overflow-hidden">
                    <div class="flex justify-between items-center">
                        <div class="h-5 w-28 rounded-lg bg-slate-300/70 dark:bg-slate-700/60"></div>
                        <div class="h-6 w-16 rounded-full bg-blue-500/20"></div>
                    </div>
                    <div class="flex items-center justify-center">
                        <div class="w-16 h-16 rounded-2xl bg-slate-300/50 dark:bg-slate-700/40 flex items-center justify-center">
                            <div class="w-8 h-8 rounded-lg bg-slate-400/50 dark:bg-slate-600/50"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-3 flex-1 rounded-full bg-slate-300/60 dark:bg-slate-700/50"></div>
                        <div class="h-3 w-16 rounded-full bg-blue-500/30"></div>
                    </div>
                </div>

                <!-- Bottom Spec Tag Rails -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="h-7 w-28 rounded-xl bg-slate-200/70 dark:bg-slate-800/80"></div>
                    <div class="h-7 w-36 rounded-xl bg-slate-200/70 dark:bg-slate-800/80"></div>
                    <div class="h-7 w-24 rounded-xl bg-blue-100/70 dark:bg-blue-950/70"></div>
                </div>
            </div>

            <!-- Cell 2: Real-time Telemetry & KPI Card (5 cols) -->
            <div class="md:col-span-6 lg:col-span-5 bento-skeleton-card bento-pulse relative overflow-hidden rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 bg-white/95 dark:bg-[#0e1526]/95 shadow-sm flex flex-col justify-between" style="animation-delay: 0.15s;">
                <div class="bento-shimmer-wave absolute inset-0 pointer-events-none"></div>

                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-2xl bg-blue-100 dark:bg-blue-950/80 flex items-center justify-center">
                            <div class="w-5 h-5 rounded-lg bg-blue-500/60"></div>
                        </div>
                        <div class="h-5 w-24 rounded-full bg-emerald-100/70 dark:bg-emerald-950/60"></div>
                    </div>
                    <div class="h-4 w-32 rounded bg-slate-200/70 dark:bg-slate-800/70 mb-2"></div>
                    <div class="h-10 w-48 rounded-xl bg-slate-200/90 dark:bg-slate-700/80 mb-4"></div>
                    <div class="h-3.5 w-3/4 rounded bg-slate-200/60 dark:bg-slate-800/60 mb-6"></div>

                    <!-- Mini Sparkline Frequency Bars -->
                    <div class="flex items-end gap-2 h-16 pt-2 px-2 rounded-xl bg-slate-50 dark:bg-[#161f38]/50 border border-slate-200/50 dark:border-slate-800/50">
                        <div class="flex-1 bg-blue-500/30 dark:bg-blue-500/40 rounded-t h-[40%]"></div>
                        <div class="flex-1 bg-blue-500/40 dark:bg-blue-500/50 rounded-t h-[65%]"></div>
                        <div class="flex-1 bg-blue-500/50 dark:bg-blue-500/60 rounded-t h-[85%]"></div>
                        <div class="flex-1 bg-[#214fe0] dark:bg-[#3b82f6] rounded-t h-[100%]"></div>
                        <div class="flex-1 bg-blue-500/60 dark:bg-blue-500/70 rounded-t h-[75%]"></div>
                        <div class="flex-1 bg-blue-500/40 dark:bg-blue-500/50 rounded-t h-[50%]"></div>
                    </div>
                </div>

                <div class="pt-5 mt-5 border-t border-slate-100 dark:border-slate-800/80 flex justify-between items-center">
                    <div class="h-4 w-28 rounded bg-slate-200/60 dark:bg-slate-800/70"></div>
                    <div class="h-4 w-16 rounded bg-slate-200/80 dark:bg-slate-700/80"></div>
                </div>
            </div>

            <!-- Cell 3: Interactive Parameters & Filters Rail (5 cols) -->
            <div class="md:col-span-6 lg:col-span-5 bento-skeleton-card bento-pulse relative overflow-hidden rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 bg-white/95 dark:bg-[#0e1526]/95 shadow-sm space-y-4" style="animation-delay: 0.3s;">
                <div class="bento-shimmer-wave absolute inset-0 pointer-events-none"></div>

                <div class="flex items-center justify-between">
                    <div class="h-5 w-36 rounded-lg bg-slate-200/90 dark:bg-slate-700/80"></div>
                    <div class="h-4 w-12 rounded bg-slate-200/60 dark:bg-slate-800/70"></div>
                </div>

                <div class="space-y-3 pt-2">
                    <div class="h-10 w-full rounded-xl bg-slate-100 dark:bg-[#161f38] border border-slate-200/60 dark:border-slate-800/60 flex items-center px-3 gap-2">
                        <div class="w-4 h-4 rounded-full bg-slate-300/70 dark:bg-slate-700/60"></div>
                        <div class="h-3.5 w-40 rounded bg-slate-200/80 dark:bg-slate-700/70"></div>
                    </div>
                    <div class="h-10 w-full rounded-xl bg-slate-100 dark:bg-[#161f38] border border-slate-200/60 dark:border-slate-800/60 flex items-center px-3 gap-2">
                        <div class="w-4 h-4 rounded-full bg-slate-300/70 dark:bg-slate-700/60"></div>
                        <div class="h-3.5 w-32 rounded bg-slate-200/80 dark:bg-slate-700/70"></div>
                    </div>
                </div>

                <div class="pt-2">
                    <div class="h-11 w-full rounded-xl bg-gradient-to-r from-[#214fe0]/80 to-[#3b82f6]/80 shadow-sm"></div>
                </div>
            </div>

            <!-- Cell 4: Structured Data Breakdown & Line Rows (7 cols) -->
            <div class="md:col-span-12 lg:col-span-7 bento-skeleton-card bento-pulse relative overflow-hidden rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 bg-white/95 dark:bg-[#0e1526]/95 shadow-sm" style="animation-delay: 0.45s;">
                <div class="bento-shimmer-wave absolute inset-0 pointer-events-none"></div>

                <div class="flex items-center justify-between mb-5">
                    <div class="space-y-1">
                        <div class="h-5 w-44 rounded-lg bg-slate-200/90 dark:bg-slate-700/80"></div>
                        <div class="h-3 w-56 rounded bg-slate-200/60 dark:bg-slate-800/70"></div>
                    </div>
                    <div class="h-7 w-20 rounded-full bg-slate-100 dark:bg-slate-800/80"></div>
                </div>

                <!-- Structured Rows -->
                <div class="space-y-3">
                    <div class="p-3 rounded-xl bg-slate-50/80 dark:bg-[#161f38]/60 border border-slate-100 dark:border-slate-800/60 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100/80 dark:bg-blue-950/80"></div>
                            <div class="space-y-1">
                                <div class="h-3.5 w-48 rounded bg-slate-200/80 dark:bg-slate-700/80"></div>
                                <div class="h-2.5 w-24 rounded bg-slate-200/50 dark:bg-slate-800/60"></div>
                            </div>
                        </div>
                        <div class="h-5 w-20 rounded-full bg-blue-100/60 dark:bg-blue-950/60"></div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50/80 dark:bg-[#161f38]/60 border border-slate-100 dark:border-slate-800/60 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-200/80 dark:bg-slate-700/80"></div>
                            <div class="space-y-1">
                                <div class="h-3.5 w-40 rounded bg-slate-200/80 dark:bg-slate-700/80"></div>
                                <div class="h-2.5 w-28 rounded bg-slate-200/50 dark:bg-slate-800/60"></div>
                            </div>
                        </div>
                        <div class="h-5 w-24 rounded-full bg-emerald-100/60 dark:bg-emerald-950/60"></div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50/80 dark:bg-[#161f38]/60 border border-slate-100 dark:border-slate-800/60 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-200/80 dark:bg-slate-700/80"></div>
                            <div class="space-y-1">
                                <div class="h-3.5 w-52 rounded bg-slate-200/80 dark:bg-slate-700/80"></div>
                                <div class="h-2.5 w-20 rounded bg-slate-200/50 dark:bg-slate-800/60"></div>
                            </div>
                        </div>
                        <div class="h-5 w-16 rounded-full bg-slate-200/70 dark:bg-slate-800/70"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endif
