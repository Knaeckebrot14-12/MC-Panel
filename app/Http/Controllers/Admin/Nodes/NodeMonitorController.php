<?php

namespace Pterodactyl\Http\Controllers\Admin\Nodes;

use Pterodactyl\Models\Node;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Services\StaffAudit;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Nodes\NodeMonitorService;
use Pterodactyl\Services\Nodes\WingsReleaseService;
use Pterodactyl\Repositories\Wings\DaemonConfigurationRepository;
use Pterodactyl\Exceptions\Http\Connection\DaemonConnectionException;

class NodeMonitorController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private NodeMonitorService $monitor,
        private WingsReleaseService $releases,
        private DaemonConfigurationRepository $repository,
    ) {
    }

    /**
     * Graph data and the latest values for the node page.
     */
    public function stats(Request $request, Node $node): JsonResponse
    {
        $range = $request->query('range') === '7d' ? '7d' : '24h';

        return new JsonResponse([
            'range' => $range,
            'points' => $this->monitor->history($node, $range),
            'last' => $node->monitor_state['last'] ?? null,
            'last_seen_at' => $node->last_seen_at?->toIso8601String(),
            'wings_version' => $node->wings_version,
            'latest_wings' => $this->releases->latest(),
            'outdated' => $this->releases->isOutdated($node->wings_version),
            'capable' => WingsReleaseService::supportsSelfUpdate($node->monitor_state),
        ]);
    }

    /**
     * Status of every node for the node list (replaces pinging Wings from the browser, which
     * needed each node's secret token in the page).
     */
    public function overview(): JsonResponse
    {
        $latest = $this->releases->latest();

        return new JsonResponse(['latest' => $latest, 'nodes' => Node::query()->get(['id', 'wings_version', 'last_seen_at', 'monitor_state', 'maintenance_mode'])->map(fn (Node $node) => [
            'id' => $node->id,
            'online' => $node->last_seen_at && $node->last_seen_at->gt(now()->subMinutes(3)),
            'version' => $node->wings_version,
            'outdated' => $this->releases->isOutdated($node->wings_version),
            'capable' => WingsReleaseService::supportsSelfUpdate($node->monitor_state),
            'cpu' => $node->monitor_state['last']['cpu'] ?? null,
            'memory' => self::percent($node->monitor_state['last']['memory_used'] ?? 0, $node->monitor_state['last']['memory_total'] ?? 0),
            'disk' => self::percent($node->monitor_state['last']['disk_used'] ?? 0, $node->monitor_state['last']['disk_total'] ?? 0),
        ])->values()->all()]);
    }

    public function updateWings(Node $node): RedirectResponse
    {
        $this->runUpdate($node, true);

        return redirect()->route('admin.nodes.view', $node->id);
    }

    public function updateAllWings(): RedirectResponse
    {
        $done = 0;
        foreach (Node::query()->get() as $node) {
            if ($this->releases->isOutdated($node->wings_version) && WingsReleaseService::supportsSelfUpdate($node->monitor_state)) {
                $done += $this->runUpdate($node, false) ? 1 : 0;
            }
        }

        $this->alert->success(trans('admin/monitoring.wings.updated_all', ['count' => $done]))->flash();

        return redirect()->route('admin.nodes');
    }

    private function runUpdate(Node $node, bool $flash): bool
    {
        $version = $this->releases->latest(true);

        if (!WingsReleaseService::supportsSelfUpdate($node->monitor_state)) {
            if ($flash) {
                $this->alert->warning(trans('admin/monitoring.wings.not_capable'))->flash();
            }

            return false;
        }

        try {
            $this->repository->setNode($node)->selfUpdate($version);
        } catch (DaemonConnectionException $exception) {
            if ($flash) {
                $this->alert->danger(trans('admin/monitoring.wings.failed', ['error' => $exception->getMessage()]))->flash();
            }

            return false;
        }

        StaffAudit::record('nodes.wings_update', $node->name, ['version' => $version]);
        Node::query()->whereKey($node->id)->update(['wings_version' => $version]);

        if ($flash) {
            $this->alert->success(trans('admin/monitoring.wings.updated', ['node' => $node->name, 'version' => $version]))->flash();
        }

        return true;
    }

    private static function percent(int $used, int $total): ?int
    {
        return $total > 0 ? (int) round($used / $total * 100) : null;
    }
}
