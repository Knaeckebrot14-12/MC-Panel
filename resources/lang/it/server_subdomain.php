<?php

return [
    'title' => 'Sottodominio',
    'current' => 'I giocatori possono entrare con:',
    'name_placeholder' => 'mioserver',
    'change' => 'Cambia',
    'create' => 'Crea',
    'remove' => 'Rimuovi',
    'hint' => '3–32 caratteri: lettere minuscole, numeri e trattini. Può volerci qualche minuto prima che l\'indirizzo funzioni ovunque.',
    'saved' => ':fqdn ora punta a questo server.',
    'errors' => [
        'disabled' => 'I sottodomini non sono disponibili.',
        'invalid_name' => 'Questo nome non è consentito. Usa 3–32 lettere minuscole, numeri o trattini.',
        'invalid_domain' => 'Questo dominio non è disponibile.',
        'taken' => 'Questo sottodominio è già in uso.',
        'no_ip' => 'Non è stato possibile determinare l\'indirizzo pubblico di questo server.',
        'cloudflare' => 'Non è stato possibile creare il record DNS: :error',
        'no_zone' => 'Il dominio :domain non è configurato correttamente. Contatta il supporto.',
    ],
];
