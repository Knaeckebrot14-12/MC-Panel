<?php

return [
    'title' => 'Impostazioni server',
    'missing' => 'Questo server non ha ancora un file server.properties. Avvialo una volta o salva qui per creare il file.',
    'saved' => 'Impostazioni salvate. Si applicano al prossimo avvio.',
    'saved_restart' => 'Impostazioni salvate. Riavvia il server per applicarle.',
    'unsaved' => ':count modifiche non salvate',
    'reset' => 'Annulla',
    'save' => 'Salva',
    'locked' => 'Porta, IP e RCON sono gestiti dal pannello e non possono essere modificati qui.',
    'groups' => [
        'general' => 'Generale',
        'world' => 'Mondo',
        'access' => 'Accesso e sicurezza',
        'resource_pack' => 'Resource pack',
        'other' => 'Altre impostazioni',
    ],
    'options' => [
        'survival' => 'Sopravvivenza',
        'creative' => 'Creativa',
        'adventure' => 'Avventura',
        'spectator' => 'Spettatore',
        'peaceful' => 'Pacifica',
        'easy' => 'Facile',
        'normal' => 'Normale',
        'hard' => 'Difficile',
        'flat' => 'Superpiatto',
        'large_biomes' => 'Biomi giganti',
        'amplified' => 'Amplificato',
    ],
    'server_list' => [
        'title' => 'Lista server',
        'line1' => 'Prima riga',
        'line2' => 'Seconda riga (facoltativa)',
        'codes_hint' => 'I codici di colore e formato iniziano con & (es. &a verde, &l grassetto, &r reimposta). Clicca un colore per inserirlo al cursore. L\'anteprima mostra come i giocatori vedono il server.',
        'icon_title' => 'Icona del server',
        'icon_upload' => 'Carica icona',
        'icon_remove' => 'Rimuovi icona',
        'icon_hint' => 'PNG, JPG, GIF o WebP. Viene ritagliata a quadrato e ridimensionata a 64×64 automaticamente. Ha effetto dopo un riavvio.',
        'icon_saved' => 'Icona del server salvata. Compare dopo il prossimo riavvio.',
        'icon_invalid' => 'Questo file non è un\'immagine leggibile.',
        'no_icon' => 'Nessuna icona',
        'format' => [
            'l' => 'Grassetto',
            'o' => 'Corsivo',
            'n' => 'Sottolineato',
            'm' => 'Barrato',
            'k' => 'Magico',
            'r' => 'Reimposta',
        ],
    ],
    'fields' => [
        'motd' => [
            'label' => 'Descrizione del server (MOTD)',
            'description' => 'Mostrata nella lista server del multiplayer.',
        ],
        'max-players' => [
            'label' => 'Giocatori massimi',
            'description' => 'Quanti giocatori possono essere online contemporaneamente.',
        ],
        'gamemode' => [
            'label' => 'Modalità di gioco',
            'description' => 'Modalità di gioco per i nuovi giocatori.',
        ],
        'difficulty' => [
            'label' => 'Difficoltà',
            'description' => 'Quanto sono pericolosi i mob e la fame.',
        ],
        'hardcore' => [
            'label' => 'Hardcore',
            'description' => 'I giocatori vengono bannati dopo essere morti una volta.',
        ],
        'pvp' => [
            'label' => 'PvP',
            'description' => 'I giocatori possono danneggiarsi a vicenda.',
        ],
        'force-gamemode' => [
            'label' => 'Forza modalità di gioco',
            'description' => 'I giocatori entrano sempre nella modalità di gioco predefinita.',
        ],
        'allow-flight' => [
            'label' => 'Consenti il volo',
            'description' => 'Necessario per alcuni plugin e mod; altrimenti i giocatori che volano vengono espulsi.',
        ],
        'level-name' => [
            'label' => 'Cartella del mondo',
            'description' => 'Nome della cartella del mondo da caricare o creare.',
        ],
        'level-seed' => [
            'label' => 'Seed',
            'description' => 'Seed per i nuovi mondi; vuoto significa casuale.',
        ],
        'level-type' => [
            'label' => 'Tipo di mondo',
            'description' => 'Usato solo quando viene generato un nuovo mondo.',
        ],
        'allow-nether' => [
            'label' => 'Nether',
            'description' => 'I giocatori possono viaggiare nel Nether.',
        ],
        'generate-structures' => [
            'label' => 'Strutture',
            'description' => 'Vengono generati villaggi, templi e altre strutture.',
        ],
        'spawn-monsters' => [
            'label' => 'Mostri',
            'description' => 'Compaiono mob ostili.',
        ],
        'spawn-npcs' => [
            'label' => 'Abitanti',
            'description' => 'Compaiono gli abitanti dei villaggi.',
        ],
        'spawn-protection' => [
            'label' => 'Protezione dello spawn',
            'description' => 'Raggio attorno allo spawn in cui solo gli operatori possono costruire (0 = disattivata).',
        ],
        'view-distance' => [
            'label' => 'Distanza di visualizzazione',
            'description' => 'Chunk inviati ai giocatori. Valori più bassi risparmiano memoria e CPU.',
        ],
        'simulation-distance' => [
            'label' => 'Distanza di simulazione',
            'description' => 'Chunk attorno ai giocatori in cui le cose si muovono e crescono.',
        ],
        'max-world-size' => [
            'label' => 'Bordo del mondo',
            'description' => 'Raggio massimo del mondo in blocchi.',
        ],
        'white-list' => [
            'label' => 'Whitelist',
            'description' => 'Possono entrare solo i giocatori nella whitelist.',
        ],
        'enforce-whitelist' => [
            'label' => 'Applica la whitelist',
            'description' => 'Espelle i giocatori online che vengono rimossi dalla whitelist.',
        ],
        'online-mode' => [
            'label' => 'Modalità online',
            'description' => 'Verifica gli account con Mojang. Disattivala solo dietro un proxy come Velocity o BungeeCord.',
        ],
        'enforce-secure-profile' => [
            'label' => 'Profilo chat sicuro',
            'description' => 'I giocatori hanno bisogno di chiavi chat firmate da Mojang.',
        ],
        'enable-command-block' => [
            'label' => 'Blocchi comando',
            'description' => 'I blocchi comando possono essere usati.',
        ],
        'op-permission-level' => [
            'label' => 'Livello operatore',
            'description' => 'Livello di permesso degli operatori (1-4).',
        ],
        'player-idle-timeout' => [
            'label' => 'Espulsione per AFK (minuti)',
            'description' => 'Espelle i giocatori inattivi dopo questo numero di minuti (0 = mai).',
        ],
        'resource-pack' => [
            'label' => 'URL del resource pack',
            'description' => 'Link di download diretto a un resource pack (zip).',
        ],
        'require-resource-pack' => [
            'label' => 'Richiedi il resource pack',
            'description' => 'I giocatori che rifiutano il pack vengono disconnessi.',
        ],
    ],
];
