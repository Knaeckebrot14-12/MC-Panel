<?php

return [
    'nav' => [
        'general' => 'Allgemein',
        'mail' => 'E-Mail',
        'coins' => 'Coins',
        'advanced' => 'Erweitert',
        'updates' => 'Updates',
        'login' => 'Login & Registrierung',
        'design' => 'Design',
        'monitoring' => 'Monitoring',
        'abuse' => 'Missbrauchserkennung',
        'subdomains' => 'Subdomains',
        'roles' => 'Rollen',
        'iplockout' => 'IP-Sperre',
    ],
    'notice' => [
        'env_only' => 'Dein Panel ist derzeit so konfiguriert, dass Einstellungen nur aus der Umgebung gelesen werden. Du musst :env_var in deiner Umgebungsdatei setzen, um Einstellungen dynamisch zu laden.',
    ],
    'index' => [
        'title' => 'Einstellungen',
        'heading' => 'Panel-Einstellungen',
        'subheading' => 'Konfiguriere Pterodactyl nach deinen Wünschen.',
        'breadcrumb_settings' => 'Einstellungen',
        'panel_settings_heading' => 'Panel-Einstellungen',
        'company_name_label' => 'Firmenname',
        'company_name_description' => 'Dies ist der Name, der im gesamten Panel und in E-Mails an Kunden verwendet wird.',
        'require_2fa_label' => 'Zwei-Faktor-Authentifizierung erforderlich',
        'require_2fa_description' => 'Wenn aktiviert, muss jedes Konto der ausgewählten Gruppe die Zwei-Faktor-Authentifizierung aktiviert haben, um das Panel nutzen zu können.',
        '2fa_not_required' => 'Nicht erforderlich',
        '2fa_admin_only' => 'Nur Admins',
        '2fa_all_users' => 'Alle Benutzer',
        'default_language_label' => 'Standardsprache',
        'default_language_description' => 'Die Standardsprache, die beim Rendern von UI-Komponenten verwendet wird.',
    ],
];
