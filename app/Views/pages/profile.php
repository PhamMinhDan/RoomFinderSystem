<?php

$title      = 'Hồ Sơ Cá Nhân – RoomFinder.vn';
$css        = ['profile.css'];
$js         = ['profile.js'];
$showFooter = false;
$activeNav  = 'profile';

ob_start();
?>

<main class="profile-root">
    <!-- Background Orbs -->
    <div class="bg-orb orb-1"></div>
    <div class="bg-orb orb-2"></div>
    <div class="bg-orb orb-3"></div>

    <div class="profile-container">

        <!-- ── LEFT COLUMN ── -->
        <aside class="profile-sidebar">
            <!-- Avatar Card -->
            <div class="avatar-card card-glass" id="avatarCard">
                <div class="avatar-ring">
                    <img id="avatarImg" src="" alt="Avatar" class="avatar-img">
                    <div class="avatar-badge" id="verifiedBadge" style="display:none">
                        <i class="fas fa-shield-check"></i>
                    </div>
                </div>
                <h2 class="sidebar-name" id="sidebarName">—</h2>
                <span class="role-chip" id="roleChip">—</span>
                <p class="sidebar-email" id="sidebarEmail">—</p>

                <div class="sidebar-stats">
                    <div class="stat-pill">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="joinedDate">—</span>
                    </div>
                    <div class="stat-pill" id="lastLoginPill">
                        <i class="fas fa-clock"></i>
                        <span id="lastLogin">—</span>
                    </div>
                </div>
            </div>

            <!-- Quick Info -->
            <div class="quick-info card-glass">
                <div class="qi-row" id="qiPhone">
                    <i class="fas fa-phone-alt qi-icon"></i>
                    <span>—</span>
                </div>
                <div class="qi-row" id="qiProvider">
                    <i class="fab fa-google qi-icon"></i>
                    <span>—</span>
                </div>
                <div class="qi-row" id="qiIdentity">
                    <i class="fas fa-id-card qi-icon"></i>
                    <span>—</span>
                </div>
            </div>
        </aside>

        <!-- ── RIGHT COLUMN ── -->
        <div class="profile-main">

            <!-- Tab Nav -->
            <nav class="tab-nav card-glass">
                <button class="tab-btn active" data-tab="info">
                    <i class="fas fa-user"></i> Thông tin
                </button>
                <button class="tab-btn" data-tab="address">
                    <i class="fas fa-map-marker-alt"></i> Địa chỉ & Bản đồ
                </button>
            </nav>

            <!-- ── TAB: Info ── -->
            <section class="tab-panel active card-glass" id="tab-info">
                <div class="panel-header">
                    <h3><i class="fas fa-user-edit"></i> Thông tin cá nhân</h3>
                    <button class="btn-edit" id="btnEditProfile">
                        <i class="fas fa-pen"></i> Chỉnh sửa
                    </button>
                </div>

                <!-- View mode -->
                <div id="profileView">
                    <div class="info-grid">
                        <div class="info-field">
                            <label>Họ và tên</label>
                            <p id="viewFullName">—</p>
                        </div>
                        <div class="info-field">
                            <label>Email</label>
                            <p id="viewEmail">—</p>
                        </div>
                        <div class="info-field">
                            <label>Số điện thoại</label>
                            <p id="viewPhone">Chưa cập nhật</p>
                        </div>
                        <div class="info-field">
                            <label>Vai trò</label>
                            <p id="viewRole">—</p>
                        </div>
                        <div class="info-field full-width">
                            <label>Giới thiệu</label>
                            <p id="viewBio">Chưa có giới thiệu.</p>
                        </div>
                    </div>
                </div>

                <!-- Edit mode -->
                <div id="profileEdit" style="display:none">
                    <form id="profileForm" novalidate>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="inputFullName">Họ và tên <span class="req">*</span></label>
                                <input type="text" id="inputFullName" name="full_name" maxlength="255" placeholder="Nhập họ và tên">
                                <span class="err-msg" id="errFullName"></span>
                            </div>
                            <div class="form-group">
                                <label for="inputPhone">Số điện thoại</label>
                                <input type="tel" id="inputPhone" name="phone_number" placeholder="0912345678">
                                <span class="err-msg" id="errPhone"></span>
                            </div>
                            <div class="form-group full-width">
                                <label for="inputBio">Giới thiệu bản thân</label>
                                <textarea id="inputBio" name="bio" rows="4" maxlength="500" placeholder="Viết vài dòng về bản thân..."></textarea>
                                <span class="char-count"><span id="bioCount">0</span>/500</span>
                                <span class="err-msg" id="errBio"></span>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-cancel" id="btnCancelEdit">Huỷ</button>
                            <button type="submit" class="btn-save" id="btnSaveProfile">
                                <span class="btn-text"><i class="fas fa-check"></i> Lưu thay đổi</span>
                                <span class="btn-loading" style="display:none"><i class="fas fa-spinner fa-spin"></i></span>
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- ── TAB: Address & Map ── -->
            <section class="tab-panel card-glass" id="tab-address" style="display:none">
                <div class="panel-header">
                    <h3><i class="fas fa-map-pin"></i> Địa chỉ & Vị trí</h3>
                    <button class="btn-edit" id="btnUpdateAddress">
                        <i class="fas fa-map-marked-alt"></i> Cập nhật địa chỉ
                    </button>
                </div>

                <!-- Address display -->
                <div class="address-display" id="addressDisplay">
                    <div class="addr-empty" id="addrEmpty">
                        <i class="fas fa-map-signs"></i>
                        <p>Chưa có địa chỉ. Hãy cập nhật để hiển thị vị trí của bạn trên bản đồ.</p>
                    </div>
                    <div id="addrInfo" style="display:none">
                        <div class="addr-grid">
                            <div class="addr-field">
                                <i class="fas fa-city"></i>
                                <div>
                                    <label>Tỉnh / Thành phố</label>
                                    <p id="addrCity">—</p>
                                </div>
                            </div>
                            <div class="addr-field">
                                <i class="fas fa-map"></i>
                                <div>
                                    <label>Quận / Huyện</label>
                                    <p id="addrDistrict">—</p>
                                </div>
                            </div>
                            <div class="addr-field">
                                <i class="fas fa-street-view"></i>
                                <div>
                                    <label>Phường / Xã</label>
                                    <p id="addrWard">—</p>
                                </div>
                            </div>
                            <div class="addr-field">
                                <i class="fas fa-road"></i>
                                <div>
                                    <label>Địa chỉ cụ thể</label>
                                    <p id="addrStreet">—</p>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>

                <!-- Map -->
                <div class="map-wrapper">
                    <div class="map-header">
                        <span><i class="fas fa-globe-asia"></i> Bản đồ vị trí</span>
                        <button class="btn-locate" id="btnLocate" title="Xác định vị trí hiện tại">
                            <i class="fas fa-location-arrow"></i> Vị trí hiện tại
                        </button>
                    </div>
                    <div id="profileMap" class="map-canvas"></div>
                    <div class="map-overlay" id="mapOverlay">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>Chưa có vị trí để hiển thị</p>
                    </div>
                </div>
            </section>

        </div><!-- /profile-main -->
    </div><!-- /profile-container -->
