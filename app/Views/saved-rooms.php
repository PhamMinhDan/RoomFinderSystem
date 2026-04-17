<?php
/**
 * Saved Rooms / Favorites Page
 * Hiển thị danh sách các phòng đã lưu yêu thích
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin đã lưu - RoomFinder.vn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/components.css">
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

        .saved-rooms-page {
            max-width: 1280px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Header Section */
        .saved-header {
            margin-bottom: 32px;
        }

        .saved-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 8px;
        }

        .saved-header p {
            font-size: 14px;
            color: var(--gray-600);
        }

        /* Filter Tabs */
        .saved-filters {
            display: flex;
            gap: 12px;
            margin-bottom: 32px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--gray-200);
            overflow-x: auto;
        }

        .saved-filter-btn {
            background: transparent;
            border: none;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-600);
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            white-space: nowrap;
            position: relative;
        }

        .saved-filter-btn:hover {
            color: var(--primary);
        }

        .saved-filter-btn.active {
            color: var(--primary);
        }

        .saved-filter-btn.active::after {
            content: '';
            position: absolute;
            bottom: -18px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary);
            border-radius: 2px;
        }

        .filter-count {
            background: var(--gray-100);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 6px;
            font-weight: 700;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-state i {
            font-size: 64px;
            color: var(--gray-300);
            margin-bottom: 16px;
        }

        .empty-state h2 {
            font-size: 20px;
            color: var(--gray-700);
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 14px;
            color: var(--gray-600);
            margin-bottom: 24px;
        }

        .empty-state-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }

        .empty-state-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Saved Rooms Grid */
        .saved-list-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .saved-room-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--gray-200);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .saved-room-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-4px);
        }

        /* Image Container */
        .saved-card-image-wrapper {
            position: relative;
            width: 100%;
            padding-bottom: 66.67%;
            overflow: hidden;
            background: var(--gray-200);
        }

        .saved-card-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .saved-room-card:hover .saved-card-image {
            transform: scale(1.05);
        }

        /* Save Button (Heart) */
        .saved-card-heart {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 40px;
            height: 40px;
            background: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .saved-card-heart:hover {
            background: var(--gray-100);
            transform: scale(1.1);
        }

        .saved-card-heart i {
            color: var(--accent);
        }

        /* Status Badge */
        .saved-card-status {
            position: absolute;
            top: 12px;
            left: 12px;
            background: var(--green);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* Card Content */
        .saved-card-content {
            padding: 16px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .saved-card-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 6px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            line-clamp: 2;
            overflow: hidden;
        }

        .saved-card-location {
            font-size: 12px;
            color: var(--gray-600);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .saved-card-location i {
            color: var(--primary);
        }

        .saved-card-price {
            font-size: 16px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 8px;
        }

        .saved-card-meta {
            display: flex;
            gap: 12px;
            font-size: 11px;
            color: var(--gray-600);
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--gray-100);
        }

        .saved-card-meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .saved-card-meta-item i {
            color: var(--gray-400);
        }

        /* Card Actions */
        .saved-card-actions {
            display: flex;
            gap: 8px;
            margin-top: auto;
        }

        .saved-card-btn {
            flex: 1;
            padding: 10px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            text-align: center;
        }

        .saved-card-btn.btn-primary {
            background: var(--primary-light);
            color: var(--primary);
        }

        .saved-card-btn.btn-primary:hover {
            background: var(--primary);
            color: white;
        }

        .saved-card-btn.btn-secondary {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .saved-card-btn.btn-secondary:hover {
            background: var(--gray-200);
        }

        .saved-card-btn.btn-icon {
            flex: 0;
            width: 38px;
            padding: 10px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .saved-rooms-page {
                padding: 32px 16px;
            }

            .saved-list-container {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
                gap: 16px;
            }

            .saved-header h1 {
                font-size: 28px;
            }
        }

        @media (max-width: 768px) {
            .saved-rooms-page {
                padding: 24px 12px;
            }

            .saved-header h1 {
                font-size: 24px;
            }

            .saved-list-container {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 12px;
            }

            .saved-filters {
                gap: 8px;
                margin-bottom: 24px;
            }

            .saved-filter-btn {
                font-size: 13px;
                padding: 8px 12px;
            }

            .saved-card-content {
                padding: 12px;
            }

            .saved-card-title {
                font-size: 14px;
            }

            .saved-card-price {
                font-size: 15px;
            }
        }

        @media (max-width: 480px) {
            .saved-list-container {
                grid-template-columns: 1fr;
            }

            .saved-header h1 {
                font-size: 20px;
            }

            .saved-filters {
                flex-wrap: nowrap;
                overflow-x: auto;
                gap: 6px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include __DIR__ . '/../../public/assets/components/header.php'; ?>

    <!-- Main Content -->
    <div class="saved-rooms-page">
        <!-- Header Section -->
        <div class="saved-header">
            <h1>Tin đã lưu</h1>
            <p>Danh sách các phòng trọ bạn yêu thích</p>
        </div>

        <!-- Filter Tabs -->
        <div class="saved-filters">
            <button class="saved-filter-btn active" onclick="filterSavedRooms('all', this)">
                Tất cả
                <span class="filter-count">5</span>
            </button>
            <button class="saved-filter-btn" onclick="filterSavedRooms('available', this)">
                Còn trống
                <span class="filter-count">3</span>
            </button>
            <button class="saved-filter-btn" onclick="filterSavedRooms('soon', this)">
                Sắp trống
                <span class="filter-count">2</span>
            </button>
        </div>

        <!-- Saved Rooms List -->
        <div class="saved-list-container">
            <!-- Room Card 1 -->
            <div class="saved-room-card" data-filter="all available">
                <div class="saved-card-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&h=300&fit=crop" alt="Gác lửng cho thuê" class="saved-card-image">
                    <button class="saved-card-heart" onclick="removeSavedRoom(event)">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="saved-card-status">Đã duyệt</div>
                </div>
                <div class="saved-card-content">
                    <h3 class="saved-card-title">Gác lửng cho thuê mặt tiền Nguyễn Huệ</h3>
                    <div class="saved-card-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Quận 1, TP.HCM</span>
                    </div>
                    <div class="saved-card-price">4,000,000 đ/tháng</div>
                    <div class="saved-card-meta">
                        <div class="saved-card-meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>14/03/2026</span>
                        </div>
                        <div class="saved-card-meta-item">
                            <i class="fas fa-hourglass-end"></i>
                            <span>Còn 15 ngày</span>
                        </div>
                    </div>
                    <div class="saved-card-actions">
                        <button class="saved-card-btn btn-primary" onclick="viewRoom()">Xem chi tiết</button>
                        <button class="saved-card-btn btn-secondary btn-icon" title="Tùy chọn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Room Card 2 -->
            <div class="saved-room-card" data-filter="all available">
                <div class="saved-card-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1469022563149-aa64dbd37daa?w=400&h=300&fit=crop" alt="Phòng 30m² mặt tiền" class="saved-card-image">
                    <button class="saved-card-heart" onclick="removeSavedRoom(event)">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="saved-card-status">Đã duyệt</div>
                </div>
                <div class="saved-card-content">
                    <h3 class="saved-card-title">Phòng 30m² mặt tiền đường Bùi Viện, full tiện ích</h3>
                    <div class="saved-card-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Quận 1, TP.HCM</span>
                    </div>
                    <div class="saved-card-price">3,500,000 đ/tháng</div>
                    <div class="saved-card-meta">
                        <div class="saved-card-meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>12/03/2026</span>
                        </div>
                        <div class="saved-card-meta-item">
                            <i class="fas fa-hourglass-end"></i>
                            <span>Còn 17 ngày</span>
                        </div>
                    </div>
                    <div class="saved-card-actions">
                        <button class="saved-card-btn btn-primary" onclick="viewRoom()">Xem chi tiết</button>
                        <button class="saved-card-btn btn-secondary btn-icon" title="Tùy chọn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Room Card 3 -->
            <div class="saved-room-card" data-filter="all available">
                <div class="saved-card-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?w=400&h=300&fit=crop" alt="Căn hộ studio" class="saved-card-image">
                    <button class="saved-card-heart" onclick="removeSavedRoom(event)">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="saved-card-status">Đã duyệt</div>
                </div>
                <div class="saved-card-content">
                    <h3 class="saved-card-title">Căn hộ studio 25m² view hồ Tây</h3>
                    <div class="saved-card-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Tây Hồ, Hà Nội</span>
                    </div>
                    <div class="saved-card-price">2,800,000 đ/tháng</div>
                    <div class="saved-card-meta">
                        <div class="saved-card-meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>10/03/2026</span>
                        </div>
                        <div class="saved-card-meta-item">
                            <i class="fas fa-hourglass-end"></i>
                            <span>Còn 20 ngày</span>
                        </div>
                    </div>
                    <div class="saved-card-actions">
                        <button class="saved-card-btn btn-primary" onclick="viewRoom()">Xem chi tiết</button>
                        <button class="saved-card-btn btn-secondary btn-icon" title="Tùy chọn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Room Card 4 -->
            <div class="saved-room-card" data-filter="all soon">
                <div class="saved-card-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&h=300&fit=crop" alt="Phòng cho nữ sinh viên" class="saved-card-image">
                    <button class="saved-card-heart" onclick="removeSavedRoom(event)">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="saved-card-status">Đã duyệt</div>
                </div>
                <div class="saved-card-content">
                    <h3 class="saved-card-title">Phòng cho nữ sinh viên, gần Đại học KHXH</h3>
                    <div class="saved-card-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Cầu Giấy, Hà Nội</span>
                    </div>
                    <div class="saved-card-price">1,800,000 đ/tháng</div>
                    <div class="saved-card-meta">
                        <div class="saved-card-meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>08/03/2026</span>
                        </div>
                        <div class="saved-card-meta-item">
                            <i class="fas fa-hourglass-end"></i>
                            <span>Còn 3 ngày</span>
                        </div>
                    </div>
                    <div class="saved-card-actions">
                        <button class="saved-card-btn btn-primary" onclick="viewRoom()">Xem chi tiết</button>
                        <button class="saved-card-btn btn-secondary btn-icon" title="Tùy chọn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Room Card 5 -->
            <div class="saved-room-card" data-filter="all soon">
                <div class="saved-card-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1545126519-38bc11f64a88?w=400&h=300&fit=crop" alt="Chung cư mini" class="saved-card-image">
                    <button class="saved-card-heart" onclick="removeSavedRoom(event)">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="saved-card-status">Đã duyệt</div>
                </div>
                <div class="saved-card-content">
                    <h3 class="saved-card-title">Chung cư mini 20m² gần Aeon Mall Bình Dương</h3>
                    <div class="saved-card-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Thủ Dầu Một, Bình Dương</span>
                    </div>
                    <div class="saved-card-price">2,200,000 đ/tháng</div>
                    <div class="saved-card-meta">
                        <div class="saved-card-meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>05/03/2026</span>
                        </div>
                        <div class="saved-card-meta-item">
                            <i class="fas fa-hourglass-end"></i>
                            <span>Còn 1 ngày</span>
                        </div>
                    </div>
                    <div class="saved-card-actions">
                        <button class="saved-card-btn btn-primary" onclick="viewRoom()">Xem chi tiết</button>
                        <button class="saved-card-btn btn-secondary btn-icon" title="Tùy chọn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../../public/assets/components/footer.php'; ?>

    <script>
        function filterSavedRooms(filterType, element) {
            // Update active button
            document.querySelectorAll('.saved-filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            element.classList.add('active');

            // Filter cards
            const cards = document.querySelectorAll('.saved-room-card');
            cards.forEach(card => {
                const filters = card.dataset.filter.split(' ');
                if (filterType === 'all' || filters.includes(filterType)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function removeSavedRoom(event) {
            event.preventDefault();
            const card = event.target.closest('.saved-room-card');
            if (confirm('Bạn muốn xóa phòng này khỏi danh sách yêu thích?')) {
                card.style.opacity = '0.5';
                setTimeout(() => {
                    card.remove();
                    // Check if no rooms left
                    const remainingCards = document.querySelectorAll('.saved-room-card');
                    if (remainingCards.length === 0) {
                        showEmptyState();
                    }
                }, 300);
            }
        }

        function viewRoom() {
            alert('Mở trang chi tiết phòng');
        }

        function showEmptyState() {
            const container = document.querySelector('.saved-list-container');
            container.innerHTML = `
                <div style="grid-column: 1/-1;">
                    <div class="empty-state">
                        <i class="far fa-heart"></i>
                        <h2>Chưa có phòng yêu thích nào</h2>
                        <p>Hãy khám phá và lưu những phòng trọ mà bạn thích</p>
                        <a href="/search" class="empty-state-btn">Tìm phòng ngay</a>
                    </div>
                </div>
            `;
        }
    </script>
</body>
</html>
