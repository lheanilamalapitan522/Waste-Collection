<?php
if (getenv('APP_ENV') !== 'development') {
    http_response_code(404);
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

$context = hash_init('sha256');
$directory = new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS);
$filtered = new RecursiveCallbackFilterIterator($directory, static function (SplFileInfo $entry): bool {
    if (!$entry->isDir()) {
        return true;
    }

    return !in_array($entry->getFilename(), ['.git', 'node_modules', 'vendor'], true);
});
$iterator = new RecursiveIteratorIterator($filtered);

foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen(__DIR__) + 1));
    $name = basename($relativePath);
    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    if (preg_match('~(^|/)\.[^/]*~', $relativePath) ||
        $name === 'dev-version.php' ||
        str_starts_with(strtolower($name), 'leaflet') ||
        !in_array($extension, ['php', 'css', 'js'], true)) {
        continue;
    }

    hash_update($context, $relativePath . "\0" . hash_file('sha256', $file->getPathname()));
}

echo hash_final($context);
