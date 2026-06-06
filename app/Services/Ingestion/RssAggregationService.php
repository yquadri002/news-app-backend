<?php

namespace App\Services\Ingestion;

use App\Enums\FeedFetchStatus;
use App\Enums\RssHealthStatus;
use App\Jobs\FetchRssSourceJob;
use App\Jobs\ProcessArticleJob;
use App\Models\RssSource;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\FeedFetchLogRepositoryInterface;
use App\Repositories\Contracts\RssSourceRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RssAggregationService
{
    public function __construct(
        private readonly RssSourceRepositoryInterface $rssSourceRepository,
        private readonly ArticleRepositoryInterface $articleRepository,
        private readonly FeedFetchLogRepositoryInterface $feedFetchLogRepository,
        private readonly RssParserService $parser,
    ) {
    }

    public function fetchAllDue(): int
    {
        $sources = $this->rssSourceRepository->getDueForFetch();
        $dispatched = 0;

        foreach ($sources as $source) {
            $this->fetchSource($source);
            $dispatched++;
        }

        return $dispatched;
    }

    public function fetchSource(RssSource $source, int $retryCount = 0): array
    {
        $startTime = microtime(true);
        $log = $this->feedFetchLogRepository->createLog($source->id, [
            'retry_count' => $retryCount,
            'metadata' => ['source_name' => $source->name],
        ]);

        $stats = ['fetched' => 0, 'processed' => 0, 'skipped' => 0, 'duplicates' => 0];

        try {
            $response = Http::timeout(20)
                ->retry(3, 1000)
                ->withHeaders(['User-Agent' => 'NewsHubPro/1.0 RSS Aggregator'])
                ->get($source->url);

            if (! $response->successful()) {
                throw new \RuntimeException("HTTP {$response->status()}");
            }

            $items = $this->parser->parse($response->body());
            $stats['fetched'] = count($items);

            foreach ($items as $item) {
                if ($this->articleRepository->findByGuid($item['guid'] ?? '')) {
                    $stats['skipped']++;

                    continue;
                }

                ProcessArticleJob::dispatch($source->id, $item, $source->name);
                $stats['processed']++;
            }

            $this->rssSourceRepository->updateHealth($source->id, [
                'health_status' => RssHealthStatus::Healthy,
                'error_count' => 0,
                'last_error' => null,
                'last_fetched_at' => now(),
            ]);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            $this->feedFetchLogRepository->completeLog($log, [
                'status' => FeedFetchStatus::Success,
                'items_fetched' => $stats['fetched'],
                'items_processed' => $stats['processed'],
                'items_skipped' => $stats['skipped'],
                'duration_ms' => $durationMs,
            ]);

            return $stats;
        } catch (\Throwable $e) {
            Log::error('RSS fetch failed', ['source_id' => $source->id, 'error' => $e->getMessage()]);

            $errorCount = $source->error_count + 1;
            $healthStatus = $errorCount >= 5
                ? RssHealthStatus::Unhealthy
                : ($errorCount >= 2 ? RssHealthStatus::Degraded : RssHealthStatus::Unknown);

            $this->rssSourceRepository->updateHealth($source->id, [
                'health_status' => $healthStatus,
                'error_count' => $errorCount,
                'last_error' => $e->getMessage(),
            ]);

            if ($retryCount < 3) {
                $this->feedFetchLogRepository->completeLog($log, [
                    'status' => FeedFetchStatus::Retrying,
                    'error_message' => $e->getMessage(),
                    'duration_ms' => (int) ((microtime(true) - $startTime) * 1000),
                ]);

                FetchRssSourceJob::dispatch($source->id)
                    ->delay(now()->addMinutes(2 ** $retryCount));
            } else {
                $this->feedFetchLogRepository->completeLog($log, [
                    'status' => FeedFetchStatus::Failed,
                    'error_message' => $e->getMessage(),
                    'duration_ms' => (int) ((microtime(true) - $startTime) * 1000),
                ]);
            }

            return $stats;
        }
    }
}
