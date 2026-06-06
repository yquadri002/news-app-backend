<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'permissions' => $this->permissions,
            'description' => $this->description,
            'is_system' => $this->is_system,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
