<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once dirname(__DIR__) . '/core/bootstrap.php';

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = '/' . trim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

if ($uri !== '/' && str_ends_with($uri, '/')) {
    $uri = rtrim($uri, '/');
}

$routes = [
    ['GET',  '/', null, null, function () {
        require dirname(__DIR__) . '/app/Views/pages/home-page.php';
    }],
    ['GET',  '/search', null, null, function () {
        require dirname(__DIR__) . '/app/Views/pages/search.php';
    }],
    ['GET',  '/chat', null, null, function () {
        require dirname(__DIR__) . '/app/Views/pages/chat.php';
    }],
    ['GET',  '/post-room', null, null, function () {
        require dirname(__DIR__) . '/app/Views/pages/post-room.php';
    }],
    ['POST', '/api/upload',             \Controllers\UploadController::class, 'upload', null],
    ['GET',  '/auth/google',            \Controllers\AuthGoogleController::class,       'redirectToGoogle',  null],
    ['GET',  '/auth/google/callback',   \Controllers\AuthGoogleController::class,       'handleGoogleCallback', null],
    ['POST', '/auth/logout',            \Controllers\AuthGoogleController::class,       'logout',         null],
    ['GET',  '/auth/me',                \Controllers\AuthGoogleController::class,       'me',             null],
];

$matched = false;
foreach ($routes as [$routeMethod, $pattern, $controllerClass, $action, $closure]) {
    if ($method !== $routeMethod) continue;
    if ($uri    !== $pattern)     continue;

    $matched = true;

    if ($closure instanceof Closure) {
        $closure();
    } else {
        $controller = new $controllerClass();
        $controller->$action();
    }

    break;
}

if (!$matched) {
    http_response_code(404);
    echo '<h1>404 – Không tìm thấy trang</h1>';
}