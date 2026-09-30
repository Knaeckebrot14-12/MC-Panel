<?php

return [
    'title' => 'Application API',
    'index' => [
        'heading' => 'Application API',
        'subheading' => 'Control access credentials for managing this Panel via the API.',
        'list_heading' => 'Credentials List',
        'create_new_button' => 'Create New',
        'table' => [
            'key' => 'Key',
            'memo' => 'Memo',
            'last_used' => 'Last Used',
            'created' => 'Created',
            'created_by' => 'Created by',
        ],
        'js' => [
            'revoke_title' => 'Revoke API Key',
            'revoke_text' => 'Once this API key is revoked any applications currently using it will stop working.',
            'revoke_confirm_button' => 'Revoke',
            'revoke_success_text' => 'API Key has been revoked.',
            'revoke_error_title' => 'Whoops!',
            'revoke_error_text' => 'An error occurred while attempting to revoke this key.',
        ],
    ],
    'new' => [
        'subheading' => 'Create a new application API key.',
        'breadcrumb_new' => 'New Credentials',
        'select_permissions_heading' => 'Select Permissions',
        'read_all_button' => 'Read All',
        'read_write_all_button' => 'Read & Write All',
        'none_all_button' => 'None All',
        'read_label' => 'Read',
        'read_write_label' => 'Read & Write',
        'none_label' => 'None',
        'description_label' => 'Description',
        'notice' => 'Once you have assigned permissions and created this set of credentials you will be unable to come back and edit it. If you need to make changes down the road you will need to create a new set of credentials.',
        'create_button' => 'Create Credentials',
    ],
];
