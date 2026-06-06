<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdAbTestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'variant_key' => $this->variant_key,
            'traffic_percentage' => $this->traffic_percentage,
            'config' => $this->config,
            'is_active' => $this->is_active,
            'impressions' => $this->impressions,
            'clicks' => $this->clicks,
            'revenue' => $this->revenue,
        ];
    }
}