</main>

<!-- ── Address Update Modal ── -->
<div class="modal-backdrop" id="addressModal">
    <div class="modal-box">
        <div class="modal-header">
            <h4><i class="fas fa-map-marked-alt"></i> Cập nhật địa chỉ</h4>
            <button class="modal-close" id="closeAddressModal"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="addressForm" novalidate>
                <div class="loc-form-grid">
                    <!-- City -->
                    <div class="form-group">
                        <label>Tỉnh / Thành phố <span class="req">*</span></label>
                        <div class="loc-select-wrap" id="cityWrap">
                            <button type="button" class="loc-trigger" id="cityTrigger">
                                <span>Chọn tỉnh/thành phố</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="loc-dropdown" id="cityDropdown" style="display:none">
                                <div class="loc-search-wrap">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="loc-search" id="citySearch" placeholder="Tìm kiếm...">
                                </div>
                                <div class="loc-list" id="cityList"></div>
                            </div>
                        </div>
                        <span class="err-msg" id="errAddrCity"></span>
                    </div>

                    <!-- District -->
                    <div class="form-group">
                        <label>Quận / Huyện <span class="req">*</span></label>
                        <div class="loc-select-wrap" id="districtWrap">
                            <button type="button" class="loc-trigger" id="districtTrigger">
                                <span>Chọn quận/huyện</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="loc-dropdown" id="districtDropdown" style="display:none">
                                <div class="loc-search-wrap">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="loc-search" id="districtSearch" placeholder="Tìm kiếm...">
                                </div>
                                <div class="loc-list" id="districtList"></div>
                            </div>
                        </div>
                        <span class="err-msg" id="errAddrDistrict"></span>
                    </div>

                    <!-- Ward -->
                    <div class="form-group">
                        <label>Phường / Xã <span class="req">*</span></label>
                        <div class="loc-select-wrap" id="wardWrap">
                            <button type="button" class="loc-trigger" id="wardTrigger">
                                <span>Chọn phường/xã</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="loc-dropdown" id="wardDropdown" style="display:none">
                                <div class="loc-search-wrap">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="loc-search" id="wardSearch" placeholder="Tìm kiếm...">
                                </div>
                                <div class="loc-list" id="wardList"></div>
                            </div>
                        </div>
                        <span class="err-msg" id="errAddrWard"></span>
                    </div>

                    <!-- Street (full width) -->
                    <div class="form-group full-width">
                        <label>Địa chỉ cụ thể (số nhà, tên đường)</label>
                        <input type="text" id="inputStreet" placeholder="VD: 12 Nguyễn Huệ" maxlength="255">
                    </div>
                </div>

                <div class="geocode-note" id="geocodeNote" style="display:none">
                    <i class="fas fa-info-circle"></i>
                    Tọa độ sẽ được tự động tạo từ địa chỉ bạn chọn.
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelAddressModal">Huỷ</button>
                    <button type="submit" class="btn-save" id="btnSaveAddress">
                        <span class="btn-text"><i class="fas fa-save"></i> Lưu địa chỉ</span>
                        <span class="btn-loading" style="display:none"><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="toast"></div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';