<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Menentukan primary key untuk tabel products
    protected $primaryKey = 'product_id';

    // Menentukan kolom yang dapat diisi secara massal (mass assignable)
    protected $fillable = [
        'seller_id',
        'category_id',
        'product_name',
        'description',
        'price',
        'stock_quantity',
        'created_at',
        'updated_at', // Sertakan ini jika kamu ingin timestamps juga bisa diisi massal
    ];

    // Menonaktifkan timestamps otomatis jika tidak diperlukan (opsional)
    public $timestamps = true;

    // Relasi ke model Seller (setiap produk dimiliki oleh satu seller)
    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    // Relasi ke model Category (setiap produk memiliki satu kategori)
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Relasi ke model GalleryProduct (setiap produk bisa memiliki banyak gambar)
    public function galleryImages()
    {
        return $this->hasMany(GalleryProduct::class, 'product_id');
    }
}
