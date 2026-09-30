<?php

return [
    'title' => 'Emplacements',
    'breadcrumb_locations' => 'Emplacements',
    'index' => [
        'heading' => 'Emplacements',
        'subheading' => 'Tous les emplacements auxquels les nodes peuvent être assignés pour faciliter leur classement.',
        'list_heading' => 'Liste des emplacements',
        'create_new_button' => 'Créer',
        'table' => [
            'id' => 'ID',
            'short_code' => 'Code court',
            'description' => 'Description',
            'nodes' => 'Nodes',
            'servers' => 'Serveurs',
        ],
        'modal' => [
            'close_aria' => 'Fermer',
            'title' => 'Créer un emplacement',
            'short_code_label' => 'Code court',
            'short_code_description' => 'Un court identifiant permettant de distinguer cet emplacement des autres. Doit contenir entre 1 et 60 caractères, par exemple :example.',
            'description_label' => 'Description',
            'description_description' => 'Une description plus longue de cet emplacement. Doit contenir moins de 191 caractères.',
            'cancel_button' => 'Annuler',
            'create_button' => 'Créer',
        ],
    ],
    'view' => [
        'title' => 'Emplacements → Voir → :location',
        'details_heading' => "Détails de l'emplacement",
        'short_code_label' => 'Code court',
        'description_label' => 'Description',
        'save_button' => 'Enregistrer',
        'nodes_heading' => 'Nodes',
        'table' => [
            'id' => 'ID',
            'name' => 'Nom',
            'fqdn' => 'FQDN',
            'servers' => 'Serveurs',
        ],
    ],
];
