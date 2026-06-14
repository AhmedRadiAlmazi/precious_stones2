<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'seller_id',
        'starting_price',
        'current_price',
        'reserve_price',
        'start_time',
        'end_time',
        'status',
        'winner_id',
        'bid_increment',
        'total_bids',
    ];

    protected $casts = [
        'starting_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'reserve_price' => 'decimal:2',
        'bid_increment' => 'decimal:2',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_bids' => 'integer',
    ];

    // Relationships

    /**
     * Get the product being auctioned
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the seller (user) of the auction
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the winner (user) of the auction
     */
    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    /**
     * Get all bids for this auction
     */
    public function bids()
    {
        return $this->hasMany(Bid::class)->orderBy('amount', 'desc');
    }

    /**
     * Get the winning bid
     */
    public function winningBid()
    {
        return $this->hasOne(Bid::class)->where('is_winning', true);
    }

    /**
     * Get the order for this auction (if sold)
     */
    public function order()
    {
        return $this->hasOne(Order::class);
    }

    // Scopes

    /**
     * Scope a query to only include active auctions (Live)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('start_time', '<=', now())
                     ->where('end_time', '>', now());
    }

    /**
     * Scope a query to only include upcoming auctions (Approved but not started)
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'active')
                     ->where('start_time', '>', now());
    }

    /**
     * Scope a query to only include ended auctions
     */
    public function scopeEnded($query)
    {
        return $query->where('status', 'ended')
                     ->orWhere(function($q) {
                         $q->where('status', 'active')
                           ->where('end_time', '<=', now());
                     });
    }

    /**
     * Scope a query to only include auctions ending soon (within 24 hours)
     */
    public function scopeEndingSoon($query)
    {
        return $query->active()
                     ->where('end_time', '<=', now()->addHours(24));
    }

    // Helper Methods

    /**
     * Check if auction is active
     */
    public function isActive()
    {
        return $this->status === 'active' && 
               now()->between($this->start_time, $this->end_time);
    }

    /**
     * Check if auction has ended
     */
    public function hasEnded()
    {
        return $this->status === 'ended' || now()->greaterThan($this->end_time);
    }
}
