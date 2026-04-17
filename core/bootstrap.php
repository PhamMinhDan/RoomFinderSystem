<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

// ── Load .env ────────────────────────────────────────────────────────────────
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            // Bỏ dấu nháy nếu có
            $value = trim($value, '"\'');
            $_ENV[$key]    = $value;
            putenv("$key=$value");
        }
    }
}

// ── APP_URL (cần cho redirect_uri) ───────────────────────────────────────────
if (empty($_ENV['APP_URL'])) {
    $scheme          = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host            = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_ENV['APP_URL'] = $scheme . '://' . $host;
}
require_once dirname(__DIR__) . '/config/cloudinary.php';


// ── PSR-4 style autoloader (không cần Composer nếu chưa có) ──────────────────
spl_autoload_register(function (string $class) {
    // Namespace → folder mapping
    $map = [
    'Controllers\\'  => __DIR__ . '/../app/Controllers/',
    'Services\\'     => __DIR__ . '/../app/Services/',
    'Repositories\\' => __DIR__ . '/../app/Repositories/',
    'Core\\'         => __DIR__ . '/../core/',
    'Models\\'       => __DIR__ . '/../app/Models/',
];

    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file     = $dir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// ── Session ───────────────────────────────────────────────────────────────────
\Core\SessionManager::start();