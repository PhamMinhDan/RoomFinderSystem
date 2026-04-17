<?php
$title = "Tìm kiếm phòng trọ";
$css = ['search.css'];
$js  = ['search.js'];
$showFooter = false;
$activeNav = 'search';



ob_start();
?>

<!-- SEARCH HERO -->
<div class="search-hero">
    <div class="container">
        <h1>Tìm phòng trọ <span>phù hợp với bạn</span></h1>
        <div class="hero-bar">
            <div class="sfield">
                <i class="fas fa-search"></i>
                <input type="text" id="kw" placeholder="Từ khóa: tên đường, khu vực..." value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
            </div>
            <div class="sfield">
                <i class="fas fa-map-marker-alt"></i>
                <select>
                    <option value="">Tất cả tỉnh thành</option>
                    <option <?= ($_GET['city']??'')==='Hồ Chí Minh'?'selected':'' ?>>Hồ Chí Minh</option>
                    <option <?= ($_GET['city']??'')==='Hà Nội'?'selected':'' ?>>Hà Nội</option>
                    <option>Đà Nẵng</option>
                    <option>Bình Dương</option>
                    <option>Cần Thơ</option>
                </select>
            </div>
            <div class="sfield">
                <i class="fas fa-tag"></i>
                <select>
                    <option value="">Mức giá</option>
                    <option>Dưới 2 triệu</option>
                    <option>2 – 5 triệu</option>
                    <option>5 – 10 triệu</option>
                </select>
            </div>
            <div class="sfield">
                <i class="fas fa-expand-arrows-alt"></i>
                <select>
                    <option value="">Diện tích</option>
                    <option>Dưới 20m²</option>
                    <option>20 – 30m²</option>
                    <option>30 – 50m²</option>
                    <option>Trên 50m²</option>
                </select>
            </div>
            <button class="btn-go"><i class="fas fa-search"></i> Tìm kiếm</button>
        </div>
    </div>
</div>

<div class="tabs-bar">
    <div class="tabs-inner">
        <button class="tab active">Tất cả</button>
        <button class="tab">Nhà trọ, phòng trọ</button>
        <button class="tab">Nhà nguyên căn</button>
        <button class="tab">Căn hộ</button>
        <button class="tab">Ký túc xá</button>
        <button class="tab">Chung cư mini</button>
    </div>
</div>

