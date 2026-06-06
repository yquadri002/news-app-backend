<?php

use App\Http\Controllers\Api\Admin\AdPlacementController as AdminAdPlacementController;
use App\Http\Controllers\Api\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Api\Admin\AppVersionController as AdminAppVersionController;
use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\BreakingNewsController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\FeedMonitoringController;
use App\Http\Controllers\Api\Admin\NewsModerationController;
use App\Http\Controllers\Api\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Api\Admin\NotificationIntelligenceController;
use App\Http\Controllers\Api\Admin\RecommendationAnalyticsController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\RssSourceController;
use App\Http\Controllers\Api\Client\AnalyticsController as ClientAnalyticsController;
use App\Http\Controllers\Api\Client\AppUpdateController;
use App\Http\Controllers\Api\Client\CategoryController as ClientCategoryController;
use App\Http\Controllers\Api\Client\DeviceController;
use App\Http\Controllers\Api\Client\NotificationController as ClientNotificationController;
use App\Http\Controllers\Api\Client\NewsController;
use App\Http\Controllers\Api\Client\PreferenceController;
use App\Http\Controllers\Api\Client\RecommendationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Admin Authentication (public)
    Route::prefix('admin/auth')->group(function () {
        Route::post('login', [AdminAuthController::class, 'login']);
        Route::post('forgot-password', [AdminAuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AdminAuthController::class, 'resetPassword']);
    });

    // Admin Protected Routes
    Route::prefix('admin')->middleware(['auth:sanctum', 'admin.active'])->group(function () {

        Route::post('auth/logout', [AdminAuthController::class, 'logout']);
        Route::get('auth/me', [AdminAuthController::class, 'me']);

        Route::get('dashboard', [DashboardController::class, 'index'])
            ->middleware('admin.permission:dashboard.view');

        // Role Management
        Route::apiResource('roles', RoleController::class)
            ->middleware('admin.permission:roles.manage');

        // Category Management
        Route::middleware('admin.permission:categories.manage')->group(function () {
            Route::apiResource('categories', AdminCategoryController::class);
            Route::post('categories/sort-order', [AdminCategoryController::class, 'sortOrder']);
            Route::patch('categories/{id}/toggle', [AdminCategoryController::class, 'toggle']);
        });

        // RSS Source Management
        Route::middleware('admin.permission:sources.manage')->group(function () {
            Route::apiResource('rss-sources', RssSourceController::class);
            Route::post('rss-sources/{id}/validate', [RssSourceController::class, 'validateSource']);
            Route::get('rss-sources-health', [RssSourceController::class, 'health']);
            Route::patch('rss-sources/{id}/priority', [RssSourceController::class, 'updatePriority']);

            // News Moderation
            Route::get('news/articles', [NewsModerationController::class, 'index']);
            Route::get('news/moderation/pending', [NewsModerationController::class, 'pending']);
            Route::post('news/moderation/{id}/approve', [NewsModerationController::class, 'approve']);
            Route::post('news/moderation/{id}/reject', [NewsModerationController::class, 'reject']);
            Route::get('news/duplicates', [NewsModerationController::class, 'duplicates']);

            // Feed Monitoring Dashboard
            Route::get('feeds/dashboard', [FeedMonitoringController::class, 'dashboard']);
            Route::get('feeds/logs', [FeedMonitoringController::class, 'logs']);
            Route::get('feeds/source-performance', [FeedMonitoringController::class, 'sourcePerformance']);
            Route::post('feeds/{sourceId}/fetch', [FeedMonitoringController::class, 'triggerFetch']);
        });

        // Breaking News Engine
        Route::middleware('admin.permission:breaking_news.manage')->prefix('breaking-news')->group(function () {
            Route::post('articles/{articleId}/mark', [BreakingNewsController::class, 'markBreaking']);
            Route::post('articles/{articleId}/push/all', [BreakingNewsController::class, 'pushToAll']);
            Route::post('articles/{articleId}/push/categories', [BreakingNewsController::class, 'pushToCategories']);
            Route::post('articles/{articleId}/push/segments', [BreakingNewsController::class, 'pushToSegments']);
        });

        // Notification Center
        Route::middleware('admin.permission:notifications.manage')->prefix('notifications')->group(function () {
            Route::get('/', [AdminNotificationController::class, 'index']);
            Route::post('/', [AdminNotificationController::class, 'store']);
            Route::get('{id}', [AdminNotificationController::class, 'show']);
            Route::post('{id}/schedule', [AdminNotificationController::class, 'schedule']);
            Route::post('{id}/send', [AdminNotificationController::class, 'send']);
            Route::post('{id}/cancel', [AdminNotificationController::class, 'cancel']);
            Route::get('{id}/analytics', [AdminNotificationController::class, 'analytics']);
        });

        // Advertisement Control Center
        Route::middleware('admin.permission:ads.manage')->prefix('ads')->group(function () {
            Route::get('placements', [AdminAdPlacementController::class, 'index']);
            Route::post('placements', [AdminAdPlacementController::class, 'store']);
            Route::put('placements/{id}', [AdminAdPlacementController::class, 'update']);
            Route::delete('placements/{id}', [AdminAdPlacementController::class, 'destroy']);
            Route::post('placements/{id}/ab-tests', [AdminAdPlacementController::class, 'storeAbTest']);
        });

        // App Update Center
        Route::middleware('admin.permission:app_updates.manage')->apiResource('app-versions', AdminAppVersionController::class);

        // Analytics
        Route::middleware('admin.permission:analytics.view')->prefix('analytics')->group(function () {
            Route::get('overview', [AdminAnalyticsController::class, 'overview']);
            Route::get('categories/{categoryId}', [AdminAnalyticsController::class, 'categoryAnalytics']);
            Route::get('search-trends', [AdminAnalyticsController::class, 'searchTrends']);
            Route::get('retention', [AdminAnalyticsController::class, 'retention']);
            Route::get('recommendations', [RecommendationAnalyticsController::class, 'index']);
            Route::post('recommendations/snapshot', [RecommendationAnalyticsController::class, 'calculateSnapshot']);
        });
    });

    // Notification Intelligence APIs (admin)
    Route::prefix('notifications')->middleware(['auth:sanctum', 'admin.active', 'admin.permission:notifications.manage'])->group(function () {
        Route::get('recommendations', [NotificationIntelligenceController::class, 'recommendations']);
        Route::post('send', [NotificationIntelligenceController::class, 'send']);
        Route::post('test', [NotificationIntelligenceController::class, 'test']);
        Route::get('analytics', [NotificationIntelligenceController::class, 'analytics']);
        Route::post('analytics/snapshot', [NotificationIntelligenceController::class, 'calculateSnapshot']);
    });

    // Personalized Recommendation APIs (authenticated)
    Route::prefix('recommendations')->middleware(['auth:sanctum', 'track.activity'])->group(function () {
        Route::get('feed', [RecommendationController::class, 'feed']);
        Route::get('trending', [RecommendationController::class, 'trending']);
        Route::get('local', [RecommendationController::class, 'local']);
        Route::get('following', [RecommendationController::class, 'following']);
        Route::get('breaking', [RecommendationController::class, 'breaking']);
        Route::get('profile', [RecommendationController::class, 'profile']);
        Route::post('feedback', [RecommendationController::class, 'feedback']);
        Route::post('behavior', [RecommendationController::class, 'trackBehavior']);
    });

    // Public News APIs
    Route::prefix('news')->group(function () {
        Route::get('feed', [NewsController::class, 'feed']);
        Route::get('trending', [NewsController::class, 'trending']);
        Route::get('breaking', [NewsController::class, 'breaking']);
        Route::get('latest', [NewsController::class, 'latest']);
        Route::get('category/{id}', [NewsController::class, 'byCategory']);
        Route::get('article/{id}', [NewsController::class, 'article'])->name('news.article');
        Route::get('search', [NewsController::class, 'search']);
    });

    // Client / Mobile App APIs
    Route::prefix('client')->group(function () {

        Route::post('device/register', [DeviceController::class, 'register']);

        Route::get('categories', [ClientCategoryController::class, 'index']);
        Route::get('app/check-update', [AppUpdateController::class, 'checkUpdate']);
        Route::get('remote-config', [AppUpdateController::class, 'remoteConfig']);

        Route::middleware(['auth:sanctum', 'track.activity'])->group(function () {
            Route::get('preferences', [PreferenceController::class, 'show']);
            Route::put('preferences', [PreferenceController::class, 'update']);
            Route::patch('preferences/interests', [PreferenceController::class, 'updateInterests']);
            Route::patch('preferences/categories', [PreferenceController::class, 'updateCategories']);
            Route::patch('preferences/sources', [PreferenceController::class, 'updateSources']);
            Route::patch('preferences/language', [PreferenceController::class, 'updateLanguage']);
            Route::patch('preferences/location', [PreferenceController::class, 'updateLocation']);

            Route::post('analytics/article-view', [ClientAnalyticsController::class, 'trackArticleView']);
            Route::post('analytics/search', [ClientAnalyticsController::class, 'trackSearch']);
            Route::post('analytics/category-view', [ClientAnalyticsController::class, 'trackCategoryView']);

            Route::post('notifications/open', [ClientNotificationController::class, 'trackOpen']);
        });
    });
});
