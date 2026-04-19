<?php
$title      = "RoomFinder.vn – Tìm phòng trọ nhanh toàn quốc";
$css        = ['homepage.css'];
$js         = ['home-page.js'];
$showFooter = true;
$activeNav  = 'home';

ob_start();
?>

<!-- ══ HERO ══════════════════════════════════════════════════════════════ -->
<section class="hero">
    <div class="hero-bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>
    <div class="container hero-content">
        <div class="hero-inner">
            <div class="hero-text">
                <div class="hero-eyebrow">
                    <span class="eyebrow-dot"></span>
                    Nền tảng tìm phòng trọ #1 Việt Nam
                </div>
                <h1>TÌM NHANH<br><span>PHÒNG TRỌ MỚI</span><br>TOÀN QUỐC</h1>
                <p>Hơn <strong>500 tin đăng mới</strong> và <strong>30.000 lượt xem</strong> mỗi ngày. Kết nối chủ nhà và người thuê hiệu quả, nhanh chóng, an toàn.</p>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <strong id="statRooms">500+</strong>
                        <span>Tin mới/ngày</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat">
                        <strong>30K</strong>
                        <span>Lượt xem/ngày</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat">
                        <strong>63</strong>
                        <span>Tỉnh thành</span>
                    </div>
                </div>
                <div class="hero-cta-row">
                    <a href="/search" class="hero-btn-primary">
                        <i class="fas fa-search"></i> Tìm phòng ngay
                    </a>
                    <button class="hero-btn-secondary" onclick="handlePostRoom()">
                        <i class="fas fa-plus"></i> Đăng tin miễn phí
                    </button>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-card-stack">
                    <div class="hero-float-card card-back">
                        <div class="hfc-img" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">🏢</div>
                        <div class="hfc-info">
                            <div class="hfc-title">Căn hộ Studio Q.3</div>
                            <div class="hfc-price">4,200,000 đ/tháng</div>
                        </div>
                    </div>
                    <div class="hero-float-card card-front">
                        <div class="hfc-img" style="background:linear-gradient(135deg,#2b3cf7,#06b6d4);">🏠</div>
                        <div class="hfc-info">
                            <div class="hfc-title">Phòng trọ Tây Hồ</div>
                            <div class="hfc-price">2,800,000 đ/tháng</div>
                            <div class="hfc-badge"><i class="fas fa-shield-check"></i> eKYC</div>
                        </div>
                    </div>
                    <div class="hero-ping">
                        <span class="ping-dot"></span>
                        <span>Vừa đăng 2 phút trước</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ SEARCH SECTION ═══════════════════════════════════════════════════ -->
<section class="search-section">
    <div class="container">
        <div class="search-tabs-bar">
            <button class="search-tab active" data-type=""> Tất cả</button>
            <button class="search-tab" data-type="motel"> Phòng trọ</button>
            <button class="search-tab" data-type="house"> Nhà nguyên căn</button>
            <button class="search-tab" data-type="apt"> Căn hộ</button>
            <button class="search-tab" data-type="dormitory"> Ký túc xá</button>
        </div>
        <div class="search-bar">
            <div class="search-field search-field-lg">
                <i class="fas fa-search"></i>
                <input type="text" id="search-keyword"
                       placeholder="Tên đường, khu vực, tiêu đề...">
            </div>
            <div class="search-field">
                <i class="fas fa-map-marker-alt"></i>
                <select id="city-select">
                    <option value="">Tất cả tỉnh thành</option>
                </select>
            </div>
            <div class="search-field">
                <i class="fas fa-tag"></i>
                <select id="price-select">
                    <option value="">Mức giá</option>
                    <option value="0-2000000">Dưới 2 triệu</option>
                    <option value="2000000-5000000">2 – 5 triệu</option>
                    <option value="5000000-10000000">5 – 10 triệu</option>
                    <option value="10000000-">Trên 10 triệu</option>
                </select>
            </div>
            <button class="btn-search" onclick="doSearch()">
                <i class="fas fa-search"></i>
                <span>Tìm kiếm</span>
            </button>
        </div>
    </div>
