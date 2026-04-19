<?php
use Core\SessionManager;
SessionManager::start();

$currentUser = SessionManager::getUser();
if (!$currentUser) {
    header('Location: /?auth_error=' . urlencode('Vui lòng đăng nhập'));
    exit;
}

$title      = "Bảng điều khiển chủ nhà";
$css        = ['landlord-dashboard.css'];
$js         = ['landlord-dashboard.js'];
$showFooter = false;

ob_start();
?>

<div class="dashboard-container">

    <!-- Tab navigation -->
    <div class="status-tabs">
        <button class="status-tab active" onclick="filterByStatus('active', this)">
            <i class="fas fa-check-circle"></i>
            Đang hiển thị
            <span class="status-count" id="count-active">–</span>
        </button>
        <button class="status-tab" onclick="filterByStatus('expired', this)">
            <i class="fas fa-clock"></i>
            Hết hạn
            <span class="status-count" id="count-expired">–</span>
        </button>
        <button class="status-tab" onclick="filterByStatus('rejected', this)">
            <i class="fas fa-ban"></i>
            Bị từ chối
            <span class="status-count" id="count-rejected">–</span>
        </button>
        <button class="status-tab" onclick="filterByStatus('pending', this)">
            <i class="fas fa-hourglass-half"></i>
            Chờ duyệt
            <span class="status-count" id="count-pending">–</span>
        </button>
        <button class="status-tab" onclick="window.location.href='/post-room'" style="margin-left:auto;background:var(--accent);color:#fff;">
            <i class="fas fa-plus"></i>
            Đăng tin mới
        </button>
    </div>

    <!-- Main content -->
    <div class="dashboard-content">
        <!-- Listings -->
        <div class="listings-section" id="listingsSection">
            <div style="text-align:center;padding:3rem;color:var(--gray-400);">
                <i class="fas fa-spinner fa-spin" style="font-size:2rem;"></i>
                <p style="margin-top:1rem;font-size:.875rem;">Đang tải dữ liệu...</p>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="dash-sidebar">
            <!-- Quick stats -->
            <div class="dash-widget">
                <h3><i class="fas fa-chart-bar"></i> Tổng quan</h3>
                <div id="quickStats" style="display:flex;flex-direction:column;gap:.5rem;font-size:.8rem;">
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--gray-500);">Tổng tin đăng</span>
                        <strong id="stat-total">–</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--green);">Đang hiển thị</span>
                        <strong id="stat-active">–</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--gold);">Chờ duyệt</span>
                        <strong id="stat-pending">–</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--gray-400);">Hết hạn</span>
                        <strong id="stat-expired">–</strong>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="dash-widget">
                <h3><i class="fas fa-calendar"></i> Tháng <?= date('n/Y') ?></h3>
                <div class="calendar" id="calendarWidget"></div>
            </div>

            <!-- Identity status -->
            <div class="dash-widget" id="identityWidget">
                <h3><i class="fas fa-shield-alt"></i> Trạng thái xác thực</h3>
                <?php if ($currentUser['identity_verified'] ?? false): ?>
                <div style="display:flex;align-items:center;gap:.5rem;font-size:.8rem;color:var(--green);font-weight:600;">
                    <i class="fas fa-check-circle"></i> Đã xác thực eKYC
                </div>
                <?php else: ?>
                <div style="font-size:.8rem;color:var(--gray-500);line-height:1.6;margin-bottom:.75rem;">
                    Xác thực danh tính để bắt đầu đăng tin và tăng độ tin cậy.
                </div>
                <a href="/verify-identity"
                   style="display:flex;align-items:center;justify-content:center;gap:.4rem;background:var(--primary);color:#fff;padding:.5rem;border-radius:.5rem;font-size:.8rem;font-weight:700;text-decoration:none;">
                    <i class="fas fa-shield-check"></i> Xác thực ngay
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';