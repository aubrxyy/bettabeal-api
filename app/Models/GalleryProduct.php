<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryProduct extends Model
{
    // Nama tabel yang digunakan
    protected $table = 'gallery_products';

    // Jika primary key bukan 'id', sebutkan secara eksplisit
    protected $primaryKey = 'gallery_id';  // Primary key dari tabel gallery_products

    // Jika primary key menggunakan auto-increment
    // public $incrementing = true;
    // protected $keyType = 'bigint';  // Primary key adalah bigint

    // Kolom yang bisa diisi secara massal
    protected $fillable = [
        'product_id', 'seller_id', 'image_url', 'uploaded_at'
    ];

    // Timestamps otomatis diaktifkan
    public $timestamps = true;

    // Definisikan relasi dengan tabel Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    // Definisikan relasi dengan tabel Seller
    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'seller_id');
    }

    
}
