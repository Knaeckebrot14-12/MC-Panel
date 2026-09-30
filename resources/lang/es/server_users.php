<?php

return [
    'title' => 'Usuarios',
    'empty' => 'Parece que no tienes ningún subusuario.',
    'new_user_button' => 'Nuevo usuario',
    'row' => [
        'two_factor_label' => '2FA activada',
        'permissions_label' => 'Permisos',
        'edit_aria' => 'Editar subusuario',
    ],
    'edit_modal' => [
        'title_modify' => 'Modificar permisos de :email',
        'title_view' => 'Ver permisos de :email',
        'title_create' => 'Crear nuevo subusuario',
        'save_button' => 'Guardar',
        'invite_button' => 'Invitar usuario',
        'permission_notice' => 'Solo se pueden seleccionar los permisos que tu cuenta tiene asignados actualmente al crear o modificar a otros usuarios.',
        'email_label' => 'Correo del usuario',
        'email_description' => 'Introduce la dirección de correo electrónico del usuario al que quieres invitar como subusuario de este servidor.',
        'validation' => [
            'email_max' => 'Las direcciones de correo electrónico no deben superar los 191 caracteres.',
            'email_invalid' => 'Debes indicar una dirección de correo electrónico válida.',
        ],
    ],
    'remove' => [
        'title' => '¿Eliminar este subusuario?',
        'confirm_button' => 'Sí, eliminar subusuario',
        'body' => '¿Seguro que quieres eliminar este subusuario? Se le revocará de inmediato todo el acceso a este servidor.',
        'delete_aria' => 'Eliminar subusuario',
    ],
];
