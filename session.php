<?php
$sessionStore = getenv('SESSION_STORE') ?: 'files';
if (!in_array($sessionStore, ['files', 'mysql'], true)) {
    error_log('SESSION_STORE must be either files or mysql.');
    http_response_code(503);
    exit('The service is temporarily unavailable. Please try again later.');
}

if ($sessionStore === 'mysql') {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/mysql-session-handler.php';

    if (!isset($pdo) || !($pdo instanceof PDO) ||
        !session_set_save_handler(new MysqlSessionHandler($pdo), true)) {
        error_log('Unable to configure the MySQL session handler.');
        http_response_code(503);
        exit('The service is temporarily unavailable. Please try again later.');
    }
}

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
// File-backed sessions keep the existing long server-side lifetime. MySQL-backed
// sessions use a bounded lifetime below, while the browser cookie remains transient.
ini_set('session.gc_maxlifetime', '2147483647');
ini_set('session.gc_probability', '0');
if ($sessionStore === 'mysql') {
    ini_set('session.gc_maxlifetime', '2592000');
    ini_set('session.gc_probability', '1');
    ini_set('session.gc_divisor', '1000');
}
session_set_cookie_params([
    'lifetime' => 0,
    'httponly' => true,
    'secure' => $secureCookies,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    try {
        $sessionStarted = session_start();
    } catch (PDOException $e) {
        error_log('Unable to start the MySQL session: ' . $e->getMessage());
        http_response_code(503);
        exit('The service is temporarily unavailable. Please try again later.');
    }

    if (!$sessionStarted) {
        error_log('Unable to start the application session.');
        http_response_code(503);
        exit('The service is temporarily unavailable. Please try again later.');
    }
}

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
