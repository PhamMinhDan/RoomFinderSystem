<?php
use Core\SessionManager;
SessionManager::start();

$activeNav   = $activeNav ?? '';
$currentUser = SessionManager::getUser();
$authError   = $_GET['auth_error'] ?? null;
?>
<header class="header">
    <div class="header-inner">

        <!-- Logo -->
        <a href="/" class="logo">
            <div class="logo-icon">🏠</div>
            RoomFinder.vn
        </a>

        <!-- Navigation -->
        <nav class="nav">
            <a href="/"       <?= $activeNav === 'home'    ? 'class="active"' : '' ?>>Trang chủ</a>
            <a href="/search" <?= $activeNav === 'search'  ? 'class="active"' : '' ?>>Tìm phòng</a>
            <a href="#"       <?= $activeNav === 'rent'    ? 'class="active"' : '' ?>>Cho thuê</a>
            <a href="#"       <?= $activeNav === 'blog'    ? 'class="active"' : '' ?>>Blog</a>
            <a href="#contact"<?= $activeNav === 'contact' ? 'class="active"' : '' ?>>Liên hệ</a>
        </nav>

        <!-- Search box -->
        <div class="header-search">
            <i class="fas fa-search"></i>
            <input
                type="text"
                placeholder="Tìm kiếm nhanh..."
                onkeydown="if(event.key==='Enter') window.location.href='/search?keyword='+encodeURIComponent(this.value)"
            >
        </div>

        <!-- Action buttons -->
        <div class="header-actions">
            <button class="btn-post-h">+ Đăng tin</button>

            <?php if ($currentUser): ?>
                <!-- ── Đã đăng nhập: hiện avatar + dropdown ── -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-trigger" onclick="toggleDropdown()">
                        <?php if (!empty($currentUser['avatar_url'])): ?>
                            <img
                                src="<?= htmlspecialchars($currentUser['avatar_url']) ?>"
                                alt="Avatar"
                                class="nav-avatar"
                                referrerpolicy="no-referrer"
                            >
                        <?php else: ?>
                            <div class="nav-avatar-placeholder">
                                <?= mb_strtoupper(mb_substr($currentUser['full_name'] ?? 'U', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <span class="nav-name">
                            <?= htmlspecialchars($currentUser['full_name'] ?? $currentUser['username']) ?>
                        </span>
                        <i class="fas fa-chevron-down nav-chevron" id="dropdownChevron"></i>
                    </div>

                    <div class="dropdown-content" id="dropdownMenu">
                        <!-- Thông tin cá nhân -->
                        <div class="dropdown-user-info">
                            <?php if (!empty($currentUser['avatar_url'])): ?>
                                <img
                                    src="<?= htmlspecialchars($currentUser['avatar_url']) ?>"
                                    alt="Avatar"
                                    class="dropdown-avatar"
                                    referrerpolicy="no-referrer"
                                >
                            <?php else: ?>
                                <div class="dropdown-avatar-placeholder">
                                    <?= mb_strtoupper(mb_substr($currentUser['full_name'] ?? 'U', 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <div class="dropdown-name">
                                    <?= htmlspecialchars($currentUser['full_name'] ?? '') ?>
                                </div>
                                <div class="dropdown-email">
                                    <?= htmlspecialchars($currentUser['email'] ?? '') ?>
                                </div>
                                <span class="dropdown-role">
                                    <?= htmlspecialchars($currentUser['role'] ?? 'RENTER') ?>
                                </span>
                            </div>
                        </div>

                        <div class="dropdown-divider"></div>

                        <a href="/profile"><i class="fas fa-user"></i> Hồ sơ cá nhân</a>
                        <a href="/my-rooms"><i class="fas fa-home"></i> Phòng của tôi</a>
                        <a href="/settings"><i class="fas fa-cog"></i> Cài đặt</a>

                        <div class="dropdown-divider"></div>

                        <!-- Đăng xuất -->
                        <form method="POST" action="/auth/logout" style="margin:0">
                            <input type="hidden"
                                   name="csrf_token"
                                   value="<?= htmlspecialchars(SessionManager::getCsrfToken()) ?>">
                            <button type="submit" class="logout-btn">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <!-- ── Chưa đăng nhập: nút Đăng nhập ── -->
                <button class="btn-login-h" onclick="openLoginModal()">Đăng nhập</button>
            <?php endif; ?>
        </div>

    </div>
</header>

<?php if ($authError): ?>
<!-- Toast thông báo lỗi auth -->
<div class="auth-error-toast" id="authErrorToast">
    <i class="fas fa-exclamation-circle"></i>
    <?= htmlspecialchars(urldecode($authError)) ?>
    <button onclick="this.parentElement.remove()">✕</button>
</div>
<script>
    setTimeout(() => {
        const t = document.getElementById('authErrorToast');
        if (t) t.classList.add('hide');
        setTimeout(() => t?.remove(), 400);
    }, 5000);
</script>
<?php endif; ?>

<script>
function toggleDropdown() {
    const menu     = document.getElementById('dropdownMenu');
    const chevron  = document.getElementById('dropdownChevron');
    const isOpen   = menu.classList.toggle('show');
    chevron.style.transform = isOpen ? 'rotate(180deg)' : 'rotate(0deg)';
}

// Đóng dropdown khi click ra ngoài
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('dropdownMenu')?.classList.remove('show');
        const chevron = document.getElementById('dropdownChevron');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }
});
</script>