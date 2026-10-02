<?php

namespace Pterodactyl\Jobs;

use Pterodactyl\Models\Server;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Pterodactyl\Services\Servers\BulkActionService;
use Pterodactyl\Repositories\Wings\DaemonPowerRepository;
use Pterodactyl\Repositories\Wings\DaemonServerRepository;
use Pterodactyl\Repositories\Wings\DaemonCommandRepository;

/**
 * One server of an admin bulk action (Admin -> Servers -> Bulk actions): either a power action or an
 * in-game "say" message. One job per server, so a slow or offline node only delays its own servers.
 */
class BulkServerActionJob implements ShouldQueue
{
    use Queueable;

    public const TYPE_POWER = 'power';

    public const TYPE_SAY = 'say';

    /** A power action must not be repeated on its own, the page reports the failure instead. */
    public int $tries = 1;

    public int $timeout = 60;

    public function __construct(
        public readonly string $runId,
        public readonly int $serverId,
        public readonly string $type,
        public readonly string $payload,
    ) {
        $this->onQueue('standard');
    }

    public function handle(
        BulkActionService $bulk,
        DaemonPowerRepository $power,
        DaemonCommandRepository $commands,
        DaemonServerRepository $servers,
    ): void {
        /** @var Server|null $server */
        $server = Server::query()->with('node')->where('id', $this->serverId)->first();

        // The server may have been suspended, deleted or moved while the job waited in the queue.
        if (!$server || $server->status !== null || $server->transfer()->exists()) {
            $bulk->record($this->runId, 'skipped');

            return;
        }

        try {
            if ($this->type === self::TYPE_POWER) {
                if (!in_array($this->payload, BulkActionService::POWER_ACTIONS, true)) {
                    throw new \InvalidArgumentException('Unknown power action.');
                }

                $power->setServer($server)->send($this->payload);
            } else {
                $message = $bulk->sanitizeMessage($this->payload);
                if ($message === '') {
                    throw new \InvalidArgumentException('Empty message.');
                }

                $details = $servers->setServer($server)->getDetails();
                if (($details['state'] ?? $details['utilization']['state'] ?? 'offline') !== 'running') {
                    $bulk->record($this->runId, 'skipped');

                    return;
                }

                $commands->setServer($server)->send('say ' . $message);
            }

            $bulk->record($this->runId, 'ok');
        } catch (\Throwable $exception) {
            $bulk->record($this->runId, 'failed', $server->name, $this->describe($exception));
        }
    }

    /**
     * Only reached when the job itself dies (e.g. the worker is killed on timeout).
     */
    public function failed(\Throwable $exception): void
    {
        $server = Server::query()->where('id', $this->serverId)->first();

        app(BulkActionService::class)->record($this->runId, 'failed', $server?->name, $this->describe($exception));
    }

    private function describe(\Throwable $exception): string
    {
        return $exception instanceof \InvalidArgumentException
            ? $exception->getMessage()
            : class_basename($exception) . ': ' . $exception->getMessage();
    }
}
