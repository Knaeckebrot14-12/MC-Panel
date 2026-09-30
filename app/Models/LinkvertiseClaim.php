<?php

namespace Pterodactyl\Models;

class LinkvertiseClaim extends Model
{
    public const RESOURCE_NAME = 'linkvertise_claim';

    protected $table = 'linkvertise_claims';

    protected $fillable = [
        'user_id',
        'token',
        'coins',
        'claimed_at',
        'expires_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'coins' => 'integer',
        'claimed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $hidden = ['id'];

    public static array $validationRules = [
        'user_id' => 'required|integer|exists:users,id',
        'token' => 'required|string|max:64|unique:linkvertise_claims,token',
        'coins' => 'required|integer|min:0',
        'claimed_at' => 'nullable|date',
        'expires_at' => 'required|date',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isClaimed(): bool
    {
        return !is_null($this->claimed_at);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
