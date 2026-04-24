<?php

$title = "Chi tiết phòng trọ";
$css   = ['room-detail.css', 'reviews.css'];
$js    = ['favourites.js', 'chat-init.js', 'room-detail.js', 'reviews.js'];
$showFooter = false;

use Core\SessionManager;
SessionManager::start();

$currentUser = SessionManager::getUser();
$roomId = $roomId ?? (int)($GLOBALS['routeParam'] ?? 0);

$room  = null;
$error = null;
try {
    $service = new \Services\RoomService();
    $room    = $service->getPublicDetail($roomId);
    if ($room) {
        $title = htmlspecialchars($room['title']) . ' - RoomFinder.vn';
    }
} catch (\Exception $e) {
    $error = $e->getMessage();
}

ob_start();

if (!$room):
?>
<div class="container" style="padding:4rem 0;text-align:center;">
    <div style="font-size:4rem;margin-bottom:1rem;">😕</div>
    <h2 style="font-size:1.4rem;font-weight:800;color:#1f2937;margin-bottom:.75rem;">
        Không tìm thấy phòng
    </h2>
    <p style="color:#6b7280;margin-bottom:1.5rem;">
        Phòng này có thể đã hết hạn hoặc không tồn tại.
    </p>
    <a href="/search" style="display:inline-flex;align-items:center;gap:.5rem;background:#2b3cf7;color:#fff;padding:.75rem 1.5rem;border-radius:10px;font-weight:700;text-decoration:none;">
        <i class="fas fa-search"></i> Tìm phòng khác
    </a>
</div>
<?php else:

$price    = number_format($room['price_per_month'], 0, ',', '.');
$deposit  = $room['deposit_amount'] ? number_format($room['deposit_amount'], 0, ',', '.') : null;
$area     = $room['area_size'] ?? null;
$capacity = $room['capacity']  ?? null;
$address  = implode(', ', array_filter([
    $room['street_address'] ?? null,
    $room['ward_name']      ?? null,
    $room['district_name']  ?? null,
    $room['city_name']      ?? null,
]));
$createdAt = $room['created_at'] ? date('d/m/Y', strtotime($room['created_at'])) : '';
$rating    = $room['average_rating'] ? number_format((float)$room['average_rating'], 1) : null;
$totalRevs = $room['total_reviews'] ?? 0;
$images    = $room['images']    ?? [];
$amenities = $room['amenities'] ?? [];
$reviews   = $room['reviews']   ?? [];
$landlordSince = $room['landlord_since'] ? date('Y', strtotime($room['landlord_since'])) : '';

$mapboxToken = $_ENV['MAPBOX_TOKEN'] ?? '';
$roomLat     = $room['latitude']  ? (float)$room['latitude']  : 0;
$roomLng     = $room['longitude'] ? (float)$room['longitude'] : 0;
$isOwner     = $currentUser
    && (string)($currentUser['user_id'] ?? '') === (string)($room['landlord_id_uuid'] ?? $room['landlord_id'] ?? '');

$userLat      = null;
$userLng      = null;
$userFromName = '';
if ($currentUser) {
    $userRepo = new \Repositories\UserRepository();
    $fullUser = $userRepo->findById($currentUser['user_id']);
    if ($fullUser && $fullUser->latitude && $fullUser->longitude) {
        $userLat = (float)$fullUser->latitude;
        $userLng = (float)$fullUser->longitude;
        $addrParts = array_filter([
            $fullUser->streetAddress, $fullUser->wardName,
            $fullUser->districtName,  $fullUser->cityName,
        ]);
        $userFromName = implode(', ', $addrParts);
    }
}

$fullMapFromParams = ($userLat && $userLng && !$isOwner)
    ? '&fromLat=' . $userLat . '&fromLng=' . $userLng . ($userFromName ? '&fromName=' . urlencode($userFromName) : '')
    : '';

$mainImg = count($images) > 0
    ? $images[0]['image_url']
    : 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=900&h=500&fit=crop';

$typeLabel = [
    'motel' => 'Phòng trọ',
    'mini'  => 'Căn hộ mini',
    'apt'   => 'Căn hộ',
    'house' => 'Nhà nguyên căn',
][$room['room_type'] ?? ''] ?? 'Phòng trọ';

$chatLandlordId = htmlspecialchars($room['landlord_id_uuid'] ?? $room['landlord_id'] ?? '');
$chatTitle      = htmlspecialchars($room['title'] ?? '');
$chatPrice      = (int)($room['price_per_month'] ?? 0);
$chatImage      = htmlspecialchars($room['primary_image'] ?? ($images[0]['image_url'] ?? ''));

