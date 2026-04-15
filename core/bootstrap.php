<?php
/**
 * Bootstrap Application
 * File khởi tạo chính cho toàn bộ ứng dụng
 */

// Định nghĩa thư mục gốc
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CORE_PATH', ROOT_PATH . '/core');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Bật hiển thị lỗi nếu ở chế độ debug
$isDevelopment = file_exists(ROOT_PATH . '/.env') ? 
    match(getenv('APP_ENV') ?: 'development') {
        'development' => true,
        default => false,
    } : false;

if ($isDevelopment) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}

// Thiết lập timezone mặc định
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Đặt 1 nếu sử dụng HTTPS
ini_set('session.cookie_samesite', 'Lax');

// Bắt đầu session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tải Config autoloader
require_once CONFIG_PATH . '/Config.php';

// Tải các class từ core
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Model.php';
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/Router.php';

// Tải Config toàn cứu
Config::load(ROOT_PATH . '/.env');

// Kết nối database
try {
    Database::connect();
} catch (Exception $e) {
    if (Config::get('APP_DEBUG') === 'true') {
        die("Database Error: " . $e->getMessage());
    } else {
        die("Unable to connect to database. Please try again later.");
    }
}

// Register custom error handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    error_log("[ERROR] {$errno}: {$errstr} in {$errfile}:{$errline}");

    if (Config::get('APP_DEBUG') === 'true') {
        echo "<pre style='color: red; font-weight: bold;'>";
        echo "[ERROR {$errno}] {$errstr}\n";
        echo "File: {$errfile}\n";
        echo "Line: {$errline}\n";
        echo "</pre>";
    }

    return false;
});

// Register autoloader cho app classes
spl_autoload_register(function ($class) {
    // Namespace path mapping
    $namespaces = [
        'App\Controllers\\' => APP_PATH . '/Controllers/',
        'App\Models\\' => APP_PATH . '/Models/',
        'App\Services\\' => APP_PATH . '/Services/',
        'App\Repositories\\' => APP_PATH . '/Repositories/',
        'App\Middleware\\' => APP_PATH . '/Middleware/',
    ];

    foreach ($namespaces as $namespace => $path) {
        if (strncmp($class, $namespace, strlen($namespace)) === 0) {
            $relativeClass = substr($class, strlen($namespace));
            $file = $path . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
    }

    return false;
});

// Định nghĩa các helper function
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed {
        return Config::get($key, $default);
    }
}

if (!function_exists('dd')) {
    function dd(...$vars): void {
        echo '<pre style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd;">';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        die();
    }
}

if (!function_exists('response')) {
    function response(string $message, mixed $data = null, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $statusCode >= 200 && $statusCode < 300 ? 'success' : 'error',
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
        exit;
    }
}
