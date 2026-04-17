<?php
$title = "Đăng tin phòng trọ";
$css = ['post-room.css'];
$js  = ['post-room.js'];
$showFooter = false;

ob_start();
?>

    <div class="post-page">
        <div class="post-topbar">
            <div class="topbar-title">Đăng tin</div>
            <div class="stepper">
                <div class="step-group">
                    <div class="step-num done">1</div>
                    <span class="step-label">Xác thực danh tính</span>
                </div>
                <div class="step-line"></div>
                <div class="step-group">
                    <div class="step-num active">2</div>
                    <span class="step-label active">Thông tin phòng trọ</span>
                </div>
            </div>
        </div>

        <form class="post-form-wrapper" id="postRoomForm">
            <!-- Location -->
            <div class="post-card">
                <div class="card-header">
                    <svg class="card-header-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    <h2>Vị trí trọ</h2>
                </div>
                <div class="card-body">
                    <div class="field-group">
                        <label class="field-label">Địa chỉ <span class="required">*</span></label>
                        <div class="address-display placeholder" id="addressDisplay" onclick="openLocationModal()" style="cursor: pointer;">
                            <i class="fas fa-map-pin" style="color: var(--gray-400);"></i>
                            <span style="color: var(--gray-400);">Chọn địa chỉ</span>
                            <i class="fas fa-chevron-right" style="margin-left: auto; color: var(--gray-400);"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="post-card">
                <div class="card-header">
                    <svg class="card-header-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z" />
                    </svg>
                    <h2>Nội dung tin đăng</h2>
                </div>
                <div class="card-body">
                    <!-- Images -->
                    <div class="upload-section">
                        <div class="upload-header">
                            <label class="upload-label">Ảnh phòng <span class="required">*</span></label>
                            <span class="upload-max">Tối đa 10 ảnh</span>
                        </div>
                        <div class="upload-zone" id="imageUploadZone" ondrop="handleImageDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" onclick="document.getElementById('imageInput').click()" style="cursor: pointer;">
                            <input type="file" id="imageInput" accept="image/*" multiple style="display: none;" onchange="handleImageSelect(event)">
                            <div id="imageGrid" class="image-grid" style="display: none;"></div>
                            <div id="imageUploadPlaceholder">
                                <div class="upload-icon"><i class="fas fa-plus"></i></div>
                                <div class="upload-text">Thêm ảnh phòng</div>
                            </div>
                        </div>
                    </div>

                    <!-- Video -->
                    <div class="upload-section">
                        <label class="upload-label">Video giới thiệu</label>
                        <div class="upload-zone" id="videoUploadZone" ondrop="handleVideoDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" onclick="document.getElementById('videoInput').click()" style="cursor: pointer;">
                            <input type="file" id="videoInput" accept="video/*" style="display: none;" onchange="handleVideoSelect(event)">
                            <div id="videoPreviewContainer" style="display: none;">
                                <video id="videoPreview" controls style="width: 100%; max-height: 14rem; border-radius: 0.5rem;"></video>
                            </div>
                            <div id="videoUploadPlaceholder">
                                <div class="upload-icon"><i class="fas fa-play"></i></div>
                                <div class="upload-text">Thêm video phòng</div>
                                <div class="upload-hint">Tối đa 20MB</div>
                            </div>
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="field-group">
                        <input type="text" id="title" placeholder="Tiêu đề tin đăng *" class="field-input" maxlength="100">
                    </div>

                    <!-- Price & Deposit -->
                    <div class="form-row">
                        <div class="field-group">
                            <label class="field-label" style="font-size: 0.75rem; color: var(--gray-500);">Giá thuê <span class="required">*</span></label>
                            <div class="price-input-wrap">
                                <input type="text" id="priceInput" inputmode="numeric" placeholder="Nhập giá" class="field-input">
                                <span class="currency-badge">đ</span>
                            </div>
                            <div class="price-preview" id="pricePreview" style="display: none;"></div>
                        </div>
                        <div class="field-group">
                            <label class="field-label" style="font-size: 0.75rem; color: var(--gray-500);">Tiền cọc</label>
                            <div class="price-input-wrap">
                                <input type="text" id="depositInput" inputmode="numeric" placeholder="Cọc (nếu có)" class="field-input">
                                <span class="currency-badge">đ</span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="field-group">
                        <textarea id="description" placeholder="Mô tả phòng..." class="field-textarea"></textarea>
                    </div>
                </div>
            </div>

            <!-- Amenities -->
            <div class="post-card">
                <div class="card-header">
                    <svg class="card-header-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2>Đặc điểm & tiện ích <span class="required">*</span></h2>
                </div>
                <div class="card-body">
                    <!-- Area & Room Type -->
                    <div class="form-row">
                        <div class="field-group">
                            <input type="number" id="areaSize" placeholder="Diện tích (m²) *" min="1" class="field-input">
                        </div>
                        <div class="field-group">
                            <select id="roomType" class="field-input">
                                <option value="">Loại phòng *</option>
                                <option value="motel">Phòng trọ</option>
                                <option value="mini">Căn hộ mini</option>
                                <option value="apt">Chung cư</option>
                            </select>
                        </div>
                    </div>

                    <!-- Capacity & Furnish -->
                    <div class="form-row">
                        <div class="field-group">
                            <select id="capacity" class="field-input">
                                <option value="">Sức chứa *</option>
                                <option value="1">1 người</option>
                                <option value="2">2 người</option>
                                <option value="3">3 người</option>
                                <option value="4">4 người</option>
                                <option value="5">5+ người</option>
                            </select>
                        </div>
                        <div class="field-group">
                            <select id="furnishLevel" class="field-input">
                                <option value="">Nội thất</option>
                                <option value="no">Chưa nội thất</option>
                                <option value="semi">Nội thất cơ bản</option>
                                <option value="full">Đầy đủ</option>
                            </select>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div>
                        <p class="field-label">Chọn tiện ích <span class="required">*</span></p>
                        <div class="amenities-grid" id="amenitiesGrid"></div>
                    </div>

                    <!-- Custom Amenities -->
                    <button type="button" class="add-amenity-btn" onclick="toggleCustom()">+ Thêm tiện ích</button>
                    <div id="customSection" style="display: none; margin-top: 1rem;">
                        <div class="custom-amenity-row">
                            <input type="text" id="customInput" placeholder="Tiện ích..." class="field-input">
                            <button type="button" class="add-btn" onclick="addCustom()">Thêm</button>
                        </div>
                        <div id="customList" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;"></div>
                    </div>
                </div>
            </div>

            <div class="info-box">Bài đăng hiển thị trong <span class="info-highlight">15 ngày</span> kể từ khi được duyệt.</div>

            <div class="submit-section">
                <button type="submit" class="submit-btn">
                    <i class="fas fa-check"></i> Đăng tin
                </button>
            </div>
        </form>
    </div>

    <!-- Location Modal -->
    <div class="modal-overlay" id="locationModal">
        <div class="location-modal">
            <div class="modal-header">
                <button type="button" class="modal-close" onclick="closeLocationModal()"><i class="fas fa-times"></i></button>
                <h3 class="modal-title">Chọn địa chỉ</h3>
                <div></div>
            </div>
            <div class="modal-body">
                <div class="field-group">
                    <div class="loc-field">
                        <div class="loc-input" onclick="toggleDropdown('city')">
                            <span id="cityDisplay" style="color: var(--gray-400);">Tỉnh, thành phố *</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="loc-dropdown" id="cityDropdown" style="display: none;">
                            <input type="text" class="loc-search" id="citySearch" placeholder="🔍 Tìm..." onkeyup="filterLocations('city')">
                            <div class="loc-list" id="cityList"></div>
                        </div>
                    </div>
                </div>
                <div class="field-group">
                    <div class="loc-field">
                        <div class="loc-input" id="districtInput" onclick="toggleDropdown('district')" style="opacity: 0.5;">
                            <span id="districtDisplay" style="color: var(--gray-400);">Quận, huyện *</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="loc-dropdown" id="districtDropdown" style="display: none;">
                            <input type="text" class="loc-search" id="districtSearch" placeholder="🔍 Tìm..." onkeyup="filterLocations('district')">
                            <div class="loc-list" id="districtList"></div>
                        </div>
                    </div>
                </div>
                <div class="field-group">
                    <div class="loc-field">
                        <div class="loc-input" id="wardInput" onclick="toggleDropdown('ward')" style="opacity: 0.5;">
                            <span id="wardDisplay" style="color: var(--gray-400);">Phường, xã *</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="loc-dropdown" id="wardDropdown" style="display: none;">
                            <input type="text" class="loc-search" id="wardSearch" placeholder="🔍 Tìm..." onkeyup="filterLocations('ward')">
                            <div class="loc-list" id="wardList"></div>
                        </div>
                    </div>
                </div>
                <div class="field-group">
                    <input type="text" id="streetName" placeholder="Tên đường *" class="field-input">
                </div>
                <div class="field-group">
                    <input type="text" id="houseNumber" placeholder="Số nhà" class="field-input">
                </div>
                <button type="button" class="confirm-btn" onclick="confirmLocation()">Xong</button>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';


