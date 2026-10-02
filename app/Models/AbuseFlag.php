<?php

namespace Pterodactyl\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A hint from the abuse scan that a server may be abused. Only a flag: staff decide what to do.
 *
 * @property int $id
 * @property int $server_id
 * @property string $type cpu|miner|network
 * @property array|null $details
 * @property CarbonInterface $first_seen_at
 * @property CarbonInterface $last_seen_at
 * @property CarbonInterface|null $resolved_at
 * @property int|null $resolved_by
 * @property Server $server
 * @property User|null $resolver
 */
class AbuseFlag extends Model
{
    public const RESOURCE_NAME = 'abuse_flag';

    public const TYPE_CPU = 'cpu';
    public const TYPE_MINER = 'miner';
    public const TYPE_NETWORK = 'network';

    public const TYPES = [self::TYPE_CPU, self::TYPE_MINER, self::TYPE_NETWORK];

    public $timestamps = false;

    protected $table = 'abuse_flags';

    protected $guarded = ['id'];

    protected $casts = [
        'server_id' => 'integer',
        'details' => 'array',
        'resolved_by' => 'integer',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public static array $validationRules = [
        'server_id' => 'required|integer',
        'type' => 'required|in:cpu,miner,network',
        'details' => 'nullable|array',
    ];

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('resolved_at');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * One localized sentence about why the server was flagged (Discord, push and the admin list).
     */
    public function describe(): string
    {
        $d = $this->details ?? [];

        switch ($this->type) {
            case self::TYPE_CPU:
                return trans('admin/abuse.detail.' . (!empty($d['unlimited']) ? 'cpu_unlimited' : 'cpu'), [
                    'min' => $d['min'] ?? 0,
                    'avg' => $d['avg'] ?? 0,
                    'minutes' => $d['minutes'] ?? 0,
                    'cap' => $d['cap'] ?? 0,
                    'percent' => $d['threshold_percent'] ?? 0,
                ]);
            case self::TYPE_NETWORK:
                return trans('admin/abuse.detail.network', [
                    'rate' => $d['mib_per_min'] ?? 0,
                    'minutes' => $d['minutes'] ?? 0,
                    'threshold' => $d['threshold'] ?? 0,
                ]);
            case self::TYPE_MINER:
                $items = [];
                foreach (array_slice($d['matches'] ?? [], 0, 3) as $match) {
                    $items[] = ($match['source'] ?? '') === 'file'
                        ? trans('admin/abuse.detail.miner_file', ['name' => $match['text'] ?? ''])
                        : trans('admin/abuse.detail.miner_log', ['keyword' => $match['keyword'] ?? '', 'text' => $match['text'] ?? '']);
                }

                return implode(' | ', $items);
        }

        return '';
    }

    /**
     * Number of unresolved flags (sidebar badge).
     */
    public static function openCount(): int
    {
        return static::query()->open()->count();
    }
}
