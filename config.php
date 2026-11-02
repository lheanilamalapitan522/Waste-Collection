<?php
define('SITE_NAME', 'Mauban Waste Collection');
define('SITE_DESC', 'Official Waste Management System of Mauban, Quezon');
define('BASE_URL', '');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'mauban_waste_collection');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

$dbSslCaPem = getenv('DB_SSL_CA_PEM');
if ($dbSslCaPem === false || trim($dbSslCaPem) === '') {
    if (getenv('APP_ENV') === 'production') {
        error_log('Database TLS CA certificate is required in production.');
        http_response_code(503);
        exit('The service is temporarily unavailable. Please try again later.');
    }
} else {
    if (openssl_x509_read($dbSslCaPem) === false) {
        error_log('Database TLS CA certificate is not a valid PEM certificate.');
        http_response_code(503);
        exit('The service is temporarily unavailable. Please try again later.');
    }

    $sslCaPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR .
        'mauban-db-ca-' . hash('sha256', $dbSslCaPem) . '.pem';

    if (!is_file($sslCaPath)) {
        $temporaryCaPath = tempnam(sys_get_temp_dir(), 'mauban-db-ca-');
        if ($temporaryCaPath === false) {
            error_log('Unable to create a temporary database TLS CA certificate file.');
            http_response_code(503);
            exit('The service is temporarily unavailable. Please try again later.');
        }

        $fileWritten = file_put_contents($temporaryCaPath, $dbSslCaPem, LOCK_EX) !== false;
        $permissionsSet = $fileWritten && chmod($temporaryCaPath, 0600);
        $fileReady = $permissionsSet &&
            (rename($temporaryCaPath, $sslCaPath) || is_file($sslCaPath));

        if (is_file($temporaryCaPath) && !unlink($temporaryCaPath)) {
            error_log('Unable to remove the temporary database TLS CA certificate file.');
            $fileReady = false;
        }

        if (!$fileReady) {
            error_log('Unable to prepare the database TLS CA certificate file.');
            http_response_code(503);
            exit('The service is temporarily unavailable. Please try again later.');
        }
    }

    $pdoOptions[PDO::MYSQL_ATTR_SSL_CA] = $sslCaPath;
    $pdoOptions[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
}

$forwardedProto = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')[0]));
$requestIsHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    $forwardedProto === 'https';

if (getenv('APP_ENV') === 'production' && !$requestIsHttps) {
    $httpsUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/');
    header('Location: ' . $httpsUrl, true, 308);
    exit;
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        $pdoOptions
    );
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(503);
    exit('The service is temporarily unavailable. Please try again later.');
}
