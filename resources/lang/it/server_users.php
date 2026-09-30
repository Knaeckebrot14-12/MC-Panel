<?php

return [
    'title' => 'Utenti',
    'empty' => 'Sembra che tu non abbia sotto-utenti.',
    'new_user_button' => 'Nuovo utente',
    'row' => [
        'two_factor_label' => '2FA attiva',
        'permissions_label' => 'Permessi',
        'edit_aria' => 'Modifica sotto-utente',
    ],
    'edit_modal' => [
        'title_modify' => 'Modifica i permessi di :email',
        'title_view' => 'Visualizza i permessi di :email',
        'title_create' => 'Crea nuovo sotto-utente',
        'save_button' => 'Salva',
        'invite_button' => 'Invita utente',
        'permission_notice' => 'Quando crei o modifichi altri utenti puoi selezionare solo i permessi attualmente assegnati al tuo account.',
        'email_label' => 'Email dell\'utente',
        'email_description' => 'Inserisci l\'indirizzo email dell\'utente che vuoi invitare come sotto-utente di questo server.',
        'validation' => [
            'email_max' => 'Gli indirizzi email non devono superare i 191 caratteri.',
            'email_invalid' => 'Devi indicare un indirizzo email valido.',
        ],
    ],
    'remove' => [
        'title' => 'Eliminare questo sotto-utente?',
        'confirm_button' => 'Sì, rimuovi il sotto-utente',
        'body' => 'Sei sicuro di voler rimuovere questo sotto-utente? Tutti i suoi accessi a questo server verranno revocati immediatamente.',
        'delete_aria' => 'Elimina sotto-utente',
    ],
];
