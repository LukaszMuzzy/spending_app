<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingType extends Model
{
    use HasFactory;

    protected $fillable = [
        'description'
    ];

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }
}
