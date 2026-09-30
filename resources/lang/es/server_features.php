<?php

return [
    'gsl_token' => [
        'title' => '¡Token GSL no válido!',
        'body_1' => 'Parece que tu Gameserver Login Token (token GSL) no es válido o ha caducado.',
        'body_2' => 'Puedes generar uno nuevo e introducirlo abajo, o dejar el campo en blanco para eliminarlo por completo.',
        'field_label' => 'Token GSL',
        'field_description' => 'Visita https://steamcommunity.com/dev/managegameservers para generar un token.',
        'update_button' => 'Actualizar token GSL',
    ],
    'hytale_oauth' => [
        'title' => 'Autenticación necesaria',
        'body' => 'Debes autenticarte con tu cuenta de Hytale para descargar o actualizar los archivos del servidor. Inicia sesión para continuar.',
        'cancel_button' => 'Cancelar',
        'login_button' => 'Iniciar sesión',
    ],
    'pid_limit' => [
        'admin_title' => 'Límite de memoria o procesos alcanzado...',
        'admin_body_1' => 'Este servidor ha alcanzado el límite máximo de procesos o de memoria.',
        'admin_body_2' => 'Aumentar <0>container_pid_limit</0> en la configuración de wings, <1>config.yml</1>, podría ayudar a resolver este problema.',
        'admin_note' => 'Nota: Wings debe reiniciarse para que los cambios en el archivo de configuración surtan efecto',
        'user_title' => 'Posible límite de recursos alcanzado...',
        'user_body' => 'Este servidor está intentando usar más recursos de los asignados. Contacta con el administrador y facilítale el error de abajo.',
        'close_button' => 'Cerrar',
    ],
    'steam_disk_space' => [
        'title' => 'Sin espacio de disco disponible...',
        'admin_body_1' => 'Este servidor se ha quedado sin espacio de disco disponible y no puede completar el proceso de instalación o actualización.',
        'admin_body_2' => 'Asegúrate de que la máquina tenga suficiente espacio en disco escribiendo <0>df -h</0> en la máquina que aloja este servidor. Elimina archivos o aumenta el espacio disponible para resolver el problema.',
        'user_body' => 'Este servidor se ha quedado sin espacio de disco disponible y no puede completar el proceso de instalación o actualización. Ponte en contacto con los administradores e infórmales de los problemas de espacio en disco.',
        'close_button' => 'Cerrar',
    ],
    'eula' => [
        'title' => 'Aceptar el EULA de Minecraft®',
        'body_prefix' => 'Al pulsar «Acepto» abajo, indicas que aceptas el',
        'body_suffix' => '.',
        'link_text' => 'EULA de Minecraft®',
        'cancel_button' => 'Cancelar',
        'accept_button' => 'Acepto',
    ],
    'java_version' => [
        'title' => 'Versión de Java no compatible',
        'body' => 'Este servidor está ejecutando actualmente una versión de Java no compatible y no se puede iniciar.',
        'body_select_notice' => ' Selecciona una versión compatible de la lista de abajo para continuar iniciando el servidor.',
        'cancel_button' => 'Cancelar',
        'update_button' => 'Actualizar imagen de Docker',
    ],
];
