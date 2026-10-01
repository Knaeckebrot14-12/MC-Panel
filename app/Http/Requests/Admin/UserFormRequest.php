<?php

namespace Pterodactyl\Http\Requests\Admin;

use Pterodactyl\Models\User;
use Illuminate\Support\Collection;

class UserFormRequest extends AdminFormRequest
{
    private const PROFILE_FIELDS = [
        'username', 'name_first', 'name_last', 'language',
        'server_memory_limit', 'server_disk_limit', 'server_cpu_limit', 'server_backup_limit', 'server_slots',
    ];

    /**
     * Whatever the team member may not change is replaced with the stored value, regardless of what
     * the browser sent (Admin -> Settings -> Roles: users.edit, users.email, users.password).
     */
    protected function prepareForValidation(): void
    {
        $actor = $this->user();
        $target = $this->route()->parameter('user');
        if (!$actor || !$target instanceof User) {
            return;
        }

        $merge = [];
        if (!$actor->hasStaffPermission('users.edit')) {
            foreach (self::PROFILE_FIELDS as $field) {
                $merge[$field] = $target->getAttribute($field);
            }
            $merge['email'] = $target->email;
        }
        if (!$actor->canSeeEmailOf($target)) {
            $merge['email'] = $target->email;
        }
        if (!$actor->is($target) && !$actor->hasStaffPermission('users.password')) {
            $merge['password'] = null;
        }

        $this->merge($merge);
    }

    /**
     * Rules to apply to requests for updating or creating a user
     * in the Admin CP.
     */
    public function rules(): array
    {
        return Collection::make(
            User::getRulesForUpdate($this->route()->parameter('user'))
        )->only([
            'email',
            'username',
            'name_first',
            'name_last',
            'password',
            'language',
            'server_memory_limit',
            'server_disk_limit',
            'server_cpu_limit',
            'server_backup_limit',
            'server_slots',
        ])->toArray();
    }
}
