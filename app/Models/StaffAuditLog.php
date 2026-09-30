<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $subject
 * @property array|null $properties
 * @property string|null $ip
 */
class StaffAuditLog extends Model
{
    public const RESOURCE_NAME = 'staff_audit_log';

    protected $table = 'staff_audit_logs';

    protected $fillable = ['user_id', 'action', 'subject', 'target_user_id', 'target_user', 'target_server_id', 'target_server', 'properties', 'ip'];

    protected $casts = ['user_id' => 'integer', 'properties' => 'array'];

    public static array $validationRules = [
        'user_id' => 'nullable|integer',
        'action' => 'required|string|max:64',
        'subject' => 'nullable|string|max:191',
        'properties' => 'nullable|array',
        'ip' => 'nullable|string|max:45',
    ];

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function targetServer(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'target_server_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Human readable, localized sentence describing what happened.
     */
    public function describe(): string
    {
        // The action codes contain a dot themselves, so look them up in the flat array rather than as nested keys.
        $actions = trans('admin/audit.actions');
        $text = is_array($actions) ? ($actions[$this->action] ?? null) : null;
        if ($text === null) {
            return $this->action;
        }

        $params = array_merge(['subject' => $this->subject ?? '—'], array_map('strval', $this->properties ?? []));
        foreach ($params as $key => $value) {
            $text = str_replace(':' . $key, $value, $text);
        }

        return $text;
    }
}
