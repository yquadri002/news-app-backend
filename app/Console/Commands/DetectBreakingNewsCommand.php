<?php

namespace App\Console\Commands;

use App\Services\Ingestion\BreakingNewsDetectionService;
use Illuminate\Console\Command;

class DetectBreakingNewsCommand extends Command
{
    protected $signature = 'news:detect-breaking';

    protected $description = 'Run breaking news detection on recent articles';

    public function handle(BreakingNewsDetectionService $service): int
    {
        $count = $service->detectAll();
        $this->info("Analyzed {$count} articles for breaking news.");

        return self::SUCCESS;
    }
}
