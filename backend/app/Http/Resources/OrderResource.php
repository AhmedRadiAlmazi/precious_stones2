<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'order_number'     => $this->order_number,
            'total_amount'     => $this->total_amount,
            'quantity'         => $this->quantity,
            'status'           => $this->status,
            'payment_method'   => $this->payment_method,
            'payment_status'   => $this->payment_status,
            'shipping_address' => $this->shipping_address,
            'tracking_number'  => $this->tracking_number,
            'notes'            => $this->notes,
            'is_from_auction'  => $this->isFromAuction(),
            'buyer'            => new UserResource($this->whenLoaded('buyer')),
            'seller'           => new UserResource($this->whenLoaded('seller')),
            'product'          => new ProductResource($this->whenLoaded('product')),
            'auction'          => new AuctionResource($this->whenLoaded('auction')),
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
        ];
    }
}
