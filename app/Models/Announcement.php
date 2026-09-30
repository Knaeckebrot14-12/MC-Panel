<?php

namespace Pterodactyl\Models;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 * @property bool $is_active
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 */
class Announcement extends Model
{
    public const RESOURCE_NAME = 'announcement';

    protected $table = 'announcements';

    protected $fillable = ['title', 'content', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public static array $validationRules = [
        'title' => 'required|string|between:1,191',
        'content' => 'required|string|max:2000',
        'is_active' => 'sometimes|boolean',
    ];
}
