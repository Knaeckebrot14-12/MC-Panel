<?php

return [
    'location' => [
        'no_location_found' => 'Er kon geen record worden gevonden dat overeenkomt met de opgegeven korte code.',
        'ask_short' => 'Korte code van de locatie',
        'ask_long' => 'Beschrijving van de locatie',
        'created' => 'Nieuwe locatie (:name) succesvol aangemaakt met ID :id.',
        'deleted' => 'De gevraagde locatie is succesvol verwijderd.',
    ],
    'user' => [
        'search_users' => 'Voer een gebruikersnaam, gebruikers-ID of e-mailadres in',
        'select_search_user' => 'ID van de te verwijderen gebruiker (voer \'0\' in om opnieuw te zoeken)',
        'deleted' => 'Gebruiker succesvol uit het paneel verwijderd.',
        'confirm_delete' => 'Weet je zeker dat je deze gebruiker uit het paneel wilt verwijderen?',
        'no_users_found' => 'Er zijn geen gebruikers gevonden voor de opgegeven zoekterm.',
        'multiple_found' => 'Er zijn meerdere accounts gevonden voor de opgegeven gebruiker; een gebruiker kan niet worden verwijderd vanwege de vlag --no-interaction.',
        'ask_admin' => 'Is deze gebruiker een beheerder?',
        'ask_email' => 'E-mailadres',
        'ask_username' => 'Gebruikersnaam',
        'ask_name_first' => 'Voornaam',
        'ask_name_last' => 'Achternaam',
        'ask_password' => 'Wachtwoord',
        'ask_password_tip' => 'Als je een account wilt aanmaken met een willekeurig wachtwoord dat naar de gebruiker wordt gemaild, voer dit commando dan opnieuw uit (CTRL+C) en geef de vlag `--no-password` mee.',
        'ask_password_help' => 'Wachtwoorden moeten minstens 8 tekens lang zijn en minstens één hoofdletter en één cijfer bevatten.',
        '2fa_help_text' => [
            0 => 'Dit commando schakelt tweestapsverificatie voor het account van een gebruiker uit, als die is ingeschakeld. Gebruik het alleen als accountherstel wanneer de gebruiker niet meer kan inloggen.',
            1 => 'Als dit niet is wat je wilde doen, druk dan op CTRL+C om dit proces te stoppen.',
        ],
        '2fa_disabled' => 'Tweestapsverificatie is uitgeschakeld voor :email.',
    ],
    'schedule' => [
        'output_line' => 'Job voor de eerste taak in `:schedule` wordt verzonden (:hash).',
    ],
    'maintenance' => [
        'deleting_service_backup' => 'Back-upbestand van de service :file wordt verwijderd.',
    ],
    'server' => [
        'rebuild_failed' => 'Het herbouwverzoek voor ":name" (#:id) op node ":node" is mislukt met de fout: :message',
        'reinstall' => [
            'failed' => 'Het herinstallatieverzoek voor ":name" (#:id) op node ":node" is mislukt met de fout: :message',
            'confirm' => 'Je staat op het punt een groep servers opnieuw te installeren. Wil je doorgaan?',
        ],
        'power' => [
            'confirm' => 'Je staat op het punt de actie :action uit te voeren op :count servers. Wil je doorgaan?',
            'action_failed' => 'Het stroomverzoek voor ":name" (#:id) op node ":node" is mislukt met de fout: :message',
        ],
    ],
    'environment' => [
        'mail' => [
            'ask_smtp_host' => 'SMTP-host (bijv. smtp.gmail.com)',
            'ask_smtp_port' => 'SMTP-poort',
            'ask_smtp_username' => 'SMTP-gebruikersnaam',
            'ask_smtp_password' => 'SMTP-wachtwoord',
            'ask_mailgun_domain' => 'Mailgun-domein',
            'ask_mailgun_endpoint' => 'Mailgun-endpoint',
            'ask_mailgun_secret' => 'Mailgun-geheim',
            'ask_mandrill_secret' => 'Mandrill-geheim',
            'ask_postmark_username' => 'Postmark-API-sleutel',
            'ask_driver' => 'Welk stuurprogramma moet worden gebruikt voor het verzenden van e-mails?',
            'ask_mail_from' => 'E-mailadres waarvan e-mails afkomstig moeten zijn',
            'ask_mail_name' => 'Naam waaronder e-mails moeten verschijnen',
            'ask_encryption' => 'Te gebruiken versleutelingsmethode',
        ],
    ],
];
