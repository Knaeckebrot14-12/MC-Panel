<?php

return [
    'gsl_token' => [
        'title' => 'Invalid GSL token!',
        'body_1' => 'It seems like your Gameserver Login Token (GSL token) is invalid or has expired.',
        'body_2' => 'You can either generate a new one and enter it below or leave the field blank to remove it completely.',
        'field_label' => 'GSL Token',
        'field_description' => 'Visit https://steamcommunity.com/dev/managegameservers to generate a token.',
        'update_button' => 'Update GSL Token',
    ],
    'hytale_oauth' => [
        'title' => 'Authentication Required',
        'body' => 'You need to authenticate with your Hytale account to download or update server files. Please log in to continue.',
        'cancel_button' => 'Cancel',
        'login_button' => 'Log in',
    ],
    'pid_limit' => [
        'admin_title' => 'Memory or process limit reached...',
        'admin_body_1' => 'This server has reached the maximum process or memory limit.',
        'admin_body_2' => 'Increasing <0>container_pid_limit</0> in the wings configuration, <1>config.yml</1>, might help resolve this issue.',
        'admin_note' => 'Note: Wings must be restarted for the configuration file changes to take effect',
        'user_title' => 'Possible resource limit reached...',
        'user_body' => 'This server is attempting to use more resources than allocated. Please contact the administrator and give them the error below.',
        'close_button' => 'Close',
    ],
    'steam_disk_space' => [
        'title' => 'Out of available disk space...',
        'admin_body_1' => 'This server has run out of available disk space and cannot complete the install or update process.',
        'admin_body_2' => 'Ensure the machine has enough disk space by typing <0>df -h</0> on the machine hosting this server. Delete files or increase the available disk space to resolve the issue.',
        'user_body' => 'This server has run out of available disk space and cannot complete the install or update process. Please get in touch with the administrator(s) and inform them of disk space issues.',
        'close_button' => 'Close',
    ],
    'eula' => [
        'title' => 'Accept Minecraft® EULA',
        'body_prefix' => 'By pressing "I Accept" below you are indicating your agreement to the',
        'body_suffix' => '.',
        'link_text' => 'Minecraft® EULA',
        'cancel_button' => 'Cancel',
        'accept_button' => 'I Accept',
    ],
    'java_version' => [
        'title' => 'Unsupported Java Version',
        'body' => 'This server is currently running an unsupported version of Java and cannot be started.',
        'body_select_notice' => ' Please select a supported version from the list below to continue starting the server.',
        'cancel_button' => 'Cancel',
        'update_button' => 'Update Docker Image',
    ],
];
