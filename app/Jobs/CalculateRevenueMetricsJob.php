<?php

namespace App\Jobs;

use App\Services\Revenue\RevenueAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateRevenueMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('analytics');
    }

    public function handle(RevenueAnalyticsService $analytics): void
    {
        $analytics->calculateDailySnapshots($this->date);
    }
}
