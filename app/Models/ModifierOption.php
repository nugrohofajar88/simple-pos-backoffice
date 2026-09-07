<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierOption extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'modifier_group_id',
        'name',
        'price_delta',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function modifierGroup(): BelongsTo
    {
        return $this->belongsTo(ModifierGroup::class);
    }
}