</section>

<!-- ══ HOT LISTINGS ══════════════════════════════════════════════════════ -->
<section class="hot-section">
    <div class="container">
        <div class="section-header">
            <div>
                <div class="section-label">🔥 Nổi bật hôm nay</div>
                <div class="section-title-big">LỰA CHỌN <span>CHỖ Ở HOT</span></div>
            </div>
            <a href="/search" class="section-link">
                Xem tất cả <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <!-- Skeleton loader hiện trong khi JS load -->
        <div class="rooms-grid" id="hotRoomsGrid">
            <?php for ($i = 0; $i < 5; $i++): ?>
            <div class="room-card skeleton-card">
                <div class="skeleton skeleton-img"></div>
                <div class="room-card-body">
                    <div class="skeleton skeleton-line" style="width:85%;height:14px;margin-bottom:8px;"></div>
                    <div class="skeleton skeleton-line" style="width:55%;height:18px;margin-bottom:8px;"></div>
                    <div class="skeleton skeleton-line" style="width:65%;height:12px;"></div>
                </div>
            </div>
            <?php endfor; ?>
        </div>

        <div style="text-align:center;margin-top:36px;">
            <a href="/search" class="btn-view-all">
                <i class="fas fa-th-large"></i>
                Xem tất cả tin đăng
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ══ FEATURED CITIES ══════════════════════════════════════════════════ -->
<section class="cities-section">
    <div class="container">
        <div class="section-header" style="text-align:center;justify-content:center;">
            <div>
                <div class="section-label">📍 Khám phá theo khu vực</div>
                <div class="section-title-big">TỈNH THÀNH <span>NỔI BẬT</span></div>
            </div>
        </div>
        <div class="cities-grid">
            <?php
            $cities = [
                ['name'=>'Hồ Chí Minh', 'img'=>'https://images.unsplash.com/photo-1583417319070-4a69db38a482?w=400&h=300&fit=crop&q=80', 'count'=>'1.2K+ phòng'],
                ['name'=>'Hà Nội',       'img'=>'https://images.unsplash.com/photo-1599946347371-68eb71b16afc?w=400&h=300&fit=crop&q=80', 'count'=>'750+ phòng'],
                ['name'=>'Đà Nẵng',      'img'=>'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?w=400&h=300&fit=crop&q=80', 'count'=>'155+ phòng'],
                ['name'=>'Thừa Thiên Huế','img'=>'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=300&fit=crop&q=80', 'count'=>'264+ phòng'],
                ['name'=>'Bình Dương',   'img'=>'https://images.unsplash.com/photo-1570168007204-dfb528c6958f?w=400&h=300&fit=crop&q=80', 'count'=>'328+ phòng'],
                ['name'=>'Hà Giang',     'img'=>'https://images.unsplash.com/photo-1501854140801-50d01698950b?w=400&h=300&fit=crop&q=80', 'count'=>'42+ phòng'],
            ];
            foreach ($cities as $city):
            ?>
            <a href="/search?city=<?= urlencode($city['name']) ?>" class="city-card">
                <img src="<?= $city['img'] ?>" alt="<?= htmlspecialchars($city['name']) ?>" loading="lazy">
                <div class="city-overlay"></div>
                <div class="city-info">
                    <div class="city-name"><?= htmlspecialchars($city['name']) ?></div>
                    <div class="city-count"><i class="fas fa-home"></i> <?= $city['count'] ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ WHY US ════════════════════════════════════════════════════════════ -->
