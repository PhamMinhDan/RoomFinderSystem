<?php
/*
 * ĐÃ CHUẨN HÓA HEADER & FOOTER - Sử dụng file chung
 * Header: public/assets/components/header.php
 * Footer: public/assets/components/footer.php
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoomFinder.vn – Tìm Nhanh, Kiếm Dễ Trọ Mới Toàn Quốc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/index.css">
</head>
<body>

<?php include __DIR__ . '/../../public/assets/components/header.php'; ?>

<!-- HERO -->
<section class="hero">
    <div class="container hero-content">
        <div class="hero-inner">
            <div class="hero-text">
                <h1>TÌM NHANH, KIẾM DỄ<br><span>TRỌ MỚI TOÀN QUỐC</span></h1>
                <p>Trang thông tin và cho thuê phòng trọ nhanh chóng, hiệu quả với hơn 500 tin đăng mới và 30.000 lượt xem mỗi ngày.</p>
                <div class="hero-stats">
                    <div class="hero-stat"><strong>500+</strong><span>Tin mới/ngày</span></div>
                    <div class="hero-stat"><strong>30K</strong><span>Lượt xem/ngày</span></div>
                    <div class="hero-stat"><strong>63</strong><span>Tỉnh thành</span></div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-house-card">
                    <span class="house-emoji">🏡</span>
                    <p>Hàng nghìn phòng trọ đang chờ bạn</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SEARCH TABS -->
<section class="search-section">
    <div class="container">
        <div class="search-tabs-bar">
            <button class="search-tab active">Tất cả</button>
            <button class="search-tab">Nhà trọ, phòng trọ</button>
            <button class="search-tab">Nhà nguyên căn</button>
            <button class="search-tab">Căn hộ</button>
            <button class="search-tab">Ký túc xá</button>
        </div>
        <div class="search-bar">
            <div class="search-field">
                <i class="fas fa-search"></i>
                <input type="text" id="search-keyword" placeholder="Từ khóa tìm kiếm (VD: phòng trọ Hà Nội...)">
            </div>
            <div class="search-field">
                <i class="fas fa-map-marker-alt"></i>
                <select>
                    <option value="">Tất cả tỉnh thành</option>
                    <option>Hồ Chí Minh</option>
                    <option>Hà Nội</option>
                    <option>Đà Nẵng</option>
                    <option>Bình Dương</option>
                    <option>Thừa Thiên Huế</option>
                </select>
            </div>
            <div class="search-field">
                <i class="fas fa-tag"></i>
                <select>
                    <option value="">Mức giá</option>
                    <option>Dưới 2 triệu</option>
                    <option>2 - 5 triệu</option>
                    <option>5 - 10 triệu</option>
                    <option>Trên 10 triệu</option>
                </select>
            </div>
            <button class="btn-search" onclick="doSearch()">
                <i class="fas fa-search"></i> Tìm kiếm
            </button>
        </div>
    </div>
</section>

<!-- HOT LISTINGS -->
<section class="hot-section">
    <div class="container">
        <div class="section-header">
            <div class="section-label">🔥 Nổi bật</div>
            <div class="section-title-big">LỰA CHỌN <span>CHỖ Ở HOT</span></div>
        </div>
        <div class="rooms-grid">
            <?php
            $rooms = [
                ['name'=>'Nhà Trọ 341 Bùi Minh Trực, Phường...','price'=>'2,3 triệu/tháng','type'=>'Nhà trọ, phòng trọ','loc'=>'Quận 8, Hồ Chí Minh','img'=>'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=400&h=250&fit=crop','badge'=>'hot'],
                ['name'=>'Nhà Trọ 73A Ngõ 37 Bằng Liệt,...','price'=>'3,5 triệu/tháng','type'=>'Nhà trọ, phòng trọ','loc'=>'Quận Hoàng Mai, Hà Nội','img'=>'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&h=250&fit=crop','badge'=>'hot'],
                ['name'=>'CCMN tại Số 6 ngách 165/46 Ngõ...','price'=>'4,2 triệu/tháng','type'=>'Nhà trọ, phòng trọ','loc'=>'Quận Hoàng Mai, Hà Nội','img'=>'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400&h=250&fit=crop','badge'=>'hot'],
                ['name'=>'Cho thuê phòng trọ ngay tại 74/2...','price'=>'3,5 triệu/tháng','type'=>'Nhà trọ, phòng trọ','loc'=>'Quận Tân Bình, Hồ Chí Minh','img'=>'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=400&h=250&fit=crop','badge'=>'hot'],
                ['name'=>'Căn hộ studio full nội thất tại Đội...','price'=>'5,8 triệu/tháng','type'=>'Căn hộ','loc'=>'Quận Ba Đình, Hà Nội','img'=>'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=400&h=250&fit=crop','badge'=>'hot','extra'=>'30m²'],
                ['name'=>'Phòng full nội thất tại Đê La Thàn...','price'=>'4,0 triệu/tháng','type'=>'Nhà trọ, phòng trọ','loc'=>'Quận Đống Đa, Hà Nội','img'=>'https://images.unsplash.com/photo-1464890100898-a385f744067f?w=400&h=250&fit=crop','badge'=>'hot'],
                ['name'=>'Nhà Trọ Tại Thôn Yên Bê, Xã Kim...','price'=>'2,0 triệu/tháng','type'=>'Nhà trọ, phòng trọ','loc'=>'Huyện Thanh Trì, Hà Nội','img'=>'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=400&h=250&fit=crop','badge'=>'new'],
                ['name'=>'Cho thuê phòng trọ ngay tại Ngô...','price'=>'3,0 triệu/tháng','type'=>'Nhà trọ, phòng trọ','loc'=>'Quận 12, Hồ Chí Minh','img'=>'https://images.unsplash.com/photo-1586105251261-72a756497a11?w=400&h=250&fit=crop','badge'=>'hot'],
                ['name'=>'Nhà Trọ 231 Ba Đình, Hưng Phú,...','price'=>'2,5 triệu/tháng','type'=>'Nhà trọ, phòng trọ','loc'=>'Quận 8, Hồ Chí Minh','img'=>'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?w=400&h=250&fit=crop','badge'=>'hot'],
                ['name'=>'Cho thuê phòng trọ tại 18 đường s...','price'=>'3,2 triệu/tháng','type'=>'Nhà trọ, phòng trọ','loc'=>'Quận Bình Thạnh, Hồ Chí Minh','img'=>'https://images.unsplash.com/photo-1536376072261-38c75010e6c9?w=400&h=250&fit=crop','badge'=>'hot'],
            ];
            foreach($rooms as $i => $r):
            ?>
            <div class="room-card" onclick="window.location.href='/room/<?= $i+1 ?>'">
                <div class="room-card-img">
                    <img src="<?= $r['img'] ?>" alt="<?= htmlspecialchars($r['name']) ?>" loading="lazy">
                    <?php if($r['badge']==='hot'): ?>
                    <span class="badge-hot">HOT</span>
                    <?php else: ?>
                    <span class="badge-new">MỚI</span>
                    <?php endif; ?>
                    <button class="btn-review" onclick="event.stopPropagation()">
                        <i class="fas fa-play-circle"></i> Review
                    </button>
                    <button class="btn-fav" onclick="event.stopPropagation(); this.style.color=this.style.color=='rgb(255,90,61)'?'':'#ff5a3d'">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
                <div class="room-card-body">
                    <div class="room-card-name"><?= htmlspecialchars($r['name']) ?></div>
                    <div class="room-card-price">Từ <?= $r['price'] ?></div>
                    <div class="room-card-type"><?= $r['type'] ?><?= isset($r['extra']) ? ' · '.$r['extra'] : '' ?></div>
                    <div class="room-card-loc"><i class="fas fa-location-dot"></i> <?= $r['loc'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center; margin-top: 32px;">
            <a href="/search" style="display:inline-flex; align-items:center; gap:8px; background: var(--primary); color: var(--white); padding: 13px 36px; border-radius: 30px; font-weight: 700; font-size: 14px; transition: all .2s;">
                Xem tất cả tin đăng <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- FEATURED CITIES -->
<section class="cities-section">
    <div class="container">
        <div class="section-header" style="text-align:center;">
            <div class="section-label">📍 Khám phá theo khu vực</div>
            <div class="section-title-big">TỈNH, THÀNH PHỐ <span>NỔI BẬT</span></div>
        </div>
        <div class="cities-grid">
            <?php
            $cities = [
                ['name'=>'Hồ Chí Minh','count'=>'1.242 phòng','img'=>'https://images.unsplash.com/photo-1583417319070-4a69db38a482?w=400&h=300&fit=crop'],
                ['name'=>'Hà Nội','count'=>'757 phòng','img'=>'https://images.unsplash.com/photo-1599946347371-68eb71b16afc?w=400&h=300&fit=crop'],
                ['name'=>'Đà Nẵng','count'=>'155 phòng','img'=>'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?w=400&h=300&fit=crop'],
                ['name'=>'Thừa Thiên Huế','count'=>'264 phòng','img'=>'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=300&fit=crop'],
                ['name'=>'Bình Dương','count'=>'328 phòng','img'=>'https://images.unsplash.com/photo-1570168007204-dfb528c6958f?w=400&h=300&fit=crop'],
                ['name'=>'Hà Giang','count'=>'42 phòng','img'=>'https://images.unsplash.com/photo-1501854140801-50d01698950b?w=400&h=300&fit=crop'],
            ];
            foreach($cities as $city):
            ?>
            <a href="/search?city=<?= urlencode($city['name']) ?>" class="city-card">
                <img src="<?= $city['img'] ?>" alt="<?= $city['name'] ?>" loading="lazy">
                <div class="city-name">
                    <?= $city['name'] ?>
                    <span class="city-count"><?= $city['count'] ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- DISCOVER MORE -->
<section class="discover-section">
    <div class="container">
        <div class="discover-inner">
            <div class="discover-title">🗺️ KHÁM PHÁ THÊM TRỌ MỚI Ở CÁC TỈNH THÀNH</div>
            <div class="discover-sub">Dưới đây là tổng hợp các tỉnh thành có nhiều trọ mới và được quan tâm nhất</div>
            <div class="discover-grid">
                <?php
                $cols = [
                    [['Thành phố Hồ Chí Minh','1.242 phòng'],['Thành phố Hà Nội','757 phòng'],['Thành phố Đà Nẵng','155 phòng']],
                    [['Tỉnh Bình Dương','328 phòng'],['Tỉnh Đồng Nai','81 phòng'],['Thành phố Hải Phòng','53 phòng']],
                    [['Thành phố Cần Thơ','45 phòng'],['Tỉnh An Giang','17 phòng'],['Tỉnh Bà Rịa – Vũng Tàu','17 phòng']],
                    [['Tỉnh Thừa Thiên Huế','264 phòng'],['Tỉnh Khánh Hòa','16 phòng'],['Tỉnh Lâm Đồng','11 phòng']],
                ];
                foreach($cols as $col):
                ?>
                <div class="discover-col">
                    <?php foreach($col as $item): ?>
                    <a href="/search?city=<?= urlencode($item[0]) ?>" class="discover-item">
                        <strong><?= $item[0] ?></strong>
                        <span><?= $item[1] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- LOGIN MODAL -->
<div class="modal-overlay" id="login-modal" onclick="if(event.target===this)closeModal()">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal()">✕</button>
        <div class="modal-logo">
            <div class="logo-icon">🏠</div> RoomFinder.vn
        </div>
        <h2 class="modal-title">Chào mừng trở lại!</h2>
        <p class="modal-sub">Đăng nhập để tìm phòng và quản lý tin đăng của bạn</p>
        <div class="social-login">
            <button class="social-btn" onclick="alert('Đăng nhập với Google')">
                <svg width="18" height="18" viewBox="0 0 20 20"><path d="M19.6 10.23c0-.82-.14-1.42-.35-2.05H10v3.72h5.5c-.15.96-.74 2.31-2.04 3.22v2.45h3.16c1.89-1.73 2.98-4.3 2.98-7.34z" fill="#4285F4"/><path d="M10 19.74c2.55 0 4.7-.85 6.27-2.3l-3.16-2.45c-.81.53-1.86 1.1-3.11 1.1-2.38 0-4.41-1.6-5.13-3.74H1.6v2.54C3.18 18.38 6.32 19.74 10 19.74z" fill="#34A853"/><path d="M4.87 11.95c-.19-.53-.3-1.1-.3-1.69s.11-1.16.29-1.69V5.03H1.62C.8 6.63.35 8.27.35 10c0 1.73.45 3.37 1.27 4.97l3.16-2.54z" fill="#FBBC04"/><path d="M10 3.88c1.44 0 2.63.48 3.54 1.4l2.64-2.64C14.66.82 12.52 0 10 0 6.32 0 3.18 1.36 1.62 3.58l3.25 2.54c.72-2.14 2.75-3.74 5.13-3.74z" fill="#EA4335"/></svg>
                Tiếp tục với Google
            </button>
            <button class="social-btn" onclick="alert('Đăng nhập với Facebook')">
                <svg width="18" height="18" viewBox="0 0 20 20"><path d="M20 10c0-5.523-4.477-10-10-10S0 4.477 0 10c0 4.991 3.657 9.128 8.438 9.879V12.89h-2.54V10h2.54V7.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V10h2.773l-.443 2.89h-2.33v6.989C16.343 19.128 20 14.991 20 10z" fill="#1877F2"/></svg>
                Tiếp tục với Facebook
            </button>
        </div>
        <div class="divider"><span>hoặc đăng nhập bằng số điện thoại</span></div>
        <div>
            <label class="form-label">Số điện thoại</label>
            <input type="tel" class="form-input" placeholder="Nhập số điện thoại của bạn" id="phone-input">
            <button class="btn-submit" onclick="handleLogin()">Tiếp tục →</button>
        </div>
        <p class="modal-footer-text">Chưa có tài khoản? <a href="#">Đăng ký ngay</a></p>
    </div>
</div>

<script>
    // Search tabs
    document.querySelectorAll('.search-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.search-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
        });
    });

    function doSearch() {
        const keyword = document.getElementById('search-keyword').value;
        window.location.href = '/search' + (keyword ? '?keyword=' + encodeURIComponent(keyword) : '');
    }
    document.getElementById('search-keyword').addEventListener('keydown', e => { if(e.key==='Enter') doSearch(); });

    function openModal() {
        document.getElementById('login-modal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        document.getElementById('login-modal').classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => { if(e.key==='Escape') closeModal(); });

    function handleLogin() {
        const phone = document.getElementById('phone-input').value;
        if (!phone) { alert('Vui lòng nhập số điện thoại'); return; }
        alert('Gửi OTP đến: ' + phone);
        closeModal();
    }
</script>

<?php include __DIR__ . '/../../public/assets/components/footer.php'; ?>

</body>
</html>