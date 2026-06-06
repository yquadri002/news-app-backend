<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'platform' => $this->platform?->value ?? $this->platform,
            'version_code' => $this->version_code,
            'version_name' => $this->version_name,
            'is_force_update' => $this->is_force_update,
            'is_soft_update' => $this->is_soft_update,
            'release_notes' => $this->release_notes,
            'download_url' => $this->download_url,
            'min_supported_version_code' => $this->min_supported_version_code,
            'is_active' => $this->is_active,
            'released_at' => $this->released_at?->toIso8601String(),
        ];
    }
}
