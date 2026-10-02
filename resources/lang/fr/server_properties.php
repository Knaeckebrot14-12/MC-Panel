<?php

return [
    'title' => 'Paramètres du serveur',
    'missing' => 'Ce serveur n\'a pas encore de server.properties. Démarrez-le une fois, ou enregistrez ici pour créer le fichier.',
    'saved' => 'Paramètres enregistrés. Ils s\'appliquent au prochain démarrage.',
    'saved_restart' => 'Paramètres enregistrés. Redémarrez le serveur pour les appliquer.',
    'unsaved' => ':count modifications non enregistrées',
    'reset' => 'Annuler',
    'save' => 'Enregistrer',
    'locked' => 'Le port, l\'IP et RCON sont gérés par le panel et ne peuvent pas être modifiés ici.',
    'groups' => [
        'general' => 'Général',
        'world' => 'Monde',
        'access' => 'Accès et sécurité',
        'resource_pack' => 'Pack de ressources',
        'other' => 'Autres paramètres',
    ],
    'options' => [
        'survival' => 'Survie',
        'creative' => 'Créatif',
        'adventure' => 'Aventure',
        'spectator' => 'Spectateur',
        'peaceful' => 'Paisible',
        'easy' => 'Facile',
        'normal' => 'Normal',
        'hard' => 'Difficile',
        'flat' => 'Superplat',
        'large_biomes' => 'Grands biomes',
        'amplified' => 'Amplifié',
    ],
    'server_list' => [
        'title' => 'Liste des serveurs',
        'line1' => 'Première ligne',
        'line2' => 'Deuxième ligne (facultative)',
        'codes_hint' => 'Les codes de couleur et de format commencent par & (p. ex. &a vert, &l gras, &r réinitialiser). Cliquez sur une couleur pour l’insérer au curseur. L’aperçu montre ce que voient les joueurs.',
        'icon_title' => 'Icône du serveur',
        'icon_upload' => 'Envoyer une icône',
        'icon_remove' => 'Supprimer l’icône',
        'icon_hint' => 'PNG, JPG, GIF ou WebP. Recadrée en carré et réduite à 64×64 automatiquement. Prise en compte après un redémarrage.',
        'icon_saved' => 'Icône enregistrée. Elle apparaît après le prochain redémarrage.',
        'icon_invalid' => 'Ce fichier n’est pas une image lisible.',
        'no_icon' => 'Aucune icône',
        'format' => [
            'l' => 'Gras',
            'o' => 'Italique',
            'n' => 'Souligné',
            'm' => 'Barré',
            'k' => 'Magique',
            'r' => 'Réinitialiser',
        ],
    ],
    'fields' => [
        'motd' => [
            'label' => 'Description du serveur (MOTD)',
            'description' => 'Affichée dans la liste des serveurs multijoueur.',
        ],
        'max-players' => [
            'label' => 'Joueurs maximum',
            'description' => 'Combien de joueurs peuvent être en ligne en même temps.',
        ],
        'gamemode' => [
            'label' => 'Mode de jeu',
            'description' => 'Mode de jeu des nouveaux joueurs.',
        ],
        'difficulty' => [
            'label' => 'Difficulté',
            'description' => 'Dangerosité des monstres et de la faim.',
        ],
        'hardcore' => [
            'label' => 'Hardcore',
            'description' => 'Les joueurs sont bannis après leur première mort.',
        ],
        'pvp' => [
            'label' => 'PvP',
            'description' => 'Les joueurs peuvent s\'infliger des dégâts.',
        ],
        'force-gamemode' => [
            'label' => 'Forcer le mode de jeu',
            'description' => 'Les joueurs rejoignent toujours dans le mode de jeu par défaut.',
        ],
        'allow-flight' => [
            'label' => 'Autoriser le vol',
            'description' => 'Nécessaire pour certains plugins et mods, sinon les joueurs qui volent sont expulsés.',
        ],
        'level-name' => [
            'label' => 'Dossier du monde',
            'description' => 'Nom du dossier du monde à charger ou créer.',
        ],
        'level-seed' => [
            'label' => 'Graine',
            'description' => 'Graine des nouveaux mondes ; vide = aléatoire.',
        ],
        'level-type' => [
            'label' => 'Type de monde',
            'description' => 'Utilisé uniquement lors de la génération d\'un nouveau monde.',
        ],
        'allow-nether' => [
            'label' => 'Nether',
            'description' => 'Les joueurs peuvent aller dans le Nether.',
        ],
        'generate-structures' => [
            'label' => 'Structures',
            'description' => 'Villages, temples et autres structures sont générés.',
        ],
        'spawn-monsters' => [
            'label' => 'Monstres',
            'description' => 'Les créatures hostiles apparaissent.',
        ],
        'spawn-npcs' => [
            'label' => 'Villageois',
            'description' => 'Les villageois apparaissent.',
        ],
        'spawn-protection' => [
            'label' => 'Protection du spawn',
            'description' => 'Rayon autour du spawn où seuls les opérateurs peuvent construire (0 = désactivé).',
        ],
        'view-distance' => [
            'label' => 'Distance d\'affichage',
            'description' => 'Chunks envoyés aux joueurs. Des valeurs basses économisent RAM et CPU.',
        ],
        'simulation-distance' => [
            'label' => 'Distance de simulation',
            'description' => 'Chunks autour des joueurs où les choses bougent et poussent.',
        ],
        'max-world-size' => [
            'label' => 'Bordure du monde',
            'description' => 'Rayon maximal du monde en blocs.',
        ],
        'white-list' => [
            'label' => 'Liste blanche',
            'description' => 'Seuls les joueurs de la liste blanche peuvent rejoindre.',
        ],
        'enforce-whitelist' => [
            'label' => 'Appliquer la liste blanche',
            'description' => 'Expulse les joueurs en ligne retirés de la liste blanche.',
        ],
        'online-mode' => [
            'label' => 'Mode en ligne',
            'description' => 'Vérifie les comptes auprès de Mojang. À désactiver uniquement derrière un proxy comme Velocity ou BungeeCord.',
        ],
        'enforce-secure-profile' => [
            'label' => 'Profil de chat sécurisé',
            'description' => 'Les joueurs ont besoin de clés de chat signées par Mojang.',
        ],
        'enable-command-block' => [
            'label' => 'Blocs de commande',
            'description' => 'Les blocs de commande peuvent être utilisés.',
        ],
        'op-permission-level' => [
            'label' => 'Niveau des opérateurs',
            'description' => 'Niveau de permission des opérateurs (1-4).',
        ],
        'player-idle-timeout' => [
            'label' => 'Expulsion AFK (minutes)',
            'description' => 'Expulse les joueurs inactifs après ce nombre de minutes (0 = jamais).',
        ],
        'resource-pack' => [
            'label' => 'URL du pack de ressources',
            'description' => 'Lien de téléchargement direct vers un pack de ressources (zip).',
        ],
        'require-resource-pack' => [
            'label' => 'Exiger le pack de ressources',
            'description' => 'Les joueurs qui refusent le pack sont déconnectés.',
        ],
    ],
];
