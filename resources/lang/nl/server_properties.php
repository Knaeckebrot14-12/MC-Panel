<?php

return [
    'title' => 'Serverinstellingen',
    'missing' => 'Deze server heeft nog geen server.properties. Start hem één keer of sla hier op om het bestand aan te maken.',
    'saved' => 'Instellingen opgeslagen. Ze gelden bij de volgende start.',
    'saved_restart' => 'Instellingen opgeslagen. Herstart de server om ze toe te passen.',
    'unsaved' => ':count niet-opgeslagen wijzigingen',
    'reset' => 'Verwerpen',
    'save' => 'Opslaan',
    'locked' => 'Poort, IP en RCON worden door het paneel beheerd en kunnen hier niet worden gewijzigd.',
    'groups' => [
        'general' => 'Algemeen',
        'world' => 'Wereld',
        'access' => 'Toegang en beveiliging',
        'resource_pack' => 'Resourcepack',
        'other' => 'Overige instellingen',
    ],
    'options' => [
        'survival' => 'Survival',
        'creative' => 'Creative',
        'adventure' => 'Avontuur',
        'spectator' => 'Toeschouwer',
        'peaceful' => 'Vreedzaam',
        'easy' => 'Makkelijk',
        'normal' => 'Normaal',
        'hard' => 'Moeilijk',
        'flat' => 'Supervlak',
        'large_biomes' => 'Grote biomen',
        'amplified' => 'Versterkt',
    ],
    'server_list' => [
        'title' => 'Serverlijst',
        'line1' => 'Eerste regel',
        'line2' => 'Tweede regel (optioneel)',
        'codes_hint' => 'Kleur- en opmaakcodes beginnen met & (bijv. &a groen, &l vet, &r herstellen). Klik op een kleur om die bij de cursor in te voegen. De voorbeeldweergave toont hoe spelers de server zien.',
        'icon_title' => 'Servericoon',
        'icon_upload' => 'Icoon uploaden',
        'icon_remove' => 'Icoon verwijderen',
        'icon_hint' => 'PNG, JPG, GIF of WebP. Wordt automatisch vierkant bijgesneden en verkleind tot 64×64. Werkt na een herstart.',
        'icon_saved' => 'Servericoon opgeslagen. Het verschijnt na de volgende herstart.',
        'icon_invalid' => 'Dit bestand is geen leesbare afbeelding.',
        'no_icon' => 'Geen icoon',
        'format' => [
            'l' => 'Vet',
            'o' => 'Cursief',
            'n' => 'Onderstreept',
            'm' => 'Doorgehaald',
            'k' => 'Magisch',
            'r' => 'Herstellen',
        ],
    ],
    'fields' => [
        'motd' => [
            'label' => 'Serverbeschrijving (MOTD)',
            'description' => 'Wordt getoond in de multiplayer-serverlijst.',
        ],
        'max-players' => [
            'label' => 'Maximaal aantal spelers',
            'description' => 'Hoeveel spelers tegelijk online kunnen zijn.',
        ],
        'gamemode' => [
            'label' => 'Spelmodus',
            'description' => 'Spelmodus voor nieuwe spelers.',
        ],
        'difficulty' => [
            'label' => 'Moeilijkheidsgraad',
            'description' => 'Hoe gevaarlijk mobs en honger zijn.',
        ],
        'hardcore' => [
            'label' => 'Hardcore',
            'description' => 'Spelers worden verbannen nadat ze één keer zijn gestorven.',
        ],
        'pvp' => [
            'label' => 'PvP',
            'description' => 'Spelers kunnen elkaar schade toebrengen.',
        ],
        'force-gamemode' => [
            'label' => 'Spelmodus forceren',
            'description' => 'Spelers joinen altijd in de standaardspelmodus.',
        ],
        'allow-flight' => [
            'label' => 'Vliegen toestaan',
            'description' => 'Nodig voor sommige plugins en mods; anders worden vliegende spelers gekickt.',
        ],
        'level-name' => [
            'label' => 'Wereldmap',
            'description' => 'Naam van de wereldmap die wordt geladen of aangemaakt.',
        ],
        'level-seed' => [
            'label' => 'Seed',
            'description' => 'Seed voor nieuwe werelden; leeg betekent willekeurig.',
        ],
        'level-type' => [
            'label' => 'Wereldtype',
            'description' => 'Alleen gebruikt wanneer een nieuwe wereld wordt gegenereerd.',
        ],
        'allow-nether' => [
            'label' => 'Nether',
            'description' => 'Spelers kunnen naar de Nether reizen.',
        ],
        'generate-structures' => [
            'label' => 'Structuren',
            'description' => 'Dorpen, tempels en andere structuren worden gegenereerd.',
        ],
        'spawn-monsters' => [
            'label' => 'Monsters',
            'description' => 'Vijandige mobs verschijnen.',
        ],
        'spawn-npcs' => [
            'label' => 'Dorpelingen',
            'description' => 'Dorpelingen verschijnen.',
        ],
        'spawn-protection' => [
            'label' => 'Spawnbescherming',
            'description' => 'Straal rond de spawn waarin alleen operators mogen bouwen (0 = uit).',
        ],
        'view-distance' => [
            'label' => 'Zichtafstand',
            'description' => 'Chunks die naar spelers worden gestuurd. Lagere waarden besparen geheugen en CPU.',
        ],
        'simulation-distance' => [
            'label' => 'Simulatieafstand',
            'description' => 'Chunks rond spelers waarin dingen bewegen en groeien.',
        ],
        'max-world-size' => [
            'label' => 'Wereldgrens',
            'description' => 'Maximale straal van de wereld in blokken.',
        ],
        'white-list' => [
            'label' => 'Whitelist',
            'description' => 'Alleen spelers op de whitelist kunnen joinen.',
        ],
        'enforce-whitelist' => [
            'label' => 'Whitelist afdwingen',
            'description' => 'Kickt online spelers die van de whitelist worden verwijderd.',
        ],
        'online-mode' => [
            'label' => 'Onlinemodus',
            'description' => 'Controleert accounts bij Mojang. Zet dit alleen uit achter een proxy zoals Velocity of BungeeCord.',
        ],
        'enforce-secure-profile' => [
            'label' => 'Veilig chatprofiel',
            'description' => 'Spelers hebben door Mojang ondertekende chatsleutels nodig.',
        ],
        'enable-command-block' => [
            'label' => 'Commandoblokken',
            'description' => 'Commandoblokken kunnen worden gebruikt.',
        ],
        'op-permission-level' => [
            'label' => 'Operatorniveau',
            'description' => 'Rechtenniveau van operators (1-4).',
        ],
        'player-idle-timeout' => [
            'label' => 'AFK-kick (minuten)',
            'description' => 'Kickt inactieve spelers na zoveel minuten (0 = nooit).',
        ],
        'resource-pack' => [
            'label' => 'URL van de resourcepack',
            'description' => 'Directe downloadlink naar een resourcepack (zip).',
        ],
        'require-resource-pack' => [
            'label' => 'Resourcepack verplichten',
            'description' => 'Spelers die de pack weigeren worden losgekoppeld.',
        ],
    ],
];
