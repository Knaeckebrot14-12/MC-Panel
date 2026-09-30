<?php

return [
    'title' => 'Einstellungen',
    'sftp' => [
        'heading' => 'SFTP-Details',
        'server_address_label' => 'Serveradresse',
        'username_label' => 'Benutzername',
        'password_notice' => 'Dein SFTP-Passwort ist dasselbe wie das Passwort, mit dem du dich in diesem Panel anmeldest.',
        'launch_button' => 'SFTP starten',
    ],
    'debug' => [
        'heading' => 'Debug-Informationen',
        'node_label' => 'Node',
        'server_id_label' => 'Server-ID',
    ],
    'rename' => [
        'heading' => 'Serverdetails ändern',
        'name_label' => 'Servername',
        'description_label' => 'Serverbeschreibung',
        'save_button' => 'Speichern',
    ],
    'reinstall' => [
        'heading' => 'Server neu installieren',
        'disabled_notice' => 'Die Neuinstallation dieses Servers wurde deaktiviert, da er so konfiguriert ist, dass das Installationsskript seines Eggs übersprungen wird. Wenn du diesen Server neu installieren möchtest, wende dich an einen Serveradministrator.',
        'body' => 'Durch die Neuinstallation deines Servers wird dieser gestoppt und anschließend das Installationsskript erneut ausgeführt, das ihn ursprünglich eingerichtet hat.',
        'body_warning' => 'Während dieses Vorgangs können Dateien gelöscht oder verändert werden. Bitte sichere deine Daten, bevor du fortfährst.',
        'reinstall_button' => 'Server neu installieren',
        'confirm_title' => 'Neuinstallation des Servers bestätigen',
        'confirm_button' => 'Ja, Server neu installieren',
        'confirm_body' => 'Dein Server wird gestoppt und während dieses Vorgangs können Dateien gelöscht oder verändert werden. Bist du sicher, dass du fortfahren möchtest?',
        'success_message' => 'Dein Server hat mit dem Neuinstallationsprozess begonnen.',
    ],
];
