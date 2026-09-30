<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;

class StatsController extends ClientApiController
{
    /**
     * CPU, memory and player history: 5-minute points for 24 hours, hourly averages for 7 days.
     */
    public function index(Request $request, Server $server): JsonResponse
    {
        $range = $request->query('range') === '7d' ? '7d' : '24h';
        $since = $range === '7d' ? now()->subDays(7) : now()->subDay();

        $query = DB::table('server_stats')
            ->where('server_id', $server->id)
            ->where('created_at', '>=', $since);

        if ($range === '7d') {
            $rows = $query
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as t, AVG(cpu) as cpu, AVG(memory) as memory, MAX(players) as players")
                ->groupBy('t')
                ->orderBy('t')
                ->get();
        } else {
            $rows = $query->select(['created_at as t', 'cpu', 'memory', 'players'])->orderBy('created_at')->get();
        }

        return new JsonResponse([
            'range' => $range,
            'memory_limit' => $server->memory > 0 ? $server->memory * 1024 * 1024 : null,
            'points' => $rows->map(fn ($row) => [
                't' => \Carbon\Carbon::parse($row->t)->toIso8601String(),
                'cpu' => round((float) $row->cpu, 1),
                'memory' => (int) $row->memory,
                'players' => is_null($row->players) ? null : (int) $row->players,
            ])->values(),
        ]);
    }
}
