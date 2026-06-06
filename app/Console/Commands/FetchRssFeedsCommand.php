<?php

namespace App\Console\Commands;

use App\Jobs\FetchRssFeedsJob;
use App\Jobs\FetchRssSourceJob;
use App\Repositories\Contracts\RssSourceRepositoryInterface;
use Illuminate\Console\Command;

class FetchRssFeedsCommand extends Command
{
    protected $signature = 'rss:fetch {--source= : Fetch a specific source ID} {--sync : Run synchronously}';

    protected $description = 'Fetch RSS feeds and dispatch article processing jobs';

    public function handle(RssSourceRepositoryInterface $rssSourceRepository): int
    {
        if ($sourceId = $this->option('source')) {
            if ($this->option('sync')) {
                app(\App\Services\Ingestion\RssAggregationService::class)
                    ->fetchSource($rssSourceRepository->findOrFail((int) $sourceId));
            } else {
                FetchRssSourceJob::dispatch((int) $sourceId);
            }

            $this->info("Dispatched fetch for source #{$sourceId}");

            return self::SUCCESS;
        }

        if ($this->option('sync')) {
            $count = app(\App\Services\Ingestion\RssAggregationService::class)->fetchAllDue();
            $this->info("Fetched {$count} due sources synchronously.");
        } else {
            FetchRssFeedsJob::dispatch();
            $this->info('Dispatched RSS fetch job.');
        }

        return self::SUCCESS;
    }
}
