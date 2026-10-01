<?php

return [
    'theme' => [
        'title' => 'Appearance',
        'dark' => 'Dark',
        'light' => 'Light',
    ],
    'install' => [
        'title' => 'App',
        'installed' => 'The panel is installed as an app on this device.',
        'description' => 'Install the panel as an app on your phone or computer.',
        'button' => 'Install app',
        'manual' => 'In the browser menu choose "Install app" or "Add to Home Screen" (iPhone: Share → Add to Home Screen).',
    ],
    'push' => [
        'title' => 'Push notifications',
        'unsupported' => 'This browser does not support push notifications here (HTTPS is required; on iPhone install the app first).',
        'description' => 'Get notified when a server crashes, a ticket is answered or coins are running out.',
        'enable' => 'Turn on',
        'disable' => 'Turn off',
        'test' => 'Send a test',
        'enabled' => 'Push notifications are on for this device.',
        'denied' => 'Notifications are blocked for this site. Allow them in the browser settings.',
        'test_sent' => 'Test notification sent.',
        'test_none' => 'No device received the test. Turn notifications off and on again.',
    ],
];