?>

<script>
window.REVIEW_USER = <?= json_encode($currentUser ? [
    'user_id' => $currentUser['user_id'],
    'name'    => $currentUser['full_name'] ?? $currentUser['name'] ?? '',
    'avatar'  => $currentUser['avatar_url'] ?? '',
] : null, JSON_UNESCAPED_UNICODE) ?>;

/* ID phòng để room-detail.js dùng khi toggle fav */
window.ROOM_DETAIL_ID = <?= (int)$roomId ?>;
</script>

<div class="container">
    <div class="breadcrumb">
        <a href="/">Trang chủ</a>
        <i class="fas fa-chevron-right"></i>
        <a href="/search">Tìm phòng</a>
        <i class="fas fa-chevron-right"></i>
        <span><?= htmlspecialchars($room['title']) ?></span>
    </div>

    <div class="detail-grid">
        <!-- ════ LEFT COLUMN ════ -->
        <div>
            <!-- GALLERY -->
            <div class="gallery-wrap">
                <div class="gallery-main-wrap">
                    <img id="main-img" class="gallery-main"
                         src="<?= htmlspecialchars($mainImg) ?>"
                         alt="<?= htmlspecialchars($room['title']) ?>"
                         style="transition:opacity .15s;">

                    <?php if ($rating && $rating >= 4.5): ?>
                    <span class="gallery-badge">NỔI BẬT</span>
                    <?php endif; ?>

                    <!-- Nút tim góc ảnh chính -->
                    <button class="gallery-fav" id="fav-btn"
                            data-fav-room="<?= $roomId ?>"
                            onclick="toggleFav(this)"
                            title="<?= $currentUser ? 'Lưu yêu thích' : 'Đăng nhập để lưu' ?>">
                        <i class="far fa-heart"></i>
                    </button>

                    <button class="gallery-share" onclick="shareRoom()">
                        <i class="fas fa-share-alt"></i>
                    </button>
                </div>

                <?php if (count($images) > 1): ?>
                <div class="thumbs">
                    <?php foreach ($images as $i => $img): ?>
                    <img src="<?= htmlspecialchars($img['image_url']) ?>"
                         class="thumb <?= $i === 0 ? 'active' : '' ?>"
                         alt="thumb" onclick="changeImg(this)">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- ROOM INFO -->
            <div class="content-card">
                <div class="content-title">Thông tin phòng trọ</div>
                <div class="address-row">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <div class="address-text"><?= htmlspecialchars($address) ?></div>
                        <div class="address-sub">Cập nhật: <?= $createdAt ?></div>
                    </div>
                </div>

                <?php if (count($amenities) > 0): ?>
                <div class="amenities-grid">
                    <?php foreach ($amenities as $am): ?>
                    <div class="amenity">
                        <i class="fas fa-check-circle" style="color:var(--primary)"></i>
                        <span><?= htmlspecialchars($am['amenity_name']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- TABS -->
            <div class="content-card">
                <div class="tab-bar">
                    <button class="tab-btn active" onclick="showTab('desc',this)">Mô tả</button>
                    <button class="tab-btn" onclick="showTab('map',this)">Bản đồ</button>
                    <button class="tab-btn" onclick="showTab('reviews',this)">
                        Đánh giá<?= $totalRevs > 0 ? ' (' . $totalRevs . ')' : '' ?>
                    </button>
                    <button class="tab-btn" onclick="showTab('similar',this)">Phòng tương tự</button>
                </div>

                <!-- DESC -->
                <div id="tab-desc">
                    <p class="desc-text">
                        <?= nl2br(htmlspecialchars($room['description'] ?? 'Chủ nhà chưa cung cấp mô tả chi tiết.')) ?>
                    </p>
                </div>

                <!-- MAP -->
                <div id="tab-map" style="display:none;">
                    <div class="map-toolbar">
                        <?php if ($isOwner): ?>
                        <div class="map-mode-info owner-mode">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Vị trí phòng của bạn</span>
                        </div>
                        <?php else: ?>
                        <div class="map-mode-info" id="mapModeInfo">
                            <i class="fas fa-route"></i>
                            <span id="mapModeText">Đang tải bản đồ…</span>
                        </div>
                        <?php endif; ?>
                        <a class="btn-open-map" id="btnOpenFullMap"
                           href="/map?toLat=<?= $roomLat ?>&toLng=<?= $roomLng ?>&toName=<?= urlencode($address) ?><?= $fullMapFromParams ?>"
                           target="_blank">
                            <i class="fas fa-external-link-alt"></i> Mở bản đồ đầy đủ
                        </a>
                    </div>

                    <div id="mapSkeleton" class="map-skeleton">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Đang xác định vị trí phòng…</span>
                    </div>

                    <div id="mapbox-map" style="display:none;"></div>

                    <?php if (!$isOwner): ?>
                    <div class="route-info" id="routeInfo" style="display:none;">
                        <div class="route-stat"><i class="fas fa-road"></i><span id="routeDistance">–</span></div>
                        <div class="route-stat"><i class="fas fa-clock"></i><span id="routeDuration">–</span></div>
                        <button class="btn-route-geo" onclick="retryGeoLocation()">
                            <i class="fas fa-crosshairs"></i> Dùng vị trí hiện tại
                        </button>
                    </div>
                    <?php endif; ?>

                    <script>
                    window.MAPBOX_TOKEN = <?= json_encode($mapboxToken) ?>;
                    window.ROOM_MAP = {
                        lat:         <?= $roomLat ?: 'null' ?>,
                        lng:         <?= $roomLng ?: 'null' ?>,
                        address:     <?= json_encode($address) ?>,
                        title:       <?= json_encode($room['title'] ?? '') ?>,
                        isOwner:     <?= $isOwner ? 'true' : 'false' ?>,
                        userLat:     <?= $userLat ? (float)$userLat : 'null' ?>,
                        userLng:     <?= $userLng ? (float)$userLng : 'null' ?>,
                        userFromName:<?= json_encode($userFromName) ?>,
                    };
                    </script>
                </div>

                <!-- REVIEWS -->
                <div id="tab-reviews" style="display:none;">
                    <div id="reviews-root" data-room-id="<?= $roomId ?>">
                        <div style="display:flex;align-items:center;justify-content:center;
                                    gap:10px;padding:40px 0;color:#9ca3af;font-size:.875rem;">
                            <i class="fas fa-spinner fa-spin"></i> Đang tải đánh giá…
                        </div>
                    </div>
                </div>

                <!-- SIMILAR -->
                <div id="tab-similar" style="display:none;">
                    <div class="similar-grid"
                         id="similarGrid"
                         data-room-id="<?= $roomId ?>"
                         data-room-city="<?= htmlspecialchars($room['city_name'] ?? '') ?>">
                        <p style="color:#9ca3af;font-size:.875rem;grid-column:1/-1;">Đang tải phòng tương tự...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════ RIGHT COLUMN ════ -->
        <div>
            <div class="info-card">
                <div class="info-top">
                    <div class="info-badge-row">
                        <span class="info-badge"><?= htmlspecialchars($typeLabel) ?></span>
                        <?php if ($room['landlord_verified']): ?>
                        <span class="info-verified">
                            <i class="fas fa-check-circle"></i> Đã xác thực eKYC
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="info-price"><?= $price ?> đ</div>
                    <div class="info-price-sub">
                        mỗi tháng<?= $deposit ? ' · tiền cọc ' . $deposit . ' đ' : '' ?>
                    </div>
                    <div class="info-title"><?= htmlspecialchars($room['title']) ?></div>
                    <?php if ($rating): ?>
                    <div class="info-rating">
                        <span class="stars"><?= str_repeat('★', (int)round((float)$rating)) ?></span>
                        <span class="rating-num"><?= $rating ?></span>
                        <span class="rating-cnt">· <?= $totalRevs ?> đánh giá</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="meta-grid">
                    <?php if ($area): ?>
                    <div class="meta-item">
                        <div class="meta-label">Diện tích</div>
                        <div class="meta-value"><?= $area ?>m²</div>
                    </div>
                    <?php endif; ?>
                    <?php if ($capacity): ?>
                    <div class="meta-item">
                        <div class="meta-label">Sức chứa</div>
                        <div class="meta-value"><?= $capacity ?> người</div>
                    </div>
                    <?php endif; ?>
                    <?php if ($deposit): ?>
                    <div class="meta-item">
                        <div class="meta-label">Tiền cọc</div>
                        <div class="meta-value"><?= $deposit ?> đ</div>
                    </div>
                    <?php endif; ?>
                    <div class="meta-item">
                        <div class="meta-label">Ngày đăng</div>
                        <div class="meta-value"><?= $createdAt ?></div>
                    </div>
                </div>

                <div class="cta-area">
                    <!-- Nhắn tin -->
                    <button class="btn-main btn-accent-cta"
                            onclick="openChatForRoom(<?= $roomId ?>, this)"
                            data-landlord-id="<?= $chatLandlordId ?>"
                            data-title="<?= $chatTitle ?>"
                            data-price="<?= $chatPrice ?>"
                            data-image="<?= $chatImage ?>">
                        <i class="fas fa-comment-dots"></i> Nhắn tin ngay
                    </button>

                    <!-- Xem SĐT -->
                    <button class="btn-main btn-outline-cta" id="phone-btn" onclick="showPhone()">
                        <i class="fas fa-phone"></i> Xem số điện thoại
                    </button>
                    <div class="phone-reveal" id="phone-reveal" style="display:none;">
                        <?php if ($currentUser): ?>
                            <?php if (!empty($room['landlord_phone'])): ?>
                                <a href="tel:<?= htmlspecialchars($room['landlord_phone']) ?>"
                                   style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:1.1rem;color:var(--primary);text-decoration:none;">
                                    <i class="fas fa-phone-alt"></i>
                                    <?= htmlspecialchars($room['landlord_phone']) ?>
                                </a>
                            <?php else: ?>
                                <p style="color:#6b7280;font-size:.9rem;">Chủ nhà chưa cập nhật số điện thoại.</p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p>Vui lòng <a href="#" onclick="openLoginModal()" style="color:var(--primary);font-weight:600;">đăng nhập</a> để xem số điện thoại.</p>
                        <?php endif; ?>
                    </div>

                    <!-- ★ Lưu yêu thích – nút sidebar ★ -->
                    <button class="btn-main btn-fav-cta" id="fav-btn-sidebar"
                            data-fav-room="<?= $roomId ?>"
                            onclick="toggleFav(this)">
                        <i class="far fa-heart"></i>
                        <span><?= $currentUser ? 'Lưu yêu thích' : 'Đăng nhập để lưu' ?></span>
                    </button>
                </div>

                <!-- LANDLORD -->
                <div class="landlord-area">
                    <div style="font-size:12px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.5px;margin-bottom:14px;">
                        Thông tin chủ nhà
                    </div>
                    <div class="landlord-header">
                        <div class="landlord-avatar">
                            <?php if ($room['landlord_avatar']): ?>
                            <img src="<?= htmlspecialchars($room['landlord_avatar']) ?>"
                                 style="width:52px;height:52px;border-radius:50%;object-fit:cover;"
                                 referrerpolicy="no-referrer">
                            <?php else: ?>👨‍💼<?php endif; ?>
                        </div>
                        <div>
                            <div class="landlord-name">
                                <?= htmlspecialchars($room['landlord_name'] ?? 'Chủ nhà') ?>
                            </div>
                            <div class="landlord-meta">Thành viên từ <?= $landlordSince ?></div>
                            <?php if ($room['landlord_verified']): ?>
                            <div class="landlord-verified">
                                <i class="fas fa-shield-check"></i> Đã xác thực eKYC
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($rating): ?>
                    <div class="landlord-stats">
                        <div class="l-stat"><strong><?= $rating ?> ★</strong><span>Đánh giá</span></div>
                        <div class="l-stat"><strong><?= $totalRevs ?></strong><span>Nhận xét</span></div>
                    </div>
                    <?php endif; ?>
                    <button class="btn-main btn-outline-cta"
                            style="font-size:13px;padding:11px;"
                            onclick="openChatForRoom(<?= $roomId ?>, this)"
                            data-landlord-id="<?= $chatLandlordId ?>"
                            data-title="<?= $chatTitle ?>"
                            data-price="<?= $chatPrice ?>"
                            data-image="<?= $chatImage ?>">
                        <i class="fas fa-comments"></i> Nhắn tin cho chủ nhà
                    </button>
                </div>

                <div class="report-link">
                    <i class="fas fa-flag"></i> Có vấn đề?
                    <a href="#" onclick="reportRoom(<?= $roomId ?>)">Báo cáo</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.REVIEW_USER = <?= json_encode($currentUser ? [
    'user_id' => $currentUser['user_id'],
    'name'    => $currentUser['full_name'] ?? $currentUser['name'] ?? '',
    'avatar'  => $currentUser['avatar_url'] ?? '',
] : null, JSON_UNESCAPED_UNICODE) ?>;

window.IS_LOGGED_IN = <?= $currentUser ? 'true' : 'false' ?>; 
window.ROOM_DETAIL_ID = <?= (int)$roomId ?>;
</script>

<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';

