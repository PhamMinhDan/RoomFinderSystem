<?php
/**
 * Header Component - Dashboard Navigation Header
 */
?>
<!-- Header -->
<header class="hdr-dashboard">
    <div class="hdr-wrapper">
        <!-- Logo -->
        <div class="hdr-logo">
            <div class="hdr-logo-icon">🏠</div>
            <span class="hdr-logo-text">Smart Room</span>
        </div>

        <!-- Navigation Menu -->
        <nav class="hdr-nav">
            <a href="/" class="hdr-nav-item">Trang chủ</a>
            <a href="/search" class="hdr-nav-item">Tìm phòng</a>
            <a href="#" class="hdr-nav-item">Blog</a>
        </nav>

        <!-- Search Box -->
        <div class="hdr-search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Tìm kiếm nhanh..." 
                   onkeydown="if(event.key==='Enter') window.location.href='/search?keyword='+encodeURIComponent(this.value)">
        </div>

        <!-- Right Actions -->
        <div class="hdr-right">
            <!-- Post Button -->
            <button class="hdr-btn-post" onclick="window.location.href='/post-room'">
                + Đăng tin
            </button>

            <!-- Icons -->
            <button class="hdr-icon-btn" title="Yêu thích" onclick="window.location.href='/saved-rooms'">
                <i class="far fa-heart"></i>
            </button>
            <button class="hdr-icon-btn" title="Thông báo">
                <i class="far fa-bell"></i>
            </button>
            <button class="hdr-icon-btn" title="Chat" onclick="window.location.href='/chat'">
                <i class="far fa-comments"></i>
            </button>

            <!-- Manage Button -->
            <button class="hdr-btn-manage" onclick="window.location.href='/landlord/listings'">
                Quản lý tin
            </button>

            <!-- User Avatar -->
            <div class="hdr-user-avatar">
                <div class="hdr-avatar-circle">L</div>
            </div>
        </div>
    </div>
</header>

<style>
    .hdr-dashboard {
        background: white;
        border-bottom: 1px solid #e5e7eb;
        padding: 0;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .hdr-wrapper {
        display: flex;
        align-items: center;
        gap: 24px;
        padding: 12px 24px;
        max-width: 100%;
    }

    .hdr-logo {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        font-size: 18px;
        color: #111827;
        text-decoration: none;
        flex-shrink: 0;
    }

    .hdr-logo-icon {
        font-size: 24px;
    }

    .hdr-nav {
        display: flex;
        gap: 24px;
        margin-left: 24px;
    }

    .hdr-nav-item {
        text-decoration: none;
        color: #4b5563;
        font-size: 14px;
        font-weight: 500;
        transition: color 0.2s;
    }

    .hdr-nav-item:hover {
        color: #111827;
    }

    .hdr-search-box {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f3f4f6;
        border-radius: 20px;
        padding: 8px 16px;
        flex: 0.3;
        min-width: 200px;
    }

    .hdr-search-box i {
        color: #9ca3af;
        font-size: 14px;
    }

    .hdr-search-box input {
        background: none;
        border: none;
        outline: none;
        font-size: 13px;
        width: 100%;
        font-family: inherit;
        color: #111827;
    }

    .hdr-search-box input::placeholder {
        color: #9ca3af;
    }

    .hdr-right {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-left: auto;
    }

    .hdr-btn-post {
        background: #ff5a3d;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .hdr-btn-post:hover {
        background: #e04d33;
    }

    .hdr-icon-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: none;
        background: #f3f4f6;
        color: #4b5563;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hdr-icon-btn:hover {
        background: #e5e7eb;
        color: #111827;
    }

    .hdr-btn-manage {
        background: #2b3cf7;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .hdr-btn-manage:hover {
        background: #1a2bde;
    }

    .hdr-user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
        cursor: pointer;
    }

    .hdr-avatar-circle {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #ff6b6b, #ff8787);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 16px;
    }

    @media (max-width: 1024px) {
        .hdr-wrapper {
            gap: 16px;
            padding: 12px 16px;
        }

        .hdr-nav {
            gap: 16px;
            margin-left: 16px;
        }

        .hdr-search-box {
            flex: 0;
            min-width: 150px;
        }

        .hdr-nav-item {
            font-size: 13px;
        }
    }

    @media (max-width: 768px) {
        .hdr-wrapper {
            gap: 8px;
            padding: 8px 12px;
        }

        .hdr-nav {
            display: none;
        }

        .hdr-search-box {
            min-width: 120px;
        }

        .hdr-btn-post {
            display: none;
        }

        .hdr-icon-btn {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }

        .hdr-btn-manage {
            font-size: 12px;
            padding: 8px 12px;
        }
    }
</style>