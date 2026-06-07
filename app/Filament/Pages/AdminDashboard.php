<?php

namespace App\Filament\Pages;

use App\Enums\AdminPermission;
use App\Filament\Concerns\AuthorizesAdminPermission;
use App\Filament\Widgets\AnalyticsChart;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\StatsOverview;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

class AdminDashboard extends BaseDashboard
{
    use AuthorizesAdminPermission;

    protected static string $routePath = '/dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = -2;

    protected static function requiredPermission(): ?AdminPermission
    {
        return AdminPermission::DashboardView;
    }

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            AnalyticsChart::class,
            RevenueChart::class,
        ];
    }
}
