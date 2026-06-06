<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedFetchLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rss_source_id' => $this->rss_source_id,
            'source_name' => $this->whenLoaded('rssSource', fn () => $this->rssSource->name),
            'status' => $this->status?->value ?? $this->status,
            'items_fetched' => $this->items_fetched,
            'items_processed' => $this->items_processed,
            'items_skipped' => $this->items_skipped,
            'items_duplicates' => $this->items_duplicates,
            'duration_ms' => $this->duration_ms,
            'retry_count' => $this->retry_count,
            'error_message' => $this->error_message,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
