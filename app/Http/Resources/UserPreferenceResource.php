<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPreferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'interests' => $this->interests ?? [],
            'category_ids' => $this->category_ids ?? [],
            'source_ids' => $this->source_ids ?? [],
            'language' => $this->language,
            'location' => $this->location,
            'notifications_enabled' => $this->notifications_enabled,
            'breaking_news_enabled' => $this->breaking_news_enabled,
        ];
    }
}
