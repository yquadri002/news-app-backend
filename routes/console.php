<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('notifications:process-scheduled')->everyMinute();
Schedule::command('rss:monitor-health')->hourly();
Schedule::command('rss:fetch')->everyFiveMinutes();
Schedule::job(new \App\Jobs\CalculateTrendingJob)->everyTenMinutes();
Schedule::command('news:detect-breaking')->everyFifteenMinutes();
Schedule::job(new \App\Jobs\CalculateInterestProfilesJob)->hourly();
Schedule::job(new \App\Jobs\GenerateRecommendationsJob)->everyFifteenMinutes();
Schedule::job(new \App\Jobs\RefreshTrendingScoresJob)->everyTenMinutes();
Schedule::job(new \App\Jobs\GenerateUserSegmentsJob)->daily();
Schedule::job(new \App\Jobs\GenerateNotificationRecommendationsJob)->everyFifteenMinutes();
Schedule::job(new \App\Jobs\GenerateDigestJob)->hourly();
Schedule::job(new \App\Jobs\SendSegmentNotificationsJob)->everyThirtyMinutes();
Schedule::job(new \App\Jobs\AnalyzeNotificationPerformanceJob)->daily();
Schedule::job(new \App\Jobs\CalculateRevenueMetricsJob)->daily();
Schedule::job(new \App\Jobs\OptimizeAdPlacementsJob)->hourly();
Schedule::job(new \App\Jobs\GenerateGrowthReportsJob)->daily();
Schedule::job(new \App\Jobs\CalculateLifetimeValueJob)->weekly();
