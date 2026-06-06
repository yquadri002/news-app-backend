<?php

namespace App\Jobs;

use App\Services\NotificationIntelligence\NotificationIntelligenceAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeNotificationPerformanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('notifications');
    }

    public function handle(NotificationIntelligenceAnalyticsService $analytics): void
    {
        $analytics->calculateDailySnapshot($this->date);
    }
}
