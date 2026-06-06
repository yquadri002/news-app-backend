<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbTestResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'test_type' => $this->test_type?->value ?? $this->test_type,
            'variants' => $this->variants,
            'status' => $this->status?->value ?? $this->status,
            'winning_variant' => $this->winning_variant,
            'metrics' => $this->metrics,
            'impressions' => $this->impressions,
            'conversions' => $this->conversions,
            'revenue' => (float) $this->revenue,
            'started_at' => $this->started_at?->toIso8601String(),
            'ended_at' => $this->ended_at?->toIso8601String(),
        ];
    }
}
