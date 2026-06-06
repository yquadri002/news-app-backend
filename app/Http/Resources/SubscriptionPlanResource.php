<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'plan_type' => $this->plan_type?->value ?? $this->plan_type,
            'billing_period' => $this->billing_period,
            'price' => (float) $this->price,
            'currency' => $this->currency,
            'trial_days' => $this->trial_days,
            'features' => $this->features,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
