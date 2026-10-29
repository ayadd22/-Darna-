<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'path'       => $this->path,
            'alt_text'   => $this->alt_text,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
