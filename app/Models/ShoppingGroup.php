<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class ShoppingGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id'
    ];

    
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class,'shopping_group_user', 'shopping_group_id', 'user_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    /**
     * Get the payments associated with the shopping group.
     */
    public function ddPayments(): HasMany
    {
        return $this->hasMany(DdPayment::class);
    }
}
