<?php

return [
    'title' => 'Subdominios',
    'subheading' => 'Los usuarios pueden dar a sus servidores una dirección como nombre.play.example.com.',
    'settings_heading' => 'Cloudflare',
    'enabled_label' => 'Los usuarios pueden crear subdominios para sus servidores',
    'token_label' => 'Token de API de Cloudflare',
    'token_saved' => 'Guardado (oculto). Introduce uno nuevo para reemplazarlo.',
    'token_description' => 'Cloudflare: Mi perfil → Tokens de API → Crear token → plantilla «Editar DNS de zona», limitado a la(s) zona(s) de los dominios de abajo.',
    'domains_label' => 'Dominios',
    'domains_description' => 'Uno por línea, p. ej. play.example.com. El dominio (o su dominio superior) debe ser una zona de tu cuenta de Cloudflare.',
    'save' => 'Guardar',
    'saved' => 'Ajustes de subdominios guardados.',
    'invalid_domains' => 'Dominio no válido: :domains',
    'status_heading' => 'Comprobación',
    'zone_ok' => 'zona encontrada, el token funciona',
    'zone_missing' => 'zona no encontrada o sin acceso con este token',
    'no_check' => 'Guarda un token y al menos un dominio para comprobarlos.',
    'count' => ':count subdominio(s) en uso.',
    'how_text' => 'Cada subdominio recibe un registro A hacia el node del servidor y, para Minecraft, un registro SRV con el puerto, para que los jugadores entren sin escribir el puerto. Los registros se eliminan al borrar el subdominio o el servidor.',
];
