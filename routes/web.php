<?php
/**
 * Web Routes
 * Định nghĩa tất cả các web routes của ứng dụng
 */

// Home page
$router->get('/', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/index.php';
});

// Search page
$router->get('/search', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/search.php';
});

// Room detail page
$router->get('/room/:id', function() {
    $id = $_GET['id'] ?? null;
    require dirname(dirname(__FILE__)) . '/app/Views/room-detail.php';
});

// eKYC page (sẽ tạo sau)
$router->get('/ekyc', function() {
    // require dirname(dirname(__FILE__)) . '/app/Views/ekyc.php';
});

// Post room page (sẽ tạo sau)
$router->get('/post-room', function() {
    // require dirname(dirname(__FILE__)) . '/app/Views/post-room.php';
});

// Dashboard (sẽ tạo sau)
$router->get('/dashboard', function() {
    // require dirname(dirname(__FILE__)) . '/app/Views/dashboard.php';
});

// Chat page (sẽ tạo sau)
$router->get('/chat', function() {
    // require dirname(dirname(__FILE__)) . '/app/Views/chat.php';
});
