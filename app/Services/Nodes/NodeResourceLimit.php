<?php

namespace Pterodactyl\Services\Nodes;

use Pterodactyl\Models\Node;
use Illuminate\Support\Facades\Cache;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Repositories\Wings\DaemonConfigurationRepository;

/**
 * No single server may get more than its node has:
 *  - CPU: a limit is a percentage of one thread (100 % = one thread), so a node with 10 threads can
 *    give a server at most 1000 %. More is no limit at all and lets one server take every core.
 *  - Memory and disk: at most the node's own memory and disk (the values set on the node).
 * 0 means "no limit" for each of them, which an admin may set on purpose.
 *
 * This is about one server; how much all servers of a node may add up to (over-allocation) is
 * checked separately where it applies (self-service, shop, transfers).
 */
class NodeResourceLimit
{
    public function __construct(private DaemonConfigurationRepository $repository)
    {
    }

    /**
     * CPU threads of the node, or null when the node can't tell right now.
     */
    public function threads(Node $node): ?int
    {
        // Recoded Ptero Wings reports it with the monitoring data every minute.
        $threads = (int) ($node->monitor_state['last']['threads'] ?? 0);
        if ($threads > 0) {
            return $threads;
        }

        $key = 'mcpanel:node-threads:' . $node->id;
        if ($cached = Cache::get($key)) {
            return (int) $cached;
        }

        try {
            $threads = (int) ($this->repository->setNode($node)->getSystemInformation()['cpu_count'] ?? 0);
        } catch (\Throwable) {
            return null;
        }
        if ($threads <= 0) {
            return null;
        }
        Cache::put($key, $threads, now()->addHour());

        return $threads;
    }

    /**
     * @throws DisplayException when one of the values is more than the node has
     */
    public function assertFits(Node $node, ?int $cpu = null, ?int $memory = null, ?int $disk = null): void
    {
        if ($memory !== null && $memory > 0 && $node->memory > 0 && $memory > $node->memory) {
            throw new DisplayException(trans('exceptions.server.memory_above_node', [
                'memory' => $memory,
                'max' => $node->memory,
                'node' => $node->name,
            ]));
        }

        if ($disk !== null && $disk > 0 && $node->disk > 0 && $disk > $node->disk) {
            throw new DisplayException(trans('exceptions.server.disk_above_node', [
                'disk' => $disk,
                'max' => $node->disk,
                'node' => $node->name,
            ]));
        }

        if ($cpu === null || $cpu <= 0) {
            return;
        }
        // A node that can't be asked is not blocked here; creating the server on it fails anyway.
        $threads = $this->threads($node);
        if ($threads && $cpu > $threads * 100) {
            throw new DisplayException(trans('exceptions.server.cpu_above_node', [
                'cpu' => $cpu,
                'max' => $threads * 100,
                'threads' => $threads,
                'node' => $node->name,
            ]));
        }
    }
}
