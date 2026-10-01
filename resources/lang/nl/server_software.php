<?php

return [
    'title' => 'Versie',
    'unsupported' => 'Bij dit type server kan de Minecraft-versie hier niet worden gewijzigd.',
    'current_title' => 'Nu geïnstalleerd',
    'current_unknown' => 'Nog niet via deze pagina geïnstalleerd (de versie uit de serverinstelling draait).',
    'install_title' => ':name installeren',
    'no_versions' => 'Er zijn op dit moment geen versies beschikbaar.',
    'version_label' => 'Versie',
    'install_button' => 'Installeren',
    'java_hint' => 'Vereist Java :java; de juiste Java-image wordt automatisch gekozen.',
    'installing' => 'Bezig met installeren… de server wordt gestopt en de nieuwe versie gedownload. Dit kan een minuut duren.',
    'confirm' => ':name :version installeren? De server wordt gestopt en zijn server-jar vervangen. Werelden, plugins en instellingen blijven behouden.',
    'confirm_downgrade' => 'Dit is een oudere versie dan de geïnstalleerde. Minecraft-werelden kunnen meestal niet met oudere versies worden geopend en kunnen beschadigd raken. Maak eerst een back-up! Toch :name :version installeren?',
    'confirm_yes' => 'Ja, installeren',
    'confirm_no' => 'Annuleren',
    'backup_hint' => 'Tip: maak een back-up voordat je naar andere software of een andere versie overstapt.',
    'installed' => ':name :version is geïnstalleerd (Java :java). Start de server om het te gebruiken.',
    'types' => [
        'paper' => [
            'name' => 'Paper',
            'description' => 'Snel, met plugins (Bukkit/Spigot)',
        ],
        'purpur' => [
            'name' => 'Purpur',
            'description' => 'Paper met veel extra opties',
        ],
        'folia' => [
            'name' => 'Folia',
            'description' => 'Paper voor zeer grote servers (multithreaded)',
        ],
        'fabric' => [
            'name' => 'Fabric',
            'description' => 'Lichte modloader',
        ],
        'vanilla' => [
            'name' => 'Vanilla',
            'description' => 'De originele server van Mojang',
        ],
        'velocity' => [
            'name' => 'Velocity',
            'description' => 'Proxy die meerdere servers verbindt',
        ],
    ],
    'errors' => [
        'unsupported' => 'Deze server kan niet van versie wisselen (de start gebruikt geen server-jar).',
        'unknown_version' => 'Deze versie is niet beschikbaar.',
        'unknown_type' => 'Onbekende serversoftware.',
        'download' => 'De nieuwe versie kon niet worden gedownload. Probeer het later opnieuw.',
        'no_build' => 'Voor deze versie is nog geen download beschikbaar.',
        'still_running' => 'De server kon niet worden gestopt. Stop hem en probeer het opnieuw.',
        'api' => 'De versielijst kon niet worden geladen. Probeer het later opnieuw.',
        'busy' => 'Op deze server wordt al een versie geïnstalleerd.',
    ],
];
