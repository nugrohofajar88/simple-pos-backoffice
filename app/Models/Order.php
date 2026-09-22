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
        'source',
        'customer_name',
        'customer_phone',
        'subtotal',
        'total',
        'payment_method',
        'fulfillment_method',
        'delivery_address',
        'delivery_fee',
        'note',
        'mobile_created_at',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'mobile_created_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
