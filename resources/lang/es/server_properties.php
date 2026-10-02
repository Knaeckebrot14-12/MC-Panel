<?php

return [
    'title' => 'Ajustes del servidor',
    'missing' => 'Este servidor aún no tiene server.properties. Arráncalo una vez o guarda aquí para crear el archivo.',
    'saved' => 'Ajustes guardados. Se aplican en el próximo arranque.',
    'saved_restart' => 'Ajustes guardados. Reinicia el servidor para aplicarlos.',
    'unsaved' => ':count cambios sin guardar',
    'reset' => 'Descartar',
    'save' => 'Guardar',
    'locked' => 'El puerto, la IP y RCON los gestiona el panel y no se pueden cambiar aquí.',
    'groups' => [
        'general' => 'General',
        'world' => 'Mundo',
        'access' => 'Acceso y seguridad',
        'resource_pack' => 'Paquete de recursos',
        'other' => 'Otros ajustes',
    ],
    'options' => [
        'survival' => 'Supervivencia',
        'creative' => 'Creativo',
        'adventure' => 'Aventura',
        'spectator' => 'Espectador',
        'peaceful' => 'Pacífico',
        'easy' => 'Fácil',
        'normal' => 'Normal',
        'hard' => 'Difícil',
        'flat' => 'Extraplano',
        'large_biomes' => 'Biomas grandes',
        'amplified' => 'Amplificado',
    ],
    'server_list' => [
        'title' => 'Lista de servidores',
        'line1' => 'Primera línea',
        'line2' => 'Segunda línea (opcional)',
        'codes_hint' => 'Los códigos de color y formato empiezan por & (p. ej. &a verde, &l negrita, &r restablecer). Haz clic en un color para insertarlo en el cursor. La vista previa muestra lo que ven los jugadores.',
        'icon_title' => 'Icono del servidor',
        'icon_upload' => 'Subir icono',
        'icon_remove' => 'Quitar icono',
        'icon_hint' => 'PNG, JPG, GIF o WebP. Se recorta en cuadrado y se reduce a 64×64 automáticamente. Se aplica tras reiniciar.',
        'icon_saved' => 'Icono guardado. Aparece tras el próximo reinicio.',
        'icon_invalid' => 'Este archivo no es una imagen legible.',
        'no_icon' => 'Sin icono',
        'format' => [
            'l' => 'Negrita',
            'o' => 'Cursiva',
            'n' => 'Subrayado',
            'm' => 'Tachado',
            'k' => 'Mágico',
            'r' => 'Restablecer',
        ],
    ],
    'fields' => [
        'motd' => [
            'label' => 'Descripción del servidor (MOTD)',
            'description' => 'Se muestra en la lista de servidores multijugador.',
        ],
        'max-players' => [
            'label' => 'Jugadores máximos',
            'description' => 'Cuántos jugadores pueden estar en línea a la vez.',
        ],
        'gamemode' => [
            'label' => 'Modo de juego',
            'description' => 'Modo de juego para jugadores nuevos.',
        ],
        'difficulty' => [
            'label' => 'Dificultad',
            'description' => 'Lo peligrosos que son los monstruos y el hambre.',
        ],
        'hardcore' => [
            'label' => 'Extremo',
            'description' => 'Los jugadores son baneados tras morir una vez.',
        ],
        'pvp' => [
            'label' => 'PvP',
            'description' => 'Los jugadores pueden hacerse daño entre sí.',
        ],
        'force-gamemode' => [
            'label' => 'Forzar modo de juego',
            'description' => 'Los jugadores siempre entran en el modo de juego predeterminado.',
        ],
        'allow-flight' => [
            'label' => 'Permitir volar',
            'description' => 'Lo necesitan algunos plugins y mods; si no, los jugadores que vuelan son expulsados.',
        ],
        'level-name' => [
            'label' => 'Carpeta del mundo',
            'description' => 'Nombre de la carpeta del mundo que se carga o crea.',
        ],
        'level-seed' => [
            'label' => 'Semilla',
            'description' => 'Semilla para mundos nuevos; vacía = aleatoria.',
        ],
        'level-type' => [
            'label' => 'Tipo de mundo',
            'description' => 'Solo se usa al generar un mundo nuevo.',
        ],
        'allow-nether' => [
            'label' => 'Nether',
            'description' => 'Los jugadores pueden viajar al Nether.',
        ],
        'generate-structures' => [
            'label' => 'Estructuras',
            'description' => 'Se generan aldeas, templos y otras estructuras.',
        ],
        'spawn-monsters' => [
            'label' => 'Monstruos',
            'description' => 'Aparecen criaturas hostiles.',
        ],
        'spawn-npcs' => [
            'label' => 'Aldeanos',
            'description' => 'Aparecen aldeanos.',
        ],
        'spawn-protection' => [
            'label' => 'Protección del spawn',
            'description' => 'Radio alrededor del spawn en el que solo los operadores pueden construir (0 = desactivado).',
        ],
        'view-distance' => [
            'label' => 'Distancia de visión',
            'description' => 'Chunks enviados a los jugadores. Valores bajos ahorran RAM y CPU.',
        ],
        'simulation-distance' => [
            'label' => 'Distancia de simulación',
            'description' => 'Chunks alrededor de los jugadores en los que las cosas se mueven y crecen.',
        ],
        'max-world-size' => [
            'label' => 'Borde del mundo',
            'description' => 'Radio máximo del mundo en bloques.',
        ],
        'white-list' => [
            'label' => 'Lista blanca',
            'description' => 'Solo los jugadores de la lista blanca pueden entrar.',
        ],
        'enforce-whitelist' => [
            'label' => 'Aplicar lista blanca',
            'description' => 'Expulsa a jugadores en línea que se quitan de la lista blanca.',
        ],
        'online-mode' => [
            'label' => 'Modo en línea',
            'description' => 'Comprueba las cuentas con Mojang. Desactívalo solo detrás de un proxy como Velocity o BungeeCord.',
        ],
        'enforce-secure-profile' => [
            'label' => 'Perfil de chat seguro',
            'description' => 'Los jugadores necesitan claves de chat firmadas por Mojang.',
        ],
        'enable-command-block' => [
            'label' => 'Bloques de comandos',
            'description' => 'Se pueden usar bloques de comandos.',
        ],
        'op-permission-level' => [
            'label' => 'Nivel de operador',
            'description' => 'Nivel de permisos de los operadores (1-4).',
        ],
        'player-idle-timeout' => [
            'label' => 'Expulsión por inactividad (minutos)',
            'description' => 'Expulsa a jugadores inactivos tras estos minutos (0 = nunca).',
        ],
        'resource-pack' => [
            'label' => 'URL del paquete de recursos',
            'description' => 'Enlace de descarga directa a un paquete de recursos (zip).',
        ],
        'require-resource-pack' => [
            'label' => 'Exigir paquete de recursos',
            'description' => 'Los jugadores que rechazan el paquete son desconectados.',
        ],
    ],
];
