<?php
/**
 * Base Model Class
 * Lớp cơ sở cho tất cả models
 */

class Model
{
    protected string $table;
    protected array $fillable = [];
    protected array $attributes = [];
    protected array $casts = [];

    /**
     * Lấy tất cả bản ghi từ bảng
     */
    public static function all(): array
    {
        $modelInstance = new static();
        $query = "SELECT * FROM {$modelInstance->table}";
        return Database::fetchAll($query);
    }

    /**
     * Tìm bản ghi theo ID
     */
    public static function find($id): ?array
    {
        $modelInstance = new static();
        $query = "SELECT * FROM {$modelInstance->table} WHERE id = ?";
        return Database::fetchOne($query, [$id]);
    }

    /**
     * Tìm bản ghi theo điều kiện
     */
    public static function where(string $column, $value): array
    {
        $modelInstance = new static();
        $query = "SELECT * FROM {$modelInstance->table} WHERE {$column} = ?";
        return Database::fetchAll($query, [$value]);
    }

    /**
     * Tạo bản ghi mới
     */
    public static function create(array $data): int
    {
        $modelInstance = new static();
        $filteredData = [];

        // Chỉ lấy các attribute trong fillable
        foreach ($modelInstance->fillable as $field) {
            if (isset($data[$field])) {
                $filteredData[$field] = $data[$field];
            }
        }

        return Database::insert($modelInstance->table, $filteredData);
    }

    /**
     * Cập nhật bản ghi
     */
    public static function updateById($id, array $data): int
    {
        $modelInstance = new static();
        $filteredData = [];

        foreach ($modelInstance->fillable as $field) {
            if (isset($data[$field])) {
                $filteredData[$field] = $data[$field];
            }
        }

        return Database::update($modelInstance->table, $filteredData, 'id = ?', [$id]);
    }

    /**
     * Xóa bản ghi theo ID
     */
    public static function deleteById($id): int
    {
        $modelInstance = new static();
        return Database::delete($modelInstance->table, 'id = ?', [$id]);
    }

    /**
     * Lấy số lượng bản ghi
     */
    public static function count(): int
    {
        $modelInstance = new static();
        $query = "SELECT COUNT(*) FROM {$modelInstance->table}";
        return (int) Database::fetchColumn($query);
    }

    /**
     * Lấy tất cả bản ghi với pagination
     */
    public static function paginate(int $pageSize = 15, int $page = 1): array
    {
        $modelInstance = new static();
        $offset = ($page - 1) * $pageSize;

        $query = "SELECT * FROM {$modelInstance->table} LIMIT ? OFFSET ?";
        $data = Database::fetchAll($query, [$pageSize, $offset]);

        $total = static::count();

        return [
            'data' => $data,
            'total' => $total,
            'perPage' => $pageSize,
            'currentPage' => $page,
            'lastPage' => ceil($total / $pageSize),
        ];
    }

    /**
     * Magic method - Lấy giá trị attribute
     */
    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Magic method - Set giá trị attribute
     */
    public function __set(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Chuyển về array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Chuyển về JSON
     */
    public function toJson(): string
    {
        return json_encode($this->attributes, JSON_UNESCAPED_UNICODE);
    }
}
