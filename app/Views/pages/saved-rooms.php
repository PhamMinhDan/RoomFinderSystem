<?php
$title      = "Tin đã lưu – RoomFinder.vn";
$css        = ['save-rooms.css'];
$js         = ['favourites.js', 'save-rooms.js'];
$showFooter = false;

use Core\SessionManager;
SessionManager::start();
$currentUser = SessionManager::getUser();

ob_start();
?>

<div class="saved-rooms-page">

    <?php if (!$currentUser): ?>
    <!-- ── Chưa đăng nhập ── -->
    <div class="saved-header">
        <h1>Tin đã lưu</h1>
        <p>Bạn cần đăng nhập để xem danh sách phòng yêu thích</p>
    </div>
    <div class="empty-state">
        <i class="far fa-heart"></i>
        <h2>Chưa đăng nhập</h2>
        <p>Đăng nhập để lưu và xem lại những phòng trọ bạn thích</p>
        <a href="#" onclick="openLoginModal()" class="empty-state-btn">Đăng nhập ngay</a>
    </div>

    <?php else: ?>

    <!-- ── Header ── -->
    <div class="saved-header">
        <div class="saved-header-content">
            <div>
                <h1>Tin đã lưu</h1>
                <p>Danh sách các phòng trọ bạn yêu thích</p>
            </div>
            <div class="saved-header-actions">
                <div class="saved-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="saved-search" placeholder="Tìm trong danh sách...">
                </div>
            </div>
        </div>
    </div>

    <!-- ── Skeleton loader ── -->
    <div id="saved-skeleton" class="saved-list-container">
        <?php for ($i = 0; $i < 4; $i++): ?>
        <div class="saved-room-card skeleton-card">
            <div class="saved-card-image-wrapper" style="padding-bottom:66.67%;">
                <div class="sk-block" style="position:absolute;inset:0;"></div>
            </div>
            <div class="saved-card-content">
                <div class="sk-block" style="height:16px;width:90%;border-radius:6px;margin-bottom:10px;"></div>
                <div class="sk-block" style="height:12px;width:60%;border-radius:6px;margin-bottom:8px;"></div>
                <div class="sk-block" style="height:20px;width:45%;border-radius:6px;margin-bottom:12px;"></div>
                <div class="sk-block" style="height:36px;border-radius:8px;"></div>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <!-- ── Filter Tabs (ẩn, JS hiện sau khi load) ── -->
    <div class="saved-filters" id="saved-filters" style="display:none;">
        <button class="saved-filter-btn active" data-filter="all" onclick="filterSavedRooms('all', this)">
            Tất cả <span class="filter-count" id="cnt-all">0</span>
        </button>
        <button class="saved-filter-btn" data-filter="approved" onclick="filterSavedRooms('approved', this)">
            Đang hiển thị <span class="filter-count" id="cnt-approved">0</span>
        </button>
        <button class="saved-filter-btn" data-filter="expiring" onclick="filterSavedRooms('expiring', this)">
            Sắp hết hạn <span class="filter-count" id="cnt-expiring">0</span>
        </button>
        <button class="saved-filter-btn" data-filter="expired" onclick="filterSavedRooms('expired', this)">
            Đã hết hạn <span class="filter-count" id="cnt-expired">0</span>
        </button>
    </div>

    <!-- ── Stats bar ── -->
    <div id="saved-stats" style="display:none;" class="saved-stats-bar">
        <span id="saved-total-text"></span>
        <div class="saved-stats-right">
            <button class="saved-sort-btn" onclick="toggleSort()">
                <i class="fas fa-sort-amount-down"></i> Sắp xếp
            </button>
            <select id="saved-sort-select" class="saved-sort-select" onchange="renderSaved()" style="display:none;">
                <option value="newest">Lưu mới nhất</option>
                <option value="oldest">Lưu cũ nhất</option>
                <option value="price_asc">Giá thấp → cao</option>
                <option value="price_desc">Giá cao → thấp</option>
            </select>
        </div>
    </div>

    <!-- ── Danh sách phòng (JS inject) ── -->
    <div class="saved-list-container" id="saved-list-container" style="display:none;"></div>

    <!-- ── Empty state ── -->
    <div id="saved-empty" class="empty-state" style="display:none;">
        <i class="far fa-heart"></i>
        <h2>Chưa có phòng yêu thích nào</h2>
        <p>Hãy khám phá và lưu những phòng trọ mà bạn thích</p>
        <a href="/search" class="empty-state-btn">
             Tìm phòng ngay
        </a>
    </div>

    <?php endif; ?>
</div>

<?php if ($currentUser): ?>
<script>
window.SAVED_USER_LOGGED_IN = true;
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';