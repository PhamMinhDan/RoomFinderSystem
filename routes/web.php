<?php

$router->get('/auth/google', 'AuthController@redirectToGoogle');
 
// Bước 2: Google callback về đây
$router->get('/auth/google/callback', 'AuthController@handleGoogleCallback');
 
// Đăng xuất
$router->post('/auth/logout', 'AuthController@logout');
 
// API: kiểm tra trạng thái đăng nhập (dùng cho JS nếu cần)
$router->get('/auth/me', 'AuthController@me');

$router->post('/api/upload', 'UploadController@upload');

// Home page
$router->get('/', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/Pages/home-page.php';
});

// Search page
$router->get('/search', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/Pages/search.php';
});

// Room detail page
$router->get('/room/:id', function($id) {
    require dirname(dirname(__FILE__)) . '/app/Views/Pages/room-detail.php';
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
