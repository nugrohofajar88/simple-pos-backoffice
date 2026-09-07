<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemModifier extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'modifier_group_name',
        'modifier_option_name',
        'price_delta',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
