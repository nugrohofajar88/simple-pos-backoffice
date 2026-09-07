<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'status',
        'customer_name',
        'subtotal',
        'total',
        'payment_method',
        'note',
        'mobile_created_at',
    ];

    protected function casts(): array
    {
        return [
            'mobile_created_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
