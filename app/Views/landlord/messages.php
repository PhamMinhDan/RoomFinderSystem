<?php
/**
 * Trang Tin nhắn (Messages)
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin nhắn - RoomFinder.vn</title>
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/landlord-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../../../public/assets/components/header.php'; ?>

    <div class="landlord-container">
        <!-- Sidebar -->
        <aside class="landlord-sidebar">
            <div class="sidebar-header">
                <div class="user-profile">
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=user" alt="Avatar" class="user-avatar">
                    <div class="user-info">
                        <div class="user-name">Linh Phạm</div>
                        <div class="user-role">Chủ trọ</div>
                    </div>
                </div>
            </div>

            <nav class="sidebar-menu">
                <a href="/landlord/dashboard" class="menu-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Bảng điều khiển</span>
                </a>
                <a href="/landlord/listings" class="menu-item">
                    <i class="fas fa-list"></i>
                    <span>Danh sách bài đăng</span>
                    <span class="badge">3</span>
                </a>
                <a href="/landlord/appointments" class="menu-item">
                    <i class="fas fa-calendar"></i>
                    <span>Lịch hẹn xem phòng</span>
                    <span class="badge">2</span>
                </a>
                <a href="/landlord/messages" class="menu-item active">
                    <i class="fas fa-comments"></i>
                    <span>Tin nhắn</span>
                    <span class="badge badge-red">5</span>
                </a>
                <a href="/landlord/account" class="menu-item">
                    <i class="fas fa-user-circle"></i>
                    <span>Tài khoản</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <a href="/logout" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Đăng xuất</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="landlord-main">
            <!-- Top Bar -->
            <div class="dashboard-topbar">
                <h1>Tin nhắn</h1>
            </div>

            <!-- Messages Section -->
            <div class="dashboard-section">
                <div style="padding: 60px 20px; text-align: center; color: #6b7280;">
                    <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.5; display: block; margin-bottom: 16px;"></i>
                    <h3 style="margin: 0; font-size: 16px; color: #4b5563;">Trang tin nhắn sẽ được cập nhật</h3>
                </div>
            </div>
        </main>
    </div>

    <?php include __DIR__ . '/../../../public/assets/components/footer.php'; ?>
</body>
</html>
