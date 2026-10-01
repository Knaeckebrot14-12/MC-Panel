<?php

return [
    'title' => 'Version',
    'unsupported' => 'This server type cannot change its Minecraft version here.',
    'current_title' => 'Installed now',
    'current_unknown' => 'Not installed through this page yet (the version from the server\'s setup is running).',
    'install_title' => 'Install :name',
    'no_versions' => 'No versions are available right now.',
    'version_label' => 'Version',
    'install_button' => 'Install',
    'java_hint' => 'Needs Java :java; the matching Java image is selected automatically.',
    'installing' => 'Installing… the server is stopped and the new version is downloaded. This can take a minute.',
    'confirm' => 'Install :name :version? The server will be stopped and its server jar replaced. Worlds, plugins and settings stay.',
    'confirm_downgrade' => 'This is an older version than the one installed. Minecraft worlds usually cannot be opened by older versions and may get damaged. Make a backup first! Install :name :version anyway?',
    'confirm_yes' => 'Yes, install',
    'confirm_no' => 'Cancel',
    'backup_hint' => 'Tip: create a backup before switching to another software or version.',
    'installed' => ':name :version was installed (Java :java). Start the server to use it.',
    'types' => [
        'paper' => [
            'name' => 'Paper',
            'description' => 'Fast, plugin support (Bukkit/Spigot)',
        ],
        'purpur' => [
            'name' => 'Purpur',
            'description' => 'Paper with many extra options',
        ],
        'folia' => [
            'name' => 'Folia',
            'description' => 'Paper for very big servers (multithreaded)',
        ],
        'fabric' => [
            'name' => 'Fabric',
            'description' => 'Lightweight mod loader',
        ],
        'vanilla' => [
            'name' => 'Vanilla',
            'description' => 'The original server from Mojang',
        ],
        'velocity' => [
            'name' => 'Velocity',
            'description' => 'Proxy that connects several servers',
        ],
    ],
    'errors' => [
        'unsupported' => 'This server cannot switch versions (its startup does not use a server jar).',
        'unknown_version' => 'This version is not available.',
        'unknown_type' => 'Unknown server software.',
        'download' => 'The new version could not be downloaded. Please try again later.',
        'no_build' => 'There is no download for this version yet.',
        'still_running' => 'The server could not be stopped. Stop it and try again.',
        'api' => 'The version list could not be loaded. Please try again later.',
        'busy' => 'A version is already being installed on this server.',
    ],
];
