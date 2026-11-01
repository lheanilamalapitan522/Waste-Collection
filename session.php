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
session_set_cookie_params([
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
