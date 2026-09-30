<?php

return [
    'title' => 'Database',
    'empty' => 'Sembra che tu non abbia database.',
    'disabled' => 'Non è possibile creare database per questo server.',
    'allocated' => 'Sono stati assegnati :used database su :limit a questo server.',
    'new_database_button' => 'Nuovo database',
    'rotate_password_button' => 'Ruota password',
    'labels' => [
        'endpoint' => 'Endpoint',
        'connections_from' => 'Connessioni da',
        'username' => 'Nome utente',
    ],
    'create' => [
        'heading' => 'Crea nuovo database',
        'name_label' => 'Nome del database',
        'name_description' => 'Un nome descrittivo per la tua istanza di database.',
        'connections_from_label' => 'Connessioni da',
        'connections_from_description' => 'Da dove devono essere consentite le connessioni. Lascia vuoto per consentire connessioni da qualsiasi luogo.',
        'cancel' => 'Annulla',
        'create_button' => 'Crea database',
        'validation' => [
            'name_required' => 'Devi indicare un nome per il database.',
            'name_min' => 'Il nome del database deve contenere almeno 3 caratteri.',
            'name_max' => 'Il nome del database non deve superare i 48 caratteri.',
            'name_format' => 'Il nome del database deve contenere solo caratteri alfanumerici, trattini bassi, trattini e/o punti.',
            'connections_from_format' => 'Devi indicare un indirizzo host valido.',
        ],
    ],
    'delete' => [
        'heading' => 'Conferma eliminazione del database',
        'body_prefix' => 'Eliminare un database è un\'azione permanente e non può essere annullata. Questo eliminerà definitivamente il database',
        'body_suffix' => ' e rimuoverà tutti i dati associati.',
        'confirm_label' => 'Conferma il nome del database',
        'confirm_description' => 'Inserisci il nome del database per confermare l\'eliminazione.',
        'confirm_required' => 'Devi indicare il nome del database.',
        'cancel' => 'Annulla',
        'delete_button' => 'Elimina database',
    ],
    'connection' => [
        'heading' => 'Dettagli di connessione al database',
        'jdbc_label' => 'Stringa di connessione JDBC',
        'password_label' => 'Password',
        'close_button' => 'Chiudi',
    ],
];
