<?php

return [
    'title' => 'API applicative',
    'index' => [
        'heading' => 'API applicative',
        'subheading' => "Gérez les identifiants d'accès permettant de piloter ce panel via l'API.",
        'list_heading' => 'Liste des identifiants',
        'create_new_button' => 'Créer',
        'table' => [
            'key' => 'Clé',
            'memo' => 'Mémo',
            'last_used' => 'Dernière utilisation',
            'created' => 'Créée',
            'created_by' => 'Créée par',
        ],
        'js' => [
            'revoke_title' => 'Révoquer la clé API',
            'revoke_text' => "Une fois cette clé API révoquée, toutes les applications qui l'utilisent actuellement cesseront de fonctionner.",
            'revoke_confirm_button' => 'Révoquer',
            'revoke_success_text' => 'La clé API a été révoquée.',
            'revoke_error_title' => 'Oups !',
            'revoke_error_text' => "Une erreur s'est produite lors de la révocation de cette clé.",
        ],
    ],
    'new' => [
        'subheading' => 'Créer une nouvelle clé API applicative.',
        'breadcrumb_new' => 'Nouveaux identifiants',
        'select_permissions_heading' => 'Sélectionner les permissions',
        'read_all_button' => 'Tout en lecture',
        'read_write_all_button' => 'Tout en lecture et écriture',
        'none_all_button' => 'Tout à aucun',
        'read_label' => 'Lecture',
        'read_write_label' => 'Lecture et écriture',
        'none_label' => 'Aucun',
        'description_label' => 'Description',
        'notice' => "Une fois les permissions attribuées et cet ensemble d'identifiants créé, vous ne pourrez plus le modifier. Si vous devez apporter des changements par la suite, vous devrez créer un nouvel ensemble d'identifiants.",
        'create_button' => 'Créer les identifiants',
    ],
];
