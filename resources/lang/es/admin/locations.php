<?php

return [
    'title' => 'Ubicaciones',
    'breadcrumb_locations' => 'Ubicaciones',
    'index' => [
        'heading' => 'Ubicaciones',
        'subheading' => 'Todas las ubicaciones a las que se pueden asignar nodos para facilitar su clasificación.',
        'list_heading' => 'Lista de ubicaciones',
        'create_new_button' => 'Crear nueva',
        'table' => [
            'id' => 'ID',
            'short_code' => 'Código corto',
            'description' => 'Descripción',
            'nodes' => 'Nodos',
            'servers' => 'Servidores',
        ],
        'modal' => [
            'close_aria' => 'Cerrar',
            'title' => 'Crear ubicación',
            'short_code_label' => 'Código corto',
            'short_code_description' => 'Un identificador corto que sirve para distinguir esta ubicación de las demás. Debe tener entre 1 y 60 caracteres, por ejemplo, :example.',
            'description_label' => 'Descripción',
            'description_description' => 'Una descripción más larga de esta ubicación. Debe tener menos de 191 caracteres.',
            'cancel_button' => 'Cancelar',
            'create_button' => 'Crear',
        ],
    ],
    'view' => [
        'title' => 'Ubicaciones → Ver → :location',
        'details_heading' => 'Detalles de la ubicación',
        'short_code_label' => 'Código corto',
        'description_label' => 'Descripción',
        'save_button' => 'Guardar',
        'nodes_heading' => 'Nodos',
        'table' => [
            'id' => 'ID',
            'name' => 'Nombre',
            'fqdn' => 'FQDN',
            'servers' => 'Servidores',
        ],
    ],
];
