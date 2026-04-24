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

// GET /api/rooms/{id}
if ($method === 'GET' && preg_match('#^/api/rooms/(\d+)$#', $uri, $m)) {
    (new \Controllers\RoomController())->show((int)$m[1]);
    exit;
}

// GET /room/{id}
if ($method === 'GET' && preg_match('#^/room/(\d+)$#', $uri, $m)) {
    $roomId = (int)$m[1];
    require dirname(__DIR__) . '/app/Views/pages/room-detail.php';
    exit;
}

// GET /chat/{id}
if ($method === 'GET' && preg_match('#^/chat/(\d+)$#', $uri, $m)) {
    (new \Controllers\ChatController())->index((int)$m[1]);
    exit;
}

// GET /api/chat/{id}/messages
if ($method === 'GET' && preg_match('#^/api/chat/(\d+)/messages$#', $uri, $m)) {
    (new \Controllers\ChatController())->messages((int)$m[1]);
    exit;
}

// POST /api/chat/{id}/messages
if ($method === 'POST' && preg_match('#^/api/chat/(\d+)/messages$#', $uri, $m)) {
    (new \Controllers\ChatController())->sendMessage((int)$m[1]);
    exit;
}

// POST /api/chat/{id}/upload
if ($method === 'POST' && preg_match('#^/api/chat/(\d+)/upload$#', $uri, $m)) {
    (new \Controllers\ChatController())->upload((int)$m[1]);
    exit;
}

// GET /api/chat/{id}/sse
if ($method === 'GET' && preg_match('#^/api/chat/(\d+)/sse$#', $uri, $m)) {
    (new \Controllers\ChatController())->sse((int)$m[1]);
    exit;
}

// POST /api/chat/{id}/recall/{mid}
if ($method === 'POST' && preg_match('#^/api/chat/(\d+)/recall/(\d+)$#', $uri, $m)) {
    (new \Controllers\ChatController())->recall((int)$m[1], (int)$m[2]);
    exit;
}

