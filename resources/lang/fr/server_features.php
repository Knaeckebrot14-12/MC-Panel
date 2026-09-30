<?php

return [
    'gsl_token' => [
        'title' => 'Jeton GSL invalide !',
        'body_1' => "Il semble que votre jeton de connexion de serveur de jeu (jeton GSL) soit invalide ou ait expiré.",
        'body_2' => 'Vous pouvez soit en générer un nouveau et le saisir ci-dessous, soit laisser le champ vide pour le supprimer complètement.',
        'field_label' => 'Jeton GSL',
        'field_description' => 'Rendez-vous sur https://steamcommunity.com/dev/managegameservers pour générer un jeton.',
        'update_button' => 'Mettre à jour le jeton GSL',
    ],
    'hytale_oauth' => [
        'title' => 'Authentification requise',
        'body' => 'Vous devez vous authentifier avec votre compte Hytale pour télécharger ou mettre à jour les fichiers du serveur. Veuillez vous connecter pour continuer.',
        'cancel_button' => 'Annuler',
        'login_button' => 'Se connecter',
    ],
    'pid_limit' => [
        'admin_title' => 'Limite de mémoire ou de processus atteinte...',
        'admin_body_1' => 'Ce serveur a atteint la limite maximale de processus ou de mémoire.',
        'admin_body_2' => "Augmenter <0>container_pid_limit</0> dans la configuration de wings, <1>config.yml</1>, peut aider à résoudre ce problème.",
        'admin_note' => 'Remarque : Wings doit être redémarré pour que les modifications du fichier de configuration prennent effet',
        'user_title' => 'Limite de ressources probablement atteinte...',
        'user_body' => "Ce serveur tente d'utiliser plus de ressources que celles allouées. Veuillez contacter l'administrateur et lui communiquer l'erreur ci-dessous.",
        'close_button' => 'Fermer',
    ],
    'steam_disk_space' => [
        'title' => "Espace disque disponible épuisé...",
        'admin_body_1' => "Ce serveur n'a plus d'espace disque disponible et ne peut pas terminer le processus d'installation ou de mise à jour.",
        'admin_body_2' => "Assurez-vous que la machine dispose de suffisamment d'espace disque en tapant <0>df -h</0> sur la machine hébergeant ce serveur. Supprimez des fichiers ou augmentez l'espace disque disponible pour résoudre le problème.",
        'user_body' => "Ce serveur n'a plus d'espace disque disponible et ne peut pas terminer le processus d'installation ou de mise à jour. Veuillez contacter le ou les administrateurs et les informer des problèmes d'espace disque.",
        'close_button' => 'Fermer',
    ],
    'eula' => [
        'title' => "Accepter le CLUF de Minecraft®",
        'body_prefix' => 'En appuyant sur « J\'accepte » ci-dessous, vous indiquez que vous acceptez le',
        'body_suffix' => '.',
        'link_text' => 'CLUF de Minecraft®',
        'cancel_button' => 'Annuler',
        'accept_button' => "J'accepte",
    ],
    'java_version' => [
        'title' => 'Version de Java non prise en charge',
        'body' => 'Ce serveur exécute actuellement une version de Java non prise en charge et ne peut pas être démarré.',
        'body_select_notice' => ' Veuillez sélectionner une version prise en charge dans la liste ci-dessous pour continuer le démarrage du serveur.',
        'cancel_button' => 'Annuler',
        'update_button' => "Mettre à jour l'image Docker",
    ],
];
