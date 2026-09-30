<?php

return [
    'nav' => [
        'general' => 'General',
        'mail' => 'Mail',
        'coins' => 'Coins',
        'advanced' => 'Advanced',
        'updates' => 'Updates',
        'login' => 'Login & Registration',
    ],
    'notice' => [
        'env_only' => 'Your Panel is currently configured to read settings from the environment only. You will need to set :env_var in your environment file in order to load settings dynamically.',
    ],
    'index' => [
        'title' => 'Settings',
        'heading' => 'Panel Settings',
        'subheading' => 'Configure Pterodactyl to your liking.',
        'breadcrumb_settings' => 'Settings',
        'panel_settings_heading' => 'Panel Settings',
        'company_name_label' => 'Company Name',
        'company_name_description' => 'This is the name that is used throughout the panel and in emails sent to clients.',
        'require_2fa_label' => 'Require 2-Factor Authentication',
        'require_2fa_description' => 'If enabled, any account falling into the selected grouping will be required to have 2-Factor authentication enabled to use the Panel.',
        '2fa_not_required' => 'Not Required',
        '2fa_admin_only' => 'Admin Only',
        '2fa_all_users' => 'All Users',
        'default_language_label' => 'Default Language',
        'default_language_description' => 'The default language to use when rendering UI components.',
    ],
];
