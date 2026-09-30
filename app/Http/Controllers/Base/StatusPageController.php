<?php

namespace Pterodactyl\Http\Controllers\Base;

use Illuminate\View\View;
use Pterodactyl\Models\Node;
use Illuminate\Support\Facades\Cache;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Repositories\Wings\DaemonConfigurationRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StatusPageController extends Controller
{
    public function __construct(private DaemonConfigurationRepository $daemon)
    {
    }

    /**
     * Public overview of which nodes are online. No addresses or server names are shown.
     */
    public function __invoke(): View
    {
        if (!filter_var(config('mcpanel.status_page.enabled'), FILTER_VALIDATE_BOOLEAN)) {
            throw new NotFoundHttpException();
        }

        $nodes = Cache::remember('status-page:nodes', 60, function () {
            return Node::query()->with('location')->withCount('servers')->orderBy('name')->get()->map(function (Node $node) {
                $online = false;
                if (!$node->maintenance_mode) {
                    try {
                        $this->daemon->setNode($node)->getSystemInformation();
                        $online = true;
                    } catch (\Throwable) {
                    }
                }

                $allocated = $node->servers()->selectRaw('COALESCE(SUM(memory), 0) as memory, COALESCE(SUM(disk), 0) as disk')->first();

                return [
                    'name' => $node->name,
                    'location' => $node->location->long ?: $node->location->short,
                    'state' => $node->maintenance_mode ? 'maintenance' : ($online ? 'online' : 'offline'),
                    'servers' => $node->servers_count,
                    'memory_percent' => $node->memory > 0 ? min(100, (int) round($allocated->memory / $node->memory * 100)) : null,
                    'disk_percent' => $node->disk > 0 ? min(100, (int) round($allocated->disk / $node->disk * 100)) : null,
                ];
            })->all();
        });

        $states = collect($nodes)->pluck('state');
        $overall = $states->isEmpty() || $states->every(fn ($state) => $state === 'online')
            ? 'operational'
            : ($states->contains('online') ? 'partial' : 'outage');

        return view('status', [
            'nodes' => $nodes,
            'overall' => $overall,
            'checkedAt' => now(),
            'maintenance' => config('mcpanel.maintenance.mode') !== 'off' ? config('mcpanel.maintenance.message') : null,
        ]);
    }
}
