<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BidResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'amount'     => $this->amount,
            'is_winning' => $this->is_winning,
            'user'       => new UserResource($this->whenLoaded('user')),
            'auction'    => new AuctionResource($this->whenLoaded('auction')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
