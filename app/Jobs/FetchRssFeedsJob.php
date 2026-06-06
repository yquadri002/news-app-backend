<?php

namespace App\Jobs;

use App\Services\Ingestion\RssAggregationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchRssFeedsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct()
    {
        $this->onQueue('rss');
    }

    public function handle(RssAggregationService $aggregationService): void
    {
        $aggregationService->fetchAllDue();
    }
}
