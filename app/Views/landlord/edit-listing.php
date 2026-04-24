<?php

use Core\SessionManager;

SessionManager::start();

$currentUser = SessionManager::getUser();
if (!$currentUser) {
    header('Location: /?auth_error=' . urlencode('Vui lòng đăng nhập'));
    exit;
}

// $id được set bởi router trong index.php
$roomId = (int) ($id ?? 0);
if (!$roomId) {
    header('Location: /landlord/listings');
    exit;
}

$title      = 'Chỉnh sửa tin đăng';
$css        = ['post-room.css', 'edit-listing.css'];
$js         = ['edit-listing.js'];
$showFooter = false;

ob_start();
?>

<div class="post-page">

    <!-- ── TOPBAR ── -->
    <div class="post-topbar" style="justify-content: space-between;">
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="/landlord/listings" style="color:var(--gray-500);text-decoration:none;font-size:18px;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="topbar-title">Chỉnh sửa tin đăng</div>
        </div>
        <div id="editStatusBadge" style="display:none;"></div>
    </div>

    <!-- ── LOADING STATE ── -->
    <div id="loadingState" class="post-form-wrapper" style="text-align:center;padding:60px 20px;">
        <i class="fas fa-spinner fa-spin" style="font-size:32px;color:var(--primary);"></i>
        <p style="margin-top:12px;color:var(--gray-500);">Đang tải dữ liệu...</p>
    </div>

    <!-- ── PENDING EDIT NOTICE ── -->
    <div id="pendingEditNotice" class="post-form-wrapper" style="display:none;">
        <div class="pending-gate-card">
            <div class="gate-icon">⏳</div>
            <h2>Đang có yêu cầu chỉnh sửa chờ duyệt</h2>
            <p>
                Tin đăng này đang trong quá trình Admin xem xét chỉnh sửa bạn đã gửi trước đó.<br>
                Tin sẽ tạm ẩn và hiển thị trở lại sau khi được phê duyệt.
            </p>
            <div class="gate-notice">
                <i class="fas fa-clock"></i>
                Thời gian xử lý thường từ <strong>1–24 giờ</strong>. Vui lòng quay lại sau!
            </div>
            <div class="gate-actions">
                <a href="/landlord/listings" class="gate-btn gate-btn-outline">
                    <i class="fas fa-list"></i> Danh sách tin
                </a>
            </div>
        </div>
    </div>

    <!-- ── EDIT FORM (ẩn trước, hiện sau khi JS load xong) ── -->
    <form class="post-form-wrapper" id="editRoomForm" style="display:none;"
          data-room-id="<?= $roomId ?>">

        <!-- Info box -->
        <div class="info-box" style="margin-bottom:0;">
            <i class="fas fa-info-circle"></i>
            <span>
                Sau khi gửi, tin đăng sẽ <strong>tạm ẩn</strong> khỏi danh sách công khai
                và chờ Admin phê duyệt. Thời gian xử lý thường từ <strong>1–24 giờ</strong>.
            </span>
        </div>

        <!-- ── Địa chỉ ── -->
        <div class="post-card">
            <div class="card-header">
                <svg class="card-header-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
                <h2>Vị trí phòng trọ</h2>
            </div>
            <div class="card-body">
                <!-- Địa chỉ hiển thị / click để mở modal -->
                <div class="field-group">
                    <label class="field-label">Địa chỉ <span class="required">*</span></label>
                    <div class="address-display" id="addressDisplay" onclick="openLocationModal()">
                        <i class="fas fa-map-pin"></i>
                        <span id="addressText">Đang tải...</span>
                        <i class="fas fa-pencil-alt" style="margin-left:auto;font-size:12px;color:var(--gray-400);"></i>
                    </div>
                    <span class="vfield-error" id="addressError"></span>
                    <div id="mapSection" style="display:none;">
                        <div id="mapContainer" style="width:100%;height:260px;border-radius:12px;margin-top:.75rem;overflow:hidden;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Nội dung tin đăng ── -->
        <div class="post-card">
            <div class="card-header">
                <svg class="card-header-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                </svg>
                <h2>Nội dung tin đăng</h2>
            </div>
            <div class="card-body">

                <!-- Ảnh phòng -->
                <div class="upload-section">
                    <div class="upload-header">
                        <label class="upload-label">Ảnh phòng <span class="required">*</span></label>
                        <span class="upload-max">Tối đa 10 ảnh</span>
                    </div>
                    <div class="upload-zone" id="imageUploadZone"
                         ondrop="handleImageDrop(event)"
                         ondragover="handleDragOver(event)"
                         ondragleave="handleDragLeave(event)"
                         onclick="triggerImageUpload(event)">
                        <input type="file" id="imageInput" accept="image/*" multiple
                               style="display:none" onchange="handleImageSelect(event)">
                        <!-- Grid ảnh hiện tại + nút thêm -->
                        <div id="imageGrid" class="image-grid"></div>
                        <!-- Placeholder khi chưa có ảnh nào -->
                        <div id="imageUploadPlaceholder" style="display:none;">
                            <div class="upload-icon"><i class="fas fa-plus"></i></div>
                            <div class="upload-text">Kéo thả hoặc click để thêm ảnh</div>
                            <div class="upload-hint">JPG, PNG, WEBP – tối đa 10MB/ảnh</div>
                        </div>
                    </div>
                    <span class="vfield-error" id="imageError"></span>
                </div>

                <!-- Tiêu đề -->
                <div class="field-group">
                    <label class="field-label">Tiêu đề <span class="required">*</span></label>
                    <input type="text" id="title" class="field-input" maxlength="150"
                           placeholder="VD: Phòng 30m² full nội thất, có ban công, Quận 1">
                    <span class="vfield-error" id="titleError"></span>
                </div>

                <!-- Giá thuê & Tiền cọc -->
                <div class="form-row">
                    <div class="field-group">
                        <label class="field-label">Giá thuê/tháng <span class="required">*</span></label>
                        <div class="price-input-wrap">
                            <input type="text" id="priceInput" inputmode="numeric" class="field-input"
                                   placeholder="VD: 3500000"
                                   oninput="formatPriceInput(this,'pricePreview')">
                            <span class="currency-badge">đ</span>
                        </div>
                        <div class="price-preview" id="pricePreview"></div>
                        <span class="vfield-error" id="priceError"></span>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Tiền cọc</label>
                        <div class="price-input-wrap">
                            <input type="text" id="depositInput" inputmode="numeric" class="field-input"
                                   placeholder="VD: 3500000"
                                   oninput="formatPriceInput(this,'depositPreview')">
                            <span class="currency-badge">đ</span>
                        </div>
                        <div class="price-preview" id="depositPreview"></div>
                    </div>
                </div>

                <!-- Mô tả -->
                <div class="field-group">
                    <label class="field-label">Mô tả chi tiết <span class="required">*</span></label>
                    <textarea id="description" class="field-textarea" rows="5"
                              placeholder="Mô tả chi tiết về phòng: vị trí, tiện nghi, giá điện/nước, nội quy..."></textarea>
                    <span class="vfield-error" id="descError"></span>
                </div>
            </div>
        </div>

        <!-- ── Đặc điểm & tiện ích ── -->
        <div class="post-card">
            <div class="card-header">
                <svg class="card-header-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2>Đặc điểm & tiện ích</h2>
            </div>
            <div class="card-body">

                <div class="form-row">
                    <div class="field-group">
                        <label class="field-label">Diện tích (m²) <span class="required">*</span></label>
                        <input type="number" id="areaSize" class="field-input" min="1" max="999" placeholder="VD: 30">
                        <span class="vfield-error" id="areaError"></span>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Loại phòng <span class="required">*</span></label>
                        <select id="roomType" class="field-input">
                            <option value="">Chọn loại phòng</option>
                            <option value="single">🏠 Phòng trọ / Nhà trọ</option>
                            <option value="shared">🤝 Phòng ở ghép</option>
                            <option value="mini_apartment">🏢 Căn hộ mini</option>
                        </select>
                        <span class="vfield-error" id="roomTypeError"></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="field-group">
                        <label class="field-label">Sức chứa <span class="required">*</span></label>
                        <select id="capacity" class="field-input">
                            <option value="">Số người ở tối đa</option>
                            <option value="1">1 người</option>
                            <option value="2">2 người</option>
                            <option value="3">3 người</option>
                            <option value="4">4 người</option>
                            <option value="5">5+ người</option>
                        </select>
                        <span class="vfield-error" id="capacityError"></span>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Nội thất</label>
                        <select id="furnishLevel" class="field-input">
                            <option value="">Tình trạng nội thất</option>
                            <option value="none">🛋️ Chưa có nội thất</option>
                            <option value="basic">🪑 Nội thất cơ bản</option>
                            <option value="full">✨ Đầy đủ nội thất</option>
                        </select>
                    </div>
                </div>

                <!-- Ngày có phòng -->
                <div class="field-group">
                    <label class="field-label">Ngày có thể nhận phòng</label>
                    <input type="date" id="availableFrom" class="field-input">
                </div>

                <!-- Tiện ích -->
                <div class="field-group">
                    <label class="field-label">Tiện ích <span class="required">*</span></label>
                    <div class="amenities-grid" id="amenitiesGrid">
                        <div style="color:var(--gray-400);font-size:13px;padding:8px;">
                            <i class="fas fa-spinner fa-spin"></i> Đang tải tiện ích...
                        </div>
                    </div>
                    <span class="vfield-error" id="amenityError"></span>
                </div>

                <!-- Tiện ích tuỳ chỉnh -->
                <button type="button" class="add-amenity-btn" onclick="toggleCustom()">
                    <i class="fas fa-plus-circle"></i> Thêm tiện ích khác
                </button>
                <div id="customSection" style="display:none;margin-top:.75rem;">
                    <div class="custom-amenity-row">
                        <input type="text" id="customInput" class="field-input" placeholder="Tên tiện ích...">
                        <button type="button" class="add-btn" onclick="addCustom()">Thêm</button>
                    </div>
                    <div id="customList" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem;"></div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="submit-section">
            <button type="submit" class="submit-btn" id="submitBtn">
                <i class="fas fa-paper-plane"></i> Gửi yêu cầu chỉnh sửa
            </button>
            <p style="text-align:center;font-size:.75rem;color:var(--gray-400);margin-top:.5rem;">
                <i class="fas fa-shield-alt"></i> Tin sẽ tạm ẩn và được Admin kiểm duyệt trước khi cập nhật
            </p>
        </div>

    </form>
