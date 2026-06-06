<?php

namespace App\Repositories;

use App\Models\AnalyticsEvent;
use App\Models\ArticleView;
use App\Models\CategoryAnalytic;
use App\Models\Notification;
use App\Models\RevenueRecord;
use App\Models\SearchAnalytic;
use App\Models\User;
use App\Models\UserRetentionSnapshot;
use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsRepository implements AnalyticsRepositoryInterface
{
    public function recordArticleView(array $data): void
    {
        ArticleView::create($data);
    }

    public function recordSearch(array $data): void
    {
        SearchAnalytic::create($data);
    }

    public function recordEvent(array $data): void
    {
        AnalyticsEvent::create($data);
    }

    public function getDashboardMetrics(array $dateRange): array
    {
        $from = Carbon::parse($dateRange['from'] ?? now()->subDays(30));
        $to = Carbon::parse($dateRange['to'] ?? now());

        return [
            'total_users' => User::count(),
            'active_users' => User::where('last_active_at', '>=', now()->subDays(7))->count(),
            'articles_opened' => ArticleView::whereBetween('viewed_at', [$from, $to])->count(),
            'notifications_sent' => Notification::where('status', 'sent')
                ->whereBetween('sent_at', [$from, $to])
                ->count(),
            'revenue_overview' => RevenueRecord::whereBetween('recorded_date', [$from, $to])
                ->select('source', DB::raw('SUM(amount) as total'))
                ->groupBy('source')
                ->get(),
            'source_performance' => DB::table('article_views')
                ->join('articles', 'article_views.article_id', '=', 'articles.id')
                ->join('rss_sources', 'articles.rss_source_id', '=', 'rss_sources.id')
                ->whereBetween('article_views.viewed_at', [$from, $to])
                ->select('rss_sources.id', 'rss_sources.name', DB::raw('COUNT(*) as views'))
                ->groupBy('rss_sources.id', 'rss_sources.name')
                ->orderByDesc('views')
                ->limit(10)
                ->get(),
        ];
    }

    public function getCategoryAnalytics(int $categoryId, array $dateRange): array
    {
        $from = Carbon::parse($dateRange['from'] ?? now()->subDays(30));
        $to = Carbon::parse($dateRange['to'] ?? now());

        return CategoryAnalytic::where('category_id', $categoryId)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    public function getRetentionData(array $dateRange): array
    {
        $from = Carbon::parse($dateRange['from'] ?? now()->subDays(90));
        $to = Carbon::parse($dateRange['to'] ?? now());

        return UserRetentionSnapshot::whereBetween('cohort_date', [$from, $to])
            ->orderBy('cohort_date')
            ->orderBy('day_number')
            ->get()
            ->groupBy('cohort_date')
            ->toArray();
    }

    public function getSearchTrends(array $dateRange, int $limit = 20): array
    {
        $from = Carbon::parse($dateRange['from'] ?? now()->subDays(30));
        $to = Carbon::parse($dateRange['to'] ?? now());

        return SearchAnalytic::whereBetween('created_at', [$from, $to])
            ->select('query', DB::raw('COUNT(*) as count'))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
