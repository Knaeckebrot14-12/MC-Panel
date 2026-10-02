<?php

return [
    'title' => 'Roles',
    'subheading' => 'What supporters, moderators and admins may do.',
    'matrix_heading' => 'Permissions per role',
    'permission' => 'Permission',
    'members' => ':count member(s)',
    'save' => 'Save',
    'saved' => 'Role permissions saved.',
    'reset' => 'Reset to defaults',
    'reset_confirm' => 'Reset all role permissions to the defaults?',
    'hint' => 'Changes apply immediately to every member of the role. The owner always has every permission.',
    'owner_only_text' => 'Panel settings (including updates, design, login, monitoring, subdomains and this page) and the application API stay with the owner only, otherwise a role could grant itself every permission.',
    'groups' => [
        'general' => 'General',
        'users' => 'Users',
        'servers' => 'Servers',
        'infrastructure' => 'Infrastructure',
        'coins' => 'Coins & shop',
        'owner_only' => 'Owner only',
    ],
    'permissions' => [
        'overview' => [
            'name' => 'Overview',
            'description' => 'Admin dashboard with statistics.',
        ],
        'audit' => [
            'name' => 'Audit log',
            'description' => 'See what the team did.',
        ],
        'maintenance' => [
            'name' => 'Maintenance mode',
            'description' => 'Turn the banner or lock-out on and off.',
        ],
        'announcements' => [
            'name' => 'Announcements',
            'description' => 'Create, hide and delete announcements.',
        ],
        'tickets' => [
            'name' => 'Tickets',
            'description' => 'Answer and manage support tickets.',
        ],
        'users_view' => [
            'name' => 'View users',
            'description' => 'User list and user pages.',
        ],
        'users_email' => [
            'name' => 'See e-mail addresses and IPs',
            'description' => 'Without it, e-mail addresses and registration IPs of other users are hidden and cannot be searched.',
        ],
        'users_edit' => [
            'name' => 'Edit and create users',
            'description' => 'Name, username, e-mail, language and resource limits.',
        ],
        'users_password' => [
            'name' => 'Change passwords',
            'description' => 'Set a new password for other users.',
        ],
        'users_moderate' => [
            'name' => 'Moderate users',
            'description' => 'Suspend, unsuspend and confirm e-mail addresses.',
        ],
        'users_coins' => [
            'name' => 'Give and take coins',
            'description' => 'Change the coin balance of users.',
        ],
        'users_roles' => [
            'name' => 'Assign roles',
            'description' => 'Give users a role below one\'s own.',
        ],
        'users_delete' => [
            'name' => 'Delete users',
            'description' => 'Delete accounts without servers.',
        ],
        'users_impersonate' => [
            'name' => 'Sign in as users (support view)',
            'description' => 'See the panel exactly as a normal user sees it, to help with problems. This also shows their e-mail address. Account settings, coins and tickets can\'t be changed meanwhile.',
        ],
        'servers_view' => [
            'name' => 'View servers',
            'description' => 'Server list and server pages.',
        ],
        'servers_moderate' => [
            'name' => 'Suspend servers',
            'description' => 'Suspend and unsuspend servers.',
        ],
        'servers_manage' => [
            'name' => 'Manage servers',
            'description' => 'Details, resources, startup, databases, mounts, reinstall and transfer.',
        ],
        'servers_create' => [
            'name' => 'Create servers',
            'description' => 'Create servers for any user.',
        ],
        'servers_delete' => [
            'name' => 'Delete servers',
            'description' => 'Delete servers.',
        ],
        'nodes' => [
            'name' => 'Nodes',
            'description' => 'Nodes, allocations, monitoring and Wings updates.',
        ],
        'locations' => [
            'name' => 'Locations',
            'description' => 'Create and edit locations.',
        ],
        'databases' => [
            'name' => 'Database hosts',
            'description' => 'Database servers for game servers.',
        ],
        'mounts' => [
            'name' => 'Mounts',
            'description' => 'Shared folders for servers.',
        ],
        'nests' => [
            'name' => 'Nests & eggs',
            'description' => 'Server types and their startup settings.',
        ],
        'coins_vouchers' => [
            'name' => 'Vouchers',
            'description' => 'Create and manage coin vouchers.',
        ],
        'coins_plans' => [
            'name' => 'Server plans',
            'description' => 'Plans sold in the coin shop.',
        ],
        'coins_settings' => [
            'name' => 'Coin settings',
            'description' => 'Rewards, prices and shop settings.',
        ],
    ],
];
