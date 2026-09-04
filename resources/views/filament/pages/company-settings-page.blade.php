<x-filament-panels::page>
    <form wire:submit.prevent="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-end gap-3 pt-4">
            <x-filament::button type="submit" size="md" icon="heroicon-m-check">
                Save Company Settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
