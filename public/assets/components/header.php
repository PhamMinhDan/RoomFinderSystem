<?php
/**
 * Header Component - Premium Navigation Header
 * Phần header dùng chung cho tất cả các trang
 */
?>
<!-- Header -->
<header class="header-nav">
    <div class="header-content">
        <!-- Logo -->
        <div class="header-logo">
            <a href="/" class="logo-brand">
                <span class="logo-icon">🏠</span>
                <span class="logo-text">RoomFinder.vn</span>
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="header-menu">
            <a href="/" class="menu-link active">Trang chủ</a>
            <a href="#" class="menu-link">Tìm phòng</a>
            <a href="#" class="menu-link">Blog</a>
        </nav>

        <!-- Search + Actions -->
        <div class="header-right">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Tìm kiếm...">
            </div>

            <button class="btn-post" onclick="alert('Đến trang đăng tin')">
                Đăng tin
            </button>
            
            <button class="btn-login" onclick="openModal('login-modal')">
                Đăng nhập
            </button>
        </div>
    </div>
</header>

<style>
    .header-nav {
        background: var(--white);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .header-content {
        max-width: 1360px;
        margin: 0 auto;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 30px;
    }

    .header-logo {
        display: flex;
        align-items: center;
        flex-shrink: 0;
    }

    .logo-brand {
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        color: var(--primary-color);
        font-weight: 700;
        font-size: 16px;
        transition: all 0.3s var(--transition);
    }

    .logo-brand:hover {
        opacity: 0.8;
    }

    .logo-icon {
        font-size: 24px;
    }

    .header-menu {
        display: flex;
        gap: 30px;
        align-items: center;
        flex: 1;
    }

    .menu-link {
        text-decoration: none;
        color: var(--gray-700);
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s var(--transition);
        position: relative;
    }

    .menu-link:hover,
    .menu-link.active {
        color: var(--primary-color);
    }

    .menu-link.active::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--primary-color);
        border-radius: 2px;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-shrink: 0;
    }

    .search-box {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: 20px;
        padding: 8px 14px;
        min-width: 200px;
    }

    .search-box i {
        color: var(--gray-400);
        font-size: 14px;
    }

    .search-box input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 14px;
        color: var(--gray-700);
        width: 100%;
    }

    .search-box input::placeholder {
        color: var(--gray-400);
    }

    .btn-post {
        background: var(--accent-color);
        color: var(--white);
        border: none;
        padding: 8px 18px;
        border-radius: 18px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.3s var(--transition);
    }

    .btn-post:hover {
        opacity: 0.9;
        transform: translateY(-2px);
    }

    .btn-login {
        background: transparent;
        color: var(--primary-color);
        border: 2px solid var(--primary-color);
        padding: 7px 18px;
        border-radius: 18px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.3s var(--transition);
    }

    .btn-login:hover {
        background: var(--primary-color);
        color: var(--white);
    }

    @media (max-width: 1024px) {
        .header-content {
            gap: 20px;
        }

        .header-menu {
            gap: 20px;
        }

        .search-box {
            min-width: 150px;
        }
    }

    @media (max-width: 768px) {
        .header-content {
            padding: 10px 15px;
            gap: 15px;
        }

        .header-menu {
            display: none;
        }

        .search-box {
            min-width: 120px;
            padding: 6px 10px;
            font-size: 12px;
        }

        .btn-post,
        .btn-login {
            padding: 6px 12px;
            font-size: 12px;
        }
    }
</style>
    </div>
</header>
