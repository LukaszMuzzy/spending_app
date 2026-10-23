<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'amount',
        'frequency',
        'next_date',
        'is_active',
        'shopping_group_id',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'next_date' => 'date:Y-m-d',
        'is_active' => 'boolean',
    ];

    public function shoppingGroup(): BelongsTo
    {
        return $this->belongsTo(ShoppingGroup::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
