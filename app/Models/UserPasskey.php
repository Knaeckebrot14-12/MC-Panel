<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * \Pterodactyl\Models\UserPasskey.
 *
 * A WebAuthn credential of a user. The private key never leaves the user's authenticator; only the
 * public key is stored here. Users manage their own passkeys, there is no admin-side access.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $credential_id
 * @property string $public_key
 * @property int $sign_count
 * @property array|null $transports
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property User $user
 */
class UserPasskey extends Model
{
    public const RESOURCE_NAME = 'user_passkey';

    /** How many passkeys one account can have. */
    public const MAX_PER_USER = 10;

    public $timestamps = false;

    protected $table = 'user_passkeys';

    protected $fillable = ['name', 'credential_id', 'public_key', 'sign_count', 'transports', 'created_at', 'last_used_at'];

    protected $hidden = ['credential_id', 'public_key'];

    protected $casts = [
        'user_id' => 'int',
        'sign_count' => 'int',
        'transports' => 'array',
        'created_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public static array $validationRules = [
        'name' => ['required', 'string', 'max:64'],
        'credential_id' => ['required', 'string', 'max:512'],
        'public_key' => ['required', 'string'],
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Pterodactyl\Models\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
