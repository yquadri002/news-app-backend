<?php

namespace App\Jobs;

use App\Models\RssSource;
use App\Services\Ingestion\ArticleIngestionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $sourceId,
        public array $rawItem,
        public string $sourceName,
    ) {
        $this->onQueue('ingestion');
    }

    public function handle(ArticleIngestionService $ingestionService): void
    {
        $source = RssSource::find($this->sourceId);

        $ingestionService->ingest(
            $this->sourceId,
            $this->rawItem,
            $this->sourceName,
            $source?->category_id,
        );
    }
}
