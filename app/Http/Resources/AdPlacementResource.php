<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdPlacementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'placement_key' => $this->placement_key,
            'format' => $this->format,
            'is_enabled' => $this->is_enabled,
            'frequency_cap' => $this->frequency_cap,
            'frequency_period_minutes' => $this->frequency_period_minutes,
            'remote_config' => $this->remote_config,
            'ab_test_variant' => $this->ab_test_variant,
            'sort_order' => $this->sort_order,
            'ab_tests' => AdAbTestResource::collection($this->whenLoaded('abTests')),
        ];
    }
}
