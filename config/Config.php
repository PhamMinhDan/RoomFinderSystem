<?php
/**
 * Environment Configuration Loader
 * Tải các biến từ file .env vào $_ENV để sử dụng toàn bộ ứng dụng
 */

class Config
{
    private static array $config = [];
    private static bool $loaded = false;

    /**
     * Load các biến từ file .env
     */
    public static function load(string $filePath = '.env'): void
    {
        if (self::$loaded) {
            return;
        }

        if (!file_exists($filePath)) {
            throw new Exception("Environment file not found: {$filePath}");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Bỏ qua comment lines
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse key=value
            if (strpos($line, '=') === false) {
                continue;
            }

            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Loại bỏ quotes nếu có
            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }

            self::$config[$key] = $value;
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }

        self::$loaded = true;
    }

    /**
     * Lấy giá trị config
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$config[$key] ?? $default;
    }

    /**
     * Kiểm tra xem config key có tồn tại không
     */
    public static function has(string $key): bool
    {
        return isset(self::$config[$key]);
    }
}
