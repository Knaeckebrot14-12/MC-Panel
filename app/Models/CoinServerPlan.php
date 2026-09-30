<?php

namespace Pterodactyl\Models;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $memory
 * @property int $disk
 * @property int $cpu
 * @property int $backups
 * @property int $monthly_price
 * @property bool $active
 * @property int $sort_order
 */
class CoinServerPlan extends Model
{
    public const RESOURCE_NAME = 'coin_server_plan';

    protected $table = 'coin_server_plans';

    protected $fillable = ['name', 'description', 'memory', 'disk', 'cpu', 'backups', 'monthly_price', 'active', 'sort_order'];

    protected $casts = [
        'memory' => 'integer',
        'disk' => 'integer',
        'cpu' => 'integer',
        'backups' => 'integer',
        'monthly_price' => 'integer',
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static array $validationRules = [
        'name' => 'required|string|min:1|max:191',
        'description' => 'nullable|string|max:191',
        'memory' => 'required|integer|min:128',
        'disk' => 'required|integer|min:128',
        'cpu' => 'required|integer|min:0',
        'backups' => 'required|integer|min:0',
        'monthly_price' => 'required|integer|min:0',
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
