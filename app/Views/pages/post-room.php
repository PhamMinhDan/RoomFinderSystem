<?php

use Core\SessionManager;
SessionManager::start();

$title      = "Đăng tin phòng trọ";
$css        = ['post-room.css'];
$js         = ['post-room.js']; 
$showFooter = false;

$currentUser = SessionManager::getUser();

// Chưa đăng nhập → về trang chủ
if (!$currentUser) {
    header('Location: /?auth_error=' . urlencode('Vui lòng đăng nhập để đăng tin'));
    exit;
}

// Kiểm tra trạng thái xác thực từ DB
$identityStatus = null;
try {
    $svc = new \Services\IdentityVerificationService();
    $identityStatus = $svc->getStatus($currentUser['user_id']);
} catch (\Exception $e) {
    // bỏ qua, hiện form kiểm tra JS
}

// approved = identity_verified = 1 trên user record, hoặc status = approved
$isVerified = ($currentUser['identity_verified'] ?? false)
           || ($identityStatus && $identityStatus['status'] === 'approved');
$isPending  = $identityStatus && $identityStatus['status'] === 'pending';

// Nếu chưa bao giờ gửi yêu cầu → chuyển sang step 1
if (!$identityStatus && !$isVerified) {
    header('Location: /verify-identity');
    exit;
}

ob_start();
?>

<div class="post-page">

    <!-- ── TOPBAR STEPPER ── -->
    <div class="post-topbar">
        <div class="topbar-title">Đăng tin</div>
        <div class="stepper">
            <div class="step-group">
                <div class="step-num <?= $isVerified ? 'done' : 'active' ?>" id="step1Num">
                    <?= $isVerified ? '✓' : '1' ?>
                </div>
                <span class="step-label <?= !$isVerified ? 'active' : '' ?>">Xác thực danh tính</span>
            </div>
            <div class="step-line"></div>
            <div class="step-group">
                <div class="step-num <?= $isVerified ? 'active' : '' ?>" id="step2Num">2</div>
                <span class="step-label <?= $isVerified ? 'active' : '' ?>">Thông tin phòng trọ</span>
            </div>
        </div>
    </div>

    <?php if ($isPending && !$isVerified): ?>
    <!-- ── TRẠNG THÁI CHỜ XÁC THỰC ── -->
    <div class="post-form-wrapper">
        <div class="pending-gate-card">
            <div class="gate-icon">⏳</div>
            <h2>Đang chờ xác thực danh tính</h2>
            <p>
                Yêu cầu xác thực của bạn đã được gửi và đang chờ quản trị viên phê duyệt.<br>
                Thời gian xử lý thường từ <strong>24–48 giờ</strong>.
            </p>
            <div class="gate-notice">
                <i class="fas fa-clock"></i>
                Bạn có thể đăng tin ngay sau khi hồ sơ được duyệt. Vui lòng quay lại sau!
            </div>
            <div class="gate-actions">
                <a href="/" class="gate-btn gate-btn-outline">
                    <i class="fas fa-home"></i> Về trang chủ
                </a>
                <a href="/verify-identity" class="gate-btn gate-btn-primary">
                    <i class="fas fa-eye"></i> Xem trạng thái
                </a>
            </div>
        </div>
    </div>

    <?php elseif ($identityStatus && $identityStatus['status'] === 'rejected'): ?>
    <!-- ── BỊ TỪ CHỐI → Gửi lại ── -->
    <div class="post-form-wrapper">
        <div class="pending-gate-card rejected">
            <div class="gate-icon">❌</div>
            <h2>Xác thực danh tính bị từ chối</h2>
            <p class="reject-reason-text">
                Lý do: <?= htmlspecialchars($identityStatus['reject_reason'] ?? 'Không có lý do cụ thể') ?>
            </p>
            <p>Vui lòng gửi lại yêu cầu xác thực với thông tin chính xác hơn.</p>
            <div class="gate-actions">
                <a href="/verify-identity" class="gate-btn gate-btn-primary">
                    <i class="fas fa-redo"></i> Gửi lại yêu cầu
                </a>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- ── FORM ĐĂNG TIN (đã xác thực) ── -->
    <form class="post-form-wrapper" id="postRoomForm">

        <!-- Địa chỉ -->
        <div class="post-card">
            <div class="card-header">
                <svg class="card-header-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
                <h2>Vị trí phòng trọ</h2>
            </div>
            <div class="card-body">
                <div class="field-group">
                    <label class="field-label">Địa chỉ <span class="required">*</span></label>
                    <div class="address-display placeholder" id="addressDisplay" onclick="openLocationModal()">
                        <i class="fas fa-map-pin"></i>
                        <span id="addressText">Chọn địa chỉ phòng trọ...</span>
                        <i class="fas fa-chevron-right" style="margin-left:auto;"></i>
                    </div>
                    <span class="vfield-error" id="addressError"></span>
                    <!-- Map hiển thị sau khi chọn địa chỉ -->
                    <div id="mapSection" style="display:none;">
                        <div id="mapContainer" style="width:100%;height:260px;border-radius:12px;margin-top:.75rem;overflow:hidden;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nội dung tin đăng -->
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
                         onclick="document.getElementById('imageInput').click()">
                        <input type="file" id="imageInput" accept="image/*" multiple style="display:none" onchange="handleImageSelect(event)">
                        <div id="imageGrid" class="image-grid" style="display:none;"></div>
                        <div id="imageUploadPlaceholder">
                            <div class="upload-icon"><i class="fas fa-plus"></i></div>
                            <div class="upload-text">Kéo thả hoặc click để thêm ảnh</div>
                            <div class="upload-hint">JPG, PNG, WEBP – tối đa 10MB/ảnh</div>
                        </div>
                    </div>
                    <span class="vfield-error" id="imageError"></span>
                </div>

                <!-- Video -->
                <div class="upload-section">
                    <label class="upload-label">Video giới thiệu <span style="color:var(--gray-400);font-weight:400;">(tuỳ chọn)</span></label>
                    <div class="upload-zone" id="videoUploadZone"
                         ondrop="handleVideoDrop(event)"
                         ondragover="handleDragOver(event)"
                         ondragleave="handleDragLeave(event)"
                         onclick="document.getElementById('videoInput').click()">
                        <input type="file" id="videoInput" accept="video/*" style="display:none" onchange="handleVideoSelect(event)">
                        <div id="videoPreviewContainer" style="display:none;"></div>
                        <div id="videoUploadPlaceholder">
                            <div class="upload-icon"><i class="fas fa-play-circle"></i></div>
                            <div class="upload-text">Thêm video phòng</div>
                            <div class="upload-hint">MP4, WEBM – tối đa 100MB</div>
                        </div>
                    </div>
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
                                   placeholder="VD: 3500000" oninput="formatPriceInput(this,'pricePreview')">
                            <span class="currency-badge">đ</span>
                        </div>
                        <div class="price-preview" id="pricePreview"></div>
                        <span class="vfield-error" id="priceError"></span>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Tiền cọc</label>
                        <div class="price-input-wrap">
                            <input type="text" id="depositInput" inputmode="numeric" class="field-input"
                                   placeholder="VD: 3500000" oninput="formatPriceInput(this,'depositPreview')">
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

        <!-- Đặc điểm & tiện ích -->
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
                            <option value="motel"> Phòng trọ / Nhà trọ</option>
                            <option value="mini"> Căn hộ mini</option>
                            <option value="apt"> Chung cư / Căn hộ</option>
                            <option value="house"> Nhà nguyên căn</option>
                            <option value="dormitory"> Ký túc xá</option>
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
                            <option value="no"> Chưa có nội thất</option>
                            <option value="semi"> Nội thất cơ bản</option>
                            <option value="full"> Đầy đủ nội thất</option>
                        </select>
                    </div>
                </div>

                <!-- Ngày có phòng -->
                <div class="field-group">
                    <label class="field-label">Ngày có thể nhận phòng</label>
                    <input type="date" id="availableFrom" class="field-input"
                           min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                </div>

                <!-- Tiện ích -->
                <div class="field-group">
                    <label class="field-label">Tiện ích <span class="required">*</span></label>
                    <div class="amenities-grid" id="amenitiesGrid"></div>
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

        <!-- Info box -->
        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            Bài đăng sẽ hiển thị trong <span class="info-highlight">15 ngày</span> kể từ khi được duyệt.
            Thời gian duyệt thường từ <strong>1–24 giờ</strong>.
        </div>

        <!-- Submit -->
        <div class="submit-section">
            <button type="submit" class="submit-btn" id="submitBtn">
                <i class="fas fa-paper-plane"></i> Gửi tin đăng
            </button>
            <p style="text-align:center;font-size:.75rem;color:var(--gray-400);margin-top:.5rem;">
                <i class="fas fa-shield-alt"></i> Bài đăng sẽ được kiểm duyệt trước khi hiển thị
            </p>
        </div>

    </form>
    <?php endif; ?>

