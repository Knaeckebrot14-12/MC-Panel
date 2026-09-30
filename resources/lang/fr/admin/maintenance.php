<?php

return [
    'title' => 'Maintenance',
    'subheading' => 'Annoncer une maintenance ou verrouiller le panel pour tous sauf l\'équipe.',
    'saved' => 'Le mode maintenance a été enregistré.',
    'badge' => 'actif',
    'current' => 'Mode actuel : :mode',
    'modes' => [
        'off' => 'Désactivé',
        'banner' => 'Bannière',
        'lock' => 'Verrouillé',
    ],
    'descriptions' => [
        'off' => 'Le panel fonctionne normalement.',
        'banner' => 'Tout le monde peut utiliser le panel ; le message s\'affiche en bannière sur chaque page.',
        'lock' => 'Seuls les supporters, modérateurs, admins et le propriétaire peuvent utiliser le panel. Les autres voient le message.',
    ],
    'message_label' => 'Message',
    'message_description' => 'Affiché dans la bannière, sur l\'écran de maintenance et sur la page de statut.',
    'info_heading' => 'Bon à savoir',
    'info_servers' => 'Les serveurs de jeu continuent de tourner ; les joueurs peuvent toujours les rejoindre.',
    'info_staff' => 'L\'équipe garde un accès complet et voit une bannière rouge pour rappel.',
    'info_status' => 'La page de statut publique affiche aussi le message.',
];
