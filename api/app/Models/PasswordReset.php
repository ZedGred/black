<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordReset extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'reset_token',
        'reset_token_expires_at',
        'is_used',
        'used_at',
    ];

    protected $casts = [
        'reset_token_expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if reset token is valid
     */
    public function isValid(): bool
    {
        return !$this->is_used && $this->reset_token_expires_at->isFuture();
    }
}
