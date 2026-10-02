<?php

return [
    'title' => 'Rôles',
    'subheading' => 'Ce que les supporters, modérateurs et admins peuvent faire.',
    'matrix_heading' => 'Permissions par rôle',
    'permission' => 'Permission',
    'members' => ':count membre(s)',
    'save' => 'Enregistrer',
    'saved' => 'Permissions des rôles enregistrées.',
    'reset' => 'Rétablir les valeurs par défaut',
    'reset_confirm' => 'Rétablir toutes les permissions des rôles par défaut ?',
    'hint' => 'Les changements s\'appliquent immédiatement à tous les membres du rôle. Le propriétaire a toujours toutes les permissions.',
    'owner_only_text' => 'Les paramètres du panel (dont les mises à jour, le design, la connexion, la surveillance, les sous-domaines et cette page) et l\'API d\'application restent réservés au propriétaire, sinon un rôle pourrait s\'attribuer toutes les permissions.',
    'groups' => [
        'general' => 'Général',
        'users' => 'Utilisateurs',
        'servers' => 'Serveurs',
        'infrastructure' => 'Infrastructure',
        'coins' => 'Coins & boutique',
        'owner_only' => 'Propriétaire uniquement',
    ],
    'permissions' => [
        'overview' => [
            'name' => 'Vue d\'ensemble',
            'description' => 'Tableau de bord admin avec statistiques.',
        ],
        'audit' => [
            'name' => 'Journal d\'audit',
            'description' => 'Voir ce que l\'équipe a fait.',
        ],
        'maintenance' => [
            'name' => 'Mode maintenance',
            'description' => 'Activer ou désactiver la bannière ou le verrouillage.',
        ],
        'announcements' => [
            'name' => 'Annonces',
            'description' => 'Créer, masquer et supprimer des annonces.',
        ],
        'tickets' => [
            'name' => 'Tickets',
            'description' => 'Répondre aux tickets de support et les gérer.',
        ],
        'users_view' => [
            'name' => 'Voir les utilisateurs',
            'description' => 'Liste et pages des utilisateurs.',
        ],
        'users_email' => [
            'name' => 'Voir les e-mails et IP',
            'description' => 'Sans cela, les adresses e-mail et IP d\'inscription des autres utilisateurs sont masquées et non recherchables.',
        ],
        'users_edit' => [
            'name' => 'Modifier et créer des utilisateurs',
            'description' => 'Nom, nom d\'utilisateur, e-mail, langue et limites de ressources.',
        ],
        'users_password' => [
            'name' => 'Changer les mots de passe',
            'description' => 'Définir un nouveau mot de passe pour d\'autres utilisateurs.',
        ],
        'users_moderate' => [
            'name' => 'Modérer les utilisateurs',
            'description' => 'Suspendre, réactiver et confirmer les adresses e-mail.',
        ],
        'users_coins' => [
            'name' => 'Donner et retirer des coins',
            'description' => 'Modifier le solde de coins des utilisateurs.',
        ],
        'users_roles' => [
            'name' => 'Attribuer des rôles',
            'description' => 'Donner aux utilisateurs un rôle inférieur au sien.',
        ],
        'users_delete' => [
            'name' => 'Supprimer des utilisateurs',
            'description' => 'Supprimer des comptes sans serveurs.',
        ],
        'users_impersonate' => [
            'name' => 'Se connecter en tant qu’utilisateur (vue support)',
            'description' => 'Voir le panel exactement comme un utilisateur normal, pour l’aider en cas de problème. Son adresse e-mail est alors visible. Les paramètres du compte, les coins et les tickets ne peuvent pas être modifiés pendant ce temps.',
        ],
        'servers_view' => [
            'name' => 'Voir les serveurs',
            'description' => 'Liste et pages des serveurs.',
        ],
        'servers_moderate' => [
            'name' => 'Suspendre des serveurs',
            'description' => 'Suspendre et réactiver des serveurs.',
        ],
        'servers_manage' => [
            'name' => 'Gérer les serveurs',
            'description' => 'Détails, ressources, démarrage, bases de données, mounts, réinstallation et transfert.',
        ],
        'servers_create' => [
            'name' => 'Créer des serveurs',
            'description' => 'Créer des serveurs pour n\'importe quel utilisateur.',
        ],
        'servers_delete' => [
            'name' => 'Supprimer des serveurs',
            'description' => 'Supprimer des serveurs.',
        ],
        'nodes' => [
            'name' => 'Nodes',
            'description' => 'Nodes, allocations, surveillance et mises à jour de Wings.',
        ],
        'locations' => [
            'name' => 'Emplacements',
            'description' => 'Créer et modifier des emplacements.',
        ],
        'databases' => [
            'name' => 'Hôtes de bases de données',
            'description' => 'Serveurs de bases de données pour les serveurs de jeu.',
        ],
        'mounts' => [
            'name' => 'Mounts',
            'description' => 'Dossiers partagés pour les serveurs.',
        ],
        'nests' => [
            'name' => 'Nests & eggs',
            'description' => 'Types de serveurs et leurs réglages de démarrage.',
        ],
        'coins_vouchers' => [
            'name' => 'Bons',
            'description' => 'Créer et gérer des bons de coins.',
        ],
        'coins_plans' => [
            'name' => 'Offres de serveurs',
            'description' => 'Offres vendues dans la boutique.',
        ],
        'coins_settings' => [
            'name' => 'Paramètres des coins',
            'description' => 'Récompenses, prix et réglages de la boutique.',
        ],
    ],
];
