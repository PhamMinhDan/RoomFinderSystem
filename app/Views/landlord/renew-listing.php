<?php
/**
 * Trang Làm mới / Gia hạn bài đăng (Renew Listing)
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gia hạn bài đăng - RoomFinder.vn</title>
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
                <a href="/landlord/listings" class="menu-item active">
                    <i class="fas fa-list"></i>
                    <span>Danh sách bài đăng</span>
                    <span class="badge">3</span>
                </a>
                <a href="/landlord/appointments" class="menu-item">
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
                <div style="display: flex; align-items: center; gap: 12px;">
                    <a href="/landlord/listings" style="color: var(--gray-500); text-decoration: none;">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1>Làm mới bài đăng</h1>
                </div>
            </div>

            <!-- Main Card -->
            <div class="renew-card">
                <!-- Listing Info -->
                <div class="listing-preview">
                    <div class="preview-header">
                        <h2>Thông tin bài đăng</h2>
                    </div>
                    <div class="preview-body">
                        <div class="preview-row">
                            <div class="preview-item">
                                <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=100&h=100&fit=crop" alt="Phòng" class="preview-img">
                                <div class="preview-info">
                                    <div class="preview-title">Phòng 30m² mặt tiền đường Nguyễn Huệ, full nội thất</div>
                                    <div class="preview-location">
                                        <i class="fas fa-map-pin"></i> Quận 1, TP.HCM
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="preview-details">
                            <div class="detail-item">
                                <span class="detail-label">Giá:</span>
                                <span class="detail-value">3.500.000 đ/tháng</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Diện tích:</span>
                                <span class="detail-value">30m²</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Loại phòng:</span>
                                <span class="detail-value">Phòng trọ</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Sức chứa:</span>
                                <span class="detail-value">1-2 người</span>
                            </div>
                        </div>

                        <div class="preview-status">
                            <div class="status-item">
                                <span class="status-label">Ngày đăng:</span>
                                <span class="status-value">14/04/2025</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Hết hạn:</span>
                                <span class="status-value" style="color: var(--accent);">29/04/2025 (15 ngày)</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Lượt xem:</span>
                                <span class="status-value">156 lượt</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Renew Section -->
                <div class="renew-section">
                    <div class="renew-header">
                        <h3>Làm mới bài đăng</h3>
                        <p>Làm mới sẽ đẩy bài đăng lên đầu danh sách tìm kiếm và cấp lại 15 ngày hiển thị</p>
                    </div>

                    <div class="renew-options">
                        <div class="renew-option">
                            <input type="radio" id="free" name="renew" value="free" checked>
                            <label for="free">
                                <div class="option-header">
                                    <span class="option-title">Làm mới miễn phí</span>
                                    <span class="option-price">Miễn phí</span>
                                </div>
                                <p class="option-desc">Thực hiện được 1 lần mỗi 24h</p>
                                <div class="option-badge free">Khuyên dùng</div>
                            </label>
                        </div>

                        <div class="renew-option">
                            <input type="radio" id="vip7" name="renew" value="vip7">
                            <label for="vip7">
                                <div class="option-header">
                                    <span class="option-title">Gói VIP 7 ngày</span>
                                    <span class="option-price">49.000 đ</span>
                                </div>
                                <p class="option-desc">Ưu tiên hiển thị + Làm mới tự động mỗi ngày</p>
                                <div class="option-features">
                                    <span><i class="fas fa-check"></i> Ưu tiên hiển thị</span>
                                    <span><i class="fas fa-check"></i> Badge VIP</span>
                                    <span><i class="fas fa-check"></i> Làm mới tự động</span>
                                </div>
                            </label>
                        </div>

                        <div class="renew-option">
                            <input type="radio" id="vip30" name="renew" value="vip30">
                            <label for="vip30">
                                <div class="option-header">
                                    <span class="option-title">Gói VIP 30 ngày</span>
                                    <span class="option-price">159.000 đ</span>
                                </div>
                                <p class="option-desc">Ưu tiên hiển thị + Làm mới tự động mỗi ngày (30 ngày)</p>
                                <div class="option-features">
                                    <span><i class="fas fa-check"></i> Ưu tiên hiển thị</span>
                                    <span><i class="fas fa-check"></i> Badge VIP</span>
                                    <span><i class="fas fa-check"></i> Làm mới tự động</span>
                                </div>
                                <div class="option-badge popular">Tiết kiệm hơn</div>
                            </label>
                        </div>
                    </div>

                    <div class="renew-actions">
                        <button class="btn-secondary" onclick="goBack()">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </button>
                        <button class="submit-btn" onclick="renewListing()">
                            <i class="fas fa-sync"></i> Xác nhận làm mới
                        </button>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box" style="margin-top: 20px;">
                <div style="display: flex; gap: 12px;">
                    <i class="fas fa-lightbulb" style="color: var(--accent); font-size: 18px;"></i>
                    <div>
                        <strong>Mẹo:</strong>
                        <p>Làm mới bài đăng sẽ giúp bài của bạn xuất hiện ở vị trí đầu tiên khi người dùng tìm kiếm phòng trọ, tăng cơ hội được liên hệ.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include __DIR__ . '/../../../public/assets/components/footer.php'; ?>

    <style>
        .renew-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .listing-preview {
            padding: 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .preview-header h2 {
            font-size: 18px;
            margin-bottom: 16px;
            color: #111827;
        }

        .preview-row {
            margin-bottom: 20px;
        }

        .preview-item {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .preview-img {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
        }

        .preview-info {
            flex: 1;
        }

        .preview-title {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .preview-location {
            color: var(--gray-500);
            font-size: 14px;
        }

        .preview-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            padding: 16px 0;
            border-top: 1px solid #f3f4f6;
            border-bottom: 1px solid #f3f4f6;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
        }

        .detail-label {
            color: var(--gray-600);
            font-weight: 500;
        }

        .detail-value {
            color: #111827;
            font-weight: 600;
        }

        .preview-status {
            display: flex;
            gap: 24px;
            padding-top: 16px;
        }

        .status-item {
            display: flex;
            flex-direction: column;
        }

        .status-label {
            color: var(--gray-600);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .status-value {
            color: #111827;
            font-weight: 600;
            font-size: 15px;
        }

        .renew-section {
            padding: 24px;
        }

        .renew-header {
            margin-bottom: 24px;
        }

        .renew-header h3 {
            font-size: 18px;
            margin-bottom: 8px;
            color: #111827;
        }

        .renew-header p {
            color: var(--gray-600);
            font-size: 14px;
        }

        .renew-options {
            display: grid;
            gap: 16px;
            margin-bottom: 24px;
        }

        .renew-option {
            position: relative;
        }

        .renew-option input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .renew-option label {
            display: block;
            padding: 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .renew-option input:checked + label {
            border-color: var(--primary);
            background: #eef0ff;
        }

        .option-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .option-title {
            font-weight: 700;
            color: #111827;
            font-size: 15px;
        }

        .option-price {
            font-weight: 700;
            color: var(--accent);
            font-size: 16px;
        }

        .option-desc {
            color: var(--gray-600);
            font-size: 13px;
            margin-bottom: 8px;
        }

        .option-features {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .option-features span {
            background: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            color: var(--gray-700);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .option-features i {
            color: var(--green);
            font-size: 11px;
        }

        .option-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 12px;
            margin-top: 8px;
        }

        .option-badge.free {
            background: #d1fae5;
            color: var(--green);
        }

        .option-badge.popular {
            background: #fef3c7;
            color: #d97706;
        }

        .renew-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .submit-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
        }
    </style>

    <script>
        function goBack() {
            window.location.href = '/landlord/listings';
        }

        function renewListing() {
            const selected = document.querySelector('input[name="renew"]:checked').value;
            let message = '';
            
            if (selected === 'free') {
                message = 'Bài đăng đã được làm mới. Tin sẽ hiển thị ưu tiên trong 24h tới.';
            } else if (selected === 'vip7') {
                message = 'Bạn sẽ thanh toán 49.000 đ cho gói VIP 7 ngày. Tiếp tục?';
            } else {
                message = 'Bạn sẽ thanh toán 159.000 đ cho gói VIP 30 ngày. Tiếp tục?';
            }

            if (confirm(message)) {
                alert('Làm mới thành công!');
                window.location.href = '/landlord/listings';
            }
        }
    </script>
</body>
</html>
