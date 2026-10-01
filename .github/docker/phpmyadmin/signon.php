<?php

/*
 * Single sign-on between Recoded Ptero and phpMyAdmin, served as /phpmyadmin/signon.php.
 *
 *  ?ticket=...  redeems a one-time ticket the panel issued (60 seconds, single use, only in the
 *               browser session that asked for it) and signs in to phpMyAdmin with the account
 *               and host stored in it. The ticket never reaches phpMyAdmin's own URLs.
 *  ?logout=1    phpMyAdmin's logout target: ends both sessions and returns to the panel.
 *  (nothing)    phpMyAdmin sends every request without a valid session here; it goes back to
 *               the panel, or shows why the database refused the sign-in.
 *
 * The panel (Laravel) is only started to redeem a ticket or to translate an error page; it
 * reads the ticket from the panel's cache and decrypts the password with APP_KEY.
 */

declare(strict_types=1);

require __DIR__ . '/config.inc.php';

header('Cache-Control: no-store, max-age=0');
header('Pragma: no-cache');
header('Referrer-Policy: no-referrer');

$recodedPteroCookie = $cfg['Servers'][1]['SignonCookieParams'];
// phpMyAdmin's own session cookie (see PhpMyAdmin\Session::setUp).
$recodedPteroPmaCookie = ['lifetime' => 0, 'path' => '/phpmyadmin/', 'domain' => '', 'secure' => $recodedPtero['https'], 'httponly' => true, 'samesite' => 'Strict'];

ini_set('session.save_handler', 'files');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');
ini_set('session.cookie_httponly', '1');
session_save_path(RECODED_PTERO_PMA_SESSION_DIR);
session_cache_limiter('');

function recodedPteroLaravel(): Illuminate\Foundation\Application
{
    static $app = null;
    if ($app === null) {
        $base = dirname(__DIR__, 2);
        require_once $base . '/vendor/autoload.php';
        $app = require $base . '/bootstrap/app.php';
        $app->instance('request', Illuminate\Http\Request::capture());
        $app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();
    }

    return $app;
}

/** Ends a session (if the browser has one) and removes its cookie. */
function recodedPteroEndSession(string $name, array $cookie): void
{
    if (!isset($_COOKIE[$name])) {
        return;
    }

    session_name($name);
    session_set_cookie_params($cookie);
    if (@session_start()) {
        $_SESSION = [];
        session_destroy();
    }

    unset($cookie['lifetime']);
    setcookie($name, '', ['expires' => 1] + $cookie);
}

function recodedPteroRedirect(string $location): never
{
    header('Location: ' . $location, true, 303);
    exit;
}

function recodedPteroTrans(string $key, string $fallback): string
{
    try {
        $text = recodedPteroLaravel()->make('translator')->get('server_databases.manager.' . $key);

        return is_string($text) && $text !== 'server_databases.manager.' . $key ? $text : $fallback;
    } catch (Throwable) {
        return $fallback;
    }
}

function recodedPteroErrorPage(int $status, string $message, ?string $detail, string $panelUrl): never
{
    $e = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; frame-ancestors 'self'");
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">',
        '<meta name="robots" content="noindex"><title>phpMyAdmin</title>',
        '<style>body{font-family:system-ui,sans-serif;background:#0f172a;color:#e2e8f0;display:flex;min-height:100vh;margin:0;align-items:center;justify-content:center}',
        'main{max-width:34rem;padding:2rem;margin:1rem;background:#1e293b;border-radius:.5rem}code{display:block;white-space:pre-wrap;word-break:break-word;margin-top:1rem;color:#fca5a5}',
        'a{color:#93c5fd}</style></head><body><main><p>', $e($message), '</p>',
        $detail !== null ? '<code>' . $e($detail) . '</code>' : '',
        '<p><a href="', $e($panelUrl), '">', $e(recodedPteroTrans('back', 'Back to the panel')), '</a></p></main></body></html>';
    exit;
}

if (isset($_GET['logout'])) {
    recodedPteroEndSession(RECODED_PTERO_PMA_SESSION, $recodedPteroCookie);
    recodedPteroEndSession('phpMyAdmin', $recodedPteroPmaCookie);
    recodedPteroEndSession('phpMyAdmin_https', $recodedPteroPmaCookie);
    recodedPteroRedirect($recodedPtero['panel_url']);
}

$ticket = $_GET['ticket'] ?? null;
if (is_string($ticket)) {
    $credentials = null;
    try {
        $app = recodedPteroLaravel();
        $credentials = $app->make(Pterodactyl\Services\Databases\PhpMyAdminService::class)
            ->redeem($ticket, $_COOKIE[(string) $app['config']->get('session.cookie')] ?? null);
    } catch (Throwable $exception) {
        // Only the type: the message could contain connection details.
        error_log('phpMyAdmin sign-on failed: ' . get_class($exception));
    }

    // An already open phpMyAdmin session is left alone (e.g. the same link opened twice).
    if ($credentials === null) {
        recodedPteroErrorPage(
            403,
            recodedPteroTrans('expired', 'This phpMyAdmin link has expired or was already used. Open phpMyAdmin again from the panel.'),
            null,
            $recodedPtero['panel_url'],
        );
    }

    // Start phpMyAdmin from scratch, so nothing of an earlier account is left in its session.
    recodedPteroEndSession('phpMyAdmin', $recodedPteroPmaCookie);
    recodedPteroEndSession('phpMyAdmin_https', $recodedPteroPmaCookie);

    session_name(RECODED_PTERO_PMA_SESSION);
    session_set_cookie_params($recodedPteroCookie);
    session_start();
    // A new id, so a session id planted in the browser beforehand never carries credentials.
    session_regenerate_id(true);
    $_SESSION = [
        'PMA_single_signon_user' => $credentials['user'],
        'PMA_single_signon_password' => $credentials['password'],
        'PMA_single_signon_host' => $credentials['host'],
        'PMA_single_signon_port' => (string) $credentials['port'],
    ];
    session_write_close();

    recodedPteroRedirect('index.php');
}

// phpMyAdmin sent the browser here: no session, an expired one, or the database refused the login.
$error = null;
if (isset($_COOKIE[RECODED_PTERO_PMA_SESSION])) {
    session_name(RECODED_PTERO_PMA_SESSION);
    session_set_cookie_params($recodedPteroCookie);
    if (@session_start()) {
        $error = $_SESSION['PMA_single_signon_error_message'] ?? null;
        session_write_close();
    }
    recodedPteroEndSession(RECODED_PTERO_PMA_SESSION, $recodedPteroCookie);
}

if (is_string($error) && $error !== '') {
    recodedPteroErrorPage(
        502,
        recodedPteroTrans('failed', 'phpMyAdmin could not sign in to the database:'),
        html_entity_decode(strip_tags($error), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        $recodedPtero['panel_url'],
    );
}

recodedPteroRedirect($recodedPtero['panel_url']);
