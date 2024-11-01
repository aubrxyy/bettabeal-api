<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'rating',
        'review_text',
        'purchase_verified',
        'review_date',
        'helpful_count'
    ];

    protected $casts = [
        'rating' => 'integer',
        'purchase_verified' => 'boolean',
        'review_date' => 'datetime',
        'helpful_count' => 'integer'
    ];

    /**
     * Relasi ke model Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relasi ke model User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke model Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relasi ke model ReviewImage
     */
    public function images()
    {
        return $this->hasMany(ReviewImage::class);
    }

    /**
     * Relasi ke model ReviewComment
     */
    public function comments()
    {
        return $this->hasMany(ReviewComment::class);
    }

    /**
     * Relasi ke model ReviewLike
     */
    public function likes()
    {
        return $this->hasMany(ReviewLike::class);
    }

    /**
     * Relasi ke model SellerResponse
     */
    public function sellerResponse()
    {
        return $this->hasOne(SellerResponse::class);
    }
}
