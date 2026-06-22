<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'account_type',
        'is_approved',
        'is_active',
        'avatar',
        'settings',
        'wallet_balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
            'is_active' => 'boolean',
            'settings' => 'array',
            'wallet_balance' => 'float',
        ];
    }

    // Relationships

    /**
     * Get all products listed by this seller
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    /**
     * Get all auctions created by this seller
     */
    public function auctions()
    {
        return $this->hasMany(Auction::class, 'seller_id');
    }

    /**
     * Get all bids placed by this user
     */
    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    /**
     * Get all orders as a buyer
     */
    public function purchases()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /**
     * Get all orders as a seller
     */
    public function sales()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    /**
     * Get all favorite products
     */
    public function favorites()
    {
        return $this->belongsToMany(Product::class, 'favorites')->withTimestamps();
    }

    /**
     * Get all reviews written by this user
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get auctions won by this user
     */
    public function wonAuctions()
    {
        return $this->hasMany(Auction::class, 'winner_id');
    }
}
