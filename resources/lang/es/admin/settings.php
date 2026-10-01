<?php

return [
    'nav' => [
        'general' => 'General',
        'mail' => 'Correo',
        'coins' => 'Coins',
        'advanced' => 'Avanzado',
        'updates' => 'Actualizaciones',
        'login' => 'Inicio de sesión y registro',
        'design' => 'Diseño',
        'monitoring' => 'Monitorización',
        'subdomains' => 'Subdominios',
        'roles' => 'Roles',
    ],
    'notice' => [
        'env_only' => 'Tu panel está configurado actualmente para leer los ajustes únicamente del entorno. Tendrás que definir :env_var en tu archivo de entorno para cargar los ajustes dinámicamente.',
    ],
    'index' => [
        'title' => 'Ajustes',
        'heading' => 'Ajustes del panel',
        'subheading' => 'Configura Pterodactyl a tu gusto.',
        'breadcrumb_settings' => 'Ajustes',
        'panel_settings_heading' => 'Ajustes del panel',
        'company_name_label' => 'Nombre de la empresa',
        'company_name_description' => 'Este es el nombre que se usa en todo el panel y en los correos enviados a los clientes.',
        'require_2fa_label' => 'Exigir autenticación de dos factores',
        'require_2fa_description' => 'Si está activado, cualquier cuenta que pertenezca al grupo seleccionado deberá tener activada la autenticación de dos factores para usar el panel.',
        '2fa_not_required' => 'No exigida',
        '2fa_admin_only' => 'Solo administradores',
        '2fa_all_users' => 'Todos los usuarios',
        'default_language_label' => 'Idioma predeterminado',
        'default_language_description' => 'El idioma predeterminado que se usará al mostrar los componentes de la interfaz.',
    ],
];
