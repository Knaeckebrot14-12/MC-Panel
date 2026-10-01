<?php

return [
    'title' => 'Subdomain',
    'current' => 'Spieler können joinen mit:',
    'name_placeholder' => 'meinserver',
    'change' => 'Ändern',
    'create' => 'Erstellen',
    'remove' => 'Entfernen',
    'hint' => '3–32 Zeichen: Kleinbuchstaben, Zahlen und Bindestriche. Es kann ein paar Minuten dauern, bis die Adresse überall funktioniert.',
    'saved' => ':fqdn zeigt jetzt auf diesen Server.',
    'errors' => [
        'disabled' => 'Subdomains sind nicht verfügbar.',
        'invalid_name' => 'Dieser Name ist nicht erlaubt. Nutze 3–32 Kleinbuchstaben, Zahlen oder Bindestriche.',
        'invalid_domain' => 'Diese Domain ist nicht verfügbar.',
        'taken' => 'Diese Subdomain ist schon vergeben.',
        'no_ip' => 'Die öffentliche Adresse dieses Servers konnte nicht ermittelt werden.',
        'cloudflare' => 'Der DNS-Eintrag konnte nicht erstellt werden: :error',
        'no_zone' => 'Die Domain :domain ist nicht richtig eingerichtet. Bitte wende dich an den Support.',
    ],
];
