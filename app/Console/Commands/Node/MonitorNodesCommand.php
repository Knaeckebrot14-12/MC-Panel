<?php

namespace Pterodactyl\Console\Commands\Node;

use Pterodactyl\Models\Node;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Services\Nodes\NodeMonitorService;

class MonitorNodesCommand extends Command
{
    protected $description = 'Checks every node (online, CPU, memory, disk), stores the history and sends alerts.';

    protected $signature = 'p:nodes:monitor';

    /** How long node history is kept. */
    private const KEEP_DAYS = 8;

    public function __construct(private NodeMonitorService $monitor)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        foreach (Node::query()->where('maintenance_mode', false)->get() as $node) {
            try {
                $this->monitor->check($node);
            } catch (\Throwable $exception) {
                report($exception);
                $this->warn("Node {$node->name}: {$exception->getMessage()}");
            }
        }

        if (now()->minute === 0) {
            DB::table('node_stats')->where('created_at', '<', now()->subDays(self::KEEP_DAYS))->delete();
        }

        return 0;
    }
}
