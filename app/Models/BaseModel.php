<?php

namespace Models;

/**
 * BaseModel – lớp cha cho tất cả Model.
 * Mỗi Model con chỉ khai báo $table, $primaryKey và các hằng số liên quan.
 * Không chứa SQL – phần đó thuộc Repository.
 */
abstract class BaseModel
{
    protected static string $table      = '';
    protected static string $primaryKey = 'id';

    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /** Truy xuất thuộc tính như $model->name */
    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /** Gán thuộc tính */
    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public static function getTable(): string
    {
        return static::$table;
    }

    public static function getPrimaryKey(): string
    {
        return static::$primaryKey;
    }
}