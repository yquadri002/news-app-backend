<?php

namespace App\Jobs;

use App\Services\Ingestion\TrendingEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshTrendingScoresJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('recommendations');
    }

    public function handle(TrendingEngineService $trendingEngine): void
    {
        $trendingEngine->updateViewMetrics();
        $trendingEngine->calculateAll();
    }
}
