<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Permission;
use Pterodactyl\Facades\Activity;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Pterodactyl\Services\Minecraft\PlayerListService;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;

class PlayerController extends ClientApiController
{
    public function __construct(private PlayerListService $players)
    {
        parent::__construct();
    }

    /**
     * Online players, whitelist, operators and bans of the server.
     */
    public function index(Request $request, Server $server): JsonResponse
    {
        $this->requirePermission($request, $server, Permission::ACTION_FILE_READ);

        return new JsonResponse($this->players->overview($server));
    }

    /**
     * Adds or removes a player from one of the lists, bans, kicks, or toggles the whitelist.
     */
    public function action(Request $request, Server $server): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(PlayerListService::ACTIONS)],
            'target' => 'nullable|string|max:64',
            'reason' => 'nullable|string|max:200',
        ]);

        // A running server takes console commands, an offline one gets its files edited.
        $running = $this->players->isRunning($server);
        $this->requirePermission($request, $server, $running ? Permission::ACTION_CONTROL_CONSOLE : Permission::ACTION_FILE_UPDATE);

        $via = $this->players->apply($server, $data['action'], $data['target'] ?? null, $data['reason'] ?? null);

        Activity::event('server:players.' . $data['action'])
            ->property(['target' => $data['target'] ?? null, 'via' => $via])
            ->log();

        return new JsonResponse(['via' => $via]);
    }

    private function requirePermission(Request $request, Server $server, string $permission): void
    {
        if (!$request->user()->can($permission, $server)) {
            throw new AuthorizationException();
        }
    }
}
