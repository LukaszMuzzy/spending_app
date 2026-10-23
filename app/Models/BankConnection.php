<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'institution_name',
        'status',
        'session_id',
        'accounts',
        'expires_at',
        'balance',
        'last_synced_at',
    ];

    protected $casts = [
        'session_id' => 'encrypted',
        'accounts' => 'array',
        'expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'balance' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
