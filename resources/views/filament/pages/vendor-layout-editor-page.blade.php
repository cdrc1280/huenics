<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-3 pt-2">
            <x-filament::button type="submit" size="lg" icon="heroicon-o-check">
                Save Layout Configuration
            </x-filament::button>

            <x-filament::button
                type="button"
                color="gray"
                size="lg"
                icon="heroicon-o-arrow-path"
                wire:click="resetToDefaults"
                wire:confirm="Are you sure you want to reset all extraction rules to standard defaults for this vendor and document type?"
            >
                Reset to Defaults
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
