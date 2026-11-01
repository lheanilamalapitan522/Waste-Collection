<?php
$secureCookies = getenv('SESSION_COOKIE_SECURE') === '1' ||
    getenv('APP_ENV') === 'production' ||
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(self), camera=(), microphone=()');
if ($secureCookies) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
// Keep idle sessions from expiring due to PHP's default 24-minute garbage collection.
// The browser session cookie still ends when the browser session closes, and logout
// explicitly destroys the session. A hosting platform may also impose its own limit.
ini_set('session.gc_maxlifetime', '2147483647');
ini_set('session.gc_probability', '0');
session_set_cookie_params([
    'lifetime' => 0,
    'httponly' => true,
    'secure' => $secureCookies,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
