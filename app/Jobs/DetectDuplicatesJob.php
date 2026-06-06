<?php

namespace App\Jobs;

use App\Models\Article;
use App\Services\Ingestion\DuplicateDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DetectDuplicatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $articleId)
    {
        $this->onQueue('ingestion');
    }

    public function handle(DuplicateDetectionService $duplicateDetection): void
    {
        $article = Article::find($this->articleId);

        if (! $article || $article->is_duplicate) {
            return;
        }

        $original = $duplicateDetection->detect($article);

        if ($original) {
            $duplicateDetection->mergeDuplicate($article, $original);
        }
    }
}
