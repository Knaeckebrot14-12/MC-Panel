<?php

return [
    'exceptions' => [
        'no_new_default_allocation' => 'Estás intentando eliminar la asignación predeterminada de este servidor, pero no hay ninguna asignación alternativa que usar.',
        'marked_as_failed' => 'Este servidor fue marcado como que falló en una instalación anterior. El estado actual no se puede cambiar en este estado.',
        'skipping_install_script' => 'Este servidor está configurado para omitir el script de instalación de su egg. La reinstalación no está disponible hasta que se desactive ese ajuste.',
        'bad_variable' => 'Hubo un error de validación con la variable :name.',
        'daemon_exception' => 'Se produjo una excepción al intentar comunicarse con el daemon, con el código de respuesta HTTP/:code. Esta excepción se ha registrado. (ID de solicitud: :request_id)',
        'default_allocation_not_found' => 'La asignación predeterminada solicitada no se encontró entre las asignaciones de este servidor.',
    ],
    'alerts' => [
        'startup_changed' => 'La configuración de inicio de este servidor se ha actualizado. Si se cambió el nest o el egg de este servidor, ahora se realizará una reinstalación.',
        'server_deleted' => 'El servidor se ha eliminado correctamente del sistema.',
        'server_created' => 'El servidor se creó correctamente en el panel. Concede unos minutos al daemon para instalar por completo este servidor.',
        'build_updated' => 'Los detalles de configuración de este servidor se han actualizado. Algunos cambios pueden requerir un reinicio para surtir efecto.',
        'suspension_toggled' => 'El estado de suspensión del servidor se ha cambiado a :status.',
        'rebuild_on_boot' => 'Este servidor ha sido marcado como que requiere reconstruir su contenedor de Docker. Ocurrirá la próxima vez que se inicie el servidor.',
        'install_toggled' => 'Se ha alternado el estado de instalación de este servidor.',
        'server_reinstalled' => 'Este servidor se ha puesto en cola para una reinstalación que comienza ahora.',
        'details_updated' => 'Los detalles del servidor se han actualizado correctamente.',
        'docker_image_updated' => 'Se cambió correctamente la imagen de Docker predeterminada de este servidor. Se necesita un reinicio para aplicar este cambio.',
        'node_required' => 'Debes tener al menos un nodo configurado antes de poder añadir un servidor a este panel.',
        'transfer_nodes_required' => 'Debes tener al menos dos nodos configurados antes de poder transferir servidores.',
        'transfer_started' => 'Se ha iniciado la transferencia del servidor.',
        'transfer_not_viable' => 'El nodo que seleccionaste no tiene el espacio de disco o la memoria necesarios para alojar este servidor.',
    ],
];
