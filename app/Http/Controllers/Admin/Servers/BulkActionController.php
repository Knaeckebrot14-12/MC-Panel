<?php

namespace Pterodactyl\Http\Controllers\Admin\Servers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Models\Node;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Services\StaffAudit;
use Pterodactyl\Jobs\BulkServerActionJob;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Servers\BulkActionService;

class BulkActionController extends Controller
{
    public function __construct(private BulkActionService $bulk)
    {
    }

    /**
     * The bulk actions page with the number of affected servers per node.
     */
    public function index(): View
    {
        return view('admin.servers.bulk', ['counts' => $this->bulk->counts(), 'messageMax' => BulkActionService::MESSAGE_MAX]);
    }

    /**
     * Start, stop, restart or kill all servers of a node (or of all nodes).
     */
    public function power(Request $request): JsonResponse
    {
        $data = $request->validate([
            'node' => 'nullable|integer|exists:nodes,id',
            'action' => 'required|string|in:' . implode(',', BulkActionService::POWER_ACTIONS),
            'confirm' => 'accepted',
        ]);

        $node = !empty($data['node']) ? Node::query()->findOrFail($data['node']) : null;
        $ids = $this->bulk->eligible($node?->id)->orderBy('servers.id')->pluck('servers.id');

        $runId = $this->queueRun($ids->all(), BulkServerActionJob::TYPE_POWER, $data['action'], [
            'action' => $data['action'],
            'node' => $node?->name,
        ]);

        StaffAudit::record($node ? 'server.bulk_power' : 'server.bulk_power_all', $node?->name, [
            'action' => $data['action'],
            'count' => $ids->count(),
        ]);

        return response()->json(['run' => $runId, 'total' => $ids->count()]);
    }

    /**
     * Sends "say <text>" to the console of all running Minecraft servers (of one node or of all).
     */
    public function message(Request $request): JsonResponse
    {
        $data = $request->validate([
            'node' => 'nullable|integer|exists:nodes,id',
            'message' => ['required', 'string', 'max:' . BulkActionService::MESSAGE_MAX, 'not_regex:/^\s*\//u'],
            'confirm' => 'accepted',
        ]);

        $message = $this->bulk->sanitizeMessage($data['message']);
        if ($message === '') {
            return response()->json([
                'message' => trans('validation.required', ['attribute' => 'message']),
                'errors' => ['message' => [trans('validation.required', ['attribute' => 'message'])]],
            ], 422);
        }

        $node = !empty($data['node']) ? Node::query()->findOrFail($data['node']) : null;
        $ids = $this->bulk->eligible($node?->id, true)->orderBy('servers.id')->pluck('servers.id');

        $runId = $this->queueRun($ids->all(), BulkServerActionJob::TYPE_SAY, $message, [
            'action' => 'say',
            'node' => $node?->name,
        ]);

        StaffAudit::record($node ? 'server.bulk_message' : 'server.bulk_message_all', $node?->name, [
            'count' => $ids->count(),
            'message' => $message,
        ]);

        return response()->json(['run' => $runId, 'total' => $ids->count()]);
    }

    /**
     * Progress of a run, polled by the page.
     */
    public function status(string $run): JsonResponse
    {
        $state = $this->bulk->run($run);
        abort_if($state === null, 404);

        return response()->json($state);
    }

    /**
     * @param int[] $serverIds
     * @param array<string, scalar|null> $meta
     */
    private function queueRun(array $serverIds, string $type, string $payload, array $meta): string
    {
        $runId = $this->bulk->createRun($type, $meta, count($serverIds));
        if (!$this->bulk->claim($runId)) {
            $this->bulk->discard($runId);

            abort(409, trans('admin/bulk.progress.busy'));
        }

        foreach ($serverIds as $id) {
            BulkServerActionJob::dispatch($runId, (int) $id, $type, $payload);
        }

        return $runId;
    }
}
