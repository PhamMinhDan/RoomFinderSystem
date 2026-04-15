<?php

require_once dirname(__DIR__) . '/core/bootstrap.php';

// Khởi tạo router
$router = new Router();

// Load routes
require_once dirname(__DIR__) . '/routes/web.php';

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
} catch (Exception $e) {
    error_log("Routing error: " . $e->getMessage());
    
    http_response_code(404);
    if (Config::get('APP_DEBUG') === 'true') {
        response('Route not found', ['error' => $e->getMessage()], 404);
    } else {
        response('Page not found', null, 404);
    }
}
