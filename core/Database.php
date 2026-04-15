<?php
/**
 * Database Connection - Kết nối cơ sở dữ liệu MySQL qua PDO
 * Sử dụng Prepared Statements để chống SQL Injection
 */

class Database
{
    private static ?PDO $connection = null;
    private static array $config = [];

    /**
     * Khởi tạo kết nối PDO
     */
    public static function connect(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        // Load environment variables
        Config::load(dirname(__DIR__) . '/.env');

        // Lấy cấu hình database từ environment
        $host = Config::get('DB_HOST', 'localhost');
        $port = Config::get('DB_PORT', '3306');
        $database = Config::get('DB_NAME', 'smart_room_finder');
        $user = Config::get('DB_USER', 'root');
        $password = Config::get('DB_PASSWORD', '');

        try {
            // Tạo DSN (Data Source Name) cho MySQL
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

            // Tạo kết nối PDO
            $pdo = new PDO(
                $dsn,
                $user,
                $password,
                [
                    // Chế độ lỗi: throw Exception nếu có lỗi
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    
                    // Chế độ mocking default cho Fetch
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    
                    // Cho phép sử dụng Transaction
                    PDO::ATTR_AUTOCOMMIT => true,
                    
                    // Thiết lập charset UTF-8
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]
            );

            self::$connection = $pdo;

            // Log connection success nếu APP_DEBUG = true
            if (Config::get('APP_DEBUG') === 'true') {
                error_log("✓ Database connected successfully: {$database}");
            }

            return $pdo;

        } catch (PDOException $e) {
            // Ghi log lỗi kết nối
            error_log("✗ Database connection failed: " . $e->getMessage());

            // Throw exception để xử lý ở controller hoặc middleware
            throw new Exception(
                "Database connection error: " . 
                (Config::get('APP_ENV') === 'production' ? 'Unable to connect to database' : $e->getMessage())
            );
        }
    }

    /**
     * Lấy instance kết nối hiện tại
     */
    public static function getInstance(): PDO
    {
        if (self::$connection === null) {
            self::connect();
        }
        return self::$connection;
    }

    /**
     * Thực thi query với Prepared Statements (chống SQL Injection)
     * 
     * @param string $query - Câu query SQL
     * @param array $params - Mảng tham số để bind
     * @return PDOStatement
     */
    public static function query(string $query, array $params = []): PDOStatement
    {
        try {
            $pdo = self::getInstance();
            $stmt = $pdo->prepare($query);

            // Bind các tham số với kiểu dữ liệu tương ứng
            foreach ($params as $key => $value) {
                $key = is_int($key) ? $key + 1 : ":{$key}";
                
                // Xác định kiểu dữ liệu PDO
                $paramType = match (gettype($value)) {
                    'boolean' => PDO::PARAM_BOOL,
                    'integer' => PDO::PARAM_INT,
                    'NULL' => PDO::PARAM_NULL,
                    default => PDO::PARAM_STR,
                };

                $stmt->bindValue($key, $value, $paramType);
            }

            $stmt->execute();

            if (Config::get('APP_DEBUG') === 'true') {
                error_log("✓ Query executed: " . substr($query, 0, 100) . "...");
            }

            return $stmt;

        } catch (PDOException $e) {
            error_log("✗ Database query error: " . $e->getMessage());
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }

    /**
     * Lấy một bản ghi dưới dạng mảng
     */
    public static function fetchOne(string $query, array $params = []): ?array
    {
        return self::query($query, $params)->fetch();
    }

    /**
     * Lấy nhiều bản ghi dưới dạng mảng
     */
    public static function fetchAll(string $query, array $params = []): array
    {
        return self::query($query, $params)->fetchAll();
    }

    /**
     * Lấy một giá trị cột đơn
     */
    public static function fetchColumn(string $query, array $params = [], int $column = 0): mixed
    {
        return self::query($query, $params)->fetchColumn($column);
    }

    /**
     * Insert dữ liệu
     */
    public static function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $query = "INSERT INTO {$table} (" . implode(',', $columns) . ") 
                 VALUES (" . implode(',', $placeholders) . ")";

        self::query($query, array_values($data));

        return (int) self::getInstance()->lastInsertId();
    }

    /**
     * Update dữ liệu
     */
    public static function update(string $table, array $data, string $where, array $params): int
    {
        $setClause = implode(', ', array_map(fn($col) => "{$col}=?", array_keys($data)));
        $query = "UPDATE {$table} SET {$setClause} WHERE {$where}";

        $allParams = array_merge(array_values($data), $params);
        
        return self::query($query, $allParams)->rowCount();
    }

    /**
     * Delete dữ liệu
     */
    public static function delete(string $table, string $where, array $params): int
    {
        $query = "DELETE FROM {$table} WHERE {$where}";
        return self::query($query, $params)->rowCount();
    }

    /**
     * Bắt đầu Transaction
     */
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    /**
     * Commit Transaction
     */
    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    /**
     * Rollback Transaction
     */
    public static function rollback(): void
    {
        self::getInstance()->rollBack();
    }

    /**
     * Đóng kết nối
     */
    public static function close(): void
    {
        self::$connection = null;
    }
}
