<?php

$router->get('/api/auth/google', 'AuthController@redirectToGoogle');
 
// Bước 2: Google callback về đây
$router->get('/api/auth/google/callback', 'AuthController@handleGoogleCallback');
 
// Đăng xuất
$router->post('/api/auth/logout', 'AuthController@logout');
 
// API: kiểm tra trạng thái đăng nhập (dùng cho JS nếu cần)
$router->get('/api/auth/me', 'AuthController@me');

$router->post('/api/upload', 'UploadController@upload');

// Home page
$router->get('/', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/Pages/home-page.php';
});

// Search page
$router->get('/search', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/Pages/search.php';
});

// Saved rooms / Favorites page
$router->get('/saved-rooms', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/saved-rooms.php';
});

// Room detail page
$router->get('/room/:id', function($id) {
    require dirname(dirname(__FILE__)) . '/app/Views/Pages/room-detail.php';
});

// Identity verification page (Step 1)
$router->get('/verify-identity', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/partials/identity-verify.php';
});

// Post room page (Step 2 - requires identity verification)
$router->get('/post-room', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/pages/post-room.php';
});

// Backward compatibility - redirect old routes to new page
$router->get('/ekyc', function() {
    header('Location: /verify-identity');
    exit;
});

$router->get('/listing', function() {
    header('Location: /post-room');
    exit;
});

// Dashboard (sẽ tạo sau)
$router->get('/dashboard', function() {
    // require dirname(dirname(__FILE__)) . '/app/Views/dashboard.php';
});

// Chat page
$router->get('/chat', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/pages/chat.php';
});

// Landlord Dashboard Routes
$router->get('/landlord/dashboard', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/landlord/dashboard.php';
});

// Landlord Listings
$router->get('/landlord/listings', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/landlord/listings.php';
});

// Edit Listing
$router->get('/landlord/listings/:id/edit', function() {
    $id = $_GET['id'] ?? null;
    require dirname(dirname(__FILE__)) . '/app/Views/landlord/edit-listing.php';
});

// Renew Listing
$router->get('/landlord/listings/:id/renew', function() {
    $id = $_GET['id'] ?? null;
    require dirname(dirname(__FILE__)) . '/app/Views/landlord/renew-listing.php';
});

// Appointments Management
$router->get('/landlord/appointments', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/landlord/appointments.php';
});

// Messages
$router->get('/landlord/messages', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/landlord/messages.php';
});

// Account Settings
$router->get('/landlord/account', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/landlord/account.php';
});

$router->get('/api/admin/identity/list', 'AdminController@identityList');
$router->post('/api/admin/identity/approve', 'AdminController@identityApprove');
$router->post('/api/admin/identity/reject', 'AdminController@identityReject');

$router->get('/api/admin/rooms/pending', 'AdminController@roomPendingList');
$router->post('/api/admin/rooms/approve', 'AdminController@roomApprove');
$router->post('/api/admin/rooms/reject', 'AdminController@roomReject');
