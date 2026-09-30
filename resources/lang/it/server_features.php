<?php

return [
    'gsl_token' => [
        'title' => 'Token GSL non valido!',
        'body_1' => 'Sembra che il tuo Gameserver Login Token (token GSL) non sia valido o sia scaduto.',
        'body_2' => 'Puoi generarne uno nuovo e inserirlo qui sotto, oppure lasciare il campo vuoto per rimuoverlo del tutto.',
        'field_label' => 'Token GSL',
        'field_description' => 'Visita https://steamcommunity.com/dev/managegameservers per generare un token.',
        'update_button' => 'Aggiorna token GSL',
    ],
    'hytale_oauth' => [
        'title' => 'Autenticazione richiesta',
        'body' => 'Devi autenticarti con il tuo account Hytale per scaricare o aggiornare i file del server. Accedi per continuare.',
        'cancel_button' => 'Annulla',
        'login_button' => 'Accedi',
    ],
    'pid_limit' => [
        'admin_title' => 'Limite di memoria o processi raggiunto...',
        'admin_body_1' => 'Questo server ha raggiunto il limite massimo di processi o di memoria.',
        'admin_body_2' => 'Aumentare <0>container_pid_limit</0> nella configurazione di wings, <1>config.yml</1>, potrebbe aiutare a risolvere il problema.',
        'admin_note' => 'Nota: Wings deve essere riavviato affinché le modifiche al file di configurazione abbiano effetto',
        'user_title' => 'Possibile limite di risorse raggiunto...',
        'user_body' => 'Questo server sta tentando di usare più risorse di quelle assegnate. Contatta l\'amministratore e fornisci l\'errore qui sotto.',
        'close_button' => 'Chiudi',
    ],
    'steam_disk_space' => [
        'title' => 'Spazio su disco esaurito...',
        'admin_body_1' => 'Questo server ha esaurito lo spazio su disco disponibile e non può completare l\'installazione o l\'aggiornamento.',
        'admin_body_2' => 'Verifica che la macchina abbia spazio su disco sufficiente digitando <0>df -h</0> sulla macchina che ospita questo server. Elimina file o aumenta lo spazio disponibile per risolvere il problema.',
        'user_body' => 'Questo server ha esaurito lo spazio su disco disponibile e non può completare l\'installazione o l\'aggiornamento. Contatta gli amministratori e segnala il problema di spazio su disco.',
        'close_button' => 'Chiudi',
    ],
    'eula' => [
        'title' => 'Accetta l\'EULA di Minecraft®',
        'body_prefix' => 'Premendo "Accetto" qui sotto indichi di accettare l\'',
        'body_suffix' => '.',
        'link_text' => 'EULA di Minecraft®',
        'cancel_button' => 'Annulla',
        'accept_button' => 'Accetto',
    ],
    'java_version' => [
        'title' => 'Versione di Java non supportata',
        'body' => 'Questo server sta usando una versione di Java non supportata e non può essere avviato.',
        'body_select_notice' => ' Seleziona una versione supportata dall\'elenco qui sotto per continuare ad avviare il server.',
        'cancel_button' => 'Annulla',
        'update_button' => 'Aggiorna immagine Docker',
    ],
];
