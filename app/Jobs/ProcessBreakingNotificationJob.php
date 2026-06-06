<?php

namespace App\Jobs;

use App\Models\Article;
use App\Services\NotificationIntelligence\BreakingNewsAutomationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBreakingNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $articleId)
    {
        $this->onQueue('notifications');
    }

    public function handle(BreakingNewsAutomationService $automation): void
    {
        $article = Article::find($this->articleId);

        if (! $article || ! $article->is_breaking) {
            return;
        }

        $threshold = config('notification_intelligence.breaking.urgency_threshold', 15.0);
        if ((float) $article->breaking_score < $threshold) {
            return;
        }

        $automation->processBreakingArticles();
    }
}
