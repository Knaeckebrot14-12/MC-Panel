<?php

return [
    'title' => 'Server settings',
    'missing' => 'This server has no server.properties yet. Start it once, or save here to create the file.',
    'saved' => 'Settings saved. They apply on the next start.',
    'saved_restart' => 'Settings saved. Restart the server to apply them.',
    'unsaved' => ':count unsaved changes',
    'reset' => 'Discard',
    'save' => 'Save',
    'locked' => 'Port, IP and RCON are managed by the panel and can\'t be changed here.',
    'groups' => [
        'general' => 'General',
        'world' => 'World',
        'access' => 'Access & security',
        'resource_pack' => 'Resource pack',
        'other' => 'Other settings',
    ],
    'options' => [
        'survival' => 'Survival',
        'creative' => 'Creative',
        'adventure' => 'Adventure',
        'spectator' => 'Spectator',
        'peaceful' => 'Peaceful',
        'easy' => 'Easy',
        'normal' => 'Normal',
        'hard' => 'Hard',
        'flat' => 'Superflat',
        'large_biomes' => 'Large biomes',
        'amplified' => 'Amplified',
    ],
    'fields' => [
        'motd' => [
            'label' => 'Server description (MOTD)',
            'description' => 'Shown in the multiplayer server list.',
        ],
        'max-players' => [
            'label' => 'Maximum players',
            'description' => 'How many players can be online at the same time.',
        ],
        'gamemode' => [
            'label' => 'Game mode',
            'description' => 'Game mode for new players.',
        ],
        'difficulty' => [
            'label' => 'Difficulty',
            'description' => 'How dangerous mobs and hunger are.',
        ],
        'hardcore' => [
            'label' => 'Hardcore',
            'description' => 'Players are banned after dying once.',
        ],
        'pvp' => [
            'label' => 'PvP',
            'description' => 'Players can damage each other.',
        ],
        'force-gamemode' => [
            'label' => 'Force game mode',
            'description' => 'Players always join in the default game mode.',
        ],
        'allow-flight' => [
            'label' => 'Allow flight',
            'description' => 'Needed by some plugins and mods; otherwise flying players get kicked.',
        ],
        'level-name' => [
            'label' => 'World folder',
            'description' => 'Name of the world folder to load or create.',
        ],
        'level-seed' => [
            'label' => 'Seed',
            'description' => 'Seed for new worlds; empty means random.',
        ],
        'level-type' => [
            'label' => 'World type',
            'description' => 'Only used when a new world is generated.',
        ],
        'allow-nether' => [
            'label' => 'Nether',
            'description' => 'Players can travel to the Nether.',
        ],
        'generate-structures' => [
            'label' => 'Structures',
            'description' => 'Villages, temples and other structures are generated.',
        ],
        'spawn-monsters' => [
            'label' => 'Monsters',
            'description' => 'Hostile mobs spawn.',
        ],
        'spawn-npcs' => [
            'label' => 'Villagers',
            'description' => 'Villagers spawn.',
        ],
        'spawn-protection' => [
            'label' => 'Spawn protection',
            'description' => 'Radius around spawn only operators can build in (0 = off).',
        ],
        'view-distance' => [
            'label' => 'View distance',
            'description' => 'Chunks sent to players. Lower values save memory and CPU.',
        ],
        'simulation-distance' => [
            'label' => 'Simulation distance',
            'description' => 'Chunks around players in which things move and grow.',
        ],
        'max-world-size' => [
            'label' => 'World border',
            'description' => 'Maximum radius of the world in blocks.',
        ],
        'white-list' => [
            'label' => 'Whitelist',
            'description' => 'Only whitelisted players can join.',
        ],
        'enforce-whitelist' => [
            'label' => 'Enforce whitelist',
            'description' => 'Kicks online players who are removed from the whitelist.',
        ],
        'online-mode' => [
            'label' => 'Online mode',
            'description' => 'Checks accounts with Mojang. Only turn off behind a proxy like Velocity or BungeeCord.',
        ],
        'enforce-secure-profile' => [
            'label' => 'Secure chat profile',
            'description' => 'Players need Mojang-signed chat keys.',
        ],
        'enable-command-block' => [
            'label' => 'Command blocks',
            'description' => 'Command blocks can be used.',
        ],
        'op-permission-level' => [
            'label' => 'Operator level',
            'description' => 'Permission level of operators (1-4).',
        ],
        'player-idle-timeout' => [
            'label' => 'AFK kick (minutes)',
            'description' => 'Kicks idle players after this many minutes (0 = never).',
        ],
        'resource-pack' => [
            'label' => 'Resource pack URL',
            'description' => 'Direct download link to a resource pack (zip).',
        ],
        'require-resource-pack' => [
            'label' => 'Require resource pack',
            'description' => 'Players who decline the pack are disconnected.',
        ],
    ],
];
