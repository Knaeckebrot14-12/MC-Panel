<?php

return [
    'title' => 'Versión',
    'unsupported' => 'Este tipo de servidor no puede cambiar su versión de Minecraft aquí.',
    'current_title' => 'Instalado ahora',
    'current_unknown' => 'Aún no instalado desde esta página (se usa la versión de la configuración del servidor).',
    'install_title' => 'Instalar :name',
    'no_versions' => 'No hay versiones disponibles en este momento.',
    'version_label' => 'Versión',
    'install_button' => 'Instalar',
    'java_hint' => 'Necesita Java :java; la imagen de Java adecuada se elige automáticamente.',
    'installing' => 'Instalando… el servidor se detiene y se descarga la nueva versión. Puede tardar un minuto.',
    'confirm' => '¿Instalar :name :version? El servidor se detendrá y se reemplazará su jar. Mundos, plugins y ajustes se conservan.',
    'confirm_downgrade' => 'Esta versión es más antigua que la instalada. Los mundos de Minecraft normalmente no se pueden abrir con versiones anteriores y pueden dañarse. ¡Haz una copia de seguridad primero! ¿Instalar :name :version de todos modos?',
    'confirm_yes' => 'Sí, instalar',
    'confirm_no' => 'Cancelar',
    'backup_hint' => 'Consejo: crea una copia de seguridad antes de cambiar de software o de versión.',
    'installed' => ':name :version se instaló (Java :java). Inicia el servidor para usarlo.',
    'types' => [
        'paper' => [
            'name' => 'Paper',
            'description' => 'Rápido, con plugins (Bukkit/Spigot)',
        ],
        'purpur' => [
            'name' => 'Purpur',
            'description' => 'Paper con muchas opciones extra',
        ],
        'folia' => [
            'name' => 'Folia',
            'description' => 'Paper para servidores muy grandes (multihilo)',
        ],
        'fabric' => [
            'name' => 'Fabric',
            'description' => 'Cargador de mods ligero',
        ],
        'vanilla' => [
            'name' => 'Vanilla',
            'description' => 'El servidor original de Mojang',
        ],
        'velocity' => [
            'name' => 'Velocity',
            'description' => 'Proxy que conecta varios servidores',
        ],
    ],
    'errors' => [
        'unsupported' => 'Este servidor no puede cambiar de versión (su inicio no usa un jar).',
        'unknown_version' => 'Esta versión no está disponible.',
        'unknown_type' => 'Software de servidor desconocido.',
        'download' => 'No se pudo descargar la nueva versión. Inténtalo más tarde.',
        'no_build' => 'Aún no hay descarga para esta versión.',
        'still_running' => 'No se pudo detener el servidor. Detenlo e inténtalo de nuevo.',
        'api' => 'No se pudo cargar la lista de versiones. Inténtalo más tarde.',
        'busy' => 'Ya se está instalando una versión en este servidor.',
        'backup_full' => 'Se alcanzó el límite de copias, así que no se pudo hacer una copia antes. Elimina una copia o cambia sin copia.',
        'backup_throttled' => 'Demasiadas copias en poco tiempo. Espera un momento e inténtalo de nuevo.',
        'backup_failed' => 'La copia falló, así que no se cambió nada. Inténtalo de nuevo o cambia sin copia.',
        'backup_timeout' => 'La copia tarda más de lo esperado y sigue en curso; no se cambió nada. Inténtalo de nuevo cuando termine.',
    ],
    'backup_first' => 'Crear primero una copia de seguridad',
    'backup_full_note' => 'La lista de copias está llena. Elimina una copia primero o cambia sin copia.',
    'installing_backup' => 'Creando la copia y luego instalando… el servidor se detiene mientras tanto. Puede tardar unos minutos.',
];
