<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'content' => $this->when($request->routeIs('*.show'), $this->content),
            'image_url' => $this->image_url,
            'external_url' => $this->external_url,
            'author' => $this->author,
            'is_breaking' => $this->is_breaking,
            'breaking_marked_at' => $this->breaking_marked_at?->toIso8601String(),
            'view_count' => $this->view_count,
            'published_at' => $this->published_at?->toIso8601String(),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'rss_source' => new RssSourceResource($this->whenLoaded('rssSource')),
        ];
    }
}
