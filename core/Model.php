<?php
use Core\Database;
use PDO;

class Model
{
    protected string $table;
    protected array $fillable = [];
    protected array $attributes = [];
    protected array $casts = [];


    protected static function db(): PDO
    {
        return Database::getInstance();
    }

    public static function all(): array
    {
        $instance = new static();

        $stmt = self::db()->query("SELECT * FROM {$instance->table}");
        return $stmt->fetchAll();
    }

    public static function find($id): ?array
    {
        $instance = new static();

        $stmt = self::db()->prepare("SELECT * FROM {$instance->table} WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public static function where(string $column, $value): array
    {
        $instance = new static();

        $sql = "SELECT * FROM {$instance->table} WHERE {$column} = ?";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$value]);

        return $stmt->fetchAll();
    }


    public static function create(array $data): int
    {
        $instance = new static();

        $filtered = [];

        foreach ($instance->fillable as $field) {
            if (isset($data[$field])) {
                $filtered[$field] = $data[$field];
            }
        }

        $columns = array_keys($filtered);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = "INSERT INTO {$instance->table} (" . implode(',', $columns) . ")
                VALUES (" . implode(',', $placeholders) . ")";

        $stmt = self::db()->prepare($sql);
        $stmt->execute(array_values($filtered));

        return (int) self::db()->lastInsertId();
    }

    public static function updateById($id, array $data): int
    {
        $instance = new static();

        $filtered = [];
        foreach ($instance->fillable as $field) {
            if (isset($data[$field])) {
                $filtered[$field] = $data[$field];
            }
        }

        $setClause = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($filtered)));

        $sql = "UPDATE {$instance->table} SET {$setClause} WHERE id = ?";

        $stmt = self::db()->prepare($sql);

        $params = array_values($filtered);
        $params[] = $id;

        $stmt->execute($params);

        return $stmt->rowCount();
    }


    public static function deleteById($id): int
    {
        $instance = new static();

        $stmt = self::db()->prepare("DELETE FROM {$instance->table} WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->rowCount();
    }

    public static function count(): int
    {
        $instance = new static();

        $stmt = self::db()->query("SELECT COUNT(*) FROM {$instance->table}");

        return (int) $stmt->fetchColumn();
    }

    public static function paginate(int $pageSize = 15, int $page = 1): array
    {
        $instance = new static();

        $offset = ($page - 1) * $pageSize;

        $stmt = self::db()->prepare(
            "SELECT * FROM {$instance->table} LIMIT ? OFFSET ?"
        );

        $stmt->bindValue(1, $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetchAll();

        $total = static::count();

        return [
            'data' => $data,
            'total' => $total,
            'perPage' => $pageSize,
            'currentPage' => $page,
            'lastPage' => ceil($total / $pageSize),
        ];
    }


    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function toJson(): string
    {
        return json_encode($this->attributes, JSON_UNESCAPED_UNICODE);
    }
}
