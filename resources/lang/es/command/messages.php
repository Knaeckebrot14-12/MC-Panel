<?php

return [
    'location' => [
        'no_location_found' => 'No se pudo encontrar un registro que coincida con el código corto proporcionado.',
        'ask_short' => 'Código corto de la ubicación',
        'ask_long' => 'Descripción de la ubicación',
        'created' => 'Se creó correctamente una nueva ubicación (:name) con el ID :id.',
        'deleted' => 'Se eliminó correctamente la ubicación solicitada.',
    ],
    'user' => [
        'search_users' => 'Introduce un nombre de usuario, ID de usuario o dirección de correo electrónico',
        'select_search_user' => "ID del usuario a eliminar (introduce '0' para volver a buscar)",
        'deleted' => 'Usuario eliminado correctamente del panel.',
        'confirm_delete' => '¿Seguro que quieres eliminar a este usuario del panel?',
        'no_users_found' => 'No se encontraron usuarios para el término de búsqueda proporcionado.',
        'multiple_found' => 'Se encontraron varias cuentas para el usuario proporcionado; no se puede eliminar un usuario debido a la opción --no-interaction.',
        'ask_admin' => '¿Es este usuario un administrador?',
        'ask_email' => 'Dirección de correo electrónico',
        'ask_username' => 'Nombre de usuario',
        'ask_name_first' => 'Nombre',
        'ask_name_last' => 'Apellidos',
        'ask_password' => 'Contraseña',
        'ask_password_tip' => 'Si quieres crear una cuenta con una contraseña aleatoria enviada por correo al usuario, vuelve a ejecutar este comando (CTRL+C) y pasa la opción `--no-password`.',
        'ask_password_help' => 'Las contraseñas deben tener al menos 8 caracteres y contener al menos una mayúscula y un número.',
        '2fa_help_text' => [
            'Este comando desactivará la autenticación de dos factores de la cuenta de un usuario si está activada. Solo debe usarse como comando de recuperación de cuenta si el usuario no puede acceder a ella.',
            'Si no era lo que querías hacer, pulsa CTRL+C para salir de este proceso.',
        ],
        '2fa_disabled' => 'La autenticación de dos factores se ha desactivado para :email.',
    ],
    'schedule' => [
        'output_line' => 'Enviando el trabajo para la primera tarea de `:schedule` (:hash).',
    ],
    'maintenance' => [
        'deleting_service_backup' => 'Eliminando el archivo de copia de seguridad del servicio :file.',
    ],
    'server' => [
        'rebuild_failed' => 'La solicitud de reconstrucción de «:name» (#:id) en el nodo «:node» falló con el error: :message',
        'reinstall' => [
            'failed' => 'La solicitud de reinstalación de «:name» (#:id) en el nodo «:node» falló con el error: :message',
            'confirm' => 'Estás a punto de reinstalar un grupo de servidores. ¿Deseas continuar?',
        ],
        'power' => [
            'confirm' => 'Estás a punto de realizar una acción :action en :count servidores. ¿Deseas continuar?',
            'action_failed' => 'La solicitud de acción de energía de «:name» (#:id) en el nodo «:node» falló con el error: :message',
        ],
    ],
    'environment' => [
        'mail' => [
            'ask_smtp_host' => 'Host SMTP (p. ej., smtp.gmail.com)',
            'ask_smtp_port' => 'Puerto SMTP',
            'ask_smtp_username' => 'Usuario SMTP',
            'ask_smtp_password' => 'Contraseña SMTP',
            'ask_mailgun_domain' => 'Dominio de Mailgun',
            'ask_mailgun_endpoint' => 'Endpoint de Mailgun',
            'ask_mailgun_secret' => 'Secreto de Mailgun',
            'ask_mandrill_secret' => 'Secreto de Mandrill',
            'ask_postmark_username' => 'Clave de API de Postmark',
            'ask_driver' => '¿Qué controlador se debe usar para enviar correos?',
            'ask_mail_from' => 'Dirección de correo desde la que se originan los correos',
            'ask_mail_name' => 'Nombre con el que aparecerán los correos',
            'ask_encryption' => 'Método de cifrado a usar',
        ],
    ],
];