<div class="container">
    <div class="main-layout">
        <!-- SIDEBAR FILTERS -->
        <aside class="sidebar" id="sidebar">
            <div class="filter-card">
                <div class="filter-head">
                    <h3><i class="fas fa-sliders-h" style="color:var(--primary);margin-right:6px"></i>Bộ lọc tìm kiếm</h3>
                    <button onclick="resetFilters()">Đặt lại</button>
                </div>

                <!-- Price -->
                <div class="filter-group">
                    <div class="filter-group-title">Khoảng giá (triệu/tháng) <i class="fas fa-chevron-up"></i></div>
                    <div class="price-grid">
                        <input type="number" class="price-input" id="price-min" placeholder="Từ" value="0">
                        <input type="number" class="price-input" id="price-max" placeholder="Đến" value="">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;margin-top:10px;">
                        <?php
                        $ranges = [['Dưới 2 triệu','','2000000'],['2 – 3 triệu','2000000','3000000'],['3 – 5 triệu','3000000','5000000'],['Trên 5 triệu','5000000','']];
                        foreach($ranges as $r):
                        ?>
                        <label class="filter-option" style="padding:4px 0;">
                            <input type="radio" name="price-range" value="<?= $r[1] ?>-<?= $r[2] ?>">
                            <span style="font-size:13px;color:var(--gray-700)"><?= $r[0] ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Room type -->
                <div class="filter-group">
                    <div class="filter-group-title">Loại phòng <i class="fas fa-chevron-up"></i></div>
                    <div>
                        <?php $types = ['Nhà trọ, phòng trọ','Nhà nguyên căn','Căn hộ','Chung cư mini','Ký túc xá']; ?>
                        <?php foreach($types as $t): ?>
                        <div class="filter-option">
                            <input type="checkbox" id="type-<?= md5($t) ?>">
                            <label for="type-<?= md5($t) ?>"><?= $t ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Area -->
                <div class="filter-group">
                    <div class="filter-group-title">Diện tích <i class="fas fa-chevron-up"></i></div>
                    <?php $areas = ['Dưới 20m²','20 – 30m²','30 – 50m²','Trên 50m²']; ?>
                    <?php foreach($areas as $a): ?>
                    <div class="filter-option">
                        <input type="checkbox">
                        <label><?= $a ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Amenities -->
                <div class="filter-group">
                    <div class="filter-group-title">Tiện nghi <i class="fas fa-chevron-up"></i></div>
                    <?php
                    $amenities = [['fa-wifi','Wi-Fi'],['fa-wind','Điều hòa'],['fa-tint','Nước nóng'],['fa-car','Bãi đỗ xe'],['fa-sun','Ban công'],['fa-shield-alt','An ninh 24/7']];
                    foreach($amenities as $am):
                    ?>
                    <div class="filter-option">
                        <input type="checkbox">
                        <label><i class="fas <?= $am[0] ?>"></i> <?= $am[1] ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Verified -->
                <div class="filter-group">
                    <div class="filter-group-title">Xác thực <i class="fas fa-chevron-up"></i></div>
                    <div class="filter-option">
                        <input type="checkbox" id="verified" checked>
                        <label for="verified"><i class="fas fa-shield-check" style="color:var(--green)"></i> Đã xác thực eKYC</label>
                    </div>
                </div>

                <div style="padding: 16px 20px;">
                    <button class="btn-apply" onclick="applyFilters()">Áp dụng bộ lọc</button>
                </div>
            </div>
        </aside>

        <!-- RESULTS -->
        <div class="results-area">
            <div class="results-header">
                <div class="results-count">Tìm thấy <strong>2.341</strong> phòng trọ phù hợp</div>
                <div class="results-controls">
                    <select class="sort-select">
                        <option>Mới nhất</option>
                        <option>Giá thấp đến cao</option>
                        <option>Giá cao đến thấp</option>
                        <option>Diện tích lớn nhất</option>
                    </select>
                    <div class="view-btns">
                        <button class="view-btn active" id="btn-grid" onclick="setView('grid')"><i class="fas fa-th"></i></button>
                        <button class="view-btn" id="btn-list" onclick="setView('list')"><i class="fas fa-list"></i></button>
                    </div>
                </div>
            </div>

            <div class="rooms-grid" id="rooms-grid">
                <?php
                $rooms = [
                    ['id'=>1,'name'=>'Phòng 30m² mặt tiền đường Nguyễn Huệ, full nội thất','price'=>'3.500.000 đ/tháng','area'=>'30m²','capacity'=>'1-2 người','loc'=>'Quận 1, TP.HCM','img'=>'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&h=250&fit=crop','badge'=>'hot','amenities'=>[['fa-wifi','WiFi'],['fa-wind','Điều hòa'],['fa-car','Đỗ xe']]],
                    ['id'=>2,'name'=>'Căn hộ studio 25m² view hồ, ban công rộng rãi','price'=>'2.800.000 đ/tháng','area'=>'25m²','capacity'=>'1 người','loc'=>'Tây Hồ, Hà Nội','img'=>'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400&h=250&fit=crop','badge'=>'new','amenities'=>[['fa-wifi','WiFi'],['fa-tint','Nước nóng']]],
                    ['id'=>3,'name'=>'Phòng 35m² full nội thất cao cấp, an ninh 24/7','price'=>'4.200.000 đ/tháng','area'=>'35m²','capacity'=>'2 người','loc'=>'Quận 3, TP.HCM','img'=>'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=400&h=250&fit=crop','badge'=>'featured','amenities'=>[['fa-wifi','WiFi'],['fa-wind','Điều hòa'],['fa-sun','Ban công']]],
                    ['id'=>4,'name'=>'Nhà trọ 20m² giá rẻ gần ĐH Bách Khoa HN','price'=>'1.800.000 đ/tháng','area'=>'20m²','capacity'=>'1 người','loc'=>'Hai Bà Trưng, Hà Nội','img'=>'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=400&h=250&fit=crop','badge'=>'hot','amenities'=>[['fa-wifi','WiFi']]],
                    ['id'=>5,'name'=>'Phòng đẹp 28m² có cửa sổ thoáng mát trung tâm','price'=>'3.200.000 đ/tháng','area'=>'28m²','capacity'=>'1-2 người','loc'=>'Quận Hoàng Mai, Hà Nội','img'=>'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=400&h=250&fit=crop','badge'=>'new','amenities'=>[['fa-wifi','WiFi'],['fa-car','Đỗ xe']]],
                    ['id'=>6,'name'=>'Căn hộ mini 40m² full nội thất đẳng cấp','price'=>'5.500.000 đ/tháng','area'=>'40m²','capacity'=>'2 người','loc'=>'Quận Bình Thạnh, TP.HCM','img'=>'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?w=400&h=250&fit=crop','badge'=>'hot','amenities'=>[['fa-wifi','WiFi'],['fa-wind','Điều hòa'],['fa-car','Đỗ xe'],['fa-sun','Ban công']]],
                ];
                foreach($rooms as $r):
                ?>
                <div class="room-card" onclick="window.location.href='/room/<?= $r['id'] ?>'">
                    <div class="rc-img">
                        <img src="<?= $r['img'] ?>" alt="<?= htmlspecialchars($r['name']) ?>" loading="lazy">
                        <?php if($r['badge']==='hot'): ?>
                        <span class="badge badge-hot">HOT</span>
                        <?php elseif($r['badge']==='new'): ?>
                        <span class="badge badge-new">MỚI</span>
                        <?php else: ?>
                        <span class="badge badge-featured">NỔI BẬT</span>
                        <?php endif; ?>
                        <button class="btn-fav-card" onclick="event.stopPropagation()"><i class="far fa-heart"></i></button>
                    </div>
                    <div class="rc-body">
                        <div class="rc-title"><?= htmlspecialchars($r['name']) ?></div>
                        <div class="rc-price"><?= $r['price'] ?></div>
                        <div class="rc-meta">
                            <span><i class="fas fa-vector-square"></i> <?= $r['area'] ?></span>
                            <span><i class="fas fa-users"></i> <?= $r['capacity'] ?></span>
                        </div>
                        <div class="rc-loc"><i class="fas fa-location-dot"></i> <?= $r['loc'] ?></div>
                        <div class="rc-amenities">
                            <?php foreach($r['amenities'] as $am): ?>
                            <span class="amenity-tag"><i class="fas <?= $am[0] ?>"></i><?= $am[1] ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="rc-actions">
                            <button class="btn-detail" onclick="event.stopPropagation(); window.location.href='/room/<?= $r['id'] ?>'">Xem chi tiết</button>
                            <button class="btn-chat" onclick="event.stopPropagation()"><i class="fas fa-comment-dots"></i></button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="pagination">
                <button class="pg-btn"><i class="fas fa-chevron-left"></i></button>
                <button class="pg-btn active">1</button>
                <button class="pg-btn">2</button>
                <button class="pg-btn">3</button>
                <span class="pg-dots">...</span>
                <button class="pg-btn">24</button>
                <button class="pg-btn"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';