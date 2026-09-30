<?php

return [
    'title' => 'Ajustes',
    'sftp' => [
        'heading' => 'Detalles de SFTP',
        'server_address_label' => 'Dirección del servidor',
        'username_label' => 'Nombre de usuario',
        'password_notice' => 'Tu contraseña de SFTP es la misma que usas para acceder a este panel.',
        'launch_button' => 'Abrir SFTP',
    ],
    'debug' => [
        'heading' => 'Información de depuración',
        'node_label' => 'Nodo',
        'server_id_label' => 'ID del servidor',
    ],
    'rename' => [
        'heading' => 'Cambiar detalles del servidor',
        'name_label' => 'Nombre del servidor',
        'description_label' => 'Descripción del servidor',
        'save_button' => 'Guardar',
    ],
    'reinstall' => [
        'heading' => 'Reinstalar servidor',
        'disabled_notice' => 'La reinstalación de este servidor se ha desactivado porque está configurado para omitir el script de instalación de su egg. Si quieres reinstalar este servidor, contacta con un administrador del servidor.',
        'body' => 'Reinstalar tu servidor lo detendrá y volverá a ejecutar el script de instalación con el que se configuró inicialmente.',
        'body_warning' => 'Es posible que algunos archivos se eliminen o modifiquen durante este proceso; haz una copia de seguridad de tus datos antes de continuar.',
        'reinstall_button' => 'Reinstalar servidor',
        'confirm_title' => 'Confirmar reinstalación del servidor',
        'confirm_button' => 'Sí, reinstalar servidor',
        'confirm_body' => 'Tu servidor se detendrá y algunos archivos podrían eliminarse o modificarse durante este proceso. ¿Seguro que quieres continuar?',
        'success_message' => 'Tu servidor ha comenzado el proceso de reinstalación.',
    ],
];
