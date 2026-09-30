<?php

return [
    'location' => [
        'no_location_found' => 'Es konnte kein Datensatz gefunden werden, der dem angegebenen Kurzcode entspricht.',
        'ask_short' => 'Kurzcode des Standorts',
        'ask_long' => 'Beschreibung des Standorts',
        'created' => 'Neuer Standort (:name) mit der ID :id wurde erfolgreich erstellt.',
        'deleted' => 'Der angeforderte Standort wurde erfolgreich gelöscht.',
    ],
    'user' => [
        'search_users' => 'Benutzername, Benutzer-ID oder E-Mail-Adresse eingeben',
        'select_search_user' => 'ID des zu löschenden Benutzers (\'0\' eingeben, um erneut zu suchen)',
        'deleted' => 'Benutzer wurde erfolgreich aus dem Panel gelöscht.',
        'confirm_delete' => 'Bist du sicher, dass du diesen Benutzer aus dem Panel löschen möchtest?',
        'no_users_found' => 'Für den angegebenen Suchbegriff wurden keine Benutzer gefunden.',
        'multiple_found' => 'Für den angegebenen Benutzer wurden mehrere Konten gefunden. Aufgrund der Option --no-interaction kann kein Benutzer gelöscht werden.',
        'ask_admin' => 'Ist dieser Benutzer ein Administrator?',
        'ask_email' => 'E-Mail-Adresse',
        'ask_username' => 'Benutzername',
        'ask_name_first' => 'Vorname',
        'ask_name_last' => 'Nachname',
        'ask_password' => 'Passwort',
        'ask_password_tip' => 'Wenn du ein Konto mit einem per E-Mail zugesandten Zufallspasswort erstellen möchtest, starte diesen Befehl erneut (STRG+C) und verwende die Option `--no-password`.',
        'ask_password_help' => 'Passwörter müssen mindestens 8 Zeichen lang sein und mindestens einen Großbuchstaben sowie eine Zahl enthalten.',
        '2fa_help_text' => [
            'Dieser Befehl deaktiviert die Zwei-Faktor-Authentifizierung für ein Benutzerkonto, sofern sie aktiviert ist. Dies sollte nur zur Kontowiederherstellung verwendet werden, wenn der Benutzer aus seinem Konto ausgesperrt ist.',
            'Falls das nicht beabsichtigt war, drücke STRG+C, um diesen Vorgang abzubrechen.',
        ],
        '2fa_disabled' => 'Die Zwei-Faktor-Authentifizierung für :email wurde deaktiviert.',
    ],
    'schedule' => [
        'output_line' => 'Job für die erste Aufgabe in `:schedule` (:hash) wird ausgeführt.',
    ],
    'maintenance' => [
        'deleting_service_backup' => 'Sicherungsdatei :file des Dienstes wird gelöscht.',
    ],
    'server' => [
        'rebuild_failed' => 'Neuaufbau-Anfrage für ":name" (#:id) auf Node ":node" fehlgeschlagen mit Fehler: :message',
        'reinstall' => [
            'failed' => 'Neuinstallations-Anfrage für ":name" (#:id) auf Node ":node" fehlgeschlagen mit Fehler: :message',
            'confirm' => 'Du bist dabei, eine Gruppe von Servern neu zu installieren. Möchtest du fortfahren?',
        ],
        'power' => [
            'confirm' => 'Du bist dabei, die Aktion :action für :count Server auszuführen. Möchtest du fortfahren?',
            'action_failed' => 'Power-Aktion für ":name" (#:id) auf Node ":node" fehlgeschlagen mit Fehler: :message',
        ],
    ],
    'environment' => [
        'mail' => [
            'ask_smtp_host' => 'SMTP-Host (z. B. smtp.gmail.com)',
            'ask_smtp_port' => 'SMTP-Port',
            'ask_smtp_username' => 'SMTP-Benutzername',
            'ask_smtp_password' => 'SMTP-Passwort',
            'ask_mailgun_domain' => 'Mailgun-Domain',
            'ask_mailgun_endpoint' => 'Mailgun-Endpunkt',
            'ask_mailgun_secret' => 'Mailgun-Secret',
            'ask_mandrill_secret' => 'Mandrill-Secret',
            'ask_postmark_username' => 'Postmark-API-Schlüssel',
            'ask_driver' => 'Welcher Treiber soll zum Versenden von E-Mails verwendet werden?',
            'ask_mail_from' => 'E-Mail-Adresse, von der E-Mails stammen sollen',
            'ask_mail_name' => 'Name, unter dem E-Mails erscheinen sollen',
            'ask_encryption' => 'Zu verwendende Verschlüsselungsmethode',
        ],
    ],
];
