<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Control Bar --}}
        <x-filament::section compact>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    {{-- S.E. Filter Dropdown --}}
                    <div class="w-56">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Filter by S.E.</label>
                        <select wire:model.live="selectedAgentId"
                            @if($filterInhouse) disabled @endif
                            class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 @if($filterInhouse) opacity-50 cursor-not-allowed @endif">
                            <option value="">All Sales Executives</option>
                            @foreach ($this->salesAgents as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Inhouse / Owner Toggle Button --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Inhouse (Owner)</label>
                        <button type="button" wire:click="$toggle('filterInhouse')"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm border {{ $filterInhouse ? 'bg-primary-600 text-white border-primary-700 shadow-primary-500/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            <x-filament::icon icon="heroicon-m-building-storefront" class="h-4 w-4" />
                            <span>{{ $filterInhouse ? 'Inhouse: Active' : 'Inhouse Dashboard' }}</span>
                        </button>
                    </div>
                </div>

                {{-- Month & Year Controls --}}
                <div class="flex items-center gap-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Month</label>
                        <select wire:model.live="selectedMonth"
                            class="text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::createFromDate(null, $m)->format('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Year</label>
                        <select wire:model.live="selectedYear"
                            class="text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            @for ($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <span>Sales Leaderboard</span>
                @if ($filterInhouse)
                    <x-filament::badge color="primary">Inhouse / Owner Filter Active</x-filament::badge>
                @elseif ($selectedAgentId)
                    <x-filament::badge color="info">Filtered by S.E.</x-filament::badge>
                @endif
            </h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ \Carbon\Carbon::createFromDate($this->selectedYear, $this->selectedMonth)->format('F Y') }}
            </span>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
