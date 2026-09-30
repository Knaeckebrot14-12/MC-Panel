<?php

return [
    'title' => 'Settings',
    'sftp' => [
        'heading' => 'SFTP Details',
        'server_address_label' => 'Server Address',
        'username_label' => 'Username',
        'password_notice' => 'Your SFTP password is the same as the password you use to access this panel.',
        'launch_button' => 'Launch SFTP',
    ],
    'debug' => [
        'heading' => 'Debug Information',
        'node_label' => 'Node',
        'server_id_label' => 'Server ID',
    ],
    'rename' => [
        'heading' => 'Change Server Details',
        'name_label' => 'Server Name',
        'description_label' => 'Server Description',
        'save_button' => 'Save',
    ],
    'reinstall' => [
        'heading' => 'Reinstall Server',
        'disabled_notice' => 'Reinstalling this server has been disabled because it is configured to skip its egg\'s install script. If you would like to reinstall this server, contact a server administrator.',
        'body' => 'Reinstalling your server will stop it, and then re-run the installation script that initially set it up.',
        'body_warning' => 'Some files may be deleted or modified during this process, please back up your data before continuing.',
        'reinstall_button' => 'Reinstall Server',
        'confirm_title' => 'Confirm server reinstallation',
        'confirm_button' => 'Yes, reinstall server',
        'confirm_body' => 'Your server will be stopped and some files may be deleted or modified during this process, are you sure you wish to continue?',
        'success_message' => 'Your server has begun the reinstallation process.',
    ],
];