</div><!-- /post-page -->

<!-- ── Location Modal (giống post-room) ── -->
<div class="modal-overlay" id="locationModal">
    <div class="location-modal">
        <div class="modal-header">
            <button type="button" class="modal-close" onclick="closeLocationModal()">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="modal-title">📍 Chỉnh sửa địa chỉ phòng</h3>
            <div></div>
        </div>
        <div class="modal-body">
            <!-- Tỉnh/Thành -->
            <div class="field-group">
                <div class="loc-field">
                    <div class="loc-input" onclick="toggleDropdown('city')">
                        <span id="cityDisplay" style="color:var(--gray-400);">Tỉnh, thành phố *</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="loc-dropdown" id="cityDropdown" style="display:none;">
                        <input type="text" class="loc-search" id="citySearch"
                               placeholder="🔍 Tìm tỉnh thành..." onkeyup="filterLocations('city')">
                        <div class="loc-list" id="cityList"></div>
                    </div>
                </div>
            </div>
            <!-- Quận/Huyện -->
            <div class="field-group">
                <div class="loc-field">
                    <div class="loc-input" id="districtInput"
                         onclick="toggleDropdown('district')" style="opacity:.45;pointer-events:none;">
                        <span id="districtDisplay" style="color:var(--gray-400);">Quận, huyện *</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="loc-dropdown" id="districtDropdown" style="display:none;">
                        <input type="text" class="loc-search" id="districtSearch"
                               placeholder="🔍 Tìm quận huyện..." onkeyup="filterLocations('district')">
                        <div class="loc-list" id="districtList"></div>
                    </div>
                </div>
            </div>
            <!-- Phường/Xã -->
            <div class="field-group">
                <div class="loc-field">
                    <div class="loc-input" id="wardInput"
                         onclick="toggleDropdown('ward')" style="opacity:.45;pointer-events:none;">
                        <span id="wardDisplay" style="color:var(--gray-400);">Phường, xã *</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="loc-dropdown" id="wardDropdown" style="display:none;">
                        <input type="text" class="loc-search" id="wardSearch"
                               placeholder="🔍 Tìm phường xã..." onkeyup="filterLocations('ward')">
                        <div class="loc-list" id="wardList"></div>
                    </div>
                </div>
            </div>
            <!-- Đường / Số nhà -->
            <div class="field-group">
                <input type="text" id="streetName" class="field-input" placeholder="Tên đường *">
            </div>
            <div class="field-group">
                <input type="text" id="houseNumber" class="field-input" placeholder="Số nhà (không bắt buộc)">
            </div>
            <button type="button" class="confirm-btn" onclick="confirmLocation()">
                <i class="fas fa-check"></i> Xác nhận địa chỉ
            </button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';