<?php

return [
    'title' => 'API de aplicación',
    'index' => [
        'heading' => 'API de aplicación',
        'subheading' => 'Controla las credenciales de acceso para gestionar este panel mediante la API.',
        'list_heading' => 'Lista de credenciales',
        'create_new_button' => 'Crear nueva',
        'table' => [
            'key' => 'Clave',
            'memo' => 'Nota',
            'last_used' => 'Último uso',
            'created' => 'Creada',
            'created_by' => 'Creada por',
        ],
        'js' => [
            'revoke_title' => 'Revocar clave de API',
            'revoke_text' => 'Una vez revocada esta clave de API, las aplicaciones que la estén usando dejarán de funcionar.',
            'revoke_confirm_button' => 'Revocar',
            'revoke_success_text' => 'La clave de API ha sido revocada.',
            'revoke_error_title' => '¡Vaya!',
            'revoke_error_text' => 'Se produjo un error al intentar revocar esta clave.',
        ],
    ],
    'new' => [
        'subheading' => 'Crear una nueva clave de API de aplicación.',
        'breadcrumb_new' => 'Nuevas credenciales',
        'select_permissions_heading' => 'Seleccionar permisos',
        'read_all_button' => 'Lectura en todo',
        'read_write_all_button' => 'Lectura y escritura en todo',
        'none_all_button' => 'Ninguno en todo',
        'read_label' => 'Lectura',
        'read_write_label' => 'Lectura y escritura',
        'none_label' => 'Ninguno',
        'description_label' => 'Descripción',
        'notice' => 'Una vez asignados los permisos y creado este conjunto de credenciales, no podrás volver a editarlo. Si necesitas hacer cambios más adelante, tendrás que crear un nuevo conjunto de credenciales.',
        'create_button' => 'Crear credenciales',
    ],
];
