<?php

return [
    'location' => [
        'no_location_found' => 'Impossibile trovare un record corrispondente al codice breve indicato.',
        'ask_short' => 'Codice breve della posizione',
        'ask_long' => 'Descrizione della posizione',
        'created' => 'Nuova posizione (:name) creata con successo con ID :id.',
        'deleted' => 'La posizione richiesta è stata eliminata con successo.',
    ],
    'user' => [
        'search_users' => 'Inserisci un nome utente, un ID utente o un indirizzo e-mail',
        'select_search_user' => 'ID dell\'utente da eliminare (inserisci \'0\' per cercare di nuovo)',
        'deleted' => 'Utente eliminato con successo dal pannello.',
        'confirm_delete' => 'Sei sicuro di voler eliminare questo utente dal pannello?',
        'no_users_found' => 'Nessun utente trovato per il termine di ricerca indicato.',
        'multiple_found' => 'Sono stati trovati più account per l\'utente indicato; impossibile eliminare un utente a causa del flag --no-interaction.',
        'ask_admin' => 'Questo utente è un amministratore?',
        'ask_email' => 'Indirizzo e-mail',
        'ask_username' => 'Nome utente',
        'ask_name_first' => 'Nome',
        'ask_name_last' => 'Cognome',
        'ask_password' => 'Password',
        'ask_password_tip' => 'Se vuoi creare un account con una password casuale inviata all\'utente via e-mail, riesegui questo comando (CTRL+C) e passa il flag `--no-password`.',
        'ask_password_help' => 'Le password devono contenere almeno 8 caratteri, almeno una lettera maiuscola e un numero.',
        '2fa_help_text' => [
            0 => 'Questo comando disattiva l\'autenticazione a due fattori dell\'account di un utente, se attiva. Va usato solo come comando di recupero dell\'account se l\'utente non riesce più ad accedervi.',
            1 => 'Se non era ciò che volevi fare, premi CTRL+C per uscire da questo processo.',
        ],
        '2fa_disabled' => 'L\'autenticazione a due fattori è stata disattivata per :email.',
    ],
    'schedule' => [
        'output_line' => 'Invio del job per la prima attività in `:schedule` (:hash).',
    ],
    'maintenance' => [
        'deleting_service_backup' => 'Eliminazione del file di backup del servizio :file.',
    ],
    'server' => [
        'rebuild_failed' => 'La richiesta di ricostruzione per ":name" (#:id) sul nodo ":node" non è riuscita con l\'errore: :message',
        'reinstall' => [
            'failed' => 'La richiesta di reinstallazione per ":name" (#:id) sul nodo ":node" non è riuscita con l\'errore: :message',
            'confirm' => 'Stai per reinstallare un gruppo di server. Vuoi continuare?',
        ],
        'power' => [
            'confirm' => 'Stai per eseguire l\'azione :action su :count server. Vuoi continuare?',
            'action_failed' => 'La richiesta di alimentazione per ":name" (#:id) sul nodo ":node" non è riuscita con l\'errore: :message',
        ],
    ],
    'environment' => [
        'mail' => [
            'ask_smtp_host' => 'Host SMTP (ad es. smtp.gmail.com)',
            'ask_smtp_port' => 'Porta SMTP',
            'ask_smtp_username' => 'Nome utente SMTP',
            'ask_smtp_password' => 'Password SMTP',
            'ask_mailgun_domain' => 'Dominio Mailgun',
            'ask_mailgun_endpoint' => 'Endpoint Mailgun',
            'ask_mailgun_secret' => 'Segreto Mailgun',
            'ask_mandrill_secret' => 'Segreto Mandrill',
            'ask_postmark_username' => 'Chiave API Postmark',
            'ask_driver' => 'Quale driver deve essere usato per inviare le e-mail?',
            'ask_mail_from' => 'Indirizzo e-mail da cui devono provenire le e-mail',
            'ask_mail_name' => 'Nome con cui devono apparire le e-mail',
            'ask_encryption' => 'Metodo di crittografia da usare',
        ],
    ],
];
