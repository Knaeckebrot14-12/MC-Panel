<?php

return [
    'exceptions' => [
        'no_new_default_allocation' => "Vous essayez de supprimer l'allocation par défaut de ce serveur, mais il n'existe aucune allocation de remplacement.",
        'marked_as_failed' => "Ce serveur a été marqué comme ayant échoué lors d'une installation précédente. Le statut actuel ne peut pas être modifié dans cet état.",
        'skipping_install_script' => "Ce serveur est configuré pour ignorer le script d'installation de son egg. La réinstallation n'est pas disponible tant que ce paramètre n'est pas désactivé.",
        'bad_variable' => 'Une erreur de validation est survenue avec la variable :name.',
        'daemon_exception' => "Une exception s'est produite lors de la communication avec le daemon, avec un code de réponse HTTP/:code. Cette exception a été consignée. (ID de requête : :request_id)",
        'default_allocation_not_found' => "L'allocation par défaut demandée n'a pas été trouvée parmi les allocations de ce serveur.",
    ],
    'alerts' => [
        'startup_changed' => "La configuration de démarrage de ce serveur a été mise à jour. Si le nest ou l'egg de ce serveur a été modifié, une réinstallation va maintenant avoir lieu.",
        'server_deleted' => 'Le serveur a été supprimé du système avec succès.',
        'server_created' => "Le serveur a été créé avec succès sur le panel. Veuillez laisser quelques minutes au daemon pour installer complètement ce serveur.",
        'build_updated' => "Les détails de configuration de ce serveur ont été mis à jour. Certaines modifications peuvent nécessiter un redémarrage pour prendre effet.",
        'suspension_toggled' => 'Le statut de suspension du serveur a été changé en :status.',
        'rebuild_on_boot' => "Ce serveur a été marqué comme nécessitant une reconstruction du conteneur Docker. Elle aura lieu au prochain démarrage du serveur.",
        'install_toggled' => "Le statut d'installation de ce serveur a été basculé.",
        'server_reinstalled' => 'Ce serveur a été mis en file d\'attente pour une réinstallation qui commence maintenant.',
        'details_updated' => 'Les détails du serveur ont été mis à jour avec succès.',
        'docker_image_updated' => "L'image Docker par défaut de ce serveur a été modifiée avec succès. Un redémarrage est nécessaire pour appliquer ce changement.",
        'node_required' => 'Vous devez avoir configuré au moins un node avant de pouvoir ajouter un serveur à ce panel.',
        'transfer_nodes_required' => 'Vous devez avoir configuré au moins deux nodes avant de pouvoir transférer des serveurs.',
        'transfer_started' => 'Le transfert du serveur a été démarré.',
        'transfer_not_viable' => "Le node que vous avez sélectionné ne dispose pas de l'espace disque ou de la mémoire nécessaires pour accueillir ce serveur.",
    ],
];
