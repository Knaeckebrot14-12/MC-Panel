<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property int $coins
 * @property int|null $max_uses
 * @property int $uses
 * @property \Carbon\CarbonImmutable|null $expires_at
 * @property bool $active
 * @property string|null $note
 */
class CoinVoucher extends Model
{
    public const RESOURCE_NAME = 'coin_voucher';

    protected $table = 'coin_vouchers';

    protected $fillable = ['code', 'coins', 'max_uses', 'uses', 'expires_at', 'active', 'note'];

    protected $casts = [
        'coins' => 'integer',
        'max_uses' => 'integer',
        'uses' => 'integer',
        'active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public static array $validationRules = [
        'code' => 'required|string|min:3|max:64',
        'coins' => 'required|integer|min:1',
        'max_uses' => 'nullable|integer|min:1',
        'uses' => 'sometimes|integer|min:0',
        'expires_at' => 'nullable|date',
        'active' => 'boolean',
        'note' => 'nullable|string|max:191',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(CoinVoucherRedemption::class, 'voucher_id');
    }

    public function isExhausted(): bool
    {
        return !is_null($this->max_uses) && $this->uses >= $this->max_uses;
    }

    public function isExpired(): bool
    {
        return !is_null($this->expires_at) && $this->expires_at->isPast();
    }
}
