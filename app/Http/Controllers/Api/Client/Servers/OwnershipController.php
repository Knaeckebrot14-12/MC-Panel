<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Pterodactyl\Facades\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Pterodactyl\Services\Servers\OwnershipTransferService;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;

/**
 * Server settings: offer the server to another user (owner only).
 */
class OwnershipController extends ClientApiController
{
    public function __construct(private OwnershipTransferService $ownership)
    {
        parent::__construct();
    }

    public function index(Request $request, Server $server): JsonResponse
    {
        $this->requireOwner($request, $server);

        return new JsonResponse(['pending' => $this->ownership->pending($server)]);
    }

    public function store(Request $request, Server $server): JsonResponse
    {
        $this->requireOwner($request, $server);
        $data = $request->validate(['username' => 'required|string|max:191']);

        $pending = $this->ownership->offer($server, $request->user(), $data['username']);
        Activity::event('server:ownership.offer')->property(['to' => $pending?->to])->log();

        return new JsonResponse(['pending' => $pending]);
    }

    public function delete(Request $request, Server $server): JsonResponse
    {
        $this->requireOwner($request, $server);
        if ($this->ownership->pending($server)) {
            $this->ownership->cancel($server);
            Activity::event('server:ownership.cancel')->log();
        }

        return new JsonResponse([], 204);
    }

    /**
     * Only the owner can give the server away; subusers and admins (who change owners in the
     * admin area) can't.
     */
    private function requireOwner(Request $request, Server $server): void
    {
        if ($server->owner_id !== $request->user()->id) {
            throw new AuthorizationException();
        }
    }
}
