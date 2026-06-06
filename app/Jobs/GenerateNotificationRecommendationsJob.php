<?php

namespace App\Jobs;

use App\Services\NotificationIntelligence\NotificationRecommendationEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateNotificationRecommendationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId = null)
    {
        $this->onQueue('notifications');
    }

    public function handle(NotificationRecommendationEngine $engine): void
    {
        $engine->generateRecommendations($this->userId);
        $engine->processDueRecommendations();
    }
}