<section class="why-section">
    <div class="container">
        <div class="section-header" style="text-align:center;justify-content:center;margin-bottom:48px;">
            <div>
                <div class="section-label">✨ Tại sao chọn chúng tôi</div>
                <div class="section-title-big">AN TOÀN · <span>TIN CẬY · NHANH CHÓNG</span></div>
            </div>
        </div>
        <div class="why-grid">
            <div class="why-card">
                <div class="why-icon" style="background:linear-gradient(135deg,#2b3cf7,#06b6d4);">
                    <i class="fas fa-shield-check"></i>
                </div>
                <h3>Xác thực eKYC</h3>
                <p>Toàn bộ chủ nhà được xác thực danh tính qua eKYC, đảm bảo an toàn 100% cho người thuê.</p>
            </div>
            <div class="why-card">
                <div class="why-icon" style="background:linear-gradient(135deg,#10b981,#059669);">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3>Đăng tin nhanh</h3>
                <p>Chỉ vài phút để đăng tin, duyệt trong 24 giờ, hiển thị ngay tới hàng nghìn người đang tìm phòng.</p>
            </div>
            <div class="why-card">
                <div class="why-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>Liên hệ trực tiếp</h3>
                <p>Nhắn tin, gọi điện trực tiếp cho chủ nhà ngay trên nền tảng. Không qua trung gian, không phí ẩn.</p>
            </div>
            <div class="why-card">
                <div class="why-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h3>Bản đồ tích hợp</h3>
                <p>Tìm phòng theo bản đồ, xem vị trí thực tế, khoảng cách đến nơi làm việc hay trường học.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══ DISCOVER ══════════════════════════════════════════════════════════ -->
<section class="discover-section">
    <div class="container">
        <div class="discover-inner">
            <div class="discover-header">
                <div class="discover-title">🗺️ KHÁM PHÁ THEO TỈNH THÀNH</div>
                <div class="discover-sub">Tổng hợp các tỉnh thành có nhiều phòng trọ mới nhất</div>
            </div>
            <div class="discover-grid">
                <?php
                $cols = [
                    [
                        ['Thành phố Hồ Chí Minh','1.242 phòng'],
                        ['Thành phố Hà Nội','757 phòng'],
                        ['Thành phố Đà Nẵng','155 phòng'],
                    ],
                    [
                        ['Tỉnh Bình Dương','328 phòng'],
                        ['Tỉnh Đồng Nai','81 phòng'],
                        ['Thành phố Hải Phòng','53 phòng'],
                    ],
                    [
                        ['Thành phố Cần Thơ','45 phòng'],
                        ['Tỉnh An Giang','17 phòng'],
                        ['Tỉnh Bà Rịa – Vũng Tàu','17 phòng'],
                    ],
                    [
                        ['Tỉnh Thừa Thiên Huế','264 phòng'],
                        ['Tỉnh Khánh Hòa','16 phòng'],
                        ['Tỉnh Lâm Đồng','11 phòng'],
                    ],
                ];
                foreach ($cols as $col):
                ?>
                <div class="discover-col">
                    <?php foreach ($col as $item): ?>
                    <a href="/search?city=<?= urlencode($item[0]) ?>" class="discover-item">
                        <span class="discover-item-name"><strong><?= htmlspecialchars($item[0]) ?></strong></span>
                        <span class="discover-item-count"><?= $item[1] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Inline styles bổ sung cho trang home -->
