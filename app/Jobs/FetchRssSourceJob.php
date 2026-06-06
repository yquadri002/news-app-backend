<?php

namespace App\Jobs;

use App\Models\RssSource;
use App\Services\Ingestion\RssAggregationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchRssSourceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 120;

    public function __construct(public int $sourceId)
    {
        $this->onQueue('rss');
    }

    public function handle(RssAggregationService $aggregationService): void
    {
        $source = RssSource::findOrFail($this->sourceId);
        $aggregationService->fetchSource($source);
    }
}
