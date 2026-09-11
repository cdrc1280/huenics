@extends('layouts.customer')

@section('title', 'About Us - Huenics Industrial Sales Inc.')

@section('content')
    <!-- About Header Hero (PDF Design: Crisp White in Light / Sleek Obsidian in Dark) -->
    <section
        class="ambient-mesh-hero lg:py-18 hisi-geometric-accent relative overflow-hidden border-b border-slate-200 bg-white py-14 transition-colors duration-200 dark:border-slate-800/80 dark:bg-[#070b14]">
        <div class="pointer-events-none absolute -bottom-10 -left-10 h-64 w-64 opacity-30 dark:opacity-20"
            style="background: repeating-linear-gradient(45deg, rgba(33, 79, 224, 0.08), rgba(33, 79, 224, 0.08) 3px, transparent 3px, transparent 12px);">
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="animate-fade-in-up max-w-3xl space-y-3">
                <div
                    class="inline-flex items-center gap-2 rounded-md border border-blue-200 bg-blue-50/80 px-2.5 py-1 font-mono text-[11px] font-bold uppercase tracking-wider text-[#214fe0] dark:border-blue-800/60 dark:bg-blue-950/40 dark:text-[#60a5fa]">
                    <svg class="h-3.5 w-3.5 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <span>Company Profile &bull; HISI Engineering</span>
                </div>
                <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-5xl dark:text-white">
                    About Huenics Industrial Sales Inc.
                </h1>
                <p class="text-sm font-black uppercase tracking-widest text-[#214fe0] sm:text-base dark:text-[#60a5fa]">
                    Colors &bull; Techniques &bull; Technology
                </p>
                <p class="pt-1 text-sm font-normal leading-relaxed text-slate-600 sm:text-base dark:text-slate-300">
                    Emerged from the strength in combining technical expertise to meet customers' demands in providing
                    innovative and reliable quality products. Committed to go beyond expectations.
                </p>
            </div>
        </div>
    </section>

    <!-- About Us Story & Heritage -->
    <section class="bg-white py-16 transition-colors duration-200 dark:bg-[#0a0e1a]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-12">
                <div class="space-y-5 lg:col-span-6">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">Our Story &
                        Commitment</span>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                        "Our Focus: You First..."
                    </h2>
                    <div
                        class="rounded-r-xl border-l-4 border-blue-600 bg-blue-50 p-4 shadow-sm dark:border-[#3b82f6] dark:bg-[#111827]">
                        <p class="text-sm font-bold italic text-blue-950 dark:text-blue-200">
                            "Focus on Pursuing Quality & Speed of Delivery."
                        </p>
                    </div>
                    <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        It has grown from a single man to a whole team of qualified employees after we identified the needs
                        of the market. Striving for excellence in all areas of our business, systems, procedures, policies,
                        and practices; utilized to ensure compliance with our legal obligations.
                    </p>
                    <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        Seeking new ideas, techniques, and innovations with energy efficiency and alternative energy
                        generation is paramount to ensuring longevity for the industry.
                    </p>

                    <div class="grid grid-cols-2 gap-3 pt-2 sm:grid-cols-3">
                        <div
                            class="card-interactive rounded-xl border border-slate-200 bg-slate-50 p-3.5 text-center dark:border-slate-800 dark:bg-[#111827]">
                            <div class="text-xl font-black text-[#214fe0] dark:text-[#60a5fa]">Citizen</div>
                            <div class="mt-1 text-[11px] font-bold text-slate-900 dark:text-slate-200">Japan C.O.B</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Exacting Color Temp</div>
                        </div>
                        <div
                            class="card-interactive rounded-xl border border-slate-200 bg-slate-50 p-3.5 text-center dark:border-slate-800 dark:bg-[#111827]">
                            <div class="font-mono text-xl font-black tabular-nums text-[#214fe0] dark:text-[#60a5fa]">1–2
                                Yrs</div>
                            <div class="mt-1 text-[11px] font-bold text-slate-900 dark:text-slate-200">Warranty</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Limited Warranty</div>
                        </div>
                        <div
                            class="card-interactive rounded-xl border border-slate-200 bg-slate-50 p-3.5 text-center dark:border-slate-800 dark:bg-[#111827]">
                            <div class="font-mono text-xl font-black tabular-nums text-[#214fe0] dark:text-[#60a5fa]">₱
                                20,000+</div>
                            <div class="mt-1 text-[11px] font-bold text-slate-900 dark:text-slate-200">Free Delivery</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Metro Manila</div>
                        </div>
                    </div>
                </div>

                <!-- Core Capabilities: Color, Techniques, Technology -->
                <div class="space-y-4 lg:col-span-6">
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-[#0b101f] divide-y divide-slate-200 dark:divide-slate-800">
                        <div class="p-6 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                            <div class="flex items-start gap-4">
                                <svg class="mt-0.5 h-6 w-6 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 21a4 4 0 01-4-4 5 5 0 013-4.582V5a2 2 0 012-2h4a2 2 0 012 2v7.418c1.378.86 2.296 2.378 2.375 4.12A4 4 0 0113 21H7z" />
                                </svg>
                                <div>
                                    <span
                                        class="font-mono text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Our
                                        Unique Feature</span>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Right Color</h3>
                                    <p class="mt-1.5 text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                                        In the world of Commercial &amp; Industrial, color is everything. The importance of color
                                        and its impact on these industries cannot be overstated. Whether it is for exclusive or
                                        simple projects, Huenics LED Technology achieves exacting color temperatures and
                                        rendering—allowing customers to get the best possible illumination.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                            <div class="flex items-start gap-4">
                                <svg class="mt-0.5 h-6 w-6 shrink-0 text-[#214fe0] dark:text-[#60a5fa]" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <div>
                                    <span
                                        class="font-mono text-[10px] font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">Our
                                        Passion</span>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Utilizing Techniques</h3>
                                    <p class="mt-1.5 text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                                        The moment the client speaks, we listen. Huenics products are designed to the customer's
                                        greatest advantage—this commitment is further demonstrated by the continuous development
                                        of techniques.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                            <div class="flex items-start gap-4">
                                <svg class="mt-0.5 h-6 w-6 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 3v2m6-2v2M9 19v2m6-2v2M3 9h2m-2 6h2m14-6h2m-2 6h2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                </svg>
                                <div>
                                    <span
                                        class="font-mono text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Our
                                        Pride</span>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Stand Out Technology</h3>
                                    <p class="mt-1.5 text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                                        There’s more than just cost—there’s also efficiency, which draws in customers and
                                        determines whether they return. Offering unmatched quality and efficiency, Huenics is
                                        becoming the option of choice for customers interested in value engineering and creating
                                        an ideal brightest atmosphere.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Goals & Guiding Principles -->
    <section
        class="border-t border-slate-200 bg-slate-50 py-16 transition-colors duration-200 dark:border-slate-800 dark:bg-[#070b14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12">
                <!-- Strategic Performance Goals -->
                <div class="space-y-6 lg:col-span-5">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">What
                            Drives Us</span>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                            Our Strategic Goals
                        </h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Five bedrock metrics that guide our
                            service execution.</p>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-[#0b101f] divide-y divide-slate-100 dark:divide-slate-800/80">
                        <div class="flex items-center justify-between p-3.5 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-xs font-black text-[#214fe0] dark:text-[#60a5fa]">[Q]</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white">Quality</span>
                            </div>
                            <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400">Unmatched precision</span>
                        </div>
                        <div class="flex items-center justify-between p-3.5 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-xs font-black text-emerald-600 dark:text-emerald-400">[S]</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white">Speed</span>
                            </div>
                            <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400">Prompt fulfillment</span>
                        </div>
                        <div class="flex items-center justify-between p-3.5 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-xs font-black text-blue-600 dark:text-blue-400">[D]</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white">Dependable</span>
                            </div>
                            <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400">Consistent execution</span>
                        </div>
                        <div class="flex items-center justify-between p-3.5 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-xs font-black text-amber-500 dark:text-amber-400">[F]</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white">Flexibility</span>
                            </div>
                            <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400">Custom indent orders</span>
                        </div>
                        <div class="flex items-center justify-between p-3.5 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-xs font-black text-slate-700 dark:text-slate-300">[C]</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white">Cost</span>
                            </div>
                            <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400">Value engineering &amp; wholesale pricing</span>
                        </div>
                    </div>
                </div>

                <!-- Guiding Principles -->
                <div class="space-y-6 lg:col-span-7">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">Company
                            Culture</span>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                            Guiding Principles
                        </h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">The foundational pillars our team lives
                            by every day.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3">
                        @php
                            $principles = [
                                [
                                    'svg' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>',
                                    'title' => 'Partnership',
                                ],
                                [
                                    'svg' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>',
                                    'title' => 'Make a Difference Every Day',
                                ],
                                [
                                    'svg' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
                                    'title' => 'Uniqueness',
                                ],
                                [
                                    'svg' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
                                    'title' => 'Continuous Learning',
                                ],
                                [
                                    'svg' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
                                    'title' => 'Long-term Vision',
                                ],
                                [
                                    'svg' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>',
                                    'title' => 'Act with Integrity',
                                ],
                                [
                                    'svg' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>',
                                    'title' => 'Passion',
                                ],
                                [
                                    'svg' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>',
                                    'title' => 'Setting Industry Standards',
                                ],
                                [
                                    'svg' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>',
                                    'title' => 'Innovation',
                                ],
                                [
                                    'svg' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
                                    'title' => 'Courageous Heart',
                                ],
                                [
                                    'svg' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>',
                                    'title' => 'Be the Next',
                                ],
                                [
                                    'svg' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
                                    'title' => 'Build a Foundation of Trust',
                                ],
                            ];
                        @endphp
                        @foreach ($principles as $p)
                            <div
                                class="card-interactive flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-[#111827]">
                                <svg class="h-4 w-4 shrink-0 text-[#214fe0] dark:text-[#60a5fa]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    {!! $p['svg'] !!}
                                </svg>
                                <span
                                    class="text-xs font-semibold leading-snug text-slate-800 dark:text-slate-200">{{ $p['title'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Lighting Clinic & Technical Maintenance -->
    <section
        class="border-t border-slate-200 bg-white py-16 transition-colors duration-200 dark:border-slate-800 dark:bg-[#0a0e1a]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-8 shadow-sm transition-colors duration-200 sm:p-10 dark:border-slate-800 dark:bg-[#0b101f]">

                <div class="relative z-10 max-w-3xl space-y-4">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-md border border-amber-400/40 bg-amber-400/10 px-2.5 py-1 font-mono text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg> Specialized Engineering Services
                    </span>
                    <h2 class="text-2xl font-black tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                        LIGHTING CLINIC <span class="text-amber-500 dark:text-amber-400">&bull;</span> Enercon
                    </h2>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#214fe0] sm:text-sm dark:text-blue-300">
                        Repair &bull; Upgrade &bull; Retrofit
                    </p>
                    <p class="text-xs leading-relaxed text-slate-600 sm:text-sm dark:text-slate-300">
                        <strong class="text-slate-900 dark:text-white">Enercon (Energy Conservation)</strong> is our
                        dedicated energy management and lighting cost analysis program. We provide specialized technical
                        services to extend the lifespan of your fixtures and maximize energy efficiency:
                    </p>
                </div>

                <!-- 3-Column Engineering Track -->
                <div class="relative z-10 mt-6 overflow-hidden rounded-xl border border-slate-200 bg-slate-50/70 shadow-sm dark:border-slate-800 dark:bg-[#070b14]">
                    <div class="grid grid-cols-1 divide-y divide-slate-200 md:grid-cols-3 md:divide-y-0 md:divide-x dark:divide-slate-800">
                        <div class="p-6 transition-colors hover:bg-white/50 dark:hover:bg-slate-900/40">
                            <div
                                class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                                <svg class="h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                                </svg> <span>We Repair</span>
                            </div>
                            <p class="mt-2 text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                                We repair different kinds of fixtures whether it is Downlights, Tracklights, Highbays, etc. as
                                long as it has its own major parts.
                            </p>
                        </div>

                        <div class="p-6 transition-colors hover:bg-white/50 dark:hover:bg-slate-900/40">
                            <div
                                class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                <svg class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg> <span>We Upgrade</span>
                            </div>
                            <p class="mt-2 text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                                We upgrade existing LED lights into high-quality, high-efficiency LED C.O.B Chips with improved
                                thermal dissipation.
                            </p>
                        </div>

                        <div class="p-6 transition-colors hover:bg-white/50 dark:hover:bg-slate-900/40">
                            <div
                                class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">
                                <svg class="h-4 w-4 shrink-0 text-[#214fe0] dark:text-[#60a5fa]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg> <span>We Retrofit</span>
                            </div>
                            <p class="mt-2 text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                                We upgrade and retrofit old fixtures or traditional lighting systems and convert them into
                                high-performance LED C.O.B systems.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Global Support Network -->
    <section
        class="border-t border-slate-200 bg-slate-50 py-16 transition-colors duration-200 dark:border-slate-800 dark:bg-[#070b14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-2xl text-center">
                <span class="text-xs font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">Technology
                    Ecosystem</span>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                    Global Support Network
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    We partner with tier-1 international component manufacturers to engineer commercial fixtures that
                    endure.
                </p>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-[#0b101f]">
                <div class="grid grid-cols-1 divide-y divide-slate-200 sm:grid-cols-2 sm:divide-y-0 sm:divide-x lg:grid-cols-4 dark:divide-slate-800">
                    <div class="p-6 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                        <div class="font-mono text-[11px] font-bold uppercase tracking-wider text-[#214fe0] dark:text-[#60a5fa]">
                            LED Solutions
                        </div>
                        <div class="mt-3 space-y-1.5 text-sm font-bold text-slate-800 dark:text-slate-200">
                            <div>Citizen <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400">(Micro HumanTech Japan)</span></div>
                            <div>OSRAM</div>
                            <div>Lumileds</div>
                        </div>
                    </div>

                    <div class="p-6 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                        <div class="font-mono text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                            Thermal &amp; Optical
                        </div>
                        <div class="mt-3 space-y-1.5 text-sm font-bold text-slate-800 dark:text-slate-200">
                            <div>Khatod Optical Solutions</div>
                            <div>MechaTronix</div>
                            <div>Darkoo</div>
                        </div>
                    </div>

                    <div class="p-6 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                        <div class="font-mono text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                            Driver Solutions
                        </div>
                        <div class="mt-3 space-y-1.5 text-sm font-bold text-slate-800 dark:text-slate-200">
                            <div>Philips LED Driver</div>
                            <div>Inventronics</div>
                            <div>Hyperion Technology</div>
                            <div>DONE</div>
                        </div>
                    </div>

                    <div class="p-6 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                        <div class="font-mono text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                            Holders &amp; Connectors
                        </div>
                        <div class="mt-3 space-y-1.5 text-sm font-bold text-slate-800 dark:text-slate-200">
                            <div>Molex</div>
                            <div>BJB <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400">(Technology for Light)</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Corporate Details & Contact Card -->
    <section
        class="border-t border-slate-200 bg-white py-16 transition-colors duration-200 dark:border-slate-800 dark:bg-[#0a0e1a]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                class="relative grid grid-cols-1 items-center gap-8 overflow-hidden rounded-2xl border border-slate-800 bg-[#080d1a] p-8 text-white shadow-lg sm:p-12 lg:grid-cols-12">
                <!-- Diagonal pinstripes from brand styling -->
                <div class="pointer-events-none absolute inset-0 opacity-10"
                    style="background: repeating-linear-gradient(-45deg, #ffffff, #ffffff 2px, transparent 2px, transparent 12px);">
                </div>

                <div class="relative z-10 space-y-4 lg:col-span-7">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-200">Head Office & Commercial
                        Desk</span>
                    <h2 class="text-2xl font-black tracking-tight sm:text-3xl">
                        Partner With Huenics Industrial Sales Inc.
                    </h2>
                    <p class="text-xs leading-relaxed text-slate-200 sm:text-sm">
                        Reach out for commercial lighting layout consulting, custom indent orders, sample demonstrations, or
                        competitive project pricing.
                    </p>
                    <div class="flex flex-col gap-2.5 pt-2 text-xs">
                        <div
                            class="rounded-lg border border-white/15 bg-white/10 px-3.5 py-2.5 backdrop-blur dark:border-white/10 dark:bg-black/20">
                            <span class="font-semibold text-blue-200">Office Address:</span>
                            <span class="ml-1 font-medium text-white">Unit 916 Avida Towers Intima, 497 Pres. Quirino Ave.
                                Ext. Cor. Zulueta St., Paco, Manila</span>
                        </div>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div
                                class="rounded-lg border border-white/15 bg-white/10 px-3.5 py-2.5 backdrop-blur dark:border-white/10 dark:bg-black/20">
                                <span class="font-semibold text-blue-200">Contact No:</span>
                                <span class="ml-1 font-medium text-white">Tel. #8561 6836</span>
                            </div>
                            <div
                                class="rounded-lg border border-white/15 bg-white/10 px-3.5 py-2.5 backdrop-blur dark:border-white/10 dark:bg-black/20">
                                <span class="font-semibold text-blue-200">Hotlines:</span>
                                <span class="ml-1 font-medium text-white">+63 968 8500720 / +63 965 6287205</span>
                            </div>
                        </div>
                        <div
                            class="rounded-lg border border-white/15 bg-white/10 px-3.5 py-2.5 backdrop-blur dark:border-white/10 dark:bg-black/20">
                            <span class="font-semibold text-blue-200">Official Email:</span>
                            <span class="ml-1 font-medium text-white">huenicsindustrialsales@gmail.com &bull;
                                crm.huenics777@gmail.com</span>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 flex flex-col justify-center gap-3 sm:flex-row lg:col-span-5 lg:flex-col">
                    <a href="{{ route('customer.quotation-builder') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-400 px-6 py-3.5 text-center text-sm font-bold text-slate-950 shadow-lg transition hover:bg-amber-300 active:scale-[0.98]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Generate Instant Quotation Estimate</span>
                    </a>
                    <a href="{{ route('customer.products') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-white/30 bg-white/15 px-6 py-3.5 text-center text-sm font-bold text-white transition hover:bg-white/25 active:scale-[0.98]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Explore Products Catalog</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
