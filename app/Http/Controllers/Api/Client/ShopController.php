<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\CoinServerPlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Services\Coins\CoinService;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Services\Servers\ServerCreationService;
use Pterodactyl\Services\Deployment\AllocationSelectionService;

class ShopController extends ClientApiController
{
    private const RESOURCE_TYPES = ['memory', 'disk', 'cpu', 'backups', 'slots'];

    public function __construct(
        private CoinService $coins,
        private ServerCreationService $creationService,
        private AllocationSelectionService $allocationSelectionService,
    ) {
        parent::__construct();
    }

    /**
     * Spend coins to permanently add to the requesting user's self-service
     * resource pool limits (memory, disk, cpu, backups, or server slots).
     *
     * @throws DisplayException
     */
    public function purchaseResource(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:' . implode(',', self::RESOURCE_TYPES),
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $user = $request->user();
        $type = $request->input('type');
        $quantity = (int) $request->input('quantity');

        [$unit, $unitPrice, $column] = match ($type) {
            'memory' => [(int) config('coins.shop.memory_unit_mib'), (int) config('coins.shop.memory_price'), 'server_memory_limit'],
            'disk' => [(int) config('coins.shop.disk_unit_mib'), (int) config('coins.shop.disk_price'), 'server_disk_limit'],
            'cpu' => [(int) config('coins.shop.cpu_unit_percent'), (int) config('coins.shop.cpu_price'), 'server_cpu_limit'],
            'backups' => [1, (int) config('coins.shop.backup_price'), 'server_backup_limit'],
            'slots' => [1, (int) config('coins.shop.slot_price'), 'server_slots'],
        };

        $cost = $unitPrice * $quantity;
        $amount = $unit * $quantity;

        $user = DB::transaction(function () use ($user, $cost, $type, $amount, $column) {
            $user = $this->coins->debit($user, $cost, 'shop:' . $type, "Purchased {$amount} more {$type}");
            $user->increment($column, $amount);

            return $user;
        });

        return new JsonResponse([
            'balance' => $user->refresh()->coins,
            'limits' => [
                'memory' => $user->server_memory_limit,
                'disk' => $user->server_disk_limit,
                'cpu' => $user->server_cpu_limit,
                'backups' => $user->server_backup_limit,
                'slots' => $user->server_slots,
            ],
        ]);
    }

    /**
     * Buy a server outright with coins, at the admin-configured fixed spec,
     * billed monthly out of the buyer's coin balance from here on.
     *
     * @throws DisplayException
     * @throws \Throwable
     */
    public function purchaseServer(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|min:1|max:191',
            'node_id' => 'required|integer|exists:nodes,id',
            'egg_id' => 'required|integer|exists:eggs,id',
        ]);

        $user = $request->user();

        // When the admin has published server plans the shop sells those; otherwise
        // it falls back to the single configured tier.
        $plan = null;
        if (CoinServerPlan::query()->where('active', true)->exists()) {
            $request->validate(['plan_id' => 'required|integer']);
            $plan = CoinServerPlan::query()->where('active', true)->findOrFail($request->input('plan_id'));
        }

        $price = $plan ? $plan->monthly_price : (int) config('coins.server.monthly_price');

        if ($user->coins < $price) {
            throw new DisplayException(trans('coins.errors.insufficient', ['needed' => $price, 'have' => $user->coins]));
        }

        /** @var Node $node */
        $node = Node::query()->where('public', true)->findOrFail($request->input('node_id'));

        $memory = $plan ? $plan->memory : (int) config('coins.server.memory');
        $disk = $plan ? $plan->disk : (int) config('coins.server.disk');
        $cpu = $plan ? $plan->cpu : (int) config('coins.server.cpu');
        $backups = $plan ? $plan->backups : (int) config('coins.server.backups');

        $usedNodeMemory = (int) Server::query()->where('node_id', $node->id)->sum('memory');
        $usedNodeDisk = (int) Server::query()->where('node_id', $node->id)->sum('disk');
        if ($usedNodeMemory + $memory > $node->memory * (1 + $node->memory_overallocate / 100)) {
            throw new DisplayException(trans('coins.errors.node_memory'));
        }
        if ($usedNodeDisk + $disk > $node->disk * (1 + $node->disk_overallocate / 100)) {
            throw new DisplayException(trans('coins.errors.node_disk'));
        }

        if (!is_null($node->maximum_servers)) {
            $serverCount = $node->servers()->count();
            if ($serverCount >= $node->maximum_servers) {
                throw new DisplayException(trans('coins.errors.node_full', ['count' => $serverCount, 'max' => $node->maximum_servers]));
            }
        }

        /** @var Egg $egg */
        $egg = Egg::query()->with('variables')->findOrFail($request->input('egg_id'));
        $environment = $egg->variables->pluck('default_value', 'env_variable')->toArray();

        $allocation = $this->allocationSelectionService
            ->setDedicated(false)
            ->setNodes([$node->id])
            ->setPorts([])
            ->handle();

        // Debit first (its own transaction, committed immediately) rather than
        // wrapping the server creation call in the same transaction: creating
        // a server triggers a synchronous call to Wings, which immediately
        // calls back into the panel to fetch the new server's config — a call
        // that needs the server row to already be committed and visible, not
        // sitting inside a still-open outer transaction.
        $this->coins->debit($user, $price, 'shop:server', 'Bought a server with coins (first month)');

        try {
            $server = $this->creationService->handle([
                'name' => $request->input('name'),
                'owner_id' => $user->id,
                'egg_id' => $egg->id,
                'nest_id' => $egg->nest_id,
                'image' => Arr::first($egg->docker_images),
                'startup' => $egg->startup,
                'environment' => $environment,
                'memory' => $memory,
                'swap' => 0,
                'disk' => $disk,
                'cpu' => $cpu,
                'threads' => null,
                'io' => 500,
                'database_limit' => 0,
                'allocation_limit' => 0,
                'backup_limit' => $backups,
                'start_on_completion' => true,
                'skip_scripts' => false,
                'node_id' => $node->id,
                'allocation_id' => $allocation->id,
            ]);
        } catch (\Throwable $exception) {
            $this->coins->credit($user, $price, 'shop:server:refund', 'Refund — server creation failed');

            throw $exception;
        }

        $server->update([
            'paid_with_coins_until' => now()->addMonth(),
            'coin_monthly_price' => $price,
        ]);

        return new JsonResponse([
            'data' => ['identifier' => $server->uuidShort],
        ], JsonResponse::HTTP_CREATED);
    }
}
