<?php

namespace App\Jobs;

use App\Services\Revenue\GrowthAnalyticsService;
use App\Services\Revenue\UserMonetizationSegmentationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateGrowthReportsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('analytics');
    }

    public function handle(
        GrowthAnalyticsService $growth,
        UserMonetizationSegmentationService $segmentation,
    ): void {
        $growth->calculateDailyMetrics($this->date);
        $segmentation->segmentAllActiveUsers();
    }
}
