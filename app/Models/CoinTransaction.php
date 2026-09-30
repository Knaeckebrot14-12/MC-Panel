<?php

namespace Pterodactyl\Models;

class CoinTransaction extends Model
{
    public const RESOURCE_NAME = 'coin_transaction';

    protected $table = 'coin_transactions';

    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'amount' => 'integer',
    ];

    public static array $validationRules = [
        'user_id' => 'required|integer|exists:users,id',
        'amount' => 'required|integer',
        'type' => 'required|string|max:64',
        'description' => 'nullable|string|max:191',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
