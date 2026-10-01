<?php

return [
    // The GitHub repository (owner/name) and branch this panel updates itself from.
    'repository' => env('MC_PANEL_REPO', 'Knaeckebrot14-12/Recoded-Ptero'),
    'branch' => env('MC_PANEL_BRANCH', 'main'),

    // Optional GitHub token, only needed for private forks or to lift the API rate limit.
    'github_token' => env('MC_PANEL_GITHUB_TOKEN'),

    // When enabled, the panel starts an update on its own as soon as one is published.
    // Editable in Admin -> Settings -> Updates.
    'auto_update' => env('MC_PANEL_AUTO_UPDATE', false),

    // Directory shared with the updater service (see installer/updater). The panel drops
    // update requests in here and reads the progress the updater writes back.
    'updater_dir' => env('MC_UPDATER_DIR', '/app/updater'),

    // Version of the Wings build the installer installs (GitHub release "wings-v<version>" of this repository).
    'wings_version' => env('MC_PANEL_WINGS_VERSION', '1.0.0'),

    // Admin -> Settings -> Login & Registration.
    'registration' => [
        // New sign-ups must confirm their e-mail address before earning coins or getting servers.
        'verify_email' => env('MC_PANEL_VERIFY_EMAIL', true),
        // How many accounts may be registered from one IP address (0 = no limit).
        'max_accounts_per_ip' => (int) env('MC_PANEL_MAX_ACCOUNTS_PER_IP', 2),
    ],

    'discord' => [
        'enabled' => env('MC_PANEL_DISCORD_ENABLED', false),
        'client_id' => env('MC_PANEL_DISCORD_CLIENT_ID'),
        'client_secret' => env('MC_PANEL_DISCORD_CLIENT_SECRET'),
        // Whether someone without an account can create one by logging in with Discord.
        'allow_registration' => env('MC_PANEL_DISCORD_ALLOW_REGISTRATION', true),
    ],

    // Public page at /status showing whether the nodes are online.
    'status_page' => [
        'enabled' => env('MC_PANEL_STATUS_PAGE', true),
    ],

    // Admin -> Settings -> Monitoring. Wings reports CPU, memory and disk every minute; alerts go to a
    // Discord webhook when a node goes offline or crosses a threshold.
    'monitoring' => [
        'discord_webhook' => env('MC_PANEL_MONITORING_WEBHOOK'),
        'notify_offline' => env('MC_PANEL_MONITORING_OFFLINE', true),
        'disk_percent' => (int) env('MC_PANEL_MONITORING_DISK', 90),
        'memory_percent' => (int) env('MC_PANEL_MONITORING_MEMORY', 95),
    ],

    // Admin -> Settings -> Design. Logo/favicon files live in /app/var/branding (kept across updates).
    'branding' => [
        'accent' => env('MC_PANEL_ACCENT', ''),
        'background' => env('MC_PANEL_BACKGROUND', ''),
        'logo' => env('MC_PANEL_LOGO', ''),
        'favicon' => env('MC_PANEL_FAVICON', ''),
        'default_theme' => env('MC_PANEL_DEFAULT_THEME', 'dark'),
    ],

    // Admin -> Settings -> Subdomains. Users pick name.<domain> for a server; the panel creates the
    // DNS records through the Cloudflare API (token with "Zone.DNS: Edit" for these zones).
    'subdomains' => [
        'enabled' => env('MC_PANEL_SUBDOMAINS', false),
        'cloudflare_token' => env('MC_PANEL_CLOUDFLARE_TOKEN'),
        // Comma separated, e.g. "play.example.com,mc.example.net".
        'domains' => env('MC_PANEL_SUBDOMAIN_DOMAINS', ''),
    ],

    // Browser push for the installable app. The keys are created automatically on first use.
    'push' => [
        'public_key' => env('MC_PANEL_VAPID_PUBLIC'),
        'private_key' => env('MC_PANEL_VAPID_PRIVATE'),
    ],

    // Admin -> Maintenance. "banner" shows the message to everybody, "lock" also keeps
    // everybody except the team out of the panel (game servers keep running).
    'maintenance' => [
        'mode' => env('MC_PANEL_MAINTENANCE_MODE', 'off'),
        'message' => env('MC_PANEL_MAINTENANCE_MESSAGE', ''),
    ],
];
