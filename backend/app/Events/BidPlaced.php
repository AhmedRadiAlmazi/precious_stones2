<?php

namespace App\Events;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bid;
    public $auction;

    /**
     * Create a new event instance.
     */
    public function __construct(Bid $bid, Auction $auction)
    {
        $this->bid = $bid;
        $this->auction = $auction;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('auction.' . $this->auction->id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->bid->id,
            'amount' => (float) $this->bid->amount,
            'user' => [
                'id' => $this->bid->user->id,
                'name' => $this->bid->user->first_name . ' ' . $this->bid->user->last_name,
            ],
            'auction' => [
                'id' => $this->auction->id,
                'current_price' => (float) $this->auction->current_price,
                'end_time' => $this->auction->end_time->toIso8601String(),
                'total_bids' => $this->auction->total_bids,
            ],
        ];
    }
}
