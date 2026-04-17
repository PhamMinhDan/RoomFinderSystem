<?php
/**
 * Trang Chỉnh sửa bài đăng (Edit Listing)
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa bài đăng - RoomFinder.vn</title>
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/landlord-dashboard.css">
    <link rel="stylesheet" href="/assets/css/edit-listing.css">
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
                    <h1>Chỉnh sửa bài đăng</h1>
                </div>
                <div class="topbar-actions">
                    <button class="btn-secondary" onclick="saveDraft()">
                        <i class="fas fa-save"></i> Lưu nháp
                    </button>
                    <button class="submit-btn" onclick="submitChanges()">
                        <i class="fas fa-check"></i> Gửi thay đổi
                    </button>
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box" style="margin-bottom: 20px;">
                <i class="fas fa-info-circle"></i>
                <span>Những thay đổi lớn sẽ được lưu vào hàng chờ duyệt. Bài đăng gốc sẽ được giữ nguyên cho đến khi Admin phê duyệt.</span>
            </div>

            <!-- Edit Form -->
            <form class="edit-listing-form" id="editForm">
                <!-- Location Section -->
                <div class="form-section">
                    <h2>Vị trí trọ</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Địa chỉ</label>
                            <input type="text" value="Số 123, Đường Nguyễn Huệ, Quận 1, TP.HCM" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Content Section -->
                <div class="form-section">
                    <h2>Nội dung bài đăng</h2>
                    
                    <!-- Images -->
                    <div class="form-group">
                        <label>Ảnh phòng <span class="required">*</span></label>
                        <div class="image-gallery">
                            <div class="image-item">
                                <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=150&h=150&fit=crop" alt="Ảnh 1">
                                <div class="image-controls">
                                    <button type="button" class="img-btn" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button type="button" class="img-btn" title="Di chuyển">
                                        <i class="fas fa-arrows"></i>
                                    </button>
                                </div>
                                <div class="image-order">1</div>
                            </div>
                            <div class="image-item">
                                <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=150&h=150&fit=crop" alt="Ảnh 2">
                                <div class="image-controls">
                                    <button type="button" class="img-btn" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button type="button" class="img-btn" title="Di chuyển">
                                        <i class="fas fa-arrows"></i>
                                    </button>
                                </div>
                                <div class="image-order">2</div>
                            </div>
                            <div class="upload-box" onclick="document.getElementById('imageInput').click()">
                                <i class="fas fa-plus"></i>
                                <span>Thêm ảnh</span>
                                <input type="file" id="imageInput" accept="image/*" style="display: none;">
                            </div>
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="form-group">
                        <label>Tiêu đề <span class="required">*</span></label>
                        <input type="text" value="Phòng 30m² mặt tiền đường Nguyễn Huệ, full nội thất" class="form-input" maxlength="100">
                    </div>

                    <!-- Price & Deposit -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>Giá thuê <span class="required">*</span></label>
                            <div class="price-input-wrap">
                                <input type="number" value="3500000" class="form-input" inputmode="numeric">
                                <span class="currency">đ/tháng</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Tiền cọc</label>
                            <div class="price-input-wrap">
                                <input type="number" value="3500000" class="form-input" inputmode="numeric">
                                <span class="currency">đ</span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label>Mô tả phòng</label>
                        <textarea class="form-textarea" rows="6">Phòng đẹp, sáng sủa, có cửa sổ thoáng, trang bị đầy đủ nội thất cao cấp. Gần các tiện ích công cộng, sạch sẽ và yên tĩnh.</textarea>
                    </div>
                </div>

                <!-- Features Section -->
                <div class="form-section">
                    <h2>Đặc điểm & tiện ích</h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Diện tích <span class="required">*</span></label>
                            <div class="size-input-wrap">
                                <input type="number" value="30" class="form-input" inputmode="numeric">
                                <span class="unit">m²</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Loại phòng <span class="required">*</span></label>
                            <select class="form-input">
                                <option value="">Chọn loại phòng</option>
                                <option value="motel" selected>Phòng trọ</option>
                                <option value="mini">Căn hộ mini</option>
                                <option value="apt">Chung cư</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Sức chứa <span class="required">*</span></label>
                            <select class="form-input">
                                <option value="">Chọn số người</option>
                                <option value="1" selected>1 người</option>
                                <option value="2">2 người</option>
                                <option value="3">3 người</option>
                                <option value="4">4 người</option>
                                <option value="5">5+ người</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nội thất</label>
                            <select class="form-input">
                                <option value="">Chọn mức nội thất</option>
                                <option value="no">Chưa nội thất</option>
                                <option value="semi">Nội thất cơ bản</option>
                                <option value="full" selected>Đầy đủ</option>
                            </select>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div class="form-group">
                        <label>Tiện ích <span class="required">*</span></label>
                        <div class="amenities-grid">
                            <label class="amenity-checkbox selected">
                                <input type="checkbox" checked>
                                <span>Wi-Fi</span>
                            </label>
                            <label class="amenity-checkbox selected">
                                <input type="checkbox" checked>
                                <span>Điều hòa</span>
                            </label>
                            <label class="amenity-checkbox">
                                <input type="checkbox">
                                <span>TV</span>
                            </label>
                            <label class="amenity-checkbox selected">
                                <input type="checkbox" checked>
                                <span>Ban công</span>
                            </label>
                            <label class="amenity-checkbox">
                                <input type="checkbox">
                                <span>Giường</span>
                            </label>
                            <label class="amenity-checkbox">
                                <input type="checkbox">
                                <span>Bàn làm việc</span>
                            </label>
                            <label class="amenity-checkbox">
                                <input type="checkbox">
                                <span>Tủ lạnh</span>
                            </label>
                            <label class="amenity-checkbox">
                                <input type="checkbox">
                                <span>Bình nóng lạnh</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="goBack()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="button" class="btn-secondary" onclick="saveDraft()">
                        <i class="fas fa-save"></i> Lưu nháp
                    </button>
                    <button type="button" class="submit-btn" onclick="submitChanges()">
                        <i class="fas fa-check"></i> Gửi thay đổi
                    </button>
                </div>
            </form>
        </main>
    </div>

    <?php include __DIR__ . '/../../../public/assets/components/footer.php'; ?>

    <script>
        function goBack() {
            if (confirm('Bạn chắc chắn muốn hủy? Những thay đổi chưa lưu sẽ bị mất.')) {
                window.location.href = '/landlord/listings';
            }
        }

        function saveDraft() {
            console.log('Saving draft...');
            alert('Bài viết đã được lưu vào nháp.');
        }

        function submitChanges() {
            console.log('Submitting changes...');
            alert('Những thay đổi đã được gửi để duyệt. Admin sẽ xem xét trong 24h.');
            window.location.href = '/landlord/listings';
        }

        // Handle image upload
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const files = e.target.files;
            console.log('Adding', files.length, 'image(s)');
            alert(`Đã thêm ${files.length} ảnh. Kéo để sắp xếp thứ tự.`);
        });

        // Amenities selection
        document.querySelectorAll('.amenity-checkbox input').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                this.parentElement.classList.toggle('selected');
            });
        });
    </script>
</body>
</html>
