<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationFeedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'data' => NewsArticleResource::collection($this->resource['articles'] ?? collect()),
            'meta' => $this->resource['meta'] ?? [],
            'scores' => $this->when(
                $request->boolean('include_scores'),
                $this->resource['scores'] ?? []
            ),
        ];
    }
}
