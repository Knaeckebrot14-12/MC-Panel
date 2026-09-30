<?php

return [
    'title' => 'Users',
    'empty' => 'It looks like you don\'t have any subusers.',
    'new_user_button' => 'New User',
    'row' => [
        'two_factor_label' => '2FA Enabled',
        'permissions_label' => 'Permissions',
        'edit_aria' => 'Edit subuser',
    ],
    'edit_modal' => [
        'title_modify' => 'Modify permissions for :email',
        'title_view' => 'View permissions for :email',
        'title_create' => 'Create new subuser',
        'save_button' => 'Save',
        'invite_button' => 'Invite User',
        'permission_notice' => 'Only permissions which your account is currently assigned may be selected when creating or modifying other users.',
        'email_label' => 'User Email',
        'email_description' => 'Enter the email address of the user you wish to invite as a subuser for this server.',
        'validation' => [
            'email_max' => 'Email addresses must not exceed 191 characters.',
            'email_invalid' => 'A valid email address must be provided.',
        ],
    ],
    'remove' => [
        'title' => 'Delete this subuser?',
        'confirm_button' => 'Yes, remove subuser',
        'body' => 'Are you sure you wish to remove this subuser? They will have all access to this server revoked immediately.',
        'delete_aria' => 'Delete subuser',
    ],
];
