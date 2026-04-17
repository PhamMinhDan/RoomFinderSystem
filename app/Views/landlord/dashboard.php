<?php
/**
 * Landlord Dashboard - Quản lý tin đăng
 * Design tương tự Smart Room management interface
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tin đăng - Smart Room</title>
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/landlord-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2b3cf7;
            --primary-dark: #1a2bde;
            --primary-light: #eef0ff;
            --accent: #ff5a3d;
            --green: #10b981;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: var(--gray-50);
            color: var(--gray-800);
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px;
        }

        /* Tab Navigation */
        .status-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            background: white;
            padding: 16px;
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            overflow-x: auto;
        }

        .status-tab {
            background: var(--gray-100);
            border: none;
            padding: 10px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            color: var(--gray-700);
            transition: all 0.2s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-tab:hover {
            background: var(--gray-200);
        }

        .status-tab.active {
            background: var(--green);
            color: white;
        }

        .status-count {
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }

        /* Main Content Grid */
        .dashboard-content {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 24px;
        }

        /* Listings Section */
        .listings-section {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .listing-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--gray-200);
            display: grid;
            grid-template-columns: auto auto 1fr auto;
            gap: 16px;
            align-items: center;
            transition: all 0.2s;
        }

        .listing-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .listing-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .listing-image {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
            background: var(--gray-200);
        }

        .listing-main {
            flex: 1;
            display: grid;
            gap: 8px;
        }

        .listing-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-900);
        }

        .listing-location {
            font-size: 12px;
            color: var(--gray-600);
        }

        .listing-price {
            font-size: 15px;
            font-weight: 700;
            color: #dc2626;
        }

        .listing-meta {
            display: flex;
            gap: 16px;
            font-size: 12px;
            color: var(--gray-600);
            margin-top: 4px;
        }

        .listing-meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .listing-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-direction: column;
        }

        .listing-badge {
            background: var(--green);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
        }

        .listing-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-small {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-renew {
            background: var(--primary-light);
            color: var(--primary);
        }

        .btn-renew:hover {
            background: var(--primary);
            color: white;
        }

        .btn-edit {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .btn-edit:hover {
            background: var(--gray-200);
        }

        .btn-more {
            background: var(--gray-100);
            color: var(--gray-600);
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-more:hover {
            background: var(--gray-200);
        }

        /* Sidebar */
        .dash-sidebar {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .dash-widget {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--gray-200);
        }

        .dash-widget h3 {
            font-size: 14px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dash-widget h3 i {
            color: var(--green);
        }

        /* Calendar */
        .calendar {
            font-size: 13px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .calendar-header h4 {
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-900);
        }

        .calendar-nav {
            display: flex;
            gap: 4px;
        }

        .calendar-nav button {
            width: 24px;
            height: 24px;
            border: none;
            background: var(--gray-100);
            border-radius: 4px;
            cursor: pointer;
            color: var(--gray-600);
            font-size: 12px;
        }

        .calendar-nav button:hover {
            background: var(--gray-200);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }

        .calendar-day {
            text-align: center;
            padding: 6px;
            border-radius: 4px;
            background: var(--gray-50);
            font-size: 11px;
            font-weight: 500;
            color: var(--gray-600);
        }

        .calendar-day.today {
            background: var(--green);
            color: white;
            font-weight: 700;
        }

        .calendar-day.header {
            font-weight: 700;
            color: var(--gray-600);
        }

        /* Today Schedule */
        .schedule-item {
            padding: 12px;
            background: var(--gray-50);
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 12px;
            color: var(--gray-700);
        }

        .schedule-time {
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 8px;
        }

        .schedule-room {
            color: var(--gray-600);
            font-size: 11px;
            line-height: 1.4;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .dashboard-content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 16px;
            }

            .listing-card {
                grid-template-columns: auto 1fr;
                gap: 12px;
            }

            .listing-image {
                width: 80px;
                height: 80px;
            }

            .listing-actions {
                grid-column: 1 / -1;
                flex-direction: row;
                margin-top: 12px;
            }

            .listing-buttons {
                width: 100%;
            }

            .status-tabs {
                padding: 12px;
            }

            .status-tab {
                font-size: 12px;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include __DIR__ . '/../../../public/assets/components/header.php'; ?>

    <div class="dashboard-container">
        <!-- Status Tabs -->
        <div class="status-tabs">
            <button class="status-tab active" onclick="filterByStatus('all', this)">
                <i class="fas fa-check-circle"></i>
                Đang hiển thị
                <span class="status-count">6</span>
            </button>
            <button class="status-tab" onclick="filterByStatus('expired', this)">
                <i class="fas fa-times-circle"></i>
                Hết hạn
                <span class="status-count">0</span>
            </button>
            <button class="status-tab" onclick="filterByStatus('rejected', this)">
                <i class="fas fa-ban"></i>
                Bị từ chối
                <span class="status-count">0</span>
            </button>
            <button class="status-tab" onclick="filterByStatus('messages', this)">
                <i class="fas fa-envelope"></i>
                Tin nhắn
                <span class="status-count">0</span>
            </button>
            <button class="status-tab" onclick="filterByStatus('pending', this)">
                <i class="fas fa-clock"></i>
                Chờ duyệt
                <span class="status-count">0</span>
            </button>
        </div>

        <!-- Main Content -->
        <div class="dashboard-content">
            <!-- Listings -->
            <div class="listings-section">
                <!-- Listing 1 -->
                <div class="listing-card" data-status="all">
                    <input type="checkbox" class="listing-checkbox">
                    <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=100&h=100&fit=crop" alt="Room" class="listing-image">
                    <div class="listing-main">
                        <div class="listing-title">Gác lửng cho thuê</div>
                        <div class="listing-location">Quận 1, TP.HCM</div>
                        <div class="listing-price">4,000,000 đ/tháng</div>
                        <div class="listing-meta">
                            <div class="listing-meta-item">
                                <i class="fas fa-calendar"></i>
                                Đăng: 14/03/2026
                            </div>
                            <div class="listing-meta-item">
                                <i class="fas fa-timer"></i>
                                Còn 15 ngày
                            </div>
                        </div>
                    </div>
                    <div class="listing-actions">
                        <div class="listing-badge">Đã duyệt</div>
                        <div class="listing-buttons">
                            <button class="btn-small btn-renew" onclick="renewListing()">Gia hạn tin</button>
                            <button class="btn-small btn-edit" onclick="editListing()">Sửa tin</button>
                            <button class="btn-small btn-more" title="Thêm tùy chọn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Listing 2 -->
                <div class="listing-card" data-status="all">
                    <input type="checkbox" class="listing-checkbox">
                    <img src="https://images.unsplash.com/photo-1469022563149-aa64dbd37daa?w=100&h=100&fit=crop" alt="Room" class="listing-image">
                    <div class="listing-main">
                        <div class="listing-title">Phòng 30m² mặt tiền Nguyễn Huệ</div>
                        <div class="listing-location">Quận 1, TP.HCM</div>
                        <div class="listing-price">3,500,000 đ/tháng</div>
                        <div class="listing-meta">
                            <div class="listing-meta-item">
                                <i class="fas fa-calendar"></i>
                                Đăng: 12/03/2026
                            </div>
                            <div class="listing-meta-item">
                                <i class="fas fa-timer"></i>
                                Còn 17 ngày
                            </div>
                        </div>
                    </div>
                    <div class="listing-actions">
                        <div class="listing-badge">Đã duyệt</div>
                        <div class="listing-buttons">
                            <button class="btn-small btn-renew" onclick="renewListing()">Gia hạn tin</button>
                            <button class="btn-small btn-edit" onclick="editListing()">Sửa tin</button>
                            <button class="btn-small btn-more" title="Thêm tùy chọn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Listing 3 -->
                <div class="listing-card" data-status="all">
                    <input type="checkbox" class="listing-checkbox">
                    <img src="https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?w=100&h=100&fit=crop" alt="Room" class="listing-image">
                    <div class="listing-main">
                        <div class="listing-title">Căn hộ studio 25m² view hồ</div>
                        <div class="listing-location">Tây Hồ, Hà Nội</div>
                        <div class="listing-price">2,800,000 đ/tháng</div>
                        <div class="listing-meta">
                            <div class="listing-meta-item">
                                <i class="fas fa-calendar"></i>
                                Đăng: 10/03/2026
                            </div>
                            <div class="listing-meta-item">
                                <i class="fas fa-timer"></i>
                                Còn 20 ngày
                            </div>
                        </div>
                    </div>
                    <div class="listing-actions">
                        <div class="listing-badge">Đã duyệt</div>
                        <div class="listing-buttons">
                            <button class="btn-small btn-renew" onclick="renewListing()">Gia hạn tin</button>
                            <button class="btn-small btn-edit" onclick="editListing()">Sửa tin</button>
                            <button class="btn-small btn-more" title="Thêm tùy chọn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="dash-sidebar">
                <!-- Calendar Widget -->
                <div class="dash-widget">
                    <h3><i class="fas fa-calendar"></i> Tháng 4 2026</h3>
                    <div class="calendar">
                        <div class="calendar-header">
                            <h4>April</h4>
                            <div class="calendar-nav">
                                <button>&lt;</button>
                                <button>&gt;</button>
                            </div>
                        </div>
                        <div class="calendar-grid">
                            <div class="calendar-day header">T2</div>
                            <div class="calendar-day header">T3</div>
                            <div class="calendar-day header">T4</div>
                            <div class="calendar-day header">T5</div>
                            <div class="calendar-day header">T6</div>
                            <div class="calendar-day header">T7</div>
                            <div class="calendar-day header">CN</div>
                            <div class="calendar-day">31</div>
                            <div class="calendar-day">1</div>
                            <div class="calendar-day">2</div>
                            <div class="calendar-day">3</div>
                            <div class="calendar-day">4</div>
                            <div class="calendar-day">5</div>
                            <div class="calendar-day">6</div>
                            <div class="calendar-day">7</div>
                            <div class="calendar-day">8</div>
                            <div class="calendar-day">9</div>
                            <div class="calendar-day">10</div>
                            <div class="calendar-day">11</div>
                            <div class="calendar-day">12</div>
                            <div class="calendar-day">13</div>
                            <div class="calendar-day">14</div>
                            <div class="calendar-day">15</div>
                            <div class="calendar-day">16</div>
                            <div class="calendar-day today">17</div>
                            <div class="calendar-day">18</div>
                            <div class="calendar-day">19</div>
                            <div class="calendar-day">20</div>
                            <div class="calendar-day">21</div>
                            <div class="calendar-day">22</div>
                            <div class="calendar-day">23</div>
                            <div class="calendar-day">24</div>
                            <div class="calendar-day">25</div>
                            <div class="calendar-day">26</div>
                            <div class="calendar-day">27</div>
                            <div class="calendar-day">28</div>
                            <div class="calendar-day">29</div>
                            <div class="calendar-day">30</div>
                        </div>
                    </div>
                </div>

                <!-- Today Schedule Widget -->
                <div class="dash-widget">
                    <h3><i class="fas fa-hourglass-start"></i> Hôm nay</h3>
                    <div>
                        <div class="schedule-time">Hôm nay (16:35)</div>
                        <div class="schedule-item">
                            <div class="schedule-room">Khách hẹn xem: Lê Tuấn Kiệt</div>
                            <div class="schedule-room">Phòng: Gác lửng cho thuê</div>
                        </div>
                        <div class="schedule-item">
                            <div class="schedule-room">Khách hẹn xem: Nguyễn Văn A</div>
                            <div class="schedule-room">Phòng: Căn hộ studio 25m²</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../../../public/assets/components/footer.php'; ?>

    <script>
        function filterListings(keyword) {
            const cards = document.querySelectorAll('.listing-card');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(keyword.toLowerCase()) ? '' : 'none';
            });
        }

        function filterByStatus(status, btn) {
            // Update active tab
            document.querySelectorAll('.status-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            btn.classList.add('active');

            // Filter listings
            const cards = document.querySelectorAll('.listing-card');
            cards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function renewListing() {
            alert('Mở trang gia hạn tin');
        }

        function editListing() {
            alert('Mở trang sửa tin');
        }
    </script>
</body>
</html>