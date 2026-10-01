<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Permission;
use Pterodactyl\Facades\Activity;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Pterodactyl\Exceptions\DisplayException;
use Illuminate\Auth\Access\AuthorizationException;
use Pterodactyl\Services\Minecraft\SoftwareService;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;

class SoftwareController extends ClientApiController
{
    public function __construct(private SoftwareService $software)
    {
        parent::__construct();
    }

    public function index(Request $request, Server $server): JsonResponse
    {
        $this->requirePermission($request, $server, Permission::ACTION_STARTUP_READ);

        return new JsonResponse([
            'supported' => $this->software->supports($server),
            'current' => $server->software,
            'types' => SoftwareService::TYPES,
            // Whether a backup can be made first: needs the right to, and a free backup slot.
            'backup' => [
                'allowed' => $request->user()->can(Permission::ACTION_BACKUP_CREATE, $server) && $server->backup_limit > 0,
                'full' => $server->backup_limit > 0 && $server->backups()->where(fn ($q) => $q->whereNull('completed_at')->orWhere('is_successful', true))->count() >= $server->backup_limit,
            ],
        ]);
    }

    public function versions(Request $request, Server $server, string $type): JsonResponse
    {
        $this->requirePermission($request, $server, Permission::ACTION_STARTUP_READ);

        return new JsonResponse(['versions' => $this->software->versions($type)]);
    }

    /**
     * Installs another server software/version. Needs the rights to change the startup image
     * and the files, since it does both.
     */
    public function install(Request $request, Server $server): JsonResponse
    {
        $this->requirePermission($request, $server, Permission::ACTION_STARTUP_DOCKER_IMAGE);
        $this->requirePermission($request, $server, Permission::ACTION_FILE_UPDATE);

        $data = $request->validate([
            'type' => ['required', Rule::in(SoftwareService::TYPES)],
            'version' => 'required|string|max:64',
            'backup' => 'sometimes|boolean',
        ]);
        $backup = $request->boolean('backup');
        if ($backup) {
            $this->requirePermission($request, $server, Permission::ACTION_BACKUP_CREATE);
        }

        // One change at a time per server.
        $lock = Cache::lock('mcpanel:software-install:' . $server->id, 900);
        if (!$lock->get()) {
            throw new DisplayException(trans('server_software.errors.busy'));
        }

        try {
            $result = $this->software->install($server, $data['type'], $data['version'], $backup);
        } finally {
            $lock->release();
        }

        Activity::event('server:software.install')
            ->property(['type' => $data['type'], 'version' => $data['version'], 'build' => $result['build'], 'backup' => $backup])
            ->log();

        return new JsonResponse($result);
    }

    private function requirePermission(Request $request, Server $server, string $permission): void
    {
        if (!$request->user()->can($permission, $server)) {
            throw new AuthorizationException();
        }
    }
}
