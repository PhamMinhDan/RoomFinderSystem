<?php
use Core\SessionManager;
SessionManager::start();

$activeNav   = $activeNav ?? '';
$currentUser = SessionManager::getUser();
$authError   = $_GET['auth_error'] ?? null;
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
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
            <a href="/"       class="<?= $activeNav==='home'   ?'active':'' ?>">Trang chủ</a>
            <a href="/search" class="<?= $activeNav==='search' ?'active':'' ?>">Tìm phòng</a>
            <a href="/chat"   class="<?= $activeNav==='chat'   ?'active':'' ?>">Liên hệ</a>
        </nav>

        <!-- Search box -->
        <div class="header-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Tìm kiếm nhanh..."
                   onkeydown="if(event.key==='Enter') window.location.href='/search?keyword='+encodeURIComponent(this.value)">
        </div>

        <!-- Actions -->
        <div class="header-actions">
            <?php if ($currentUser): ?>
                <button class="hdr-btn-manage" onclick="window.location.href='/landlord/dashboard'">
                    <i class="fas fa-th-large"></i> Quản lý tin
                </button>
            <?php endif; ?>

            <button class="btn-post-h" onclick="handlePostRoom()">
                <i class="fas fa-plus"></i> Đăng tin
            </button>

            <button class="hdr-icon-btn" title="Thông báo" id="notifBtn">
                <i class="far fa-bell"></i>
            </button>

            <?php if ($currentUser): ?>
                <!-- Đã đăng nhập -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-trigger" onclick="toggleDropdown()">
                        <?php if (!empty($currentUser['avatar_url'])): ?>
                            <img src="<?= htmlspecialchars($currentUser['avatar_url']) ?>"
                                 alt="Avatar" class="nav-avatar" referrerpolicy="no-referrer">
                        <?php else: ?>
                            <div class="nav-avatar-placeholder">
                                <?= mb_strtoupper(mb_substr($currentUser['full_name'] ?? 'U', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <span class="nav-name">
                            <?= htmlspecialchars($currentUser['full_name'] ?? $currentUser['username'] ?? '') ?>
                        </span>
                        <i class="fas fa-chevron-down nav-chevron" id="dropdownChevron"></i>
                    </div>

                    <div class="dropdown-content" id="dropdownMenu">
                        <div class="dropdown-user-info">
                            <?php if (!empty($currentUser['avatar_url'])): ?>
                                <img src="<?= htmlspecialchars($currentUser['avatar_url']) ?>"
                                     alt="Avatar" class="dropdown-avatar" referrerpolicy="no-referrer">
                            <?php else: ?>
                                <div class="dropdown-avatar-placeholder">
                                    <?= mb_strtoupper(mb_substr($currentUser['full_name'] ?? 'U', 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <div class="dropdown-name"><?= htmlspecialchars($currentUser['full_name'] ?? '') ?></div>
                                <div class="dropdown-email"><?= htmlspecialchars($currentUser['email'] ?? '') ?></div>
                                <span class="dropdown-role">
                                    <?php
                                    $roleLabel = ['ADMIN'=>'👑 Admin','LANDLORD'=>'🏠 Chủ nhà','RENTER'=>'🔍 Người thuê'];
                                    echo $roleLabel[$currentUser['role'] ?? 'RENTER'] ?? $currentUser['role'];
                                    ?>
                                </span>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="/profile"><i class="fas fa-user"></i> Hồ sơ cá nhân</a>
                        <a href="/landlord/dashboard"><i class="fas fa-home"></i> Quản lý tin đăng</a>
                        <a href="/saved-rooms"><i class="fas fa-heart"></i> Phòng đã lưu</a>
                        <a href="/landlord/account"><i class="fas fa-cog"></i> Cài đặt</a>
                        <?php if (($currentUser['role'] ?? '') === 'ADMIN'): ?>
                        <div class="dropdown-divider"></div>
                        <a href="/admin/dashboard" style="color:var(--primary);font-weight:700;">
                            <i class="fas fa-shield-alt"></i> Admin Panel
                        </a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="/api/auth/logout" style="margin:0">
                            <input type="hidden" name="csrf_token"
                                   value="<?= htmlspecialchars(SessionManager::getCsrfToken()) ?>">
                            <button type="submit" class="logout-btn">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <!-- Chưa đăng nhập -->
                <button class="btn-login-h" onclick="openLoginModal()">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </button>
            <?php endif; ?>
        </div>

    </div>
</header>

<?php if ($authError): ?>
<div class="auth-error-toast" id="authErrorToast">
    <i class="fas fa-exclamation-circle"></i>
    <?= htmlspecialchars(urldecode($authError)) ?>
    <button onclick="this.parentElement.remove()">✕</button>
</div>
<script>
    setTimeout(() => {
        const t = document.getElementById('authErrorToast');
        if (t) { t.style.opacity='0'; t.style.transition='opacity .4s'; setTimeout(()=>t?.remove(),400); }
    }, 5000);
</script>
<?php endif; ?>

<script>
    window.APP_CONFIG = {
        isLoggedIn: <?= $currentUser ? 'true' : 'false' ?>
    };
</script>

<script src="/assets/js/header.js"></script>
