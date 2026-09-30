<?php

return [
    'exceptions' => [
        'no_new_default_allocation' => 'Du versuchst, die Standard-Allokation für diesen Server zu löschen, es gibt jedoch keine Ausweich-Allokation.',
        'marked_as_failed' => 'Dieser Server wurde als fehlgeschlagen bei einer vorherigen Installation markiert. Der aktuelle Status kann in diesem Zustand nicht geändert werden.',
        'skipping_install_script' => 'Dieser Server ist so konfiguriert, dass das Installationsskript seines Eggs übersprungen wird. Eine Neuinstallation ist erst möglich, wenn diese Einstellung deaktiviert wird.',
        'bad_variable' => 'Bei der Variable :name ist ein Validierungsfehler aufgetreten.',
        'daemon_exception' => 'Bei der Kommunikation mit dem Daemon ist ein Fehler aufgetreten, der zu einem HTTP/:code-Statuscode geführt hat. Dieser Fehler wurde protokolliert. (Anfrage-ID: :request_id)',
        'default_allocation_not_found' => 'Die angeforderte Standard-Allokation wurde in den Allokationen dieses Servers nicht gefunden.',
    ],
    'alerts' => [
        'startup_changed' => 'Die Startkonfiguration dieses Servers wurde aktualisiert. Falls das Nest oder Egg dieses Servers geändert wurde, erfolgt jetzt eine Neuinstallation.',
        'server_deleted' => 'Der Server wurde erfolgreich aus dem System gelöscht.',
        'server_created' => 'Der Server wurde erfolgreich im Panel erstellt. Bitte gib dem Daemon ein paar Minuten Zeit, um diesen Server vollständig zu installieren.',
        'build_updated' => 'Die Build-Details dieses Servers wurden aktualisiert. Einige Änderungen erfordern möglicherweise einen Neustart, um wirksam zu werden.',
        'suspension_toggled' => 'Der Sperrstatus des Servers wurde auf :status geändert.',
        'rebuild_on_boot' => 'Dieser Server wurde für einen Neuaufbau des Docker-Containers markiert. Das geschieht beim nächsten Start des Servers.',
        'install_toggled' => 'Der Installationsstatus dieses Servers wurde geändert.',
        'server_reinstalled' => 'Für diesen Server wurde ab sofort eine Neuinstallation eingeplant.',
        'details_updated' => 'Die Server-Details wurden erfolgreich aktualisiert.',
        'docker_image_updated' => 'Das Standard-Docker-Image für diesen Server wurde erfolgreich geändert. Ein Neustart ist erforderlich, damit die Änderung wirksam wird.',
        'node_required' => 'Du musst mindestens eine Node konfiguriert haben, bevor du einen Server zu diesem Panel hinzufügen kannst.',
        'transfer_nodes_required' => 'Du musst mindestens zwei Nodes konfiguriert haben, bevor du Server übertragen kannst.',
        'transfer_started' => 'Die Serverübertragung wurde gestartet.',
        'transfer_not_viable' => 'Die ausgewählte Node verfügt nicht über genügend freien Speicherplatz oder Arbeitsspeicher für diesen Server.',
    ],
];
