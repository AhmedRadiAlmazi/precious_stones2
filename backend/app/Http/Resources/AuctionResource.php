<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'starting_price'  => $this->starting_price,
            'current_price'   => $this->current_price,
            'reserve_price'   => $this->reserve_price,
            'bid_increment'   => $this->bid_increment,
            'total_bids'      => $this->total_bids,
            'status'          => $this->status,
            'start_time'      => $this->start_time?->toISOString(),
            'end_time'        => $this->end_time?->toISOString(),
            'is_active'       => $this->isActive(),
            'has_ended'       => $this->hasEnded(),
            'product'         => new ProductResource($this->whenLoaded('product')),
            'seller'          => new UserResource($this->whenLoaded('seller')),
            'winner'          => new UserResource($this->whenLoaded('winner')),
            'bids_count'      => $this->when(isset($this->bids_count), $this->bids_count),
            'bids'            => BidResource::collection($this->whenLoaded('bids')),
            'winning_bid'     => new BidResource($this->whenLoaded('winningBid')),
            'created_at'      => $this->created_at?->toISOString(),
        ];
    }
}
