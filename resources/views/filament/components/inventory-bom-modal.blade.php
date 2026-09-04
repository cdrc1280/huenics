<div class="space-y-6">
    {{-- 1. Top KPI Summary Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Item Identity Card --}}
        <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/80 dark:bg-white/5 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider uppercase text-gray-500 dark:text-gray-400">Inventory Item</span>
                @if ($parentComponents->isNotEmpty() && $usedInParents->isNotEmpty())
                    <x-filament::badge color="purple" size="sm" icon="heroicon-m-arrows-up-down">Hybrid Assembly & Part</x-filament::badge>
                @elseif ($parentComponents->isNotEmpty())
                    <x-filament::badge color="info" size="sm" icon="heroicon-m-squares-plus">Assembly Parent</x-filament::badge>
                @elseif ($usedInParents->isNotEmpty())
                    <x-filament::badge color="warning" size="sm" icon="heroicon-m-puzzle-piece">Sub-Component</x-filament::badge>
                @else
                    <x-filament::badge color="gray" size="sm">Standard Item</x-filament::badge>
                @endif
            </div>
            <div class="text-sm font-bold text-gray-950 dark:text-white leading-snug">
                {{ $record->product?->canonical_name ?? 'N/A' }}
            </div>
            <div class="flex items-center gap-2 text-xs font-mono text-gray-500 dark:text-gray-400">
                <span>SKU: {{ $record->product?->sku ?? '—' }}</span>
                <span>•</span>
                <span>Code: {{ $record->product?->product_code ?? '—' }}</span>
            </div>
        </div>

        {{-- Finished Stock Balance Card --}}
        <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/80 dark:bg-white/5 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider uppercase text-gray-500 dark:text-gray-400">Stock Balance On Hand</span>
                @if ($record->quantity_on_hand <= 0)
                    <x-filament::badge color="danger" size="sm" icon="heroicon-m-x-circle">Out of Stock</x-filament::badge>
                @elseif ($record->quantity_on_hand <= ($record->reorder_point ?? 10))
                    <x-filament::badge color="warning" size="sm" icon="heroicon-m-exclamation-triangle">Low Stock</x-filament::badge>
                @else
                    <x-filament::badge color="success" size="sm" icon="heroicon-m-check-circle">Available</x-filament::badge>
                @endif
            </div>
            <div class="text-2xl font-black font-mono tabular-nums text-gray-950 dark:text-white">
                {{ number_format($record->quantity_on_hand, 0) }} <span class="text-xs font-sans font-semibold text-gray-500">{{ $record->unit ?: 'pcs' }}</span>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center justify-between">
                <span>Reserved: <strong class="font-mono text-gray-700 dark:text-gray-300">{{ number_format($record->quantity_reserved, 0) }}</strong></span>
                <span>Safety Reorder: <strong class="font-mono text-gray-700 dark:text-gray-300">{{ number_format($record->reorder_point, 0) }}</strong></span>
            </div>
        </div>

        {{-- Cost Rollup / Assembly Capacity Card --}}
        @php
            $totalBomCost = 0;
            $maxAssemblable = null;
            $bottleneckPart = null;

            if ($parentComponents->isNotEmpty()) {
                foreach ($parentComponents as $comp) {
                    $uCost = (float) ($comp->cost_price ?: ($comp->additional_cost ?: ($comp->componentProduct?->base_cost_price ?? 0)));
                    $reqQty = (float) ($comp->quantity ?: 1);
                    $totalBomCost += ($uCost * $reqQty);

                    $childStock = $comp->componentProduct?->inventoryItem?->quantity_on_hand;
                    if ($childStock !== null && $reqQty > 0) {
                        $possible = (int) floor($childStock / $reqQty);
                        if ($maxAssemblable === null || $possible < $maxAssemblable) {
                            $maxAssemblable = $possible;
                            $bottleneckPart = $comp->effective_name;
                        }
                    }
                }
            }
        @endphp

        <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/80 dark:bg-white/5 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider uppercase text-gray-500 dark:text-gray-400">
                    {{ $parentComponents->isNotEmpty() ? 'Total BOM Assembly Cost' : 'Unit Cost Valuation' }}
                </span>
                <x-filament::badge color="primary" size="sm">PHP</x-filament::badge>
            </div>
            <div class="text-2xl font-black font-mono tabular-nums text-primary-600 dark:text-primary-400">
                ₱{{ number_format($parentComponents->isNotEmpty() ? $totalBomCost : (float) ($record->cost_price ?: ($record->product?->base_cost_price ?? 0)), 2) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                @if ($parentComponents->isNotEmpty() && $maxAssemblable !== null)
                    <span>Can Assemble: <strong class="font-mono text-gray-950 dark:text-white">{{ $maxAssemblable }} units</strong></span>
                    @if ($maxAssemblable === 0 && $bottleneckPart)
                        <span class="text-rose-600 dark:text-rose-400 font-semibold block text-[11px] mt-0.5">Bottleneck: {{ \Illuminate\Support\Str::limit($bottleneckPart, 22) }}</span>
                    @endif
                @else
                    <span>Category: <strong class="text-gray-900 dark:text-white">{{ $record->product?->category ?: 'General' }}</strong></span>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. Case 1: Item is an Assembly Parent (Has Sub-Components) --}}
    @if ($parentComponents->isNotEmpty())
        <x-filament::section icon="heroicon-m-puzzle-piece">
            <x-slot name="heading">
                Sub-Components Breakdown (Bill of Materials)
            </x-slot>
            <x-slot name="description">
                Raw parts, sub-assemblies, and child materials required to manufacture or assemble 1 unit of this product
            </x-slot>
            <x-slot name="headerEnd">
                <x-filament::badge color="info" size="sm">
                    {{ $parentComponents->count() }} Line Item{{ $parentComponents->count() > 1 ? 's' : '' }}
                </x-filament::badge>
            </x-slot>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900">
                <table class="w-full text-start border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-700 dark:text-gray-300 uppercase tracking-wider font-semibold">
                            <th class="py-3 px-4 text-start">Sub-Component / Part Name</th>
                            <th class="py-3 px-4 text-start">Category</th>
                            <th class="py-3 px-4 text-center">Qty / Assembly</th>
                            <th class="py-3 px-4 text-center">Part Stock Status</th>
                            <th class="py-3 px-4 text-end">Unit Cost</th>
                            <th class="py-3 px-4 text-end">Subtotal Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($parentComponents as $comp)
                            @php
                                $unitCost = (float) ($comp->cost_price ?: ($comp->additional_cost ?: ($comp->componentProduct?->base_cost_price ?? 0)));
                                $subtotal = $unitCost * (float) ($comp->quantity ?: 1);
                                $childInv = $comp->componentProduct?->inventoryItem;
                                $childStock = $childInv?->quantity_on_hand;
                            @endphp
                            <tr class="hover:bg-gray-50/75 dark:hover:bg-white/5 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-gray-950 dark:text-white flex items-center gap-1.5">
                                        <x-filament::icon icon="heroicon-m-cube" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                                        <span>{{ $comp->effective_name }}</span>
                                    </div>
                                    <div class="font-mono text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 ps-5">
                                        Code: {{ $comp->effective_code ?: '—' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <x-filament::badge color="gray" size="sm">
                                        {{ $comp->effective_category }}
                                    </x-filament::badge>
                                </td>
                                <td class="py-3 px-4 text-center font-mono font-bold text-gray-950 dark:text-white">
                                    {{ (float) $comp->quantity }} <span class="text-[11px] font-sans font-normal text-gray-500">{{ $comp->effective_unit }}</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if ($childInv)
                                        @if ($childStock >= $comp->quantity)
                                            <x-filament::badge color="success" size="sm" icon="heroicon-m-check">
                                                {{ number_format($childStock, 0) }} {{ $childInv->unit ?: 'pcs' }}
                                            </x-filament::badge>
                                        @elseif ($childStock > 0)
                                            <x-filament::badge color="warning" size="sm" icon="heroicon-m-exclamation-triangle">
                                                {{ number_format($childStock, 0) }} {{ $childInv->unit ?: 'pcs' }} (Low)
                                            </x-filament::badge>
                                        @else
                                            <x-filament::badge color="danger" size="sm" icon="heroicon-m-x-circle">
                                                0 {{ $childInv->unit ?: 'pcs' }} (Out)
                                            </x-filament::badge>
                                        @endif
                                    @else
                                        <x-filament::badge color="gray" size="sm">
                                            Untracked
                                        </x-filament::badge>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end font-mono tabular-nums text-gray-600 dark:text-gray-400">
                                    ₱{{ number_format($unitCost, 2) }}
                                </td>
                                <td class="py-3 px-4 text-end font-mono tabular-nums font-bold text-gray-950 dark:text-white">
                                    ₱{{ number_format($subtotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-300 dark:border-white/15 bg-gray-50/90 dark:bg-white/5">
                            <td colspan="5" class="py-3 px-4 text-end uppercase tracking-wider font-bold text-xs text-gray-700 dark:text-gray-300">
                                Total Rolled-Up BOM Cost:
                            </td>
                            <td class="py-3 px-4 text-end font-mono tabular-nums font-black text-sm text-primary-600 dark:text-primary-400">
                                ₱{{ number_format($totalBomCost, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-filament::section>
    @endif

    {{-- 3. Case 2: Item is Used as a Sub-Component in Parent Products --}}
    @if ($usedInParents->isNotEmpty())
        <x-filament::section icon="heroicon-m-squares-plus">
            <x-slot name="heading">
                Parent Assemblies Consuming This Part
            </x-slot>
            <x-slot name="description">
                Finished products and higher-level assemblies that incorporate this part into their Bill of Materials
            </x-slot>
            <x-slot name="headerEnd">
                <x-filament::badge color="warning" size="sm">
                    {{ $usedInParents->count() }} Parent Product{{ $usedInParents->count() > 1 ? 's' : '' }}
                </x-filament::badge>
            </x-slot>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900">
                <table class="w-full text-start border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-700 dark:text-gray-300 uppercase tracking-wider font-semibold">
                            <th class="py-3 px-4 text-start">Parent Finished Product</th>
                            <th class="py-3 px-4 text-start">Parent SKU</th>
                            <th class="py-3 px-4 text-start">Category</th>
                            <th class="py-3 px-4 text-center">Parent Stock On Hand</th>
                            <th class="py-3 px-4 text-center">Qty Needed per Assembly</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($usedInParents as $parent)
                            @foreach ($parent->components as $c)
                                <tr class="hover:bg-gray-50/75 dark:hover:bg-white/5 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-gray-950 dark:text-white flex items-center gap-1.5">
                                            <x-filament::icon icon="heroicon-m-arrow-up-right" class="w-3.5 h-3.5 text-primary-500 shrink-0" />
                                            <span>{{ $parent->canonical_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-gray-500 dark:text-gray-400">
                                        {{ $parent->sku ?: '—' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <x-filament::badge color="gray" size="sm">
                                            {{ $parent->category ?: 'General' }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @php $pStock = $parent->inventoryItem?->quantity_on_hand ?? 0; @endphp
                                        <x-filament::badge :color="$pStock > 0 ? 'success' : 'danger'" size="sm">
                                            {{ number_format($pStock, 0) }} pcs
                                        </x-filament::badge>
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono font-bold text-gray-950 dark:text-white">
                                        {{ (float) $c->quantity }} <span class="text-[11px] font-sans font-normal text-gray-500">{{ $c->effective_unit }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif

    {{-- 4. Case 3: Empty State (Neither Parent nor Sub-Component) --}}
    @if ($parentComponents->isEmpty() && $usedInParents->isEmpty())
        <div class="p-8 text-center rounded-xl border border-dashed border-gray-300 dark:border-white/15 bg-gray-50/50 dark:bg-white/5 space-y-2">
            <div class="w-10 h-10 mx-auto rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 dark:text-gray-400">
                <x-filament::icon icon="heroicon-o-cube" class="w-5 h-5" />
            </div>
            <h4 class="font-bold text-sm text-gray-900 dark:text-white">No BOM Relationships Configured</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                This item is currently a standalone product and is not linked to any parent assemblies or child sub-components.
            </p>
        </div>
    @endif
</div>
