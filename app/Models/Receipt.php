<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'shopping_type_id',
        'payment_method_id',
        'shop_id',
        'price',
        'note',
        'date',
        'shopping_group_id',
        'user_id',
        'dd_payment_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shopping_type(): BelongsTo
    {
        return $this->belongsTo(ShoppingType::class);
    }

    public function payment_method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function shopping_group(): BelongsTo
    {
        return $this->belongsTo(ShoppingGroup::class);
    }

    public function dd_payment(): BelongsTo
    {
        return $this->belongsTo(DdPayment::class);
    }

}
