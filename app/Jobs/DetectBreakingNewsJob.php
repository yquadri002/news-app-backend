<?php

namespace App\Jobs;

use App\Models\Article;
use App\Services\Ingestion\BreakingNewsDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DetectBreakingNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $articleId)
    {
        $this->onQueue('ingestion');
    }

    public function handle(BreakingNewsDetectionService $breakingNewsDetection): void
    {
        $article = Article::find($this->articleId);

        if (! $article || $article->is_duplicate) {
            return;
        }

        $breakingNewsDetection->detect($article);
    }
}
