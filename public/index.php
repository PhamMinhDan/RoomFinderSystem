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

// ── Dynamic routes (cần xử lý trước vòng foreach) ─────────────────────────

// GET /api/rooms/{id}
if ($method === 'GET' && preg_match('#^/api/rooms/(\d+)$#', $uri, $m)) {
    $ctrl = new \Controllers\RoomController();
    $ctrl->show((int)$m[1]);
    exit;
}

// GET /room/{id}  – trang chi tiết phòng
if ($method === 'GET' && preg_match('#^/room/(\d+)$#', $uri, $m)) {
    $roomId = (int)$m[1];
    require dirname(__DIR__) . '/app/Views/pages/room-detail.php';
    exit;
}

// GET /landlord/listings/{id}/edit
if ($method === 'GET' && preg_match('#^/landlord/listings/(\d+)/edit$#', $uri, $m)) {
    $id = (int)$m[1];
    require dirname(__DIR__) . '/app/Views/landlord/edit-listing.php';
    exit;
}

// GET /landlord/listings/{id}/renew
if ($method === 'GET' && preg_match('#^/landlord/listings/(\d+)/renew$#', $uri, $m)) {
    $id = (int)$m[1];
    require dirname(__DIR__) . '/app/Views/landlord/renew-listing.php';
    exit;
}

// ── Static routes ──────────────────────────────────────────────────────────
$routes = [
    // Pages
    ['GET',  '/',                          null, null, function () {
        require dirname(__DIR__) . '/app/Views/pages/home-page.php';
    }],
    ['GET',  '/search',                    null, null, function () {
        require dirname(__DIR__) . '/app/Views/pages/search.php';
    }],
    ['GET',  '/chat',                      null, null, function () {
        require dirname(__DIR__) . '/app/Views/pages/chat.php';
    }],
    ['GET',  '/post-room',                 null, null, function () {
        require dirname(__DIR__) . '/app/Views/pages/post-room.php';
    }],
    ['GET',  '/verify-identity',           null, null, function () {
        require dirname(__DIR__) . '/app/Views/partials/identity-verify.php';
    }],
    ['GET',  '/saved-rooms',               null, null, function () {
        require dirname(__DIR__) . '/app/Views/pages/saved-rooms.php';
    }],

    // Landlord pages
    ['GET',  '/landlord/dashboard',        null, null, function () {
        require dirname(__DIR__) . '/app/Views/landlord/dashboard.php';
    }],
    ['GET',  '/landlord/listings',         null, null, function () {
        require dirname(__DIR__) . '/app/Views/landlord/listings.php';
    }],
    ['GET',  '/landlord/messages',         null, null, function () {
        require dirname(__DIR__) . '/app/Views/landlord/messages.php';
    }],
    ['GET',  '/landlord/appointments',     null, null, function () {
        require dirname(__DIR__) . '/app/Views/landlord/appointments.php';
    }],
    ['GET',  '/landlord/account',          null, null, function () {
        require dirname(__DIR__) . '/app/Views/landlord/account.php';
    }],

    // Admin page
    ['GET',  '/admin/dashboard',           null, null, function () {
        require dirname(__DIR__) . '/app/Views/admin/dashboard-admin.php';
    }],

    // Backward compatibility redirects
    ['GET',  '/ekyc',                      null, null, function () {
        header('Location: /verify-identity'); exit;
    }],
    ['GET',  '/listing',                   null, null, function () {
        header('Location: /post-room'); exit;
    }],

    // ── API: Upload ──────────────────────────────────────────────────────
    ['POST', '/api/upload',                \Controllers\UploadController::class,              'upload',            null],

    // ── API: Auth ────────────────────────────────────────────────────────
    ['GET',  '/api/auth/google',           \Controllers\AuthGoogleController::class,          'redirectToGoogle',  null],
    ['GET',  '/api/auth/google/callback',  \Controllers\AuthGoogleController::class,          'handleGoogleCallback', null],
    ['POST', '/api/auth/logout',           \Controllers\AuthGoogleController::class,          'logout',            null],
    ['GET',  '/api/auth/me',               \Controllers\AuthGoogleController::class,          'me',                null],

    // ── API: Identity Verification ───────────────────────────────────────
    ['POST', '/api/identity/submit',       \Controllers\IdentityVerificationController::class, 'submit',           null],
    ['GET',  '/api/identity/status',       \Controllers\IdentityVerificationController::class, 'status',           null],
    ['GET',  '/api/admin/identity/list',   \Controllers\AdminController::class, 'identityList',    null],
    ['POST', '/api/admin/identity/approve',\Controllers\AdminController::class, 'identityApprove', null],
    ['POST', '/api/admin/identity/reject', \Controllers\AdminController::class, 'identityReject',  null],

    
    // ── API: Rooms ───────────────────────────────────────────────────────
    ['GET',  '/api/check-post-eligibility',\Controllers\RoomController::class,                'checkEligibility',  null],
    ['POST', '/api/rooms',                 \Controllers\RoomController::class,                'store',             null],
    ['GET',  '/api/rooms/public',          \Controllers\RoomController::class,                'publicList',        null],
    ['GET',  '/api/landlord/rooms',        \Controllers\RoomController::class,                'landlordList',      null],
    ['GET',  '/api/admin/rooms/pending',   \Controllers\AdminController::class, 'roomPendingList', null],
    ['POST', '/api/admin/rooms/approve',   \Controllers\AdminController::class, 'roomApprove',    null],
    ['POST', '/api/admin/rooms/reject',    \Controllers\AdminController::class, 'roomReject',     null],
];

// ── Router ────────────────────────────────────────────────────────────────
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
    require dirname(__DIR__) . '/app/Views/errors/404.php';
}