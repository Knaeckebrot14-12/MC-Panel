<?php

return [
    'title' => 'Roles',
    'subheading' => 'Lo que pueden hacer supporters, moderadores y admins.',
    'matrix_heading' => 'Permisos por rol',
    'permission' => 'Permiso',
    'members' => ':count miembro(s)',
    'save' => 'Guardar',
    'saved' => 'Permisos de roles guardados.',
    'reset' => 'Restablecer valores predeterminados',
    'reset_confirm' => '¿Restablecer todos los permisos de roles a los valores predeterminados?',
    'hint' => 'Los cambios se aplican de inmediato a todos los miembros del rol. El propietario siempre tiene todos los permisos.',
    'owner_only_text' => 'Los ajustes del panel (incluidas actualizaciones, diseño, inicio de sesión, monitorización, subdominios y esta página) y la API de aplicación quedan solo para el propietario; de lo contrario un rol podría darse todos los permisos.',
    'groups' => [
        'general' => 'General',
        'users' => 'Usuarios',
        'servers' => 'Servidores',
        'infrastructure' => 'Infraestructura',
        'coins' => 'Monedas y tienda',
        'security' => 'Seguridad',
        'owner_only' => 'Solo propietario',
    ],
    'permissions' => [
        'overview' => [
            'name' => 'Resumen',
            'description' => 'Panel de administración con estadísticas.',
        ],
        'audit' => [
            'name' => 'Registro de auditoría',
            'description' => 'Ver lo que hizo el equipo.',
        ],
        'maintenance' => [
            'name' => 'Modo mantenimiento',
            'description' => 'Activar o desactivar el banner o el bloqueo.',
        ],
        'announcements' => [
            'name' => 'Anuncios',
            'description' => 'Crear, ocultar y eliminar anuncios.',
        ],
        'tickets' => [
            'name' => 'Tickets',
            'description' => 'Responder y gestionar tickets de soporte.',
        ],
        'users_view' => [
            'name' => 'Ver usuarios',
            'description' => 'Lista y páginas de usuarios.',
        ],
        'users_email' => [
            'name' => 'Ver correos e IPs',
            'description' => 'Sin esto, los correos y las IP de registro de otros usuarios están ocultos y no se pueden buscar.',
        ],
        'users_edit' => [
            'name' => 'Editar y crear usuarios',
            'description' => 'Nombre, usuario, correo, idioma y límites de recursos.',
        ],
        'users_password' => [
            'name' => 'Cambiar contraseñas',
            'description' => 'Poner una nueva contraseña a otros usuarios.',
        ],
        'users_moderate' => [
            'name' => 'Moderar usuarios',
            'description' => 'Suspender, reactivar y confirmar correos.',
        ],
        'users_coins' => [
            'name' => 'Dar y quitar monedas',
            'description' => 'Cambiar el saldo de monedas de los usuarios.',
        ],
        'users_roles' => [
            'name' => 'Asignar roles',
            'description' => 'Dar a los usuarios un rol inferior al propio.',
        ],
        'users_delete' => [
            'name' => 'Eliminar usuarios',
            'description' => 'Eliminar cuentas sin servidores.',
        ],
        'users_impersonate' => [
            'name' => 'Iniciar sesión como usuario (vista de soporte)',
            'description' => 'Ver el panel exactamente como lo ve un usuario normal, para ayudar con problemas. También muestra su correo. Mientras tanto no se pueden cambiar los ajustes de la cuenta, los coins ni los tickets.',
        ],
        'security_ipblock' => [
            'name' => 'IP bloqueadas',
            'description' => 'Ver las direcciones IP bloqueadas por demasiados inicios de sesión fallidos, desbloquearlas o bloquear una IP manualmente. Los ajustes del bloqueo son solo del propietario.',
        ],
        'servers_view' => [
            'name' => 'Ver servidores',
            'description' => 'Lista y páginas de servidores.',
        ],
        'servers_moderate' => [
            'name' => 'Suspender servidores',
            'description' => 'Suspender y reactivar servidores.',
        ],
        'servers_manage' => [
            'name' => 'Gestionar servidores',
            'description' => 'Detalles, recursos, inicio, bases de datos, mounts, reinstalación y traslado.',
        ],
        'servers_create' => [
            'name' => 'Crear servidores',
            'description' => 'Crear servidores para cualquier usuario.',
        ],
        'servers_delete' => [
            'name' => 'Eliminar servidores',
            'description' => 'Eliminar servidores.',
        ],
        'servers_bulk' => [
            'name' => 'Acciones masivas en servidores',
            'description' => 'Iniciar, detener, reiniciar o forzar el cierre de todos los servidores de un nodo a la vez y enviar un mensaje a la consola de todos los servidores de Minecraft en ejecución.',
        ],
        'nodes' => [
            'name' => 'Nodes',
            'description' => 'Nodes, asignaciones, monitorización y actualizaciones de Wings.',
        ],
        'locations' => [
            'name' => 'Ubicaciones',
            'description' => 'Crear y editar ubicaciones.',
        ],
        'databases' => [
            'name' => 'Hosts de bases de datos',
            'description' => 'Servidores de bases de datos para servidores de juego.',
        ],
        'mounts' => [
            'name' => 'Mounts',
            'description' => 'Carpetas compartidas para servidores.',
        ],
        'nests' => [
            'name' => 'Nests y eggs',
            'description' => 'Tipos de servidor y sus ajustes de inicio.',
        ],
        'coins_vouchers' => [
            'name' => 'Cupones',
            'description' => 'Crear y gestionar cupones de monedas.',
        ],
        'coins_plans' => [
            'name' => 'Planes de servidor',
            'description' => 'Planes que se venden en la tienda.',
        ],
        'coins_settings' => [
            'name' => 'Ajustes de monedas',
            'description' => 'Recompensas, precios y ajustes de la tienda.',
        ],
    ],
];
