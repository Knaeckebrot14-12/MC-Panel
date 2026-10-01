<?php

return [
    'password' => 'Passwords must be at least six characters and match the confirmation.',
    'reset' => 'Your password has been reset!',
    'sent' => 'We have e-mailed your password reset link!',
    'token' => 'This password reset token is invalid.',
    'user' => 'We can\'t find a user with that e-mail address.',
    'generated_password_sent' => 'If an account matching that email or username exists, we have sent it an email with a confirmation link.',
    'confirm_mail' => [
        'subject' => 'Confirm your password reset',
        'intro' => 'Someone asked for a new password for your account (:username).',
        'button' => 'Send me a new password',
        'expires' => 'The link works for 60 minutes. After clicking it you get a new password by email.',
        'ignore' => 'If this was not you, just ignore this email; your password stays the same.',
    ],
    'confirm_page' => [
        'sent_title' => 'New password sent',
        'sent_text' => 'We have emailed you a new password. After logging in you choose your own password.',
        'invalid_title' => 'Link no longer valid',
        'invalid_text' => 'This link has expired or was already used. Request a new one on the login page.',
        'login' => 'To the login',
    ],
];
