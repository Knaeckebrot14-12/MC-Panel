<?php

return [
    'title' => 'Maintenance',
    'subheading' => 'Announce maintenance or lock the panel for everyone but the team.',
    'saved' => 'The maintenance mode was saved.',
    'badge' => 'on',
    'current' => 'Current mode: :mode',
    'modes' => [
        'off' => 'Off',
        'banner' => 'Banner',
        'lock' => 'Locked',
    ],
    'descriptions' => [
        'off' => 'The panel works normally.',
        'banner' => 'Everybody can use the panel; the message is shown as a banner on every page.',
        'lock' => 'Only supporters, moderators, admins and the owner can use the panel. Everybody else sees the message.',
    ],
    'message_label' => 'Message',
    'message_description' => 'Shown in the banner, on the maintenance screen and on the status page.',
    'info_heading' => 'Good to know',
    'info_servers' => 'Game servers keep running; players can still join them.',
    'info_staff' => 'The team keeps full access and sees a red banner as a reminder.',
    'info_status' => 'The public status page shows the message too.',
];
