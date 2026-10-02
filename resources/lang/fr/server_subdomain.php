<?php

return [
    'title' => 'Sous-domaine',
    'current' => 'Les joueurs peuvent se connecter avec :',
    'name_placeholder' => 'monserveur',
    'change' => 'Modifier',
    'create' => 'Créer',
    'remove' => 'Supprimer',
    'hint' => '3 à 32 caractères : lettres minuscules, chiffres et tirets. L\'adresse peut mettre quelques minutes à fonctionner partout.',
    'saved' => ':fqdn pointe maintenant vers ce serveur.',
    'errors' => [
        'disabled' => 'Les sous-domaines ne sont pas disponibles.',
        'not_minecraft' => 'Les sous-domaines ne sont disponibles que pour les serveurs Minecraft.',
        'invalid_name' => 'Ce nom n\'est pas autorisé. Utilisez 3 à 32 lettres minuscules, chiffres ou tirets.',
        'invalid_domain' => 'Ce domaine n\'est pas disponible.',
        'taken' => 'Ce sous-domaine est déjà pris.',
        'busy' => 'Ce sous-domaine est en cours de modification. Réessayez dans un instant.',
        'no_ip' => 'L\'adresse publique de ce serveur n\'a pas pu être déterminée.',
        'cloudflare' => 'L\'enregistrement DNS n\'a pas pu être créé : :error',
        'no_zone' => 'Le domaine :domain n\'est pas configuré correctement. Contactez le support.',
    ],
];
