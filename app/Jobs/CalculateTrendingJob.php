<?php

namespace App\Jobs;

use App\Services\Ingestion\TrendingEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateTrendingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('ingestion');
    }

    public function handle(TrendingEngineService $trendingEngine): void
    {
        $trendingEngine->updateViewMetrics();
        $trendingEngine->calculateAll();
    }
}
