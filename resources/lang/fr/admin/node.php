<?php

return [
    'validation' => [
        'fqdn_not_resolvable' => "Le FQDN ou l'adresse IP fourni ne correspond à aucune adresse IP valide.",
        'fqdn_required_for_ssl' => "Un nom de domaine complet qui résout vers une adresse IP publique est requis pour utiliser SSL sur ce node.",
    ],
    'notices' => [
        'allocations_added' => 'Les allocations ont été ajoutées avec succès à ce node.',
        'node_deleted' => 'Le node a été supprimé du panel avec succès.',
        'location_required' => 'Vous devez avoir configuré au moins un emplacement avant de pouvoir ajouter un node à ce panel.',
        'node_created' => "Nouveau node créé avec succès. Vous pouvez configurer automatiquement le daemon sur cette machine en vous rendant dans l'onglet « Configuration ». Avant de pouvoir ajouter des serveurs, vous devez d'abord allouer au moins une adresse IP et un port.",
        'node_updated' => "Les informations du node ont été mises à jour. Si des paramètres du daemon ont été modifiés, vous devrez le redémarrer pour que ces changements prennent effet.",
        'unallocated_deleted' => 'Tous les ports non alloués de <code>:ip</code> ont été supprimés.',
    ],
];
