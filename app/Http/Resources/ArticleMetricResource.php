<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleMetricResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'read_count' => $this->read_count,
            'share_count' => $this->share_count,
            'views_1h' => $this->views_1h,
            'views_24h' => $this->views_24h,
            'trending_score' => (float) $this->trending_score,
            'velocity_score' => (float) $this->velocity_score,
            'engagement_score' => (float) $this->engagement_score,
            'breaking_score' => (float) $this->breaking_score,
            'source_confirmation_count' => $this->source_confirmation_count,
        ];
    }
}
