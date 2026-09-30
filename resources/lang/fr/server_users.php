<?php

return [
    'title' => 'Utilisateurs',
    'empty' => "Il semble que vous n'ayez aucun sous-utilisateur.",
    'new_user_button' => 'Nouvel utilisateur',
    'row' => [
        'two_factor_label' => '2FA activée',
        'permissions_label' => 'Permissions',
        'edit_aria' => 'Modifier le sous-utilisateur',
    ],
    'edit_modal' => [
        'title_modify' => 'Modifier les permissions de :email',
        'title_view' => 'Voir les permissions de :email',
        'title_create' => 'Créer un nouveau sous-utilisateur',
        'save_button' => 'Enregistrer',
        'invite_button' => "Inviter l'utilisateur",
        'permission_notice' => "Seules les permissions actuellement attribuées à votre compte peuvent être sélectionnées lors de la création ou de la modification d'autres utilisateurs.",
        'email_label' => "E-mail de l'utilisateur",
        'email_description' => "Saisissez l'adresse e-mail de l'utilisateur que vous souhaitez inviter comme sous-utilisateur de ce serveur.",
        'validation' => [
            'email_max' => 'Les adresses e-mail ne doivent pas dépasser 191 caractères.',
            'email_invalid' => 'Une adresse e-mail valide doit être fournie.',
        ],
    ],
    'remove' => [
        'title' => 'Supprimer ce sous-utilisateur ?',
        'confirm_button' => 'Oui, supprimer le sous-utilisateur',
        'body' => "Voulez-vous vraiment supprimer ce sous-utilisateur ? Tous ses accès à ce serveur seront révoqués immédiatement.",
        'delete_aria' => 'Supprimer le sous-utilisateur',
    ],
];
