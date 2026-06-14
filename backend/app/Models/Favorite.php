<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    public $timestamps = false; // Only created_at

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the user who favorited
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the favorited product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
