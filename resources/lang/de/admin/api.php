<?php

return [
    'title' => 'Anwendungs-API',
    'index' => [
        'heading' => 'Anwendungs-API',
        'subheading' => 'Verwalte die Zugangsdaten zur Steuerung dieses Panels über die API.',
        'list_heading' => 'Liste der Zugangsdaten',
        'create_new_button' => 'Neu erstellen',
        'table' => [
            'key' => 'Schlüssel',
            'memo' => 'Notiz',
            'last_used' => 'Zuletzt verwendet',
            'created' => 'Erstellt',
            'created_by' => 'Erstellt von',
        ],
        'js' => [
            'revoke_title' => 'API-Schlüssel widerrufen',
            'revoke_text' => 'Sobald dieser API-Schlüssel widerrufen wird, funktionieren Anwendungen, die ihn derzeit verwenden, nicht mehr.',
            'revoke_confirm_button' => 'Widerrufen',
            'revoke_success_text' => 'Der API-Schlüssel wurde widerrufen.',
            'revoke_error_title' => 'Hoppla!',
            'revoke_error_text' => 'Beim Widerrufen dieses Schlüssels ist ein Fehler aufgetreten.',
        ],
    ],
    'new' => [
        'subheading' => 'Einen neuen Anwendungs-API-Schlüssel erstellen.',
        'breadcrumb_new' => 'Neue Zugangsdaten',
        'select_permissions_heading' => 'Berechtigungen auswählen',
        'read_all_button' => 'Alle lesen',
        'read_write_all_button' => 'Alle lesen & schreiben',
        'none_all_button' => 'Alle keine',
        'read_label' => 'Lesen',
        'read_write_label' => 'Lesen & Schreiben',
        'none_label' => 'Keine',
        'description_label' => 'Beschreibung',
        'notice' => 'Sobald du Berechtigungen zugewiesen und diese Zugangsdaten erstellt hast, kannst du sie nicht mehr nachträglich bearbeiten. Falls du später Änderungen vornehmen musst, musst du neue Zugangsdaten erstellen.',
        'create_button' => 'Zugangsdaten erstellen',
    ],
];
