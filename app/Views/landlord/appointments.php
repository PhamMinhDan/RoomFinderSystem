<?php
/**
 * Trang Quản lý lịch hẹn xem phòng (Manage Appointments)
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch hẹn xem phòng - RoomFinder.vn</title>
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
                <a href="/landlord/appointments" class="menu-item active">
                    <i class="fas fa-calendar"></i>
                    <span>Lịch hẹn xem phòng</span>
                    <span class="badge">2</span>
                </a>
                <a href="/landlord/messages" class="menu-item">
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
                <h1>Lịch hẹn xem phòng</h1>
            </div>

            <!-- Tabs -->
            <div class="appointments-tabs">
                <button class="tab-btn active" onclick="switchTab('upcoming')">
                    <i class="fas fa-clock"></i> Sắp tới
                    <span class="tab-count">2</span>
                </button>
                <button class="tab-btn" onclick="switchTab('confirmed')">
                    <i class="fas fa-check-circle"></i> Đã xác nhận
                    <span class="tab-count">5</span>
                </button>
                <button class="tab-btn" onclick="switchTab('cancelled')">
                    <i class="fas fa-times-circle"></i> Đã hủy
                    <span class="tab-count">1</span>
                </button>
                <button class="tab-btn" onclick="switchTab('completed')">
                    <i class="fas fa-check"></i> Hoàn thành
                    <span class="tab-count">8</span>
                </button>
            </div>

            <!-- Appointments List -->
            <div class="appointments-container">
                <!-- Upcoming Appointments -->
                <div id="upcoming" class="appointments-tab active">
                    <div class="appointment-card-full">
                        <div class="appointment-card-header">
                            <div class="appointment-tenant">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=tenant1" alt="Avatar" class="tenant-avatar">
                                <div class="tenant-info">
                                    <h3 class="tenant-name">Nguyễn Văn A</h3>
                                    <p class="tenant-phone">
                                        <i class="fas fa-phone"></i> 0912 345 678
                                    </p>
                                    <p class="tenant-email">
                                        <i class="fas fa-envelope"></i> nguyenvana@email.com
                                    </p>
                                </div>
                            </div>
                            <div class="appointment-status">
                                <span class="badge-status pending">Chưa xác nhận</span>
                            </div>
                        </div>

                        <div class="appointment-card-body">
                            <div class="appointment-item">
                                <div class="item-label">Bài đăng:</div>
                                <div class="item-value">Phòng 30m² mặt tiền đường Nguyễn Huệ</div>
                            </div>
                            <div class="appointment-item">
                                <div class="item-label">Thời gian:</div>
                                <div class="item-value">
                                    <i class="fas fa-calendar"></i> 16/04/2025 - 14:00
                                </div>
                            </div>
                            <div class="appointment-item">
                                <div class="item-label">Mục đích:</div>
                                <div class="item-value">Xem phòng trực tiếp</div>
                            </div>
                            <div class="appointment-item">
                                <div class="item-label">Ghi chú:</div>
                                <div class="item-value">Tôi rất quan tâm đến phòng này, mong được xem trực tiếp.</div>
                            </div>
                        </div>

                        <div class="appointment-card-footer">
                            <button class="btn-primary" onclick="confirmAppointment(1)">
                                <i class="fas fa-check"></i> Xác nhận
                            </button>
                            <button class="btn-danger" onclick="cancelAppointment(1)">
                                <i class="fas fa-times"></i> Hủy
                            </button>
                            <button class="btn-secondary" onclick="viewTenant(1)">
                                <i class="fas fa-user"></i> Xem hồ sơ
                            </button>
                        </div>
                    </div>

                    <div class="appointment-card-full">
                        <div class="appointment-card-header">
                            <div class="appointment-tenant">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=tenant2" alt="Avatar" class="tenant-avatar">
                                <div class="tenant-info">
                                    <h3 class="tenant-name">Trần Thị B</h3>
                                    <p class="tenant-phone">
                                        <i class="fas fa-phone"></i> 0987 654 321
                                    </p>
                                    <p class="tenant-email">
                                        <i class="fas fa-envelope"></i> tranthib@email.com
                                    </p>
                                </div>
                            </div>
                            <div class="appointment-status">
                                <span class="badge-status pending">Chưa xác nhận</span>
                            </div>
                        </div>

                        <div class="appointment-card-body">
                            <div class="appointment-item">
                                <div class="item-label">Bài đăng:</div>
                                <div class="item-value">Căn hộ studio 25m² view hồ ban công rộng</div>
                            </div>
                            <div class="appointment-item">
                                <div class="item-label">Thời gian:</div>
                                <div class="item-value">
                                    <i class="fas fa-calendar"></i> 17/04/2025 - 10:30
                                </div>
                            </div>
                            <div class="appointment-item">
                                <div class="item-label">Mục đích:</div>
                                <div class="item-value">Xem phòng trực tiếp</div>
                            </div>
                            <div class="appointment-item">
                                <div class="item-label">Ghi chú:</div>
                                <div class="item-value">-</div>
                            </div>
                        </div>

                        <div class="appointment-card-footer">
                            <button class="btn-primary" onclick="confirmAppointment(2)">
                                <i class="fas fa-check"></i> Xác nhận
                            </button>
                            <button class="btn-danger" onclick="cancelAppointment(2)">
                                <i class="fas fa-times"></i> Hủy
                            </button>
                            <button class="btn-secondary" onclick="viewTenant(2)">
                                <i class="fas fa-user"></i> Xem hồ sơ
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Confirmed Appointments -->
                <div id="confirmed" class="appointments-tab">
                    <div class="empty-state">
                        <i class="fas fa-calendar-check"></i>
                        <h3>Không có lịch hẹn đã xác nhận sắp tới</h3>
                    </div>
                </div>

                <!-- Cancelled Appointments -->
                <div id="cancelled" class="appointments-tab">
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>Không có lịch hẹn đã hủy</h3>
                    </div>
                </div>

                <!-- Completed Appointments -->
                <div id="completed" class="appointments-tab">
                    <div class="empty-state">
                        <i class="fas fa-check-double"></i>
                        <h3>Chưa có lịch hẹn hoàn thành</h3>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include __DIR__ . '/../../../public/assets/components/footer.php'; ?>

    <style>
        .appointments-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .tab-btn {
            padding: 12px 16px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-600);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            font-family: inherit;
        }

        .tab-btn:hover {
            color: #111827;
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-count {
            background: #f3f4f6;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
        }

        .tab-btn.active .tab-count {
            background: #eef0ff;
            color: var(--primary);
        }

        .appointments-container {
            background: white;
            border-radius: 12px;
        }

        .appointments-tab {
            display: none;
            padding: 0;
        }

        .appointments-tab.active {
            display: block;
        }

        .appointment-card-full {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 16px;
            transition: all 0.3s;
        }

        .appointment-card-full:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }

        .appointment-card-header {
            padding: 20px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .appointment-tenant {
            display: flex;
            gap: 12px;
            flex: 1;
        }

        .tenant-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
        }

        .tenant-info {
            flex: 1;
        }

        .tenant-name {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }

        .tenant-phone,
        .tenant-email {
            font-size: 13px;
            color: var(--gray-600);
            margin: 4px 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .appointment-status {
            display: flex;
            align-items: center;
        }

        .badge-status {
            font-size: 12px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 20px;
        }

        .badge-status.pending {
            background: #fef3c7;
            color: #d97706;
        }

        .appointment-card-body {
            padding: 20px;
        }

        .appointment-item {
            display: flex;
            gap: 16px;
            margin-bottom: 12px;
            align-items: flex-start;
        }

        .appointment-item:last-child {
            margin-bottom: 0;
        }

        .item-label {
            min-width: 100px;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 14px;
        }

        .item-value {
            color: #111827;
            font-size: 14px;
            flex: 1;
        }

        .appointment-card-footer {
            padding: 16px 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 12px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-danger {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-danger:hover {
            background: #fecaca;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray-500);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state h3 {
            margin: 0;
            font-size: 16px;
            color: var(--gray-600);
        }
    </style>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.appointments-tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName).classList.add('active');

            // Update active button
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('.tab-btn').classList.add('active');
        }

        function confirmAppointment(id) {
            alert('Đã xác nhận lịch hẹn. Khách hàng sẽ nhận được thông báo xác nhận.');
            location.reload();
        }

        function cancelAppointment(id) {
            if (confirm('Bạn chắc chắn muốn hủy lịch hẹn này?')) {
                alert('Đã hủy lịch hẹn. Khách hàng sẽ nhận được thông báo hủy.');
                location.reload();
            }
        }

        function viewTenant(id) {
            window.location.href = `/landlord/tenants/${id}`;
        }
    </script>
</body>
</html>
