<?php

namespace App\Filament\Widgets;

use App\Enums\AdminPermission;
use App\Models\Article;
use App\Models\Category;
use App\Models\Notification;
use App\Models\RevenueEvent;
use App\Models\RssSource;
use App\Services\DashboardService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $admin = auth('admin')->user();

        return $admin?->hasPermission(AdminPermission::DashboardView->value) ?? false;
    }

    protected function getStats(): array
    {
        $overview = app(DashboardService::class)->getOverview();
        $revenueTotal = RevenueEvent::where('occurred_at', '>=', now()->subDays(30))->sum('amount');

        return [
            Stat::make('Articles', number_format(Article::count()))
                ->description('Total in database')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
            Stat::make('Categories', number_format(Category::count()))
                ->description('Content categories')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('success'),
            Stat::make('RSS Sources', number_format(RssSource::count()))
                ->description(RssSource::where('is_active', true)->count().' active')
                ->descriptionIcon('heroicon-m-rss')
                ->color('info'),
            Stat::make('Users', number_format($overview['total_users']))
                ->description($overview['active_users_7d'].' active (7d)')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
            Stat::make('Notifications', number_format(Notification::count()))
                ->description($overview['notifications_sent'].' sent (30d)')
                ->descriptionIcon('heroicon-m-bell')
                ->color('danger'),
            Stat::make('Revenue (30d)', '$'.number_format((float) $revenueTotal, 2))
                ->description('Tracked ad events')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
