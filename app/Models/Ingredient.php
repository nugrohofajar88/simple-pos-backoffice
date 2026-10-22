<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'purchase_price',
        'purchase_qty',
        'unit',
        'is_active',
        'sort_order',
    ];

    protected $appends = [
        'price_per_unit',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'purchase_qty' => 'decimal:2',
        ];
    }

    public function getPricePerUnitAttribute(): float
    {
        return $this->purchase_qty > 0 ? $this->purchase_price / $this->purchase_qty : 0;
    }

    public function productIngredients(): HasMany
    {
        return $this->hasMany(ProductIngredient::class);
    }
}
