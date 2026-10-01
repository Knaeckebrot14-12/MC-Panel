<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Permission;
use Pterodactyl\Facades\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Pterodactyl\Services\Subdomains\SubdomainService;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;

class SubdomainController extends ClientApiController
{
    public function __construct(private SubdomainService $subdomains)
    {
        parent::__construct();
    }

    public function index(Request $request, Server $server): JsonResponse
    {
        $this->requirePermission($request, $server, Permission::ACTION_ALLOCATION_READ);
        $current = $this->subdomains->current($server);

        return new JsonResponse([
            'enabled' => $this->subdomains->enabled(),
            'domains' => $this->subdomains->domains(),
            'current' => $current ? ['name' => $current->name, 'domain' => $current->domain, 'fqdn' => $current->name . '.' . $current->domain] : null,
        ]);
    }

    public function store(Request $request, Server $server): JsonResponse
    {
        $this->requirePermission($request, $server, Permission::ACTION_ALLOCATION_UPDATE);
        $data = $request->validate([
            'name' => 'required|string|max:32',
            'domain' => 'required|string|max:190',
        ]);

        $current = $this->subdomains->assign($server, $data['name'], $data['domain']);
        Activity::event('server:subdomain.set')->property(['subdomain' => $current->name . '.' . $current->domain])->log();

        return new JsonResponse(['name' => $current->name, 'domain' => $current->domain, 'fqdn' => $current->name . '.' . $current->domain]);
    }

    public function delete(Request $request, Server $server): JsonResponse
    {
        $this->requirePermission($request, $server, Permission::ACTION_ALLOCATION_UPDATE);
        $this->subdomains->remove($server);
        Activity::event('server:subdomain.delete')->log();

        return new JsonResponse([], 204);
    }

    private function requirePermission(Request $request, Server $server, string $permission): void
    {
        if (!$request->user()->can($permission, $server)) {
            throw new AuthorizationException();
        }
    }
}
