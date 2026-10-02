<?php

namespace Pterodactyl\Services\Servers;

use Pterodactyl\Models\User;
use Pterodactyl\Models\Server;
use Pterodactyl\Facades\Activity;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Notifications\ServerOwnershipOffered;
use Pterodactyl\Services\Notifications\PushService;

/**
 * A server owner gives their server to another user. The other user has to accept; until then
 * nothing changes. On acceptance the recipient becomes the owner (also of the coin renewals, if
 * the server was bought with coins) and the previous owner loses access.
 */
class OwnershipTransferService
{
    public const EXPIRES_DAYS = 7;

    /**
     * @throws DisplayException
     */
    public function offer(Server $server, User $from, string $username): object
    {
        if ($server->owner_id !== $from->id) {
            throw new DisplayException(trans('server_ownership.errors.not_owner'));
        }
        $this->assertTransferable($server);

        $to = User::query()->where('username', mb_strtolower(trim($username)))->first();
        if (!$to) {
            throw new DisplayException(trans('server_ownership.errors.no_user'));
        }
        if ($to->id === $from->id) {
            throw new DisplayException(trans('server_ownership.errors.self'));
        }
        // Checked now so nobody waits for an offer they can't accept; checked again on acceptance.
        $this->assertFitsPool($to, $server);

        DB::table('server_ownership_requests')->updateOrInsert(
            ['server_id' => $server->id],
            ['from_user_id' => $from->id, 'to_user_id' => $to->id, 'expires_at' => now()->addDays(self::EXPIRES_DAYS), 'created_at' => now(), 'updated_at' => now()]
        );

        $this->tell($to, fn ($locale) => [
            trans('server_ownership.push.offer_title', [], $locale),
            trans('server_ownership.push.offer_body', ['user' => $from->username, 'server' => $server->name], $locale),
        ], '/', new ServerOwnershipOffered($server, $from));

        return $this->pending($server);
    }

    /**
     * The open offer of a server, if any: ['to' => username, 'expires_at' => ...].
     */
    public function pending(Server $server): ?object
    {
        $this->purgeExpired();

        return DB::table('server_ownership_requests as r')
            ->join('users as u', 'u.id', '=', 'r.to_user_id')
            ->where('r.server_id', $server->id)
            ->first(['r.id', 'u.username as to', 'r.expires_at']);
    }

    public function cancel(Server $server): void
    {
        DB::table('server_ownership_requests')->where('server_id', $server->id)->delete();
    }

    /**
     * Offers waiting for $user, with what they would take over.
     */
    public function incoming(User $user): array
    {
        $this->purgeExpired();

        return DB::table('server_ownership_requests as r')
            ->join('servers as s', 's.id', '=', 'r.server_id')
            ->join('users as f', 'f.id', '=', 'r.from_user_id')
            ->where('r.to_user_id', $user->id)
            ->orderBy('r.id')
            ->get(['r.id', 'r.expires_at', 's.name as server', 's.memory', 's.disk', 's.cpu', 's.coin_monthly_price', 's.paid_with_coins_until', 'f.username as from'])
            ->map(fn ($row) => [
                'id' => $row->id,
                'server' => $row->server,
                'from' => $row->from,
                'memory' => (int) $row->memory,
                'disk' => (int) $row->disk,
                'cpu' => (int) $row->cpu,
                'coins' => $row->paid_with_coins_until ? ['monthly' => (int) $row->coin_monthly_price, 'paid_until' => $row->paid_with_coins_until] : null,
                'expires_at' => $row->expires_at,
            ])->all();
    }

