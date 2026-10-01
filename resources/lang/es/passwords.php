<?php

return [
    'password' => 'Las contraseñas deben tener al menos seis caracteres y coincidir con la confirmación.',
    'reset' => '¡Tu contraseña ha sido restablecida!',
    'sent' => '¡Te hemos enviado por correo el enlace para restablecer tu contraseña!',
    'token' => 'Este token de restablecimiento de contraseña no es válido.',
    'user' => 'No encontramos ningún usuario con esa dirección de correo electrónico.',
    'generated_password_sent' => 'Si existe una cuenta con ese correo o nombre de usuario, le hemos enviado un correo con un enlace de confirmación.',
    'confirm_mail' => [
        'subject' => 'Confirma el restablecimiento de tu contraseña',
        'intro' => 'Alguien pidió una nueva contraseña para tu cuenta (:username).',
        'button' => 'Enviarme una nueva contraseña',
        'expires' => 'El enlace es válido durante 60 minutos. Tras hacer clic recibirás una nueva contraseña por correo.',
        'ignore' => 'Si no fuiste tú, ignora este correo; tu contraseña no cambia.',
    ],
    'confirm_page' => [
        'sent_title' => 'Nueva contraseña enviada',
        'sent_text' => 'Te hemos enviado una nueva contraseña por correo. Después de iniciar sesión elegirás tu propia contraseña.',
        'invalid_title' => 'Enlace no válido',
        'invalid_text' => 'Este enlace ha caducado o ya se usó. Solicita uno nuevo en la página de inicio de sesión.',
        'login' => 'Ir al inicio de sesión',
    ],
];
