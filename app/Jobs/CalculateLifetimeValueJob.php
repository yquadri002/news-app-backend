<?php

namespace App\Jobs;

use App\Services\Revenue\GrowthAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateLifetimeValueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('analytics');
    }

    public function handle(GrowthAnalyticsService $growth): void
    {
        $growth->calculateAllLtv();
    }
}
