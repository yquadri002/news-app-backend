<x-filament-panels::page>
    <div class="mb-4 flex gap-2">
        <x-filament::button
            :color="$this->activeTab === 'pending' ? 'primary' : 'gray'"
            wire:click="setActiveTab('pending')"
        >
            Pending Articles
        </x-filament::button>
        <x-filament::button
            :color="$this->activeTab === 'duplicates' ? 'primary' : 'gray'"
            wire:click="setActiveTab('duplicates')"
        >
            Duplicate Detection
        </x-filament::button>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
