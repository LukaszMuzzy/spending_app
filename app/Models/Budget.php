<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'shopping_group_id',
        'shopping_type_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function shoppingGroup(): BelongsTo
    {
        return $this->belongsTo(ShoppingGroup::class);
    }

    public function shoppingType(): BelongsTo
    {
        return $this->belongsTo(ShoppingType::class);
    }
}
