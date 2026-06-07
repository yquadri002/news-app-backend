<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <x-filament::section>
            <div class="text-sm text-gray-500">Click-through rate</div>
            <div class="text-3xl font-semibold">{{ number_format(($metrics['ctr'] ?? 0) * 100, 2) }}%</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500">Read completion rate</div>
            <div class="text-3xl font-semibold">{{ number_format(($metrics['read_completion_rate'] ?? 0) * 100, 2) }}%</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500">Retention rate</div>
            <div class="text-3xl font-semibold">{{ number_format(($metrics['retention_rate'] ?? 0) * 100, 2) }}%</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500">Avg session duration</div>
            <div class="text-3xl font-semibold">{{ number_format($metrics['avg_session_duration_seconds'] ?? 0) }}s</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500">Recommendation accuracy</div>
            <div class="text-3xl font-semibold">{{ number_format(($metrics['recommendation_accuracy'] ?? 0) * 100, 2) }}%</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500">Snapshot days</div>
            <div class="text-3xl font-semibold">{{ count($metrics['daily'] ?? []) }}</div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
