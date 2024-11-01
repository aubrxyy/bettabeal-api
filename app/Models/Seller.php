<?php

// app/Models/Seller.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;
    protected $primaryKey = 'seller_id';

    protected $fillable = [
        'user_id', 'store_name', 'store_address', 'store_logo', 'store_description', 'store_rating', 'total_sales', 'phone_number', 'email'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

