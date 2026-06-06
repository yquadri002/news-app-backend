<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationRecommendationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'article_id' => $this->article_id,
            'relevance_score' => (float) $this->relevance_score,
            'urgency_score' => (float) $this->urgency_score,
            'combined_score' => (float) $this->combined_score,
            'optimal_send_at' => $this->optimal_send_at?->toIso8601String(),
            'status' => $this->status?->value ?? $this->status,
            'reason' => $this->reason,
            'score_breakdown' => $this->score_breakdown,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'device_id' => $this->user->device_id,
                'language' => $this->user->language,
            ]),
            'article' => $this->whenLoaded('article', fn () => [
                'id' => $this->article->id,
                'title' => $this->article->title,
                'summary' => $this->article->summary,
                'image_url' => $this->article->image_url,
                'category' => $this->article->relationLoaded('category') && $this->article->category
                    ? ['id' => $this->article->category->id, 'name' => $this->article->category->name]
                    : null,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
