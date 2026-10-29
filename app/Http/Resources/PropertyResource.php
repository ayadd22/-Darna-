<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ImageResource;


class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,

            'property_type' => $this->property_type->value,
            'listing_type' => $this->listing_type->value,
            'status' => $this->status->value,

            'price' => $this->price,
            'currency' => $this->currency->value,
            'area' => $this->area,

            'address' => $this->address,
            'contact_phone' => $this->contact_phone,

            'office_id' => $this->office_id,
            'owner_id' => $this->owner_id,
            'governorate_id' => $this->governorate_id,
            'images' => ImageResource::collection($this->whenLoaded('images')),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
