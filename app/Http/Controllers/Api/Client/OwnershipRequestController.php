<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Services\Servers\OwnershipTransferService;

/**
 * Dashboard: servers other users want to give to the signed-in user.
 */
class OwnershipRequestController extends ClientApiController
{
    public function __construct(private OwnershipTransferService $ownership)
    {
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        return new JsonResponse(['data' => $this->ownership->incoming($request->user())]);
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        $server = $this->ownership->accept($request->user(), $id);

        return new JsonResponse(['server' => $server->uuidShort]);
    }

    public function decline(Request $request, int $id): JsonResponse
    {
        $this->ownership->decline($request->user(), $id);

        return new JsonResponse([], 204);
    }
}
