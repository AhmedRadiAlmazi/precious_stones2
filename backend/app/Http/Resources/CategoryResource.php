<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'description'    => $this->description,
            'icon'           => $this->icon,
            'is_active'      => $this->is_active,
            'products_count' => $this->when(isset($this->products_count), $this->products_count),
            'created_at'     => $this->created_at?->toISOString(),
        ];
    }
}
