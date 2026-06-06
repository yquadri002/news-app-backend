<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\RssSourceRepositoryInterface;
use App\Services\RssSourceService;
use Illuminate\Console\Command;

class MonitorRssSources extends Command
{
    protected $signature = 'rss:monitor-health';

    protected $description = 'Validate and monitor RSS source health';

    public function handle(
        RssSourceRepositoryInterface $rssSourceRepository,
        RssSourceService $rssSourceService,
    ): int {
        $sources = $rssSourceRepository->getActiveByPriority();

        foreach ($sources as $source) {
            $result = $rssSourceService->validateSource($source->id);
            $status = $result['health_status'] ?? 'unknown';
            $this->line("{$source->name}: {$status}");
        }

        $this->info("Monitored {$sources->count()} RSS sources.");

        return self::SUCCESS;
    }
}
