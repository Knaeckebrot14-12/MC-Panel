<?php

namespace Pterodactyl\Services\Users;

use Pterodactyl\Models\User;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

/**
 * What supporters, moderators and admins may do in the admin area. The owner sets this under
 * Admin -> Settings -> Roles; the owner always has every permission. The panel settings, the
 * application API and this page stay owner-only, otherwise an admin could grant himself anything.
 */
class RolePermissions
{
    /** Roles whose permissions can be edited (owner always has all of them). */
    public const ROLES = [User::ROLE_SUPPORTER, User::ROLE_MODERATOR, User::ROLE_ADMIN];

    /** Every permission, grouped for the settings page. */
    public const GROUPS = [
        'general' => ['overview', 'audit', 'maintenance', 'announcements', 'tickets'],
        'users' => ['users.view', 'users.email', 'users.edit', 'users.password', 'users.moderate', 'users.coins', 'users.roles', 'users.delete', 'users.impersonate'],
        'servers' => ['servers.view', 'servers.moderate', 'servers.manage', 'servers.create', 'servers.delete', 'servers.bulk'],
        'infrastructure' => ['nodes', 'locations', 'databases', 'mounts', 'nests'],
        'coins' => ['coins.vouchers', 'coins.plans', 'coins.settings'],
        'security' => ['security.ipblock'],
    ];

    /** What each role could do before permissions became editable; used until the owner saves. */
    public const DEFAULTS = [
        User::ROLE_SUPPORTER => ['tickets'],
        User::ROLE_MODERATOR => ['tickets', 'announcements', 'users.view', 'users.email', 'users.moderate', 'servers.view', 'servers.moderate'],
        User::ROLE_ADMIN => [
            'overview', 'audit', 'maintenance', 'announcements', 'tickets',
            'users.view', 'users.email', 'users.edit', 'users.password', 'users.moderate', 'users.coins', 'users.roles', 'users.delete',
            'servers.view', 'servers.moderate', 'servers.manage', 'servers.create', 'servers.delete',
            'databases', 'coins.vouchers', 'coins.plans', 'coins.settings',
        ],
    ];

    private static ?array $resolved = null;

    public static function all(): array
    {
        return array_merge(...array_values(self::GROUPS));
    }

    /**
     * @return array<string, string[]> role => permissions
     */
    public static function matrix(): array
    {
        if (self::$resolved !== null) {
            return self::$resolved;
        }

        $stored = json_decode((string) config('mcpanel.roles.permissions'), true);
        $matrix = [];
        foreach (self::ROLES as $role) {
            $granted = is_array($stored) && isset($stored[$role]) && is_array($stored[$role]) ? $stored[$role] : self::DEFAULTS[$role];
            $matrix[$role] = array_values(array_intersect(self::all(), $granted));
        }

        return self::$resolved = $matrix;
    }

    public static function grants(string $role, string $permission): bool
    {
        if ($role === User::ROLE_OWNER) {
            return true;
        }

        return in_array($permission, self::matrix()[$role] ?? [], true);
    }

    /**
     * @param array<string, string[]> $matrix
     */
    public static function save(array $matrix): void
    {
        $clean = [];
        foreach (self::ROLES as $role) {
            $clean[$role] = array_values(array_intersect(self::all(), (array) ($matrix[$role] ?? [])));
        }

        app(SettingsRepositoryInterface::class)->set('settings::mcpanel:roles:permissions', json_encode($clean));
        config(['mcpanel.roles.permissions' => json_encode($clean)]);
        self::$resolved = null;
    }

    public static function flush(): void
    {
        self::$resolved = null;
    }
}
