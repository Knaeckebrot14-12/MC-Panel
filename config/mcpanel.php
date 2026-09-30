<?php

return [
    // The GitHub repository (owner/name) and branch this panel updates itself from.
    'repository' => env('MC_PANEL_REPO', 'Knaeckebrot14-12/MC-Panel'),
    'branch' => env('MC_PANEL_BRANCH', 'main'),

    // Optional GitHub token, only needed for private forks or to lift the API rate limit.
    'github_token' => env('MC_PANEL_GITHUB_TOKEN'),

    // When enabled, the panel starts an update on its own as soon as one is published.
    // Editable in Admin -> Settings -> Updates.
    'auto_update' => env('MC_PANEL_AUTO_UPDATE', false),

    // Directory shared with the updater service (see installer/updater). The panel drops
    // update requests in here and reads the progress the updater writes back.
    'updater_dir' => env('MC_UPDATER_DIR', '/app/updater'),
];
