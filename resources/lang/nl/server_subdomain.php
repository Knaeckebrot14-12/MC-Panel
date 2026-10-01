<?php

return [
    'title' => 'Subdomein',
    'current' => 'Spelers kunnen joinen met:',
    'name_placeholder' => 'mijnserver',
    'change' => 'Wijzigen',
    'create' => 'Aanmaken',
    'remove' => 'Verwijderen',
    'hint' => '3–32 tekens: kleine letters, cijfers en streepjes. Het kan een paar minuten duren voordat het adres overal werkt.',
    'saved' => ':fqdn verwijst nu naar deze server.',
    'errors' => [
        'disabled' => 'Subdomeinen zijn niet beschikbaar.',
        'invalid_name' => 'Deze naam is niet toegestaan. Gebruik 3–32 kleine letters, cijfers of streepjes.',
        'invalid_domain' => 'Dit domein is niet beschikbaar.',
        'taken' => 'Dit subdomein is al in gebruik.',
        'no_ip' => 'Het openbare adres van deze server kon niet worden bepaald.',
        'cloudflare' => 'Het DNS-record kon niet worden aangemaakt: :error',
        'no_zone' => 'Het domein :domain is niet goed ingesteld. Neem contact op met support.',
    ],
];
