<?php

return [
    'title' => 'Rollen',
    'subheading' => 'Was Supporter, Moderatoren und Admins dürfen.',
    'matrix_heading' => 'Rechte pro Rolle',
    'permission' => 'Recht',
    'members' => ':count Mitglied(er)',
    'save' => 'Speichern',
    'saved' => 'Rollenrechte gespeichert.',
    'reset' => 'Auf Standard zurücksetzen',
    'reset_confirm' => 'Alle Rollenrechte auf den Standard zurücksetzen?',
    'hint' => 'Änderungen gelten sofort für alle Mitglieder der Rolle. Der Owner hat immer alle Rechte.',
    'owner_only_text' => 'Panel-Einstellungen (inklusive Updates, Design, Login, Monitoring, Subdomains und dieser Seite) und die Anwendungs-API bleiben dem Owner vorbehalten – sonst könnte sich eine Rolle selbst alle Rechte geben.',
    'groups' => [
        'general' => 'Allgemein',
        'users' => 'Benutzer',
        'servers' => 'Server',
        'infrastructure' => 'Infrastruktur',
        'coins' => 'Coins & Shop',
        'security' => 'Sicherheit',
        'owner_only' => 'Nur Owner',
    ],
    'permissions' => [
        'overview' => [
            'name' => 'Übersicht',
            'description' => 'Admin-Dashboard mit Statistiken.',
        ],
        'audit' => [
            'name' => 'Audit-Log',
            'description' => 'Sehen, was das Team gemacht hat.',
        ],
        'maintenance' => [
            'name' => 'Wartungsmodus',
            'description' => 'Banner oder Sperre ein- und ausschalten.',
        ],
        'announcements' => [
            'name' => 'Ankündigungen',
            'description' => 'Ankündigungen erstellen, ausblenden und löschen.',
        ],
        'tickets' => [
            'name' => 'Tickets',
            'description' => 'Support-Tickets beantworten und verwalten.',
        ],
        'users_view' => [
            'name' => 'Benutzer ansehen',
            'description' => 'Benutzerliste und Benutzerseiten.',
        ],
        'users_email' => [
            'name' => 'E-Mail-Adressen und IPs sehen',
            'description' => 'Ohne dieses Recht sind E-Mail-Adressen und Registrierungs-IPs anderer Benutzer ausgeblendet und nicht durchsuchbar.',
        ],
        'users_edit' => [
            'name' => 'Benutzer bearbeiten und erstellen',
            'description' => 'Name, Benutzername, E-Mail, Sprache und Ressourcen-Limits.',
        ],
        'users_password' => [
            'name' => 'Passwörter ändern',
            'description' => 'Anderen Benutzern ein neues Passwort setzen.',
        ],
        'users_moderate' => [
            'name' => 'Benutzer moderieren',
            'description' => 'Sperren, entsperren und E-Mail-Adressen bestätigen.',
        ],
        'users_coins' => [
            'name' => 'Coins geben und nehmen',
            'description' => 'Den Coin-Stand von Benutzern ändern.',
        ],
        'users_roles' => [
            'name' => 'Rollen vergeben',
            'description' => 'Benutzern eine Rolle unterhalb der eigenen geben.',
        ],
        'users_delete' => [
            'name' => 'Benutzer löschen',
            'description' => 'Konten ohne Server löschen.',
        ],
        'users_impersonate' => [
            'name' => 'Als Nutzer anmelden (Support-Ansicht)',
            'description' => 'Das Panel genau so sehen wie ein normaler Nutzer, um bei Problemen zu helfen. Dabei ist auch seine E-Mail-Adresse sichtbar. Kontoeinstellungen, Coins und Tickets lassen sich währenddessen nicht ändern.',
        ],
        'security_ipblock' => [
            'name' => 'Gesperrte IPs',
            'description' => 'IP-Adressen sehen, die wegen zu vieler fehlgeschlagener Logins gesperrt wurden, sie entsperren oder eine IP von Hand sperren. Die Einstellungen der Sperre bleiben beim Owner.',
        ],
        'servers_view' => [
            'name' => 'Server ansehen',
            'description' => 'Serverliste und Serverseiten.',
        ],
        'servers_moderate' => [
            'name' => 'Server sperren',
            'description' => 'Server sperren und entsperren.',
        ],
        'servers_manage' => [
            'name' => 'Server verwalten',
            'description' => 'Details, Ressourcen, Start, Datenbanken, Mounts, Neuinstallation und Umzug.',
        ],
        'servers_create' => [
            'name' => 'Server erstellen',
            'description' => 'Server für beliebige Benutzer erstellen.',
        ],
        'servers_delete' => [
            'name' => 'Server löschen',
            'description' => 'Server löschen.',
        ],
        'servers_bulk' => [
            'name' => 'Sammelaktionen für Server',
            'description' => 'Alle Server einer Node auf einmal starten, stoppen, neu starten oder beenden und eine Nachricht an die Konsole aller laufenden Minecraft-Server senden.',
        ],
        'nodes' => [
            'name' => 'Nodes',
            'description' => 'Nodes, Zuweisungen, Monitoring und Wings-Updates.',
        ],
        'locations' => [
            'name' => 'Standorte',
            'description' => 'Standorte erstellen und bearbeiten.',
        ],
        'databases' => [
            'name' => 'Datenbank-Hosts',
            'description' => 'Datenbankserver für Gameserver.',
        ],
        'mounts' => [
            'name' => 'Mounts',
            'description' => 'Geteilte Ordner für Server.',
        ],
        'nests' => [
            'name' => 'Nests & Eggs',
            'description' => 'Server-Arten und ihre Start-Einstellungen.',
        ],
        'coins_vouchers' => [
            'name' => 'Gutscheine',
            'description' => 'Coin-Gutscheine erstellen und verwalten.',
        ],
        'coins_plans' => [
            'name' => 'Server-Pakete',
            'description' => 'Pakete, die im Coin-Shop verkauft werden.',
        ],
        'coins_settings' => [
            'name' => 'Coin-Einstellungen',
            'description' => 'Belohnungen, Preise und Shop-Einstellungen.',
        ],
    ],
];