</div><!-- /post-page -->

<!-- Location Modal -->
<div class="modal-overlay" id="locationModal">
    <div class="location-modal">
        <div class="modal-header">
            <button type="button" class="modal-close" onclick="closeLocationModal()">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="modal-title">📍 Chọn địa chỉ phòng</h3>
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
                        <input type="text" class="loc-search" id="citySearch" placeholder="🔍 Tìm tỉnh thành..." onkeyup="filterLocations('city')">
                        <div class="loc-list" id="cityList"></div>
                    </div>
                </div>
            </div>
            <!-- Quận/Huyện -->
            <div class="field-group">
                <div class="loc-field">
                    <div class="loc-input" id="districtInput" onclick="toggleDropdown('district')" style="opacity:.45;pointer-events:none;">
                        <span id="districtDisplay" style="color:var(--gray-400);">Quận, huyện *</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="loc-dropdown" id="districtDropdown" style="display:none;">
                        <input type="text" class="loc-search" id="districtSearch" placeholder="🔍 Tìm quận huyện..." onkeyup="filterLocations('district')">
                        <div class="loc-list" id="districtList"></div>
                    </div>
                </div>
            </div>
            <!-- Phường/Xã -->
            <div class="field-group">
                <div class="loc-field">
                    <div class="loc-input" id="wardInput" onclick="toggleDropdown('ward')" style="opacity:.45;pointer-events:none;">
                        <span id="wardDisplay" style="color:var(--gray-400);">Phường, xã *</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="loc-dropdown" id="wardDropdown" style="display:none;">
                        <input type="text" class="loc-search" id="wardSearch" placeholder="🔍 Tìm phường xã..." onkeyup="filterLocations('ward')">
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