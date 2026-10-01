<?php

return [
    'title' => 'Sous-domaines',
    'subheading' => 'Les utilisateurs peuvent donner à leurs serveurs une adresse comme nom.play.example.com.',
    'settings_heading' => 'Cloudflare',
    'enabled_label' => 'Les utilisateurs peuvent créer des sous-domaines pour leurs serveurs',
    'token_label' => 'Jeton API Cloudflare',
    'token_saved' => 'Enregistré (masqué). Saisissez-en un nouveau pour le remplacer.',
    'token_description' => 'Cloudflare : Mon profil → Jetons API → Créer un jeton → modèle « Modifier le DNS de zone », limité à la ou les zones des domaines ci-dessous.',
    'domains_label' => 'Domaines',
    'domains_description' => 'Un par ligne, p. ex. play.example.com. Le domaine (ou son parent) doit être une zone de votre compte Cloudflare.',
    'save' => 'Enregistrer',
    'saved' => 'Paramètres des sous-domaines enregistrés.',
    'invalid_domains' => 'Domaine non valide : :domains',
    'status_heading' => 'Vérification',
    'zone_ok' => 'zone trouvée, le jeton fonctionne',
    'zone_missing' => 'zone introuvable ou pas d\'accès avec ce jeton',
    'no_check' => 'Enregistrez un jeton et au moins un domaine pour les vérifier.',
    'count' => ':count sous-domaine(s) utilisé(s).',
    'how_text' => 'Chaque sous-domaine reçoit un enregistrement A vers le node du serveur et, pour Minecraft, un enregistrement SRV avec le port, pour que les joueurs se connectent sans saisir de port. Les enregistrements sont supprimés avec le sous-domaine ou le serveur.',
];
