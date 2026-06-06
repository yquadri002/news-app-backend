<?php

namespace App\Jobs;

use App\Services\Revenue\AdMediationService;
use App\Services\Revenue\AdOptimizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OptimizeAdPlacementsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $placementId = null)
    {
        $this->onQueue('analytics');
    }

    public function handle(AdOptimizationService $optimization, AdMediationService $mediation): void
    {
        $optimization->optimizePlacements();
        $mediation->optimizeWaterfall($this->placementId);
    }
}
