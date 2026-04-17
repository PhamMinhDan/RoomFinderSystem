<?php

return [
    'host'     => $_ENV['DB_HOST']     ?? 'DB_HOST is not set in .env',
    'port'     => $_ENV['DB_PORT']     ?? 'DB_PORT is not set in .env',
    'dbname'   => $_ENV['DB_NAME']     ?? 'DB_NAME is not set in .env',
    'username' => $_ENV['DB_USER']     ?? 'DB_USER is not set in .env',
    'password' => $_ENV['DB_PASSWORD'] ?? 'DB_PASSWORD is not set in .env',
    'charset'  => 'utf8mb4',
];