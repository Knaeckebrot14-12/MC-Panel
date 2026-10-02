<?php

return [
    'title' => 'Subdominio',
    'current' => 'Los jugadores pueden entrar con:',
    'name_placeholder' => 'miservidor',
    'change' => 'Cambiar',
    'create' => 'Crear',
    'remove' => 'Eliminar',
    'hint' => '3–32 caracteres: minúsculas, números y guiones. La dirección puede tardar unos minutos en funcionar en todas partes.',
    'saved' => ':fqdn ahora apunta a este servidor.',
    'errors' => [
        'disabled' => 'Los subdominios no están disponibles.',
        'not_minecraft' => 'Los subdominios solo están disponibles para servidores de Minecraft.',
        'invalid_name' => 'Este nombre no está permitido. Usa 3–32 minúsculas, números o guiones.',
        'invalid_domain' => 'Este dominio no está disponible.',
        'taken' => 'Este subdominio ya está en uso.',
        'no_ip' => 'No se pudo determinar la dirección pública de este servidor.',
        'cloudflare' => 'No se pudo crear el registro DNS: :error',
        'no_zone' => 'El dominio :domain no está configurado correctamente. Contacta con soporte.',
    ],
];
