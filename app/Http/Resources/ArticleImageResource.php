<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'url' => $this->url,
            'is_primary' => $this->is_primary,
            'width' => $this->width,
            'height' => $this->height,
            'alt_text' => $this->alt_text,
        ];
    }
}
