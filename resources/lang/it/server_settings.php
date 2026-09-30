<?php

return [
    'title' => 'Impostazioni',
    'sftp' => [
        'heading' => 'Dettagli SFTP',
        'server_address_label' => 'Indirizzo del server',
        'username_label' => 'Nome utente',
        'password_notice' => 'La tua password SFTP è la stessa che usi per accedere a questo pannello.',
        'launch_button' => 'Avvia SFTP',
    ],
    'debug' => [
        'heading' => 'Informazioni di debug',
        'node_label' => 'Nodo',
        'server_id_label' => 'ID del server',
    ],
    'rename' => [
        'heading' => 'Modifica dettagli del server',
        'name_label' => 'Nome del server',
        'description_label' => 'Descrizione del server',
        'save_button' => 'Salva',
    ],
    'reinstall' => [
        'heading' => 'Reinstalla server',
        'disabled_notice' => 'La reinstallazione di questo server è stata disattivata perché è configurato per saltare lo script di installazione del suo egg. Se vuoi reinstallarlo, contatta un amministratore del server.',
        'body' => 'Reinstallare il tuo server lo arresterà e poi eseguirà di nuovo lo script di installazione che lo ha configurato inizialmente.',
        'body_warning' => 'Alcuni file potrebbero essere eliminati o modificati durante questo processo: fai un backup dei tuoi dati prima di continuare.',
        'reinstall_button' => 'Reinstalla server',
        'confirm_title' => 'Conferma reinstallazione del server',
        'confirm_button' => 'Sì, reinstalla il server',
        'confirm_body' => 'Il tuo server verrà arrestato e alcuni file potrebbero essere eliminati o modificati durante questo processo: sei sicuro di voler continuare?',
        'success_message' => 'Il tuo server ha avviato il processo di reinstallazione.',
    ],
];
