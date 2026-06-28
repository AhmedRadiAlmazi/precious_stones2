<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'weight',
        'origin_country',
        'certification',
        'images',
        'is_featured',
        'is_active',
        'promotion_status',
        'views_count',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'views_count' => 'integer',
    ];

    // Relationships

    /**
     * Get the seller (user) that owns the product
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the category that the product belongs to
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the auction for this product
     */
    public function auction()
    {
        return $this->hasOne(Auction::class);
    }

    /**
     * Get all reviews for this product
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get all users who favorited this product
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /**
     * Get all orders for this product
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Scopes

    /**
     * Scope a query to only include active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured products
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include in-stock products
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }
}
