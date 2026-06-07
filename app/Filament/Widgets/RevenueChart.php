<?php

namespace App\Filament\Widgets;

use App\Enums\AdminPermission;
use App\Models\RevenueEvent;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Revenue Events (Last 14 Days)';

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    public static function canView(): bool
    {
        $admin = auth('admin')->user();

        return $admin?->hasPermission(AdminPermission::RevenueManage->value) ?? false;
    }

    protected function getData(): array
    {
        $labels = [];
        $impressions = [];
        $clicks = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('M j');
            $impressions[] = RevenueEvent::where('event_type', 'impression')->whereDate('occurred_at', $date)->count();
            $clicks[] = RevenueEvent::where('event_type', 'click')->whereDate('occurred_at', $date)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Impressions',
                    'data' => $impressions,
                    'backgroundColor' => '#10b981',
                ],
                [
                    'label' => 'Clicks',
                    'data' => $clicks,
                    'backgroundColor' => '#f59e0b',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
