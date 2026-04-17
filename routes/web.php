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

// Saved rooms / Favorites page
$router->get('/saved-rooms', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/saved-rooms.php';
});

// Room detail page
$router->get('/room/:id', function() {
    $id = $_GET['id'] ?? null;
    require dirname(dirname(__FILE__)) . '/app/Views/room-detail.php';
});

// Identity verification page (Step 1)
$router->get('/verify-identity', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/partials/identity-verify.php';
});

// Post room page (Step 2 - requires identity verification)
$router->get('/post-room', function() {
    require dirname(dirname(__FILE__)) . '/app/Views/post-room.php';
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

// Chat page (sẽ tạo sau)
$router->get('/chat', function() {
    // require dirname(dirname(__FILE__)) . '/app/Views/chat.php';
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
