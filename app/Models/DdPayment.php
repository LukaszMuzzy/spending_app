<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DdPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'payment_date',
        'end_date',
        'frequency',
        'is_active',
        'shopping_group_id',
        'shop_id',
        'payment_method_id',
        'shopping_type_id',
        'price',
    ];

    protected $casts = [
        'payment_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'is_active' => 'boolean',
        'price' => 'float',
    ];

    public function shoppingGroup(): BelongsTo
    {
        return $this->belongsTo(ShoppingGroup::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function shoppingType(): BelongsTo
    {
        return $this->belongsTo(ShoppingType::class);
    }
}
