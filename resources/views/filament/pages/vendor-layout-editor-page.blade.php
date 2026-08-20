<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Layout Preset Selector --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-building-storefront" class="w-5 h-5 text-gray-500" />
                Select Vendor & Document Format
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Vendor</label>
                    <select
                        wire:model.live="selectedVendorId"
                        class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        @foreach($this->vendors as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Document Type</label>
                    <select
                        wire:model.live="selectedDocumentType"
                        class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="purchase_order">Purchase Order</option>
                        <option value="order_slip">Order Slip</option>
                        <option value="vendors_agreement">Vendors Agreement Form (Quotation)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Header Detection Regex (Optional)</label>
                    <input
                        type="text"
                        wire:model="headerRegex"
                        placeholder="e.g. /PURCHASE ORDER/i"
                        class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                </div>
            </div>
        </div>

        {{-- Dynamic Field Mappings Table --}}
        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-table-cells" class="w-5 h-5 text-gray-500" />
                    Field Extraction Rules ({{ count($mappings) }})
                </h3>
                <button
                    type="button"
                    wire:click="addMapping"
                    class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium flex items-center gap-1"
                >
                    <x-filament::icon icon="heroicon-o-plus" class="w-4 h-4" />
                    Add Field Rule
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                            <th class="py-2.5 px-3">Field Key</th>
                            <th class="py-2.5 px-3">Scope</th>
                            <th class="py-2.5 px-3">Strategy</th>
                            <th class="py-2.5 px-3">Pattern / Regex / Bounds</th>
                            <th class="py-2.5 px-3">Transform</th>
                            <th class="py-2.5 px-3 text-center">Req</th>
                            <th class="py-2.5 px-2 text-center w-8"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($mappings as $index => $m)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750">
                                <td class="py-2 px-3">
                                    <select
                                        wire:model="mappings.{{ $index }}.field_key"
                                        class="w-full text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="document_number">document_number</option>
                                        <option value="document_date">document_date</option>
                                        <option value="line_no">line_no</option>
                                        <option value="material_code">material_code</option>
                                        <option value="description">description</option>
                                        <option value="qty">qty</option>
                                        <option value="unit">unit</option>
                                        <option value="unit_price">unit_price</option>
                                        <option value="printed_total">printed_total</option>
                                        <option value="printed_subtotal">printed_subtotal</option>
                                        <option value="printed_vat">printed_vat</option>
                                        <option value="negotiated_amount">negotiated_amount</option>
                                        <option value="custom_field">custom_field</option>
                                    </select>
                                </td>
                                <td class="py-2 px-3">
                                    <select
                                        wire:model="mappings.{{ $index }}.target_scope"
                                        class="w-full text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="header">Header</option>
                                        <option value="line_item">Line Item</option>
                                        <option value="totals">Totals</option>
                                    </select>
                                </td>
                                <td class="py-2 px-3">
                                    <select
                                        wire:model="mappings.{{ $index }}.extraction_strategy"
                                        class="w-full text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="regex_header">Regex Header</option>
                                        <option value="column_position">Column Slicing</option>
                                        <option value="keyword_offset">Keyword Anchor</option>
                                        <option value="table_row_index">Row Index</option>
                                    </select>
                                </td>
                                <td class="py-2 px-3">
                                    @if(($m['extraction_strategy'] ?? '') === 'column_position')
                                        <div class="flex items-center gap-1">
                                            <input
                                                type="number"
                                                wire:model="mappings.{{ $index }}.column_start"
                                                placeholder="Start"
                                                class="w-16 text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            />
                                            <span>-</span>
                                            <input
                                                type="number"
                                                wire:model="mappings.{{ $index }}.column_end"
                                                placeholder="End"
                                                class="w-16 text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            />
                                        </div>
                                    @else
                                        <input
                                            type="text"
                                            wire:model="mappings.{{ $index }}.regex_pattern"
                                            placeholder="Regex pattern e.g. /(?:PO #)(\d+)/i"
                                            class="w-full text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        />
                                    @endif
                                </td>
                                <td class="py-2 px-3">
                                    <select
                                        wire:model="mappings.{{ $index }}.post_process"
                                        class="w-full text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="none">None</option>
                                        <option value="trim">Trim Whitespace</option>
                                        <option value="strip_commas">Strip Commas</option>
                                        <option value="parse_decimal">Decimal (₱)</option>
                                        <option value="parse_int">Integer</option>
                                        <option value="parse_date">Date (Y-m-d)</option>
                                        <option value="uppercase">Uppercase</option>
                                    </select>
                                </td>
                                <td class="py-2 px-3 text-center">
                                    <input
                                        type="checkbox"
                                        wire:model="mappings.{{ $index }}.is_required"
                                        class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500"
                                    />
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <button
                                        type="button"
                                        wire:click="removeMapping({{ $index }})"
                                        class="text-gray-400 hover:text-red-500"
                                    >
                                        <x-filament::icon icon="heroicon-o-trash" class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-gray-500 italic">No rules defined. Click "Add Field Rule" above.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end pt-3">
                <button
                    type="button"
                    wire:click="saveLayout"
                    class="px-5 py-2 text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 rounded-lg shadow flex items-center gap-1.5"
                >
                    <x-filament::icon icon="heroicon-o-check" class="w-4 h-4" />
                    Save Layout Preset
                </button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
