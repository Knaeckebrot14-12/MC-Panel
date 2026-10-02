<?php

return [
    'title' => 'Subdomain',
    'current' => 'Players can join with:',
    'name_placeholder' => 'myserver',
    'change' => 'Change',
    'create' => 'Create',
    'remove' => 'Remove',
    'hint' => '3–32 characters: lowercase letters, digits and dashes. It can take a few minutes until the address works everywhere.',
    'saved' => ':fqdn now points to this server.',
    'errors' => [
        'disabled' => 'Subdomains are not available.',
        'not_minecraft' => 'Subdomains are only available for Minecraft servers.',
        'invalid_name' => 'This name is not allowed. Use 3–32 lowercase letters, digits or dashes.',
        'invalid_domain' => 'This domain is not available.',
        'taken' => 'This subdomain is already taken.',
        'busy' => 'Someone is just changing this subdomain. Please try again in a moment.',
        'no_ip' => 'The public address of this server could not be determined.',
        'cloudflare' => 'The DNS record could not be created: :error',
        'no_zone' => 'The domain :domain is not set up correctly. Please contact the support.',
    ],
];
