<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Modern Filter Control Bar --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                
                {{-- Left Section: S.E. and Inhouse Filters --}}
                <div class="flex flex-wrap items-center gap-3">
                    {{-- S.E. Filter Dropdown --}}
                    <div class="w-64">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 flex items-center gap-1.5">
                            <x-filament::icon icon="heroicon-m-user" class="w-3.5 h-3.5 text-primary-500" />
                            <span>Filter by S.E.</span>
                        </label>
                        <select
                            wire:model.live="selectedAgentId"
                            @if($filterInhouse) disabled @endif
                            class="w-full text-xs font-medium rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 @if($filterInhouse) opacity-50 cursor-not-allowed @endif"
                        >
                            <option value="">All Sales Executives</option>
                            @foreach ($this->salesAgents as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Inhouse / Owner Toggle Button --}}
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 flex items-center gap-1.5">
                            <x-filament::icon icon="heroicon-m-building-office-2" class="w-3.5 h-3.5 text-primary-500" />
                            <span>Inhouse (Owner)</span>
                        </label>
                        <button
                            type="button"
                            wire:click="$toggle('filterInhouse')"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold transition shadow-sm border {{ $filterInhouse ? 'bg-primary-600 text-white border-primary-600 ring-2 ring-primary-500/20' : 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600' }}"
                        >
                            <x-filament::icon icon="heroicon-m-building-storefront" class="w-4 h-4" />
                            <span>{{ $filterInhouse ? 'Inhouse Active' : 'Inhouse Dashboard' }}</span>
                        </button>
                    </div>
                </div>

                {{-- Right Section: Granularity Segmented Switcher & Date Controls --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    
                    {{-- Granularity Switcher Buttons --}}
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 flex items-center gap-1.5">
                            <x-filament::icon icon="heroicon-m-clock" class="w-3.5 h-3.5 text-primary-500" />
                            <span>Period</span>
                        </label>
                        <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-700/80 rounded-lg border border-gray-200 dark:border-gray-600">
                            <button
                                type="button"
                                wire:click="setPeriodType('days')"
                                class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ $periodType === 'days' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm font-bold' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}"
                            >
                                Days
                            </button>
                            <button
                                type="button"
                                wire:click="setPeriodType('weeks')"
                                class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ $periodType === 'weeks' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm font-bold' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}"
                            >
                                Weeks
                            </button>
                            <button
                                type="button"
                                wire:click="setPeriodType('month')"
                                class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ $periodType === 'month' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm font-bold' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}"
                            >
                                Month
                            </button>
                            <button
                                type="button"
                                wire:click="setPeriodType('years')"
                                class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ $periodType === 'years' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm font-bold' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}"
                            >
                                Years
                            </button>
                        </div>
                    </div>

                    {{-- Dynamic Adaptive Timeframe Controls --}}
                    @if ($periodType === 'days')
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 flex items-center gap-1.5">
                                <x-filament::icon icon="heroicon-m-calendar" class="w-3.5 h-3.5 text-primary-500" />
                                <span>Select Day</span>
                            </label>
                            <div class="flex items-center gap-1.5">
                                <input
                                    type="date"
                                    wire:model.live="selectedDate"
                                    class="text-xs font-medium rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <button
                                    type="button"
                                    wire:click="setToday"
                                    class="px-2.5 py-2 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition shadow-sm"
                                >
                                    Today
                                </button>
                                <button
                                    type="button"
                                    wire:click="setYesterday"
                                    class="px-2.5 py-2 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition shadow-sm"
                                >
                                    Yesterday
                                </button>
                            </div>
                        </div>
                    @elseif ($periodType === 'weeks')
                        <div class="flex items-center gap-2">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-calendar-days" class="w-3.5 h-3.5 text-primary-500" />
                                    <span>Week</span>
                                </label>
                                <select
                                    wire:model.live="selectedWeek"
                                    class="text-xs font-medium rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    @for ($w = 1; $w <= 52; $w++)
                                        @php
                                            $wStart = \Carbon\Carbon::now()->setISODate($selectedYear ?: now()->year, $w)->startOfWeek();
                                            $wEnd = $wStart->copy()->endOfWeek();
                                        @endphp
                                        <option value="{{ $w }}">Week {{ $w }} ({{ $wStart->format('M d') }} - {{ $wEnd->format('M d') }})</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Year</label>
                                <select
                                    wire:model.live="selectedYear"
                                    class="text-xs font-medium rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    @for ($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="pt-5">
                                <button
                                    type="button"
                                    wire:click="setThisWeek"
                                    class="px-2.5 py-2 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition shadow-sm"
                                >
                                    This Week
                                </button>
                            </div>
                        </div>
                    @elseif ($periodType === 'years')
                        <div class="flex items-center gap-2">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-calendar" class="w-3.5 h-3.5 text-primary-500" />
                                    <span>Select Year</span>
                                </label>
                                <select
                                    wire:model.live="selectedYear"
                                    class="text-xs font-medium rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    @for ($y = now()->year - 3; $y <= now()->year + 2; $y++)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="pt-5">
                                <button
                                    type="button"
                                    wire:click="setThisYear"
                                    class="px-2.5 py-2 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition shadow-sm"
                                >
                                    This Year
                                </button>
                            </div>
                        </div>
                    @else
                        {{-- Month Mode --}}
                        <div class="flex items-center gap-2">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-calendar" class="w-3.5 h-3.5 text-primary-500" />
                                    <span>Month</span>
                                </label>
                                <select
                                    wire:model.live="selectedMonth"
                                    class="text-xs font-medium rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">{{ \Carbon\Carbon::createFromDate(null, $m)->format('F') }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Year</label>
                                <select
                                    wire:model.live="selectedYear"
                                    class="text-xs font-medium rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    @for ($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="pt-5">
                                <button
                                    type="button"
                                    wire:click="setThisMonth"
                                    class="px-2.5 py-2 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition shadow-sm"
                                >
                                    This Month
                                </button>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Summary Context Info Bar --}}
            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/60 text-xs text-gray-600 dark:text-gray-300">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-400 dark:text-gray-500">Active Timeframe:</span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 px-2.5 py-1 rounded-md">
                        <x-filament::icon icon="heroicon-o-calendar-days" class="w-3.5 h-3.5 text-primary-500" />
                        {{ $this->getDateRange()[2] }}
                    </span>
                    <span class="inline-flex items-center font-semibold text-xs px-2 py-0.5 rounded-md bg-primary-50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300 uppercase tracking-wide">
                        {{ strtoupper($periodType) }} MODE
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    @if ($filterInhouse)
                        <x-filament::badge color="primary" icon="heroicon-m-building-storefront">
                            Inhouse / Owner Filter Active
                        </x-filament::badge>
                    @elseif ($selectedAgentId)
                        <x-filament::badge color="info" icon="heroicon-m-user">
                            Filtered: {{ $this->salesAgents[$selectedAgentId] ?? 'Selected S.E.' }}
                        </x-filament::badge>
                    @else
                        <span class="text-gray-400 dark:text-gray-500">Showing all sales representatives</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Leaderboard Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
