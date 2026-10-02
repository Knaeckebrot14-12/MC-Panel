<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Permission;
use Pterodactyl\Facades\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Pterodactyl\Exceptions\DisplayException;
use Illuminate\Auth\Access\AuthorizationException;
use Pterodactyl\Services\Minecraft\GeyserService;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;

/**
 * Bedrock players through Geyser + Floodgate (Plugins tab).
 */
class GeyserController extends ClientApiController
{
    public function __construct(private GeyserService $geyser)
    {
        parent::__construct();
    }

    public function index(Request $request, Server $server): JsonResponse
    {
        $this->requirePermission($request, $server, Permission::ACTION_FILE_READ);

        return new JsonResponse($this->geyser->status($server));
    }

    public function install(Request $request, Server $server): JsonResponse
    {
        $this->requirePermission($request, $server, Permission::ACTION_FILE_CREATE);
        $this->requirePermission($request, $server, Permission::ACTION_FILE_UPDATE);

        $viaFor = null;
        $this->locked($server, function () use ($server, &$viaFor) {
            $viaFor = $this->geyser->install($server);
        });
        Activity::event('server:geyser.install')->log();

        return new JsonResponse($this->geyser->status($server) + ['viaversion_for' => $viaFor]);
    }

    public function uninstall(Request $request, Server $server): JsonResponse
    {
        $this->requirePermission($request, $server, Permission::ACTION_FILE_DELETE);

        $this->locked($server, fn () => $this->geyser->uninstall($server));
        Activity::event('server:geyser.uninstall')->log();

        return new JsonResponse($this->geyser->status($server));
    }

    /**
     * One change at a time per server (a double click must not write the jars twice at once).
     */
    private function locked(Server $server, \Closure $callback): void
    {
        $lock = Cache::lock('mcpanel:geyser:' . $server->id, 300);
        if (!$lock->get()) {
            throw new DisplayException(trans('server_plugins.geyser.errors.busy'));
        }
        try {
            $callback();
        } finally {
            $lock->release();
        }
    }

    private function requirePermission(Request $request, Server $server, string $permission): void
    {
        if (!$request->user()->can($permission, $server)) {
            throw new AuthorizationException();
        }
    }
}
