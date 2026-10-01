<?php

return [
    'title' => 'Version',
    'unsupported' => 'Bei dieser Server-Art kann die Minecraft-Version hier nicht gewechselt werden.',
    'current_title' => 'Aktuell installiert',
    'current_unknown' => 'Noch nicht über diese Seite installiert (es läuft die Version aus der Server-Einrichtung).',
    'install_title' => ':name installieren',
    'no_versions' => 'Gerade sind keine Versionen verfügbar.',
    'version_label' => 'Version',
    'install_button' => 'Installieren',
    'java_hint' => 'Braucht Java :java; das passende Java-Image wird automatisch eingestellt.',
    'installing' => 'Wird installiert … der Server wird gestoppt und die neue Version heruntergeladen. Das kann eine Minute dauern.',
    'confirm' => ':name :version installieren? Der Server wird gestoppt und seine Server-Jar ersetzt. Welten, Plugins und Einstellungen bleiben erhalten.',
    'confirm_downgrade' => 'Das ist eine ältere Version als die installierte. Minecraft-Welten lassen sich meist nicht mit älteren Versionen öffnen und können beschädigt werden. Mach vorher ein Backup! Trotzdem :name :version installieren?',
    'confirm_yes' => 'Ja, installieren',
    'confirm_no' => 'Abbrechen',
    'backup_hint' => 'Tipp: Erstelle ein Backup, bevor du zu einer anderen Software oder Version wechselst.',
    'installed' => ':name :version wurde installiert (Java :java). Starte den Server, um sie zu nutzen.',
    'types' => [
        'paper' => [
            'name' => 'Paper',
            'description' => 'Schnell, mit Plugins (Bukkit/Spigot)',
        ],
        'purpur' => [
            'name' => 'Purpur',
            'description' => 'Paper mit vielen Extra-Optionen',
        ],
        'folia' => [
            'name' => 'Folia',
            'description' => 'Paper für sehr große Server (Multithreading)',
        ],
        'fabric' => [
            'name' => 'Fabric',
            'description' => 'Leichter Mod-Loader',
        ],
        'vanilla' => [
            'name' => 'Vanilla',
            'description' => 'Der originale Server von Mojang',
        ],
        'velocity' => [
            'name' => 'Velocity',
            'description' => 'Proxy, der mehrere Server verbindet',
        ],
    ],
    'errors' => [
        'unsupported' => 'Dieser Server kann die Version nicht wechseln (sein Start nutzt keine Server-Jar).',
        'unknown_version' => 'Diese Version ist nicht verfügbar.',
        'unknown_type' => 'Unbekannte Server-Software.',
        'download' => 'Die neue Version konnte nicht heruntergeladen werden. Bitte versuch es später noch einmal.',
        'no_build' => 'Für diese Version gibt es noch keinen Download.',
        'still_running' => 'Der Server konnte nicht gestoppt werden. Stoppe ihn und versuch es noch einmal.',
        'api' => 'Die Versionsliste konnte nicht geladen werden. Bitte versuch es später noch einmal.',
        'busy' => 'Auf diesem Server wird gerade schon eine Version installiert.',
        'backup_full' => 'Das Backup-Limit ist erreicht, deshalb konnte vorher kein Backup erstellt werden. Lösche ein Backup oder wechsle ohne Backup.',
        'backup_throttled' => 'Zu viele Backups in kurzer Zeit. Warte einen Moment und versuch es noch einmal.',
        'backup_failed' => 'Das Backup ist fehlgeschlagen, deshalb wurde nichts geändert. Versuch es noch einmal oder wechsle ohne Backup.',
        'backup_timeout' => 'Das Backup dauert länger als erwartet und läuft weiter; es wurde nichts geändert. Versuch es noch einmal, wenn es fertig ist.',
    ],
    'backup_first' => 'Vorher ein Backup erstellen',
    'backup_full_note' => 'Die Backup-Liste ist voll. Lösche zuerst ein Backup oder wechsle ohne Backup.',
    'installing_backup' => 'Backup wird erstellt, danach wird installiert … der Server ist solange gestoppt. Das kann ein paar Minuten dauern.',
];
