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
