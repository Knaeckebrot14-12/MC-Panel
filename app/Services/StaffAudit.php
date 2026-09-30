<?php

namespace Pterodactyl\Services;

use Pterodactyl\Models\User;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\StaffAuditLog;

class StaffAudit
{
    /**
     * Records something a staff member did. Never lets a logging problem break the action itself.
     *
     * @param array<string, scalar|null> $properties
     * @param User|null $user the user the action affected
     * @param Server|null $server the server the action affected
     */
    public static function record(
        string $action,
        ?string $subject = null,
        array $properties = [],
        ?User $user = null,
        ?Server $server = null,
    ): void {
        try {
            $request = request();

            StaffAuditLog::query()->create([
                'user_id' => optional($request->user())->id,
                'action' => $action,
                'subject' => $subject !== null ? mb_substr($subject, 0, 191) : null,
                'target_user_id' => optional($user)->id,
                'target_user' => optional($user)->username,
                'target_server_id' => optional($server)->id,
                'target_server' => optional($server)->name,
                'properties' => $properties ?: null,
                'ip' => $request->ip(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
