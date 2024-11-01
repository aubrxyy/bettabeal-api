<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $primaryKey = 'category_id'; // Tambahkan ini

    protected $fillable = [
        'category_name',
        'slug',
        'description',
        'icon',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    // Auto-generate slug
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            $category->slug = Str::slug($category->category_name);
        });
    }

    // Products Relation
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Get active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Get ordered categories
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}