<?php

$title = "Chi tiết phòng trọ";
$css   = ['room-detail.css'];
$js    = ['room-detail.js'];
$showFooter = false;

use Core\SessionManager;
SessionManager::start();

$roomId = $roomId ?? (int)($GLOBALS['routeParam'] ?? 0);

// Lấy dữ liệu từ service
$room = null;
$error = null;
try {
    $service = new \Services\RoomService();
    $room = $service->getPublicDetail($roomId);
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

// Chuẩn bị dữ liệu hiển thị
$price    = number_format($room['price_per_month'], 0, ',', '.');
$deposit  = $room['deposit_amount'] ? number_format($room['deposit_amount'], 0, ',', '.') : null;
$area     = $room['area_size'] ?? null;
$capacity = $room['capacity'] ?? null;
$address  = implode(', ', array_filter([
    $room['street_address'] ?? null,
    $room['ward_name']      ?? null,
    $room['district_name']  ?? null,
    $room['city_name']      ?? null,
]));
$createdAt = $room['created_at'] ? date('d/m/Y', strtotime($room['created_at'])) : '';
$rating    = $room['average_rating'] ? number_format((float)$room['average_rating'], 1) : null;
$totalRevs = $room['total_reviews'] ?? 0;
$images    = $room['images'] ?? [];
$amenities = $room['amenities'] ?? [];
$reviews   = $room['reviews'] ?? [];
$landlordSince = $room['landlord_since'] ? date('Y', strtotime($room['landlord_since'])) : '';

$mainImg = count($images) > 0 ? $images[0]['image_url']
         : 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=900&h=500&fit=crop';

$typeLabel = [
    'motel' => 'Phòng trọ',
    'mini'  => 'Căn hộ mini',
    'apt'   => 'Căn hộ',
    'house' => 'Nhà nguyên căn',
][$room['room_type'] ?? ''] ?? 'Phòng trọ';

?>

<div class="container">
    <div class="breadcrumb">
        <a href="/">Trang chủ</a>
        <i class="fas fa-chevron-right"></i>
        <a href="/search">Tìm phòng</a>
        <i class="fas fa-chevron-right"></i>
        <span><?= htmlspecialchars($room['title']) ?></span>
    </div>

    <div class="detail-grid">
        <!-- LEFT COLUMN -->
        <div>
            <!-- GALLERY -->
            <div class="gallery-wrap">
                <div class="gallery-main-wrap">
                    <img id="main-img" class="gallery-main"
                         src="<?= htmlspecialchars($mainImg) ?>"
                         alt="<?= htmlspecialchars($room['title']) ?>">
                    <?php if ($rating && $rating >= 4.5): ?>
                    <span class="gallery-badge">NỔI BẬT</span>
                    <?php endif; ?>
                    <button class="gallery-fav" id="fav-btn" onclick="toggleFav()">
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
                        Đánh giá (<?= $totalRevs ?>)
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
                    <?php if ($room['latitude'] && $room['longitude']): ?>
                    <div id="map" style="width:100%;height:300px;border-radius:10px;overflow:hidden;"></div>
                    <script>
                        function initMap() {
                            const pos = { lat: <?= (float)$room['latitude'] ?>, lng: <?= (float)$room['longitude'] ?> };
                            const map = new google.maps.Map(document.getElementById('map'), { zoom: 16, center: pos });
                            new google.maps.Marker({ position: pos, map });
                        }
                    </script>
                    <?php else: ?>
                    <div class="map-placeholder">
                        <i class="fas fa-map-marked-alt"></i>
                        <span>Bản đồ</span>
                        <small><?= htmlspecialchars($address) ?></small>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- REVIEWS -->
                <div id="tab-reviews" style="display:none;">
                    <div class="review-form">
                        <h4>✍️ Viết đánh giá của bạn</h4>
                        <div class="star-row" id="star-row">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button class="star-btn" onclick="setRating(<?= $i ?>)">★</button>
                            <?php endfor; ?>
                        </div>
                        <textarea class="review-textarea" id="reviewText"
                                  placeholder="Chia sẻ trải nghiệm của bạn về phòng này..."></textarea>
                        <button class="btn-review-submit" onclick="submitReview(<?= $roomId ?>)">
                            Gửi đánh giá
                        </button>
                    </div>

                    <?php if (count($reviews) === 0): ?>
                    <p style="text-align:center;color:#9ca3af;padding:1.5rem;font-size:.875rem;">
                        Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá!
                    </p>
                    <?php else: ?>
                    <?php foreach ($reviews as $rv): ?>
                    <div class="review-item">
                        <div class="review-avatar">
                            <?php if ($rv['reviewer_avatar']): ?>
                            <img src="<?= htmlspecialchars($rv['reviewer_avatar']) ?>"
                                 style="width:40px;height:40px;border-radius:50%;object-fit:cover;"
                                 referrerpolicy="no-referrer">
                            <?php else: ?>
                            <?= mb_strtoupper(mb_substr($rv['reviewer_name'] ?? 'U', 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div>
                                <span class="review-name"><?= htmlspecialchars($rv['reviewer_name'] ?? 'Người dùng') ?></span>
                                <span class="review-date"><?= date('d/m/Y', strtotime($rv['created_at'])) ?></span>
                            </div>
                            <div class="review-stars">
                                <?= str_repeat('★', (int)$rv['rating']) . str_repeat('☆', 5 - (int)$rv['rating']) ?>
                                (<?= number_format((float)$rv['rating'], 1) ?>)
                            </div>
                            <div class="review-text"><?= htmlspecialchars($rv['comment'] ?? '') ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
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

        <!-- RIGHT COLUMN -->
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
                    <button class="btn-main btn-accent-cta"
                            onclick="alert('Mở chat với chủ nhà')">
                        <i class="fas fa-comment-dots"></i> Nhắn tin ngay
                    </button>
                    <button class="btn-main btn-outline-cta" id="phone-btn" onclick="showPhone()">
                        <i class="fas fa-phone"></i> Xem số điện thoại
                    </button>
                    <div class="phone-reveal" id="phone-reveal" style="display:none;">
                        <p>Để xem số điện thoại, vui lòng đăng nhập</p>
                    </div>
                    <button class="btn-main btn-primary-cta" onclick="toggleFav()">
                        <i class="far fa-heart" id="fav-icon-card"></i> Lưu yêu thích
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
                            <?php else: ?>
                            👨‍💼
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="landlord-name">
                                <?= htmlspecialchars($room['landlord_name'] ?? 'Chủ nhà') ?>
                            </div>
                            <div class="landlord-meta">
                                Thành viên từ <?= $landlordSince ?>
                            </div>
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
                            onclick="alert('Chat với chủ nhà')">
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

<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';