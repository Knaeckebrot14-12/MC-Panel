<?php

// Brings an nginx config written by an older version up to date (run by entrypoint.sh on every
// start; changes nothing when the config is already current):
//  - only TLS 1.2 and 1.3,
//  - the shared security headers from /etc/nginx/recoded-ptero/security.conf in every server block,
//    replacing the few headers older configs set themselves.

$file = $argv[1] ?? '/etc/nginx/http.d/panel.conf';
if (!is_file($file)) {
    exit(0);
}

$config = $original = file_get_contents($file);

$config = str_replace('ssl_protocols TLSv1 TLSv1.1 TLSv1.2;', 'ssl_protocols TLSv1.2 TLSv1.3;', $config);
$config = preg_replace('/^\s*add_header (X-Content-Type-Options|X-XSS-Protection|X-Robots-Tag|Content-Security-Policy) [^\n]*\n/m', '', $config);

if (strpos($config, 'recoded-ptero/security.conf') === false) {
    $config = preg_replace('/^([ \t]*)(server_name [^;]+;)/m', "$1$2\n$1include /etc/nginx/recoded-ptero/security.conf;", $config);
}

if ($config !== $original) {
    file_put_contents($file, $config);
    echo "Updated the nginx security settings.\n";
}
