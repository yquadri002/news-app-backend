<?php

namespace App\Filament\Pages;

use App\Enums\AdminPermission;
use App\Filament\Concerns\AuthorizesAdminPermission;
use App\Services\Recommendation\RecommendationAnalyticsService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class RecommendationAnalytics extends Page
{
    use AuthorizesAdminPermission;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Analytics';

    protected static ?string $navigationLabel = 'Recommendation Analytics';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.recommendation-analytics';

    public array $metrics = [];

    protected static function requiredPermission(): ?AdminPermission
    {
        return AdminPermission::AnalyticsView;
    }

    public function mount(RecommendationAnalyticsService $analytics): void
    {
        $this->metrics = $analytics->getMetrics([
            'from' => now()->subDays(30)->toDateString(),
            'to' => now()->toDateString(),
        ]);
    }
}
