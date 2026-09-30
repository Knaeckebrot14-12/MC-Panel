<?php

return [
    'nav' => [
        'general' => 'Général',
        'mail' => 'E-mail',
        'coins' => 'Coins',
        'advanced' => 'Avancé',
        'updates' => 'Mises à jour',
        'login' => 'Connexion et inscription',
    ],
    'notice' => [
        'env_only' => 'Votre panel est actuellement configuré pour lire les paramètres uniquement depuis l\'environnement. Vous devrez définir :env_var dans votre fichier d\'environnement pour charger les paramètres dynamiquement.',
    ],
    'index' => [
        'title' => 'Paramètres',
        'heading' => 'Paramètres du panel',
        'subheading' => 'Configurez Pterodactyl à votre goût.',
        'breadcrumb_settings' => 'Paramètres',
        'panel_settings_heading' => 'Paramètres du panel',
        'company_name_label' => 'Nom de l\'entreprise',
        'company_name_description' => 'C\'est le nom utilisé dans tout le panel et dans les e-mails envoyés aux clients.',
        'require_2fa_label' => 'Exiger l\'authentification à deux facteurs',
        'require_2fa_description' => 'Si activé, tout compte appartenant au groupe sélectionné devra avoir l\'authentification à deux facteurs activée pour utiliser le panel.',
        '2fa_not_required' => 'Non exigée',
        '2fa_admin_only' => 'Admins uniquement',
        '2fa_all_users' => 'Tous les utilisateurs',
        'default_language_label' => 'Langue par défaut',
        'default_language_description' => 'La langue par défaut à utiliser pour l\'affichage des composants de l\'interface.',
    ],
];
