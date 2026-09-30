<?php

return [
    'title' => 'Standorte',
    'breadcrumb_locations' => 'Standorte',
    'index' => [
        'heading' => 'Standorte',
        'subheading' => 'Alle Standorte, denen Nodes zur einfacheren Kategorisierung zugewiesen werden können.',
        'list_heading' => 'Standortliste',
        'create_new_button' => 'Neu erstellen',
        'table' => [
            'id' => 'ID',
            'short_code' => 'Kurzcode',
            'description' => 'Beschreibung',
            'nodes' => 'Nodes',
            'servers' => 'Server',
        ],
        'modal' => [
            'close_aria' => 'Schließen',
            'title' => 'Standort erstellen',
            'short_code_label' => 'Kurzcode',
            'short_code_description' => 'Eine kurze Kennung, um diesen Standort von anderen zu unterscheiden. Muss zwischen 1 und 60 Zeichen lang sein, zum Beispiel :example.',
            'description_label' => 'Beschreibung',
            'description_description' => 'Eine ausführlichere Beschreibung dieses Standorts. Muss weniger als 191 Zeichen umfassen.',
            'cancel_button' => 'Abbrechen',
            'create_button' => 'Erstellen',
        ],
    ],
    'view' => [
        'title' => 'Standorte → Ansicht → :location',
        'details_heading' => 'Standortdetails',
        'short_code_label' => 'Kurzcode',
        'description_label' => 'Beschreibung',
        'save_button' => 'Speichern',
        'nodes_heading' => 'Nodes',
        'table' => [
            'id' => 'ID',
            'name' => 'Name',
            'fqdn' => 'FQDN',
            'servers' => 'Server',
        ],
    ],
];
