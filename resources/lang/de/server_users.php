<?php

return [
    'title' => 'Benutzer',
    'empty' => 'Es sieht so aus, als hättest du keine Unterbenutzer.',
    'new_user_button' => 'Neuer Benutzer',
    'row' => [
        'two_factor_label' => '2FA aktiviert',
        'permissions_label' => 'Berechtigungen',
        'edit_aria' => 'Unterbenutzer bearbeiten',
    ],
    'edit_modal' => [
        'title_modify' => 'Berechtigungen für :email bearbeiten',
        'title_view' => 'Berechtigungen für :email ansehen',
        'title_create' => 'Neuen Unterbenutzer erstellen',
        'save_button' => 'Speichern',
        'invite_button' => 'Benutzer einladen',
        'permission_notice' => 'Beim Erstellen oder Bearbeiten anderer Benutzer können nur Berechtigungen ausgewählt werden, die deinem Konto derzeit zugewiesen sind.',
        'email_label' => 'E-Mail-Adresse',
        'email_description' => 'Gib die E-Mail-Adresse des Benutzers ein, den du als Unterbenutzer für diesen Server einladen möchtest.',
        'validation' => [
            'email_max' => 'Die E-Mail-Adresse darf 191 Zeichen nicht überschreiten.',
            'email_invalid' => 'Es muss eine gültige E-Mail-Adresse angegeben werden.',
        ],
    ],
    'remove' => [
        'title' => 'Diesen Unterbenutzer löschen?',
        'confirm_button' => 'Ja, Unterbenutzer entfernen',
        'body' => 'Bist du sicher, dass du diesen Unterbenutzer entfernen möchtest? Ihm wird sofort jeglicher Zugriff auf diesen Server entzogen.',
        'delete_aria' => 'Unterbenutzer löschen',
    ],
];
