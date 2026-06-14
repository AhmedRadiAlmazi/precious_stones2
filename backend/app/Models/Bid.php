<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    use HasFactory;

    public $timestamps = false; // Only created_at

    protected $fillable = [
        'auction_id',
        'user_id',
        'amount',
        'is_winning',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_winning' => 'boolean',
        'created_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the auction this bid belongs to
     */
    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }

    /**
     * Get the user who placed this bid
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes

    /**
     * Scope a query to only include winning bids
     */
    public function scopeWinning($query)
    {
        return $query->where('is_winning', true);
    }

    /**
     * Scope a query to get latest bids first
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
