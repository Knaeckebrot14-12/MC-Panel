<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Permission;
use Pterodactyl\Facades\Activity;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;

class AutoBackupController extends ClientApiController
{
    /** Intervals (in hours) a user can pick; 0 turns automatic backups off. */
    public const INTERVALS = [0, 6, 12, 24, 48, 168];

    public function show(Request $request, Server $server): JsonResponse
    {
        $this->requirePermission($request, $server, Permission::ACTION_BACKUP_READ);

        return new JsonResponse($this->payload($server));
    }

    public function update(Request $request, Server $server): JsonResponse
    {
        $this->requirePermission($request, $server, Permission::ACTION_BACKUP_CREATE);
        $data = $request->validate(['hours' => ['required', 'integer', Rule::in(self::INTERVALS)]]);

        $server->forceFill(['auto_backup_hours' => $data['hours']])->save();

        Activity::event('server:backup.auto')->property('hours', $data['hours'])->log();

        return new JsonResponse($this->payload($server));
    }

    private function payload(Server $server): array
    {
        $next = null;
        if ($server->auto_backup_hours > 0) {
            $next = $server->auto_backup_last_at
                ? $server->auto_backup_last_at->copy()->addHours($server->auto_backup_hours)
                : now();
            $next = ($next->isPast() ? now()->addMinutes(5) : $next)->toIso8601String();
        }

        return [
            'hours' => (int) $server->auto_backup_hours,
            'intervals' => self::INTERVALS,
            'last_at' => optional($server->auto_backup_last_at)->toIso8601String(),
            'next_at' => $next,
            'backup_limit' => (int) $server->backup_limit,
        ];
    }

    private function requirePermission(Request $request, Server $server, string $permission): void
    {
        if (!$request->user()->can($permission, $server)) {
            throw new AuthorizationException();
        }
    }
}
