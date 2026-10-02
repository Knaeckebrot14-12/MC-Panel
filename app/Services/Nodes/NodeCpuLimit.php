<?php

namespace Pterodactyl\Services\Nodes;

use Pterodactyl\Models\Node;
use Illuminate\Support\Facades\Cache;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Repositories\Wings\DaemonConfigurationRepository;

/**
 * A server's CPU limit is a percentage of one CPU thread (100 % = one thread), so a node with
 * 10 threads can give a single server at most 1000 %. More than that is no limit at all and
 * lets one server take every core of the machine, so it is refused.
 */
class NodeCpuLimit
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

    public function maxPercent(Node $node): ?int
    {
        $threads = $this->threads($node);

        return $threads ? $threads * 100 : null;
    }

    /**
     * @throws DisplayException when $cpu is more than the node has
     */
    public function assertFits(Node $node, int $cpu): void
    {
        // 0 means "no limit", which an admin may set on purpose.
        if ($cpu <= 0) {
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
