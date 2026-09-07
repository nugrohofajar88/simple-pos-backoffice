<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'name',
        'selection_type',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ModifierOption::class);
    }
}
