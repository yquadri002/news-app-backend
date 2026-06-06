<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RssSourceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'priority' => $this->priority,
            'is_active' => $this->is_active,
            'health_status' => $this->health_status?->value ?? $this->health_status,
            'error_count' => $this->error_count,
            'last_error' => $this->last_error,
            'is_validated' => $this->is_validated,
            'last_validated_at' => $this->last_validated_at?->toIso8601String(),
            'last_fetched_at' => $this->last_fetched_at?->toIso8601String(),
            'fetch_interval_minutes' => $this->fetch_interval_minutes,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
