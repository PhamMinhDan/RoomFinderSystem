<?php

use Core\SessionManager;
SessionManager::start();

$currentUser = SessionManager::getUser();
if (!$currentUser || ($currentUser['role'] ?? '') !== 'ADMIN') {
    header('Location: /?auth_error=' . urlencode('Bạn không có quyền truy cập trang này'));
    exit;
}

$title = "Admin - Bảng điều khiển";
$css   = ['dashboard-admin.css'];
$js    = ['dashboard-admin.js'];
$showFooter = false;
$activeNav = '';

ob_start();
?>

<div class="admin-page">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-logo">
            <span>🛡️</span>
            <span>Admin Panel</span>
        </div>
        <nav class="admin-nav">
            <button class="admin-nav-item active" onclick="showSection('identity')">
                <i class="fas fa-id-card"></i> Xác thực danh tính
                <span class="admin-badge" id="identityBadge">0</span>
            </button>
            <button class="admin-nav-item" onclick="showSection('rooms')">
                <i class="fas fa-home"></i> Duyệt bài đăng
                <span class="admin-badge" id="roomsBadge">0</span>
            </button>
        </nav>
    </aside>

    <!-- Main -->
    <main class="admin-main">
        <!-- IDENTITY SECTION -->
        <div id="sectionIdentity" class="admin-section">
            <div class="admin-section-header">
                <h1><i class="fas fa-id-card"></i> Xác thực danh tính chờ duyệt</h1>
                <button class="btn-refresh" onclick="loadIdentityList()">
                    <i class="fas fa-sync-alt"></i> Làm mới
                </button>
            </div>
            <div id="identityList" class="admin-list">
                <div class="loading-state"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>
            </div>
        </div>

        <!-- ROOMS SECTION -->
        <div id="sectionRooms" class="admin-section" style="display:none;">
            <div class="admin-section-header">
                <h1><i class="fas fa-home"></i> Bài đăng phòng chờ duyệt</h1>
                <button class="btn-refresh" onclick="loadRoomsList()">
                    <i class="fas fa-sync-alt"></i> Làm mới
                </button>
            </div>
            <div id="roomsList" class="admin-list">
                <div class="loading-state"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>
            </div>
        </div>
    </main>
</div>

<!-- Modal xem ảnh -->
<div id="imgModal" class="img-modal" style="display: none;" onclick="closeImgModal()">
    <div class="img-modal-content" onclick="event.stopPropagation()">
        <button class="img-modal-close" onclick="closeImgModal()"><i class="fas fa-times"></i></button>
        <img id="imgModalImg" src="" alt="Preview">
    </div>
</div>

<!-- Toast -->
<div id="adminToast" class="admin-toast" style="display:none;"></div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';