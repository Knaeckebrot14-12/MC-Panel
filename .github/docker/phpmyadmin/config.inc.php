<?php

/*
 * phpMyAdmin configuration of Recoded Ptero. The Dockerfile copies this file to
 * /app/public/phpmyadmin/config.inc.php; phpMyAdmin itself is downloaded at build time.
 *
 * There is no login form. phpMyAdmin uses "signon" authentication: it only accepts the session
 * that signon.php creates from a one-time ticket the panel issued after checking permissions
 * (Admin -> Databases for the host account, the server's Databases tab for a database user).
 * Every other request is sent to signon.php, which sends it back to the panel. This file holds
 * no credentials; the database host and account always come from the panel's ticket.
 *
 * signon.php includes this file too, so both share the session settings below.
 */

declare(strict_types=1);

$recodedPtero = (static function (): array {
    // The panel's settings: environment variables first, then the .env file (the same order
    // Laravel uses). Read directly, so phpMyAdmin requests don't have to start the panel.
    $dotenv = [];
    $contents = @file_get_contents(dirname(__DIR__, 2) . '/.env');
    if (is_string($contents)) {
        foreach (preg_split('/\R/', $contents) ?: [] as $line) {
            if (preg_match('/^\s*(?:export\s+)?([A-Z0-9_]+)\s*=\s*(.*?)\s*$/', $line, $match)) {
                $dotenv[$match[1]] = trim($match[2], "\"'");
            }
        }
    }

    $env = static function (string $key) use ($dotenv): string {
        $value = getenv($key);

        return is_string($value) && $value !== '' ? $value : ($dotenv[$key] ?? '');
    };

    $appUrl = rtrim($env('APP_URL'), '/');

    return [
        'app_key' => $env('APP_KEY'),
        'panel_url' => $appUrl !== '' ? $appUrl . '/' : '/',
        'https' => str_starts_with(strtolower($appUrl), 'https://'),
    ];
})();

// Shared with signon.php and Pterodactyl\Services\Databases\PhpMyAdminService.
if (!defined('RECODED_PTERO_PMA_SESSION')) {
    define('RECODED_PTERO_PMA_SESSION', 'RecodedPteroPMA');
    define('RECODED_PTERO_PMA_SESSION_DIR', '/tmp/phpmyadmin/sessions');
    define('RECODED_PTERO_PMA_TEMP_DIR', '/tmp/phpmyadmin/tmp');
    // A phpMyAdmin session ends after 30 minutes without a request.
    define('RECODED_PTERO_PMA_IDLE_SECONDS', 1800);
}

if ($recodedPtero['app_key'] === '') {
    http_response_code(503);
    exit('phpMyAdmin is not available: the panel has no APP_KEY.');
}

foreach ([RECODED_PTERO_PMA_SESSION_DIR, RECODED_PTERO_PMA_TEMP_DIR] as $directory) {
    if (!is_dir($directory)) {
        @mkdir($directory, 0700, true);
    }
}

// Expire idle sessions here instead of relying on PHP's random session cleanup: a session
// file that was not touched for 30 minutes is deleted before phpMyAdmin reads it.
foreach ([RECODED_PTERO_PMA_SESSION, 'phpMyAdmin', 'phpMyAdmin_https'] as $cookie) {
    $id = $_COOKIE[$cookie] ?? null;
    if (is_string($id) && preg_match('/^[A-Za-z0-9,-]{22,256}$/', $id)) {
        $file = RECODED_PTERO_PMA_SESSION_DIR . '/sess_' . $id;
        $modified = @filemtime($file);
        if ($modified !== false && $modified < time() - RECODED_PTERO_PMA_IDLE_SECONDS) {
            @unlink($file);
        }
    }
}
ini_set('session.gc_maxlifetime', (string) RECODED_PTERO_PMA_IDLE_SECONDS);

// "LOAD DATA LOCAL INFILE" would let the database connection read files of the panel container
// (e.g. its .env). phpMyAdmin only allows it for the LDI import format, which is removed from the
// image and refused here as well.
if (($_POST['format'] ?? $_GET['format'] ?? null) === 'ldi') {
    http_response_code(403);
    exit('This import format is disabled.');
}

// Derived from the panel's APP_KEY: 32 bytes, as phpMyAdmin's sodium encryption requires.
$cfg['blowfish_secret'] = hash('sha256', 'recoded-ptero-phpmyadmin|' . $recodedPtero['app_key'], true);

$cfg['Servers'] = [
    1 => [
        'auth_type' => 'signon',
        // Placeholder only: signon.php puts the host and port of the panel's ticket into the session.
        'host' => '127.0.0.1',
        'port' => '',
        'verbose' => 'Recoded Ptero',
        'compress' => false,
        'AllowRoot' => true,
        'AllowNoPassword' => false,
        'SignonSession' => RECODED_PTERO_PMA_SESSION,
        'SignonCookieParams' => [
            'lifetime' => 0,
            'path' => '/phpmyadmin/',
            'domain' => '',
            'secure' => $recodedPtero['https'],
            'httponly' => true,
            'samesite' => 'Strict',
        ],
        'SignonURL' => '/phpmyadmin/signon.php',
        // Ends the signon session as well, then returns to the panel.
        'LogoutURL' => '/phpmyadmin/signon.php?logout=1',
        // No phpMyAdmin configuration storage: nothing is written into the users' databases.
        'controluser' => '',
        'controlpass' => '',
        'pmadb' => '',
    ],
];
$cfg['ServerDefault'] = 1;
$cfg['AllowArbitraryServer'] = false;
$cfg['ZeroConf'] = false;

$cfg['SessionSavePath'] = RECODED_PTERO_PMA_SESSION_DIR;
$cfg['TempDir'] = RECODED_PTERO_PMA_TEMP_DIR;
$cfg['LoginCookieValidity'] = RECODED_PTERO_PMA_IDLE_SECONDS;
$cfg['CookieSameSite'] = 'Strict';
$cfg['AllowThirdPartyFraming'] = false;

// No file access on the panel server for imports and exports (only uploads and downloads).
$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';

// The password belongs to the panel: changing it here would break the server's connection details.
$cfg['ShowChgPassword'] = false;
$cfg['ShowServerInfo'] = false;
$cfg['ShowPhpInfo'] = false;
$cfg['ShowGitRevision'] = false;
$cfg['VersionCheck'] = false;
$cfg['SendErrorReports'] = 'never';

$cfg['PmaNoRelation_DisableWarning'] = true;
$cfg['LoginCookieValidityDisableWarning'] = true;
$cfg['SuhosinDisableWarning'] = true;

// The image only keeps the panel's languages.
$cfg['FilterLanguages'] = '^(en|de|fr|es|it|nl|pl|pt|pt_BR|ru)';
