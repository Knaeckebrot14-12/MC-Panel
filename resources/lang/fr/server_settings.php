<?php

return [
    'title' => 'Paramètres',
    'sftp' => [
        'heading' => 'Détails SFTP',
        'server_address_label' => 'Adresse du serveur',
        'username_label' => "Nom d'utilisateur",
        'password_notice' => 'Votre mot de passe SFTP est le même que celui que vous utilisez pour accéder à ce panel.',
        'launch_button' => 'Lancer SFTP',
    ],
    'debug' => [
        'heading' => 'Informations de débogage',
        'node_label' => 'Node',
        'server_id_label' => 'ID du serveur',
    ],
    'rename' => [
        'heading' => 'Modifier les détails du serveur',
        'name_label' => 'Nom du serveur',
        'description_label' => 'Description du serveur',
        'save_button' => 'Enregistrer',
    ],
    'reinstall' => [
        'heading' => 'Réinstaller le serveur',
        'disabled_notice' => "La réinstallation de ce serveur a été désactivée car il est configuré pour ignorer le script d'installation de son egg. Si vous souhaitez réinstaller ce serveur, contactez un administrateur du serveur.",
        'body' => "Réinstaller votre serveur l'arrêtera, puis réexécutera le script d'installation qui l'a initialement configuré.",
        'body_warning' => 'Certains fichiers peuvent être supprimés ou modifiés pendant ce processus, veuillez sauvegarder vos données avant de continuer.',
        'reinstall_button' => 'Réinstaller le serveur',
        'confirm_title' => 'Confirmer la réinstallation du serveur',
        'confirm_button' => 'Oui, réinstaller le serveur',
        'confirm_body' => 'Votre serveur sera arrêté et certains fichiers peuvent être supprimés ou modifiés pendant ce processus. Voulez-vous vraiment continuer ?',
        'success_message' => 'Votre serveur a commencé le processus de réinstallation.',
    ],
];
