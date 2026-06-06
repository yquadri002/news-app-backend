<?php

namespace App\Enums;

enum AdminPermission: string
{
    case DashboardView = 'dashboard.view';
    case CategoriesManage = 'categories.manage';
    case SourcesManage = 'sources.manage';
    case BreakingNewsManage = 'breaking_news.manage';
    case NotificationsManage = 'notifications.manage';
    case AdsManage = 'ads.manage';
    case AppUpdatesManage = 'app_updates.manage';
    case AnalyticsView = 'analytics.view';
    case RevenueManage = 'revenue.manage';
    case RolesManage = 'roles.manage';
    case AdminsManage = 'admins.manage';

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
