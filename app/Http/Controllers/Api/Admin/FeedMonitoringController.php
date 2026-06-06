<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\FeedFetchStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\FeedFetchLogResource;
use App\Repositories\Contracts\FeedFetchLogRepositoryInterface;
use App\Repositories\Contracts\RssSourceRepositoryInterface;
use App\Services\RssSourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedMonitoringController extends Controller
{
    public function __construct(
        private readonly FeedFetchLogRepositoryInterface $feedFetchLogRepository,
        private readonly RssSourceRepositoryInterface $rssSourceRepository,
        private readonly RssSourceService $rssSourceService,
    ) {
    }

    public function dashboard(Request $request): JsonResponse
    {
        $stats = $this->feedFetchLogRepository->getDashboardStats([
            'from' => $request->get('from'),
            'to' => $request->get('to'),
        ]);

        $health = $this->rssSourceService->getHealthReport();

        return response()->json([
            'data' => [
                'fetch_stats' => $stats,
                'source_health' => [
                    'total' => $health['total'],
                    'healthy' => $health['healthy'],
                    'degraded' => $health['degraded'],
                    'unhealthy' => $health['unhealthy'],
                ],
            ],
        ]);
    }

    public function logs(Request $request): JsonResponse
    {
        $logs = $this->feedFetchLogRepository->paginate(
            (int) $request->get('per_page', 20),
            $request->only(['rss_source_id', 'status']),
        );

        return response()->json([
            'data' => FeedFetchLogResource::collection($logs),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    public function sourcePerformance(Request $request): JsonResponse
    {
        $sources = $this->rssSourceRepository->getActiveByPriority();

        $performance = $sources->map(function ($source) {
            $recentLogs = $this->feedFetchLogRepository->getRecentForSource($source->id, 10);

            return [
                'id' => $source->id,
                'name' => $source->name,
                'health_status' => $source->health_status?->value ?? $source->health_status,
                'last_fetched_at' => $source->last_fetched_at?->toIso8601String(),
                'article_count' => $source->articles()->count(),
                'recent_fetches' => $recentLogs->count(),
                'success_rate' => $recentLogs->count() > 0
                    ? round($recentLogs->where('status', FeedFetchStatus::Success)->count() / $recentLogs->count() * 100, 1)
                    : 0,
                'avg_duration_ms' => (int) $recentLogs->avg('duration_ms'),
            ];
        });

        return response()->json(['data' => $performance]);
    }

    public function triggerFetch(int $sourceId): JsonResponse
    {
        \App\Jobs\FetchRssSourceJob::dispatch($sourceId);

        return response()->json(['message' => 'Fetch job dispatched for source.']);
    }
}
