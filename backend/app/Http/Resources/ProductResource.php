<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'description'    => $this->description,
            'price'          => $this->price,
            'stock'          => $this->stock,
            'weight'         => $this->weight,
            'origin_country' => $this->origin_country,
            'certification'  => $this->certification,
            'images'         => $this->images ?? [],
            'is_featured'    => $this->is_featured,
            'is_active'      => $this->is_active,
            'views_count'    => $this->views_count,
            'seller'         => new UserResource($this->whenLoaded('seller')),
            'category'       => new CategoryResource($this->whenLoaded('category')),
            'reviews_count'  => $this->when(isset($this->reviews_count), $this->reviews_count),
            'reviews'        => ReviewResource::collection($this->whenLoaded('reviews')),
            'auction'        => new AuctionResource($this->whenLoaded('auction')),
            'created_at'     => $this->created_at?->toISOString(),
            'updated_at'     => $this->updated_at?->toISOString(),
        ];
    }
}
