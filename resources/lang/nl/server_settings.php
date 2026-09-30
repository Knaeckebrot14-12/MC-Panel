<?php

return [
    'title' => 'Instellingen',
    'sftp' => [
        'heading' => 'SFTP-gegevens',
        'server_address_label' => 'Serveradres',
        'username_label' => 'Gebruikersnaam',
        'password_notice' => 'Je SFTP-wachtwoord is hetzelfde als het wachtwoord dat je gebruikt om dit paneel te openen.',
        'launch_button' => 'SFTP starten',
    ],
    'debug' => [
        'heading' => 'Foutopsporingsinformatie',
        'node_label' => 'Node',
        'server_id_label' => 'Server-ID',
    ],
    'rename' => [
        'heading' => 'Serverdetails wijzigen',
        'name_label' => 'Servernaam',
        'description_label' => 'Serverbeschrijving',
        'save_button' => 'Opslaan',
    ],
    'reinstall' => [
        'heading' => 'Server opnieuw installeren',
        'disabled_notice' => 'Opnieuw installeren van deze server is uitgeschakeld omdat hij is geconfigureerd om het installatiescript van zijn egg over te slaan. Neem contact op met een serverbeheerder als je hem opnieuw wilt installeren.',
        'body' => 'Als je je server opnieuw installeert, wordt hij gestopt en wordt het installatiescript waarmee hij oorspronkelijk is ingesteld opnieuw uitgevoerd.',
        'body_warning' => 'Tijdens dit proces kunnen bestanden worden verwijderd of gewijzigd, maak een back-up van je gegevens voordat je doorgaat.',
        'reinstall_button' => 'Server opnieuw installeren',
        'confirm_title' => 'Herinstallatie van de server bevestigen',
        'confirm_button' => 'Ja, server opnieuw installeren',
        'confirm_body' => 'Je server wordt gestopt en tijdens dit proces kunnen bestanden worden verwijderd of gewijzigd. Weet je zeker dat je wilt doorgaan?',
        'success_message' => 'Je server is begonnen met het herinstallatieproces.',
    ],
];
