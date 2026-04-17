<?php
$title = "Trang chủ";
$css = ['homepage.css'];
$js  = ['home-page.js'];
$showFooter = true;
$activeNav = 'home';


ob_start();
?>

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

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';