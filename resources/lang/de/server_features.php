<?php

return [
    'gsl_token' => [
        'title' => 'Ungültiges GSL-Token!',
        'body_1' => 'Es sieht so aus, als wäre dein Gameserver-Login-Token (GSL-Token) ungültig oder abgelaufen.',
        'body_2' => 'Du kannst entweder ein neues generieren und unten eingeben, oder das Feld leer lassen, um es vollständig zu entfernen.',
        'field_label' => 'GSL-Token',
        'field_description' => 'Besuche https://steamcommunity.com/dev/managegameservers, um ein Token zu generieren.',
        'update_button' => 'GSL-Token aktualisieren',
    ],
    'hytale_oauth' => [
        'title' => 'Authentifizierung erforderlich',
        'body' => 'Du musst dich mit deinem Hytale-Konto authentifizieren, um Serverdateien herunterzuladen oder zu aktualisieren. Bitte melde dich an, um fortzufahren.',
        'cancel_button' => 'Abbrechen',
        'login_button' => 'Anmelden',
    ],
    'pid_limit' => [
        'admin_title' => 'Arbeitsspeicher- oder Prozesslimit erreicht...',
        'admin_body_1' => 'Dieser Server hat das maximale Prozess- oder Arbeitsspeicherlimit erreicht.',
        'admin_body_2' => 'Das Erhöhen von <0>container_pid_limit</0> in der Wings-Konfiguration <1>config.yml</1> könnte helfen, dieses Problem zu beheben.',
        'admin_note' => 'Hinweis: Wings muss neu gestartet werden, damit die Änderungen an der Konfigurationsdatei wirksam werden',
        'user_title' => 'Möglicherweise wurde ein Ressourcenlimit erreicht...',
        'user_body' => 'Dieser Server versucht, mehr Ressourcen zu nutzen, als zugewiesen wurden. Bitte wende dich an den Administrator und teile ihm den untenstehenden Fehler mit.',
        'close_button' => 'Schließen',
    ],
    'steam_disk_space' => [
        'title' => 'Kein verfügbarer Speicherplatz mehr...',
        'admin_body_1' => 'Diesem Server ist der verfügbare Speicherplatz ausgegangen, sodass die Installation oder Aktualisierung nicht abgeschlossen werden kann.',
        'admin_body_2' => 'Stelle sicher, dass genügend Speicherplatz vorhanden ist, indem du <0>df -h</0> auf dem Rechner ausführst, auf dem dieser Server läuft. Lösche Dateien oder erweitere den verfügbaren Speicherplatz, um das Problem zu beheben.',
        'user_body' => 'Diesem Server ist der verfügbare Speicherplatz ausgegangen, sodass die Installation oder Aktualisierung nicht abgeschlossen werden kann. Bitte wende dich an die Administratoren und informiere sie über das Speicherplatzproblem.',
        'close_button' => 'Schließen',
    ],
    'eula' => [
        'title' => 'Minecraft®-EULA akzeptieren',
        'body_prefix' => 'Indem du unten auf „Ich akzeptiere“ klickst, erklärst du dich mit der',
        'body_suffix' => ' einverstanden.',
        'link_text' => 'Minecraft®-EULA',
        'cancel_button' => 'Abbrechen',
        'accept_button' => 'Ich akzeptiere',
    ],
    'java_version' => [
        'title' => 'Nicht unterstützte Java-Version',
        'body' => 'Dieser Server verwendet derzeit eine nicht unterstützte Java-Version und kann nicht gestartet werden.',
        'body_select_notice' => ' Bitte wähle unten eine unterstützte Version aus der Liste aus, um den Server weiter zu starten.',
        'cancel_button' => 'Abbrechen',
        'update_button' => 'Docker-Image aktualisieren',
    ],
];