// POST /api/chat/{id}/read
if ($method === 'POST' && preg_match('#^/api/chat/(\d+)/read$#', $uri, $m)) {
    (new \Controllers\ChatController())->markRead((int)$m[1]);
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

// GET  /api/rooms/{id}/reviews
if ($method === 'GET' && preg_match('#^/api/rooms/(\d+)/reviews$#', $uri, $m)) {
    (new \Controllers\ReviewController())->index((int)$m[1]);
    exit;
}
 
// POST /api/rooms/{id}/reviews
if ($method === 'POST' && preg_match('#^/api/rooms/(\d+)/reviews$#', $uri, $m)) {
    (new \Controllers\ReviewController())->store((int)$m[1]);
    exit;
}

$routes = [
    // ── Pages ──────────────────────────────────────────────────────────────
    ['GET',  '/',                 null, null, fn() => require dirname(__DIR__) . '/app/Views/pages/home-page.php'],
    ['GET',  '/search',           null, null, fn() => require dirname(__DIR__) . '/app/Views/pages/search.php'],
    ['GET',  '/chat',             null, null, fn() => (new \Controllers\ChatController())->index()],
    ['GET',  '/post-room',        null, null, fn() => require dirname(__DIR__) . '/app/Views/pages/post-room.php'],
    ['GET',  '/verify-identity',  null, null, fn() => require dirname(__DIR__) . '/app/Views/partials/identity-verify.php'],
    ['GET',  '/saved-rooms',      null, null, fn() => require dirname(__DIR__) . '/app/Views/pages/saved-rooms.php'],
    ['GET',  '/profile',          null, null, fn() => require dirname(__DIR__) . '/app/Views/pages/profile.php'],
    ['GET', '/map',               null, null, fn() => require dirname(__DIR__) . '/app/Views/pages/map-full.php'],


    // ── Landlord pages ──────────────────────────────────────────────────────
    ['GET',  '/landlord/dashboard',    null, null, fn() => require dirname(__DIR__) . '/app/Views/landlord/dashboard.php'],
    ['GET',  '/landlord/listings',     null, null, fn() => require dirname(__DIR__) . '/app/Views/landlord/listings.php'],
    ['GET',  '/landlord/messages',     null, null, fn() => require dirname(__DIR__) . '/app/Views/landlord/messages.php'],
    ['GET',  '/landlord/appointments', null, null, fn() => require dirname(__DIR__) . '/app/Views/landlord/appointments.php'],
    ['GET',  '/landlord/account',      null, null, fn() => require dirname(__DIR__) . '/app/Views/landlord/account.php'],

    // ── Admin pages ─────────────────────────────────────────────────────────
    ['GET',  '/admin/dashboard',  null, null, fn() => require dirname(__DIR__) . '/app/Views/admin/dashboard.php'],

    // ── Redirects ────────────────────────────────────────────────────────────
    ['GET',  '/ekyc',    null, null, fn() => (header('Location: /verify-identity') ?: exit())],
    ['GET',  '/listing', null, null, fn() => (header('Location: /post-room')       ?: exit())],

    // ── API: Upload ──────────────────────────────────────────────────────────
    ['POST', '/api/upload', \Controllers\UploadController::class, 'upload', null],

    // ── API: Auth ────────────────────────────────────────────────────────────
    ['GET',  '/api/auth/google',          \Controllers\AuthGoogleController::class, 'redirectToGoogle',     null],
    ['GET',  '/api/auth/google/callback', \Controllers\AuthGoogleController::class, 'handleGoogleCallback', null],
    ['POST', '/api/auth/logout',          \Controllers\AuthGoogleController::class, 'logout',               null],
    ['GET',  '/api/auth/me',              \Controllers\AuthGoogleController::class, 'me',                   null],

    // ── API: Identity Verification ────────────────────────────────────────────
    ['POST', '/api/identity/submit',        \Controllers\IdentityVerificationController::class, 'submit',         null],
    ['GET',  '/api/identity/status',        \Controllers\IdentityVerificationController::class, 'status',         null],
    ['GET',  '/api/admin/identity/list',    \Controllers\AdminController::class,                'identityList',   null],
    ['POST', '/api/admin/identity/approve', \Controllers\AdminController::class,                'identityApprove',null],
    ['POST', '/api/admin/identity/reject',  \Controllers\AdminController::class,                'identityReject', null],

    // ── API: Rooms ────────────────────────────────────────────────────────────
    ['GET',  '/api/check-post-eligibility', \Controllers\RoomController::class, 'checkEligibility', null],
    ['POST', '/api/rooms',                  \Controllers\RoomController::class, 'store',            null],
    ['GET',  '/api/rooms/public',           \Controllers\RoomController::class, 'publicList', null],
    ['GET', '/api/amenities',               \Controllers\RoomController::class, 'getAmenities', null],
    ['GET',  '/api/landlord/rooms',         \Controllers\RoomController::class, 'landlordList',     null],
    ['GET',  '/api/admin/rooms/pending',    \Controllers\AdminController::class,'roomPendingList',  null],
    ['POST', '/api/admin/rooms/approve',    \Controllers\AdminController::class,'roomApprove',      null],
    ['POST', '/api/admin/rooms/reject',     \Controllers\AdminController::class,'roomReject',       null],

    // ── API: Chat  ────────────────────────────────────────────────────
    ['POST', '/api/chat/room-preview',   \Controllers\ChatController::class, 'roomPreview',   null],
    ['POST', '/api/chat/open-room',      \Controllers\ChatController::class, 'openRoom',      null],
    ['POST', '/api/chat/open-direct',    \Controllers\ChatController::class, 'openDirect',    null],
    ['GET',  '/api/chat/conversations',  \Controllers\ChatController::class, 'conversations', null],

    // ── API: Profile ───────────────────────────────────────────────────────
    ['GET',   '/api/profile',          \Controllers\ProfileController::class, 'show',          null],
    ['PATCH', '/api/profile',          \Controllers\ProfileController::class, 'update',         null],
    ['PUT',   '/api/profile/address',  \Controllers\ProfileController::class, 'updateAddress',  null],

    // ── API: Notifications ──────────────────────────────────────────────────
    ['GET',  '/api/notifications',           \Controllers\NotificationController::class, 'index',        null],
    ['GET',  '/api/notifications/unread',    \Controllers\NotificationController::class, 'unreadCount',  null],
    ['POST', '/api/notifications/read',      \Controllers\NotificationController::class, 'markAsRead',   null],
    ['POST', '/api/notifications/read-all',  \Controllers\NotificationController::class, 'markAllRead',  null],

    // ── API: Favourites ────────────────────────────────────────────────────────
    ['POST', '/api/favourites/toggle', \Controllers\FavouriteController::class, 'toggle', null],
    ['GET',  '/api/favourites',        \Controllers\FavouriteController::class, 'index',  null],
    ['GET',  '/api/favourites/ids',    \Controllers\FavouriteController::class, 'ids',    null],

    ['GET',  '/api/landlord/rooms/(\d+)/edit-data',    \Controllers\RoomEditController::class, 'getEditData', null],
    ['POST', '/api/landlord/rooms/(\d+)/edit-request', \Controllers\RoomEditController::class, 'submitEdit',  null],

    // ── API: Admin Edit Requests ──────────────────────────────────────────────
    ['GET',  '/api/admin/edit-requests/pending', \Controllers\AdminController::class, 'editRequestList',    null],
    ['POST', '/api/admin/edit-requests/approve', \Controllers\AdminController::class, 'editRequestApprove', null],
    ['POST', '/api/admin/edit-requests/reject',  \Controllers\AdminController::class, 'editRequestReject',  null],
];

// ══════════════════════════════════════════════════════════════════════════
// Router
// ══════════════════════════════════════════════════════════════════════════
$matched = false;
foreach ($routes as [$routeMethod, $pattern, $controllerClass, $action, $closure]) {
    if ($method !== $routeMethod) continue;

    $regex = '#^' . $pattern . '$#';

    if (preg_match($regex, $uri, $matches)) {
        $matched = true;
        
        array_shift($matches); 

        $params = array_map(function($value) {
            return is_numeric($value) ? (int)$value : $value;
        }, $matches);

        if ($closure instanceof Closure) {
            call_user_func_array($closure, $params);
        } else {
            $controller = new $controllerClass();
            call_user_func_array([$controller, $action], $params);
        }
        break;
    }
}

if (!$matched) {
    http_response_code(404);
    require dirname(__DIR__) . '/app/Views/errors/404.php';
}