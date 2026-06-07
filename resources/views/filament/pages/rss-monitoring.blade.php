<x-filament-panels::page>
    <div class="mb-6 grid gap-4 md:grid-cols-3 xl:grid-cols-6">
        @foreach ($this->getStats() as $stat)
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $stat->getLabel() }}</div>
                <div class="text-2xl font-semibold">{{ $stat->getValue() }}</div>
            </x-filament::section>
        @endforeach
    </div>

    {{ $this->table }}
</x-filament-panels::page>
