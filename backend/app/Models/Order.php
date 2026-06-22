<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'product_id',
        'auction_id',
        'order_number',
        'total_amount',
        'quantity',
        'status',
        'payment_method',
        'payment_status',
        'shipping_address',
        'tracking_number',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'quantity' => 'integer',
        'shipping_address' => 'array',
    ];

    // Relationships

    /**
     * Get the buyer (user) of this order
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the seller (user) of this order
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the product for this order
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the auction for this order (if from auction)
     */
    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }

    // Scopes

    /**
     * Scope a query to only include pending orders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include paid orders
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'completed');
    }

    /**
     * Scope a query to only include shipped orders
     */
    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    /**
     * Scope a query to only include delivered orders
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    // Helper Methods

    /**
     * Check if order is from auction
     */
    public function isFromAuction()
    {
        return !is_null($this->auction_id);
    }

    /**
     * Check if order is paid
     */
    public function isPaid()
    {
        return $this->payment_status === 'completed';
    }
}
