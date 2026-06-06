<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserInterestProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'is_cold_start' => $this->is_cold_start,
            'primary_segment' => $this->primary_segment,
            'top_topics' => $this->top_topics ?? [],
            'profile_strength' => (float) $this->profile_strength,
            'total_events' => $this->total_events,
            'last_calculated_at' => $this->last_calculated_at?->toIso8601String(),
            'category_scores' => $this->whenLoaded('categoryScores', fn () =>
                $this->categoryScores->map(fn ($s) => [
                    'category_id' => $s->category_id,
                    'category_name' => $s->category?->name,
                    'score' => (float) $s->score,
                ])
            ),
            'source_scores' => $this->whenLoaded('sourceScores', fn () =>
                $this->sourceScores->map(fn ($s) => [
                    'source_id' => $s->rss_source_id,
                    'source_name' => $s->rssSource?->name,
                    'score' => (float) $s->score,
                ])
            ),
        ];
    }
}
