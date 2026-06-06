<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'content' => $this->when($request->routeIs('*.article'), $this->content),
            'image_url' => $this->image_url,
            'images' => ArticleImageResource::collection($this->whenLoaded('images')),
            'author' => $this->author,
            'source_name' => $this->source_name,
            'external_url' => $this->external_url,
            'canonical_url' => $this->canonical_url,
            'is_breaking' => $this->is_breaking,
            'breaking_score' => (float) $this->breaking_score,
            'trending_score' => (float) $this->trending_score,
            'view_count' => $this->view_count,
            'published_at' => $this->published_at?->toIso8601String(),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'auto_category' => new CategoryResource($this->whenLoaded('autoCategory')),
            'categories' => CategoryResource::collection($this->whenLoaded('assignedCategories')),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->pluck('tag')),
            'metrics' => new ArticleMetricResource($this->whenLoaded('metrics')),
            'rss_source' => $this->whenLoaded('rssSource', fn () => [
                'id' => $this->rssSource->id,
                'name' => $this->rssSource->name,
            ]),
        ];
    }
}
