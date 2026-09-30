<?php

return [
    'validation' => [
        'fqdn_not_resolvable' => 'Der angegebene FQDN oder die IP-Adresse lässt sich nicht zu einer gültigen IP-Adresse auflösen.',
        'fqdn_required_for_ssl' => 'Für die Nutzung von SSL auf dieser Node ist ein vollqualifizierter Domainname erforderlich, der zu einer öffentlichen IP-Adresse aufgelöst werden kann.',
    ],
    'notices' => [
        'allocations_added' => 'Die Allokationen wurden erfolgreich zu dieser Node hinzugefügt.',
        'node_deleted' => 'Die Node wurde erfolgreich aus dem Panel entfernt.',
        'location_required' => 'Du musst mindestens einen Standort konfiguriert haben, bevor du eine Node zu diesem Panel hinzufügen kannst.',
        'node_created' => 'Neue Node erfolgreich erstellt. Du kannst den Daemon auf dieser Maschine automatisch konfigurieren, indem du den Tab "Konfiguration" besuchst. Bevor du Server hinzufügen kannst, musst du zunächst mindestens eine IP-Adresse und einen Port zuweisen.',
        'node_updated' => 'Die Node-Informationen wurden aktualisiert. Falls Daemon-Einstellungen geändert wurden, musst du ihn neu starten, damit die Änderungen wirksam werden.',
        'unallocated_deleted' => 'Alle nicht zugewiesenen Ports für <code>:ip</code> wurden gelöscht.',
    ],
];
