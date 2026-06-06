<?php

use App\Jobs\AnalyzeNotificationPerformanceJob;
use App\Jobs\CalculateInterestProfilesJob;
use App\Jobs\CalculateLifetimeValueJob;
use App\Jobs\CalculateRevenueMetricsJob;
use App\Jobs\CalculateTrendingJob;
use App\Jobs\GenerateDigestJob;
use App\Jobs\GenerateGrowthReportsJob;
use App\Jobs\GenerateNotificationRecommendationsJob;
use App\Jobs\GenerateRecommendationsJob;
use App\Jobs\GenerateUserSegmentsJob;
use App\Jobs\OptimizeAdPlacementsJob;
use App\Jobs\RefreshTrendingScoresJob;
use App\Jobs\SendSegmentNotificationsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$scheduled = static function ($task) {
    return $task->withoutOverlapping(10)->onOneServer();
};

$scheduled(Schedule::command('notifications:process-scheduled'))->everyMinute();
$scheduled(Schedule::command('rss:monitor-health'))->hourly();
$scheduled(Schedule::command('rss:fetch'))->everyFiveMinutes();
$scheduled(Schedule::job(new CalculateTrendingJob))->everyTenMinutes();
$scheduled(Schedule::command('news:detect-breaking'))->everyFifteenMinutes();
$scheduled(Schedule::job(new CalculateInterestProfilesJob))->hourly();
$scheduled(Schedule::job(new GenerateRecommendationsJob))->everyFifteenMinutes();
$scheduled(Schedule::job(new RefreshTrendingScoresJob))->everyTenMinutes();
$scheduled(Schedule::job(new GenerateUserSegmentsJob))->daily();
$scheduled(Schedule::job(new GenerateNotificationRecommendationsJob))->everyFifteenMinutes();
$scheduled(Schedule::job(new GenerateDigestJob))->hourly();
$scheduled(Schedule::job(new SendSegmentNotificationsJob))->everyThirtyMinutes();
$scheduled(Schedule::job(new AnalyzeNotificationPerformanceJob))->daily();
$scheduled(Schedule::job(new CalculateRevenueMetricsJob))->daily();
$scheduled(Schedule::job(new OptimizeAdPlacementsJob))->hourly();
$scheduled(Schedule::job(new GenerateGrowthReportsJob))->daily();
$scheduled(Schedule::job(new CalculateLifetimeValueJob))->weekly();
$scheduled(Schedule::command('horizon:snapshot'))->everyFiveMinutes();
$scheduled(Schedule::command('infrastructure:monitor'))->everyFiveMinutes();
$scheduled(Schedule::command('backup:database'))->dailyAt('02:00');
$scheduled(Schedule::command('backup:verify'))->dailyAt('03:00');
