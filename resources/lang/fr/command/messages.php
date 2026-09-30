<?php

return [
    'location' => [
        'no_location_found' => 'Impossible de trouver un enregistrement correspondant au code court fourni.',
        'ask_short' => "Code court de l'emplacement",
        'ask_long' => "Description de l'emplacement",
        'created' => 'Nouvel emplacement (:name) créé avec succès avec l\'ID :id.',
        'deleted' => "L'emplacement demandé a été supprimé avec succès.",
    ],
    'user' => [
        'search_users' => "Saisissez un nom d'utilisateur, un ID utilisateur ou une adresse e-mail",
        'select_search_user' => "ID de l'utilisateur à supprimer (saisissez '0' pour relancer la recherche)",
        'deleted' => 'Utilisateur supprimé avec succès du panel.',
        'confirm_delete' => 'Voulez-vous vraiment supprimer cet utilisateur du panel ?',
        'no_users_found' => "Aucun utilisateur trouvé pour le terme de recherche fourni.",
        'multiple_found' => "Plusieurs comptes ont été trouvés pour l'utilisateur fourni, impossible de supprimer un utilisateur à cause de l'option --no-interaction.",
        'ask_admin' => 'Cet utilisateur est-il un administrateur ?',
        'ask_email' => 'Adresse e-mail',
        'ask_username' => "Nom d'utilisateur",
        'ask_name_first' => 'Prénom',
        'ask_name_last' => 'Nom',
        'ask_password' => 'Mot de passe',
        'ask_password_tip' => "Si vous souhaitez créer un compte avec un mot de passe aléatoire envoyé par e-mail à l'utilisateur, relancez cette commande (CTRL+C) en passant l'option `--no-password`.",
        'ask_password_help' => 'Les mots de passe doivent contenir au moins 8 caractères, dont au moins une majuscule et un chiffre.',
        '2fa_help_text' => [
            "Cette commande désactivera l'authentification à deux facteurs du compte d'un utilisateur si elle est activée. Elle ne doit être utilisée que comme commande de récupération de compte si l'utilisateur est bloqué hors de son compte.",
            "Si ce n'est pas ce que vous vouliez faire, appuyez sur CTRL+C pour quitter ce processus.",
        ],
        '2fa_disabled' => "L'authentification à deux facteurs a été désactivée pour :email.",
    ],
    'schedule' => [
        'output_line' => 'Envoi du job pour la première tâche de `:schedule` (:hash).',
    ],
    'maintenance' => [
        'deleting_service_backup' => 'Suppression du fichier de sauvegarde de service :file.',
    ],
    'server' => [
        'rebuild_failed' => 'La demande de reconstruction pour « :name » (#:id) sur le node « :node » a échoué avec l\'erreur : :message',
        'reinstall' => [
            'failed' => 'La demande de réinstallation pour « :name » (#:id) sur le node « :node » a échoué avec l\'erreur : :message',
            'confirm' => 'Vous êtes sur le point de réinstaller un groupe de serveurs. Voulez-vous continuer ?',
        ],
        'power' => [
            'confirm' => 'Vous êtes sur le point d\'effectuer une action :action sur :count serveurs. Voulez-vous continuer ?',
            'action_failed' => 'La demande d\'action d\'alimentation pour « :name » (#:id) sur le node « :node » a échoué avec l\'erreur : :message',
        ],
    ],
    'environment' => [
        'mail' => [
            'ask_smtp_host' => 'Hôte SMTP (p. ex. smtp.gmail.com)',
            'ask_smtp_port' => 'Port SMTP',
            'ask_smtp_username' => "Nom d'utilisateur SMTP",
            'ask_smtp_password' => 'Mot de passe SMTP',
            'ask_mailgun_domain' => 'Domaine Mailgun',
            'ask_mailgun_endpoint' => 'Point de terminaison Mailgun',
            'ask_mailgun_secret' => 'Secret Mailgun',
            'ask_mandrill_secret' => 'Secret Mandrill',
            'ask_postmark_username' => 'Clé API Postmark',
            'ask_driver' => "Quel pilote utiliser pour l'envoi des e-mails ?",
            'ask_mail_from' => "Adresse e-mail d'origine des e-mails",
            'ask_mail_name' => "Nom d'expéditeur des e-mails",
            'ask_encryption' => 'Méthode de chiffrement à utiliser',
        ],
    ],
];
