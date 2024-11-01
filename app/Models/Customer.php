<?php
// app/Models/Customer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $primaryKey = 'customer_id';

    protected $fillable = [
        'full_name',
        'birth_date',
        'phone_number',
        'email',
        'address',
        'profile_image',
        'gender',
        // ... tambahkan field lain yang perlu diupdate
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
