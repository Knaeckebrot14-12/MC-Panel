<?php

return [
    'title' => 'Mantenimiento',
    'subheading' => 'Anunciar un mantenimiento o bloquear el panel para todos salvo el equipo.',
    'saved' => 'Se guardó el modo mantenimiento.',
    'badge' => 'activo',
    'current' => 'Modo actual: :mode',
    'modes' => [
        'off' => 'Desactivado',
        'banner' => 'Banner',
        'lock' => 'Bloqueado',
    ],
    'descriptions' => [
        'off' => 'El panel funciona con normalidad.',
        'banner' => 'Todos pueden usar el panel; el mensaje aparece como banner en cada página.',
        'lock' => 'Solo los supporters, moderadores, admins y el propietario pueden usar el panel. Los demás ven el mensaje.',
    ],
    'message_label' => 'Mensaje',
    'message_description' => 'Se muestra en el banner, en la pantalla de mantenimiento y en la página de estado.',
    'info_heading' => 'Bueno saber',
    'info_servers' => 'Los servidores de juego siguen funcionando; los jugadores pueden seguir entrando.',
    'info_staff' => 'El equipo mantiene acceso completo y ve un banner rojo como recordatorio.',
    'info_status' => 'La página de estado pública también muestra el mensaje.',
];