<style>
/* ── Hero enhancements ── */
.hero { position:relative; overflow:hidden; }
.hero-bg-blobs { position:absolute;inset:0;pointer-events:none;z-index:0; }
.blob {
    position:absolute; border-radius:50%;
    filter:blur(80px); opacity:.25;
}
.blob-1 { width:500px;height:500px;background:#4f46e5;top:-100px;right:-100px; }
.blob-2 { width:350px;height:350px;background:#06b6d4;bottom:-80px;left:10%; }
.blob-3 { width:250px;height:250px;background:#8b5cf6;top:50%;left:30%; }

.hero-content { position:relative;z-index:1; }

.hero-eyebrow {
    display:inline-flex;align-items:center;gap:8px;
    background:rgba(255,255,255,.12);backdrop-filter:blur(8px);
    border:1px solid rgba(255,255,255,.2);
    padding:6px 14px;border-radius:20px;
    font-size:12px;font-weight:600;color:rgba(255,255,255,.9);
    margin-bottom:20px;
}
.eyebrow-dot {
    width:8px;height:8px;border-radius:50%;background:#10b981;
    box-shadow:0 0 8px #10b981;animation:blink 2s infinite;
}
@keyframes blink{0%,100%{opacity:1;}50%{opacity:.4;}}

.hero-stat-divider {
    width:1px;height:36px;background:rgba(255,255,255,.2);
}
.hero-cta-row { display:flex;gap:12px;flex-wrap:wrap;margin-top:28px; }
.hero-btn-primary {
    display:inline-flex;align-items:center;gap:8px;
    background:#fff;color:var(--primary,#2b3cf7);
    padding:13px 28px;border-radius:12px;font-weight:800;font-size:14px;
    text-decoration:none;transition:all .2s;box-shadow:0 8px 24px rgba(0,0,0,.15);
}
.hero-btn-primary:hover { transform:translateY(-2px);box-shadow:0 12px 32px rgba(0,0,0,.2); }
.hero-btn-secondary {
    display:inline-flex;align-items:center;gap:8px;
    background:rgba(255,255,255,.15);backdrop-filter:blur(8px);
    border:1.5px solid rgba(255,255,255,.3);color:#fff;
    padding:13px 28px;border-radius:12px;font-weight:700;font-size:14px;
    cursor:pointer;font-family:inherit;transition:all .2s;
}
.hero-btn-secondary:hover { background:rgba(255,255,255,.25); }

/* ── Floating card stack ── */
.hero-card-stack { position:relative;width:280px;height:240px; }
.hero-float-card {
    position:absolute;background:rgba(255,255,255,.12);backdrop-filter:blur(16px);
    border:1px solid rgba(255,255,255,.2);border-radius:20px;
    padding:16px;display:flex;gap:14px;align-items:center;
    width:260px;box-shadow:0 20px 40px rgba(0,0,0,.2);
}
.card-front { bottom:0;left:0;z-index:2;animation:floatUp 3s ease-in-out infinite; }
.card-back  { top:0;right:0;z-index:1;opacity:.7;animation:floatUp 3s ease-in-out infinite .5s; }
@keyframes floatUp{0%,100%{transform:translateY(0);}50%{transform:translateY(-10px);}}

.hfc-img {
    width:52px;height:52px;border-radius:12px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;font-size:24px;
}
.hfc-title { font-size:13px;font-weight:700;color:#fff;margin-bottom:4px; }
.hfc-price { font-size:12px;color:rgba(255,255,255,.8); }
.hfc-badge {
    display:inline-flex;align-items:center;gap:4px;margin-top:6px;
    background:rgba(16,185,129,.3);color:#6ee7b7;
    font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;
}
.hero-ping {
    position:absolute;top:-16px;left:16px;
    background:#fff;border-radius:20px;
    padding:6px 12px;font-size:11px;font-weight:600;color:#374151;
    display:flex;align-items:center;gap:6px;
    box-shadow:0 4px 12px rgba(0,0,0,.1);
}
.ping-dot {
    width:8px;height:8px;border-radius:50%;background:#10b981;
    box-shadow:0 0 0 3px rgba(16,185,129,.3);animation:ping 1.5s infinite;
}
@keyframes ping{0%,100%{transform:scale(1);}50%{transform:scale(1.4);}}

/* ── Section header with right link ── */
.section-header { display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:28px; }
.section-link {
    display:inline-flex;align-items:center;gap:6px;
    font-size:13px;font-weight:700;color:var(--primary,#2b3cf7);
    text-decoration:none;padding:8px 16px;
    border:1.5px solid var(--primary,#2b3cf7);border-radius:20px;
    transition:all .2s;white-space:nowrap;
}
.section-link:hover { background:var(--primary,#2b3cf7);color:#fff; }

/* ── Skeleton ── */
.skeleton-card { pointer-events:none; }
.skeleton { background:linear-gradient(90deg,#f3f4f6 25%,#e5e7eb 50%,#f3f4f6 75%);
    background-size:200% 100%;animation:shimmer 1.4s infinite;border-radius:8px; }
.skeleton-img { width:100%;height:180px;border-radius:0; }
.skeleton-line { border-radius:6px; }
@keyframes shimmer{0%{background-position:200% 0;}100%{background-position:-200% 0;}}

/* ── Btn view all ── */
.btn-view-all {
    display:inline-flex;align-items:center;gap:10px;
    background:var(--primary,#2b3cf7);color:#fff;
    padding:14px 36px;border-radius:30px;font-weight:700;font-size:14px;
    text-decoration:none;transition:all .25s;
    box-shadow:0 8px 20px rgba(43,60,247,.3);
}
.btn-view-all:hover { transform:translateY(-3px);box-shadow:0 12px 28px rgba(43,60,247,.4); }

/* ── City card enhancements ── */
.city-overlay {
    position:absolute;inset:0;
    background:linear-gradient(to top,rgba(0,0,0,.75) 0%,rgba(0,0,0,.05) 60%,transparent 100%);
    transition:opacity .3s;
}
.city-card:hover .city-overlay { opacity:.85; }
.city-info {
    position:absolute;bottom:0;left:0;right:0;z-index:2;
    padding:16px;
}
.city-name { font-size:15px;font-weight:800;color:#fff;margin-bottom:4px; }
.city-count {
    font-size:12px;color:rgba(255,255,255,.8);
    display:flex;align-items:center;gap:5px;
}

/* ── Why section ── */
.why-section { padding:72px 0;background:var(--gray-50,#f9fafb); }
.why-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:24px; }
.why-card {
    background:#fff;border-radius:16px;padding:28px 24px;
    border:1px solid var(--gray-200,#e5e7eb);
    transition:all .3s;text-align:center;
}
.why-card:hover { transform:translateY(-6px);box-shadow:0 16px 40px rgba(0,0,0,.1);border-color:transparent; }
.why-icon {
    width:60px;height:60px;border-radius:16px;
    display:flex;align-items:center;justify-content:center;
    font-size:24px;color:#fff;margin:0 auto 20px;
}
.why-card h3 { font-size:15px;font-weight:800;color:var(--gray-900,#111827);margin-bottom:10px; }
.why-card p  { font-size:13px;color:var(--gray-500,#6b7280);line-height:1.7; }

/* ── Discover enhancements ── */
.discover-inner { background:linear-gradient(135deg,#0f1b8c08,#2b3cf710);border-radius:20px;padding:44px 40px;border:1px solid #2b3cf720; }
.discover-header { margin-bottom:28px; }
.discover-title { font-size:1.1rem;font-weight:800;color:var(--primary,#2b3cf7);margin-bottom:6px; }
.discover-sub { font-size:13px;color:var(--gray-500,#6b7280); }
.discover-item-name { font-size:13px; }
.discover-item-count { font-size:12px;color:var(--primary,#2b3cf7);font-weight:700;background:var(--primary-light,#eef0ff);padding:2px 8px;border-radius:10px; }

/* ── Responsive additions ── */
@media(max-width:1024px){
    .why-grid { grid-template-columns:repeat(2,1fr); }
    .hero-card-stack { display:none; }
    .hero-inner { grid-template-columns:1fr; }
}
@media(max-width:640px){
    .why-grid { grid-template-columns:1fr; }
    .hero-cta-row { flex-direction:column; }
    .hero-btn-primary,.hero-btn-secondary { justify-content:center; }
    .discover-inner { padding:24px 20px; }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';