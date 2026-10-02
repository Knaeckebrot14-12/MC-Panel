<?php

return [
    'title' => 'Server-Einstellungen',
    'missing' => 'Dieser Server hat noch keine server.properties. Starte ihn einmal oder speichere hier, um die Datei anzulegen.',
    'saved' => 'Einstellungen gespeichert. Sie gelten beim nächsten Start.',
    'saved_restart' => 'Einstellungen gespeichert. Starte den Server neu, damit sie gelten.',
    'unsaved' => ':count ungespeicherte Änderungen',
    'reset' => 'Verwerfen',
    'save' => 'Speichern',
    'locked' => 'Port, IP und RCON verwaltet das Panel, sie können hier nicht geändert werden.',
    'groups' => [
        'general' => 'Allgemein',
        'world' => 'Welt',
        'access' => 'Zugang & Sicherheit',
        'resource_pack' => 'Ressourcenpaket',
        'other' => 'Weitere Einstellungen',
    ],
    'options' => [
        'survival' => 'Überleben',
        'creative' => 'Kreativ',
        'adventure' => 'Abenteuer',
        'spectator' => 'Zuschauer',
        'peaceful' => 'Friedlich',
        'easy' => 'Einfach',
        'normal' => 'Normal',
        'hard' => 'Schwer',
        'flat' => 'Flachland',
        'large_biomes' => 'Große Biome',
        'amplified' => 'Zerklüftet',
    ],
    'server_list' => [
        'title' => 'Serverliste',
        'line1' => 'Erste Zeile',
        'line2' => 'Zweite Zeile (optional)',
        'codes_hint' => 'Farb- und Formatcodes beginnen mit & (z. B. &a grün, &l fett, &r zurücksetzen). Klick auf eine Farbe fügt sie am Cursor ein. Die Vorschau zeigt, wie Spieler den Server sehen.',
        'icon_title' => 'Server-Icon',
        'icon_upload' => 'Icon hochladen',
        'icon_remove' => 'Icon entfernen',
        'icon_hint' => 'PNG, JPG, GIF oder WebP. Wird automatisch quadratisch zugeschnitten und auf 64×64 verkleinert. Wirkt nach einem Neustart.',
        'icon_saved' => 'Server-Icon gespeichert. Es erscheint nach dem nächsten Neustart.',
        'icon_invalid' => 'Diese Datei ist kein lesbares Bild.',
        'no_icon' => 'Kein Icon',
        'format' => [
            'l' => 'Fett',
            'o' => 'Kursiv',
            'n' => 'Unterstrichen',
            'm' => 'Durchgestrichen',
            'k' => 'Magisch',
            'r' => 'Zurücksetzen',
        ],
    ],
    'fields' => [
        'motd' => [
            'label' => 'Serverbeschreibung (MOTD)',
            'description' => 'Wird in der Mehrspieler-Serverliste angezeigt.',
        ],
        'max-players' => [
            'label' => 'Maximale Spieler',
            'description' => 'Wie viele Spieler gleichzeitig online sein können.',
        ],
        'gamemode' => [
            'label' => 'Spielmodus',
            'description' => 'Spielmodus für neue Spieler.',
        ],
        'difficulty' => [
            'label' => 'Schwierigkeit',
            'description' => 'Wie gefährlich Monster und Hunger sind.',
        ],
        'hardcore' => [
            'label' => 'Hardcore',
            'description' => 'Spieler werden nach dem ersten Tod gebannt.',
        ],
        'pvp' => [
            'label' => 'PvP',
            'description' => 'Spieler können sich gegenseitig Schaden zufügen.',
        ],
        'force-gamemode' => [
            'label' => 'Spielmodus erzwingen',
            'description' => 'Spieler treten immer im Standard-Spielmodus bei.',
        ],
        'allow-flight' => [
            'label' => 'Fliegen erlauben',
            'description' => 'Brauchen manche Plugins und Mods, sonst werden fliegende Spieler gekickt.',
        ],
        'level-name' => [
            'label' => 'Weltordner',
            'description' => 'Name des Weltordners, der geladen oder erstellt wird.',
        ],
        'level-seed' => [
            'label' => 'Seed',
            'description' => 'Seed für neue Welten, leer bedeutet zufällig.',
        ],
        'level-type' => [
            'label' => 'Welttyp',
            'description' => 'Wird nur beim Erzeugen einer neuen Welt verwendet.',
        ],
        'allow-nether' => [
            'label' => 'Nether',
            'description' => 'Spieler können in den Nether reisen.',
        ],
        'generate-structures' => [
            'label' => 'Bauwerke',
            'description' => 'Dörfer, Tempel und andere Bauwerke werden erzeugt.',
        ],
        'spawn-monsters' => [
            'label' => 'Monster',
            'description' => 'Feindliche Kreaturen spawnen.',
        ],
        'spawn-npcs' => [
            'label' => 'Dorfbewohner',
            'description' => 'Dorfbewohner spawnen.',
        ],
        'spawn-protection' => [
            'label' => 'Spawn-Schutz',
            'description' => 'Radius um den Spawn, in dem nur Operatoren bauen dürfen (0 = aus).',
        ],
        'view-distance' => [
            'label' => 'Sichtweite',
            'description' => 'An Spieler gesendete Chunks. Kleinere Werte sparen RAM und CPU.',
        ],
        'simulation-distance' => [
            'label' => 'Simulationsdistanz',
            'description' => 'Chunks um Spieler, in denen sich etwas bewegt und wächst.',
        ],
        'max-world-size' => [
            'label' => 'Weltgrenze',
            'description' => 'Maximaler Radius der Welt in Blöcken.',
        ],
        'white-list' => [
            'label' => 'Whitelist',
            'description' => 'Nur Spieler auf der Whitelist können beitreten.',
        ],
        'enforce-whitelist' => [
            'label' => 'Whitelist durchsetzen',
            'description' => 'Kickt Online-Spieler, die von der Whitelist entfernt werden.',
        ],
        'online-mode' => [
            'label' => 'Online-Modus',
            'description' => 'Prüft Accounts bei Mojang. Nur hinter einem Proxy wie Velocity oder BungeeCord ausschalten.',
        ],
        'enforce-secure-profile' => [
            'label' => 'Sicheres Chat-Profil',
            'description' => 'Spieler brauchen von Mojang signierte Chat-Schlüssel.',
        ],
        'enable-command-block' => [
            'label' => 'Befehlsblöcke',
            'description' => 'Befehlsblöcke können benutzt werden.',
        ],
        'op-permission-level' => [
            'label' => 'Operator-Stufe',
            'description' => 'Berechtigungsstufe der Operatoren (1-4).',
        ],
        'player-idle-timeout' => [
            'label' => 'AFK-Kick (Minuten)',
            'description' => 'Kickt untätige Spieler nach so vielen Minuten (0 = nie).',
        ],
        'resource-pack' => [
            'label' => 'Ressourcenpaket-URL',
            'description' => 'Direkter Download-Link zu einem Ressourcenpaket (zip).',
        ],
        'require-resource-pack' => [
            'label' => 'Ressourcenpaket erzwingen',
            'description' => 'Spieler, die das Paket ablehnen, werden getrennt.',
        ],
    ],
];
