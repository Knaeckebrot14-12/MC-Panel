<?php

return [
    'title' => 'Version',
    'unsupported' => 'Ce type de serveur ne peut pas changer sa version de Minecraft ici.',
    'current_title' => 'Installé actuellement',
    'current_unknown' => 'Pas encore installé via cette page (la version de la configuration du serveur est utilisée).',
    'install_title' => 'Installer :name',
    'no_versions' => 'Aucune version n\'est disponible pour le moment.',
    'version_label' => 'Version',
    'install_button' => 'Installer',
    'java_hint' => 'Nécessite Java :java ; l\'image Java adaptée est choisie automatiquement.',
    'installing' => 'Installation… le serveur est arrêté et la nouvelle version téléchargée. Cela peut prendre une minute.',
    'confirm' => 'Installer :name :version ? Le serveur sera arrêté et son jar remplacé. Mondes, plugins et réglages sont conservés.',
    'confirm_downgrade' => 'Cette version est plus ancienne que celle installée. Les mondes Minecraft ne peuvent souvent pas être ouverts par des versions plus anciennes et peuvent être endommagés. Faites d\'abord une sauvegarde ! Installer quand même :name :version ?',
    'confirm_yes' => 'Oui, installer',
    'confirm_no' => 'Annuler',
    'backup_hint' => 'Astuce : créez une sauvegarde avant de changer de logiciel ou de version.',
    'installed' => ':name :version a été installé (Java :java). Démarrez le serveur pour l\'utiliser.',
    'types' => [
        'paper' => [
            'name' => 'Paper',
            'description' => 'Rapide, avec plugins (Bukkit/Spigot)',
        ],
        'purpur' => [
            'name' => 'Purpur',
            'description' => 'Paper avec de nombreuses options en plus',
        ],
        'folia' => [
            'name' => 'Folia',
            'description' => 'Paper pour très grands serveurs (multithread)',
        ],
        'fabric' => [
            'name' => 'Fabric',
            'description' => 'Chargeur de mods léger',
        ],
        'vanilla' => [
            'name' => 'Vanilla',
            'description' => 'Le serveur original de Mojang',
        ],
        'velocity' => [
            'name' => 'Velocity',
            'description' => 'Proxy reliant plusieurs serveurs',
        ],
    ],
    'errors' => [
        'unsupported' => 'Ce serveur ne peut pas changer de version (son démarrage n\'utilise pas de jar).',
        'unknown_version' => 'Cette version n\'est pas disponible.',
        'unknown_type' => 'Logiciel de serveur inconnu.',
        'download' => 'La nouvelle version n\'a pas pu être téléchargée. Réessayez plus tard.',
        'no_build' => 'Il n\'y a pas encore de téléchargement pour cette version.',
        'still_running' => 'Le serveur n\'a pas pu être arrêté. Arrêtez-le puis réessayez.',
        'api' => 'La liste des versions n\'a pas pu être chargée. Réessayez plus tard.',
        'busy' => 'Une version est déjà en cours d\'installation sur ce serveur.',
        'backup_full' => 'La limite de sauvegardes est atteinte, aucune sauvegarde n\'a pu être faite. Supprimez une sauvegarde ou changez sans sauvegarde.',
        'backup_throttled' => 'Trop de sauvegardes en peu de temps. Attendez un instant et réessayez.',
        'backup_failed' => 'La sauvegarde a échoué, rien n\'a été modifié. Réessayez ou changez sans sauvegarde.',
        'backup_timeout' => 'La sauvegarde prend plus de temps que prévu et continue ; rien n\'a été modifié. Réessayez quand elle sera terminée.',
    ],
    'backup_first' => 'Créer d\'abord une sauvegarde',
    'backup_full_note' => 'La liste des sauvegardes est pleine. Supprimez d\'abord une sauvegarde ou changez sans sauvegarde.',
    'installing_backup' => 'Création de la sauvegarde, puis installation… le serveur est arrêté entre-temps. Cela peut prendre quelques minutes.',
];
