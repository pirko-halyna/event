<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'payment_status',
        'payment_method',
        'purchase_date'
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
