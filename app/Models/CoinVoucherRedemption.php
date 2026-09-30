<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoinVoucherRedemption extends Model
{
    public const RESOURCE_NAME = 'coin_voucher_redemption';

    protected $table = 'coin_voucher_redemptions';

    protected $fillable = ['voucher_id', 'user_id', 'coins'];

    protected $casts = ['voucher_id' => 'integer', 'user_id' => 'integer', 'coins' => 'integer'];

    public static array $validationRules = [
        'voucher_id' => 'required|integer|exists:coin_vouchers,id',
        'user_id' => 'required|integer|exists:users,id',
        'coins' => 'required|integer|min:0',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(CoinVoucher::class, 'voucher_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
