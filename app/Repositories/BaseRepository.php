<?php

namespace Repositories;

use Core\Database;

abstract class BaseRepository
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function uuidToBin(string $uuid): string
    {
        return hex2bin(str_replace('-', '', $uuid));
    }
}