<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'image_url' => $this->image_url,
            'action_type' => $this->action_type,
            'action_data' => $this->action_data,
            'target_type' => $this->target_type?->value ?? $this->target_type,
            'status' => $this->status?->value ?? $this->status,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'total_recipients' => $this->total_recipients,
            'delivered_count' => $this->delivered_count,
            'opened_count' => $this->opened_count,
            'failed_count' => $this->failed_count,
            'creator' => new AdminResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