    /**
     * @throws DisplayException
     */
    public function accept(User $to, int $requestId): Server
    {
        [$server, $fromId] = DB::transaction(function () use ($to, $requestId) {
            $request = DB::table('server_ownership_requests')->where('id', $requestId)->where('to_user_id', $to->id)->lockForUpdate()->first();
            if (!$request || now()->greaterThan($request->expires_at)) {
                throw new DisplayException(trans('server_ownership.errors.gone'));
            }

            /** @var Server $server */
            $server = Server::query()->whereKey($request->server_id)->lockForUpdate()->firstOrFail();
            // The owner may have changed in between (admin, an earlier offer): then this offer is void.
            if ($server->owner_id !== (int) $request->from_user_id) {
                DB::table('server_ownership_requests')->where('id', $request->id)->delete();
                throw new DisplayException(trans('server_ownership.errors.gone'));
            }
            $this->assertTransferable($server);
            $this->assertFitsPool($to, $server);

            $server->forceFill(['owner_id' => $to->id])->saveOrFail();
            // The new owner has every right anyway; a subuser entry would only be confusing.
            DB::table('subusers')->where('server_id', $server->id)->where('user_id', $to->id)->delete();
            DB::table('server_ownership_requests')->where('id', $request->id)->delete();

            return [$server, (int) $request->from_user_id];
        });

        $previous = User::query()->find($fromId);
        Activity::event('server:ownership.accept')->subject($server)->property(['from' => $previous?->username, 'to' => $to->username])->log();
        if ($previous) {
            $this->tell($previous, fn ($locale) => [
                trans('server_ownership.push.accepted_title', [], $locale),
                trans('server_ownership.push.accepted_body', ['user' => $to->username, 'server' => $server->name], $locale),
            ], '/');
        }

        return $server;
    }

    public function decline(User $to, int $requestId): void
    {
        $request = DB::table('server_ownership_requests')->where('id', $requestId)->where('to_user_id', $to->id)->first();
        if (!$request) {
            return;
        }
        DB::table('server_ownership_requests')->where('id', $request->id)->delete();

        $server = Server::query()->find($request->server_id);
        $from = User::query()->find($request->from_user_id);
        if ($server) {
            Activity::event('server:ownership.decline')->subject($server)->property(['to' => $to->username])->log();
        }
        if ($from && $server) {
            $this->tell($from, fn ($locale) => [
                trans('server_ownership.push.declined_title', [], $locale),
                trans('server_ownership.push.declined_body', ['user' => $to->username, 'server' => $server->name], $locale),
            ], '/server/' . $server->uuidShort . '/settings');
        }
    }

    /**
     * @throws DisplayException
     */
    private function assertTransferable(Server $server): void
    {
        if ($server->isSuspended() || $server->status !== null || $server->transfer) {
            throw new DisplayException(trans('server_ownership.errors.busy'));
        }
    }

    /**
     * Servers that count against the free resource pool need room in the recipient's pool, the
     * same rule as creating one; otherwise friends could pile up resources for each other.
     *
     * @throws DisplayException
     */
    private function assertFitsPool(User $to, Server $server): void
    {
        if ($to->root_admin) {
            return;
        }
        $counts = fn (Server $s) => config('coins.server.count_towards_pool') || $s->paid_with_coins_until === null;
        if (!$counts($server)) {
            return;
        }

        $owned = Server::query()->where('owner_id', $to->id)->where('id', '!=', $server->id);
        if (!config('coins.server.count_towards_pool')) {
            $owned->whereNull('paid_with_coins_until');
        }
        $checks = [
            ['slots', (clone $owned)->count() + 1, (int) $to->server_slots],
            ['memory', (int) (clone $owned)->sum('memory') + (int) $server->memory, (int) $to->server_memory_limit],
            ['disk', (int) (clone $owned)->sum('disk') + (int) $server->disk, (int) $to->server_disk_limit],
            ['cpu', (int) (clone $owned)->sum('cpu') + (int) $server->cpu, (int) $to->server_cpu_limit],
            ['backups', (int) (clone $owned)->sum('backup_limit') + (int) $server->backup_limit, (int) $to->server_backup_limit],
        ];
        foreach ($checks as [$what, $needed, $limit]) {
            if ($needed > $limit) {
                throw new DisplayException(trans('server_ownership.errors.pool', ['user' => $to->username, 'what' => trans('server_ownership.units.' . $what)]));
            }
        }
    }

    private function purgeExpired(): void
    {
        DB::table('server_ownership_requests')->where('expires_at', '<', now())->delete();
    }

    /**
     * Push (and mail, when given) to a user in their language; failures never block the transfer.
     */
    private function tell(User $user, \Closure $texts, string $url, ?object $mail = null): void
    {
        $locale = $user->language ?: config('app.locale');
        try {
            [$title, $body] = $texts($locale);
            app(PushService::class)->sendToUsers($user->id, $title, $body, $url, 'ownership');
        } catch (\Throwable $exception) {
            report($exception);
        }
        if ($mail) {
            try {
                $user->notify($mail->locale($locale));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
    }
}
