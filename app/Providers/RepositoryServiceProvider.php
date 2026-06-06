<?php

namespace App\Providers;

use App\Repositories\AbTestResultRepository;
use App\Repositories\AdminRepository;
use App\Repositories\AdRevenueSnapshotRepository;
use App\Repositories\FeedFetchLogRepository;
use App\Repositories\GrowthMetricRepository;
use App\Repositories\RecommendationRepository;
use App\Repositories\RevenueEventRepository;
use App\Repositories\SubscriptionPlanRepository;
use App\Repositories\UserBehaviorRepository;
use App\Repositories\UserSubscriptionRepository;
use App\Repositories\AdPlacementRepository;
use App\Repositories\AnalyticsRepository;
use App\Repositories\AppVersionRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\Contracts\AbTestResultRepositoryInterface;
use App\Repositories\Contracts\AdminRepositoryInterface;
use App\Repositories\Contracts\AdRevenueSnapshotRepositoryInterface;
use App\Repositories\Contracts\FeedFetchLogRepositoryInterface;
use App\Repositories\Contracts\GrowthMetricRepositoryInterface;
use App\Repositories\Contracts\RecommendationRepositoryInterface;
use App\Repositories\Contracts\RevenueEventRepositoryInterface;
use App\Repositories\Contracts\SubscriptionPlanRepositoryInterface;
use App\Repositories\Contracts\UserSubscriptionRepositoryInterface;
use App\Repositories\Contracts\UserBehaviorRepositoryInterface;
use App\Repositories\Contracts\AdPlacementRepositoryInterface;
use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use App\Repositories\Contracts\AppVersionRepositoryInterface;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\RssSourceRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\NotificationRepository;
use App\Repositories\RoleRepository;
use App\Repositories\RssSourceRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminRepositoryInterface::class, AdminRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(RssSourceRepositoryInterface::class, RssSourceRepository::class);
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class, NotificationRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AdPlacementRepositoryInterface::class, AdPlacementRepository::class);
        $this->app->bind(AppVersionRepositoryInterface::class, AppVersionRepository::class);
        $this->app->bind(AnalyticsRepositoryInterface::class, AnalyticsRepository::class);
        $this->app->bind(FeedFetchLogRepositoryInterface::class, FeedFetchLogRepository::class);
        $this->app->bind(UserBehaviorRepositoryInterface::class, UserBehaviorRepository::class);
        $this->app->bind(RecommendationRepositoryInterface::class, RecommendationRepository::class);
        $this->app->bind(RevenueEventRepositoryInterface::class, RevenueEventRepository::class);
        $this->app->bind(SubscriptionPlanRepositoryInterface::class, SubscriptionPlanRepository::class);
        $this->app->bind(UserSubscriptionRepositoryInterface::class, UserSubscriptionRepository::class);
        $this->app->bind(AdRevenueSnapshotRepositoryInterface::class, AdRevenueSnapshotRepository::class);
        $this->app->bind(GrowthMetricRepositoryInterface::class, GrowthMetricRepository::class);
        $this->app->bind(AbTestResultRepositoryInterface::class, AbTestResultRepository::class);
    }
}

