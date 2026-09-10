<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherIncome extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'amount',
        'mobile_created_at',
    ];

    protected function casts(): array
    {
        return [
            'mobile_created_at' => 'datetime',
        ];
    }
}
