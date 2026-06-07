<?php

namespace App\Filament\Widgets;

use App\Enums\AdminPermission;
use App\Models\ArticleView;
use Filament\Widgets\ChartWidget;

class AnalyticsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Article Views (Last 14 Days)';

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    public static function canView(): bool
    {
        $admin = auth('admin')->user();

        return $admin?->hasPermission(AdminPermission::AnalyticsView->value) ?? false;
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M j');
            $data[] = ArticleView::whereDate('viewed_at', $date->toDateString())->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Article views',
                    'data' => $data,
                    'borderColor' => '#6366f1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
