<?php

return [
    'password' => 'Le password devono contenere almeno sei caratteri e corrispondere alla conferma.',
    'reset' => 'La tua password è stata reimpostata!',
    'sent' => 'Ti abbiamo inviato via email il link per reimpostare la password!',
    'token' => 'Questo token di reimpostazione della password non è valido.',
    'user' => 'Non riusciamo a trovare un utente con quell\'indirizzo email.',
    'generated_password_sent' => 'Se esiste un account con questa email o nome utente, gli abbiamo inviato un\'email con un link di conferma.',
    'confirm_mail' => [
        'subject' => 'Conferma il ripristino della password',
        'intro' => 'Qualcuno ha richiesto una nuova password per il tuo account (:username).',
        'button' => 'Inviami una nuova password',
        'expires' => 'Il link è valido per 60 minuti. Dopo il clic riceverai una nuova password via email.',
        'ignore' => 'Se non sei stato tu, ignora questa email; la tua password resta la stessa.',
    ],
    'confirm_page' => [
        'sent_title' => 'Nuova password inviata',
        'sent_text' => 'Ti abbiamo inviato una nuova password via email. Dopo l\'accesso sceglierai la tua password.',
        'invalid_title' => 'Link non più valido',
        'invalid_text' => 'Questo link è scaduto o è già stato usato. Richiedine uno nuovo nella pagina di accesso.',
        'login' => 'Vai all\'accesso',
    ],
];
