<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'price'
    ];

    /**
     * Get the shopping group associated with the payment.
     */
    public function shoppingGroup()
    {
        return $this->belongsTo(ShoppingGroup::class);
    }

    /**
     * Get the shop associated with the payment.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the payment method associated with the payment.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the shopping type associated with the payment.
     */
    public function shoppingType()
    {
        return $this->belongsTo(ShoppingType::class);
    }
}
