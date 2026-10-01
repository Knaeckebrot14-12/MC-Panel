<?php

return [
    'title' => 'Versione',
    'unsupported' => 'Questo tipo di server non può cambiare qui la versione di Minecraft.',
    'current_title' => 'Installato ora',
    'current_unknown' => 'Non ancora installato da questa pagina (è in uso la versione della configurazione del server).',
    'install_title' => 'Installa :name',
    'no_versions' => 'Al momento non ci sono versioni disponibili.',
    'version_label' => 'Versione',
    'install_button' => 'Installa',
    'java_hint' => 'Richiede Java :java; l\'immagine Java adatta viene scelta automaticamente.',
    'installing' => 'Installazione… il server viene fermato e la nuova versione scaricata. Può richiedere un minuto.',
    'confirm' => 'Installare :name :version? Il server verrà fermato e il suo jar sostituito. Mondi, plugin e impostazioni restano.',
    'confirm_downgrade' => 'Questa versione è più vecchia di quella installata. I mondi di Minecraft di solito non si possono aprire con versioni più vecchie e possono danneggiarsi. Fai prima un backup! Installare comunque :name :version?',
    'confirm_yes' => 'Sì, installa',
    'confirm_no' => 'Annulla',
    'backup_hint' => 'Suggerimento: crea un backup prima di passare a un altro software o versione.',
    'installed' => ':name :version è stato installato (Java :java). Avvia il server per usarlo.',
    'types' => [
        'paper' => [
            'name' => 'Paper',
            'description' => 'Veloce, con plugin (Bukkit/Spigot)',
        ],
        'purpur' => [
            'name' => 'Purpur',
            'description' => 'Paper con molte opzioni extra',
        ],
        'folia' => [
            'name' => 'Folia',
            'description' => 'Paper per server molto grandi (multithread)',
        ],
        'fabric' => [
            'name' => 'Fabric',
            'description' => 'Mod loader leggero',
        ],
        'vanilla' => [
            'name' => 'Vanilla',
            'description' => 'Il server originale di Mojang',
        ],
        'velocity' => [
            'name' => 'Velocity',
            'description' => 'Proxy che collega più server',
        ],
    ],
    'errors' => [
        'unsupported' => 'Questo server non può cambiare versione (il suo avvio non usa un jar).',
        'unknown_version' => 'Questa versione non è disponibile.',
        'unknown_type' => 'Software server sconosciuto.',
        'download' => 'Non è stato possibile scaricare la nuova versione. Riprova più tardi.',
        'no_build' => 'Per questa versione non c\'è ancora un download.',
        'still_running' => 'Non è stato possibile fermare il server. Fermalo e riprova.',
        'api' => 'Non è stato possibile caricare l\'elenco delle versioni. Riprova più tardi.',
        'busy' => 'Su questo server si sta già installando una versione.',
    ],
];
