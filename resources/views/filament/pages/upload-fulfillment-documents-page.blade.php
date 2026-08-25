<x-filament-panels::page>
    <div class="mb-4 p-4 rounded-xl border border-sky-200 bg-sky-50 dark:border-sky-800 dark:bg-sky-950/40 text-sm text-sky-900 dark:text-sky-200">
        <div class="flex items-start gap-3">
            <x-filament::icon icon="heroicon-o-information-circle" class="w-6 h-6 text-sky-600 dark:text-sky-400 shrink-0 mt-0.5" />
            <div>
                <h4 class="font-semibold text-sky-950 dark:text-sky-100">Order Fulfillment & Realization Trigger</h4>
                <p class="mt-1 text-xs sm:text-sm text-sky-800 dark:text-sky-300">
                    Uploading both the <strong>Delivery Receipt (DR)</strong> and <strong>Sales Invoice (SI)</strong> represents the definitive finalization of the transaction lifecycle. Upon completion, inventory will be automatically deducted from the product catalog and BOM components, and revenue metrics will be credited to executive dashboards and sales leaderboards.
                </p>
            </div>
        </div>
    </div>

    <form wire:submit="submit" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
            <x-filament::button
                type="button"
                color="gray"
                tag="a"
                href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('index') }}"
            >
                Cancel
            </x-filament::button>

            <x-filament::button
                type="submit"
                size="lg"
                color="primary"
                icon="heroicon-o-check-badge"
            >
                Complete Fulfillment & Deduct Stock
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
