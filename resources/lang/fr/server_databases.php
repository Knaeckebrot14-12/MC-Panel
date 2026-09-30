<?php

return [
    'title' => 'Bases de données',
    'empty' => "Il semble que vous n'ayez aucune base de données.",
    'disabled' => 'Des bases de données ne peuvent pas être créées pour ce serveur.',
    'allocated' => ':used bases de données sur :limit ont été allouées à ce serveur.',
    'new_database_button' => 'Nouvelle base de données',
    'rotate_password_button' => 'Renouveler le mot de passe',
    'labels' => [
        'endpoint' => 'Point de terminaison',
        'connections_from' => 'Connexions depuis',
        'username' => "Nom d'utilisateur",
    ],
    'create' => [
        'heading' => 'Créer une nouvelle base de données',
        'name_label' => 'Nom de la base de données',
        'name_description' => 'Un nom descriptif pour votre instance de base de données.',
        'connections_from_label' => 'Connexions depuis',
        'connections_from_description' => "D'où les connexions doivent être autorisées. Laissez vide pour autoriser les connexions de n'importe où.",
        'cancel' => 'Annuler',
        'create_button' => 'Créer la base de données',
        'validation' => [
            'name_required' => 'Un nom de base de données doit être fourni.',
            'name_min' => 'Le nom de la base de données doit contenir au moins 3 caractères.',
            'name_max' => 'Le nom de la base de données ne doit pas dépasser 48 caractères.',
            'name_format' => 'Le nom de la base de données ne doit contenir que des caractères alphanumériques, des tirets bas, des tirets et/ou des points.',
            'connections_from_format' => 'Une adresse hôte valide doit être fournie.',
        ],
    ],
    'delete' => [
        'heading' => 'Confirmer la suppression de la base de données',
        'body_prefix' => "Supprimer une base de données est une action définitive qui ne peut pas être annulée. Cela supprimera définitivement la base de données ",
        'body_suffix' => ' et toutes les données associées.',
        'confirm_label' => 'Confirmer le nom de la base de données',
        'confirm_description' => 'Saisissez le nom de la base de données pour confirmer la suppression.',
        'confirm_required' => 'Le nom de la base de données doit être fourni.',
        'cancel' => 'Annuler',
        'delete_button' => 'Supprimer la base de données',
    ],
    'connection' => [
        'heading' => 'Détails de connexion à la base de données',
        'jdbc_label' => 'Chaîne de connexion JDBC',
        'password_label' => 'Mot de passe',
        'close_button' => 'Fermer',
    ],
];
