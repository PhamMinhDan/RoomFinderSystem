<?php
$title      = "Tìm kiếm phòng trọ – RoomFinder.vn";
$css        = ['search.css'];
$js         = ['search.js'];
$showFooter = false;
$activeNav  = 'search';

// Đọc params từ URL để pre-fill
$keyword    = htmlspecialchars($_GET['keyword'] ?? '');
$cityParam  = htmlspecialchars($_GET['city']    ?? '');

ob_start();
?>

<!-- ══ SEARCH HERO ══════════════════════════════════════════════════════ -->
<div class="search-hero">
    <div class="container">
        <h1>Tìm phòng trọ <span>phù hợp với bạn</span></h1>
        <div class="hero-bar">
            <div class="sfield sfield-lg">
                <i class="fas fa-search"></i>
                <input type="text" id="kw"
                       placeholder="Tên đường, khu vực, tiêu đề..."
                       value="<?= $keyword ?>">
            </div>
            <div class="sfield">
                <i class="fas fa-map-marker-alt"></i>
                <select id="city-select">
                    <option value="">Tất cả tỉnh thành</option>
                    <!-- inject bởi location-service.js -->
                </select>
            </div>
            <div class="sfield">
                <i class="fas fa-tag"></i>
                <select id="price-filter">
                    <option value="">Mức giá</option>
                    <option value="0-2000000">Dưới 2 triệu</option>
                    <option value="2000000-5000000">2 – 5 triệu</option>
                    <option value="5000000-10000000">5 – 10 triệu</option>
                    <option value="10000000-">Trên 10 triệu</option>
                </select>
            </div>
            <div class="sfield">
                <i class="fas fa-expand-arrows-alt"></i>
                <select id="area-filter">
                    <option value="">Diện tích</option>
                    <option value="0-20">Dưới 20m²</option>
                    <option value="20-30">20 – 30m²</option>
                    <option value="30-50">30 – 50m²</option>
                    <option value="50-">Trên 50m²</option>
                </select>
            </div>
            <button class="btn-go" onclick="applySearch()">
                <i class="fas fa-search"></i> Tìm kiếm
            </button>
        </div>
    </div>
</div>

<!-- ══ TABS ══════════════════════════════════════════════════════════════ -->
<div class="tabs-bar">
    <div class="tabs-inner">
        <button class="tab active" data-type=""> Tất cả</button>
        <button class="tab" data-type="motel"> Phòng trọ</button>
        <button class="tab" data-type="house"> Nhà nguyên căn</button>
        <button class="tab" data-type="apt"> Căn hộ</button>
        <button class="tab" data-type="dormitory"> Ký túc xá</button>
        <button class="tab" data-type="mini"> Chung cư mini</button>
    </div>
</div>

<!-- ══ MAIN LAYOUT ════════════════════════════════════════════════════════ -->
<div class="container">
    <div class="main-layout">

        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="filter-card">
                <div class="filter-head">
                    <h3><i class="fas fa-sliders-h"></i> Bộ lọc</h3>
                    <button onclick="resetFilters()">Đặt lại</button>
                </div>

                <!-- Giá -->
                <div class="filter-group">
                    <div class="filter-group-title">
                        Khoảng giá (triệu/tháng)
                        <i class="fas fa-chevron-up"></i>
                    </div>
                    <div class="price-grid">
                        <input type="number" class="price-input" id="price-min"
                               placeholder="Từ" min="0" step="0.5">
                        <input type="number" class="price-input" id="price-max"
                               placeholder="Đến" min="0" step="0.5">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;margin-top:10px;">
                        <?php
                        $ranges = [
                            ['Dưới 2 triệu', '', '2'],
                            ['2 – 3 triệu',  '2', '3'],
                            ['3 – 5 triệu',  '3', '5'],
                            ['5 – 8 triệu',  '5', '8'],
                            ['Trên 8 triệu', '8', ''],
                        ];
                        foreach ($ranges as $r):
                        ?>
                        <label class="filter-option" onclick="setQuickPrice('<?= $r[1] ?>','<?= $r[2] ?>')">
                            <input type="radio" name="price-range" value="<?= $r[1] ?>-<?= $r[2] ?>">
                            <span style="font-size:13px;color:var(--gray-700)"><?= $r[0] ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Loại phòng -->
                <div class="filter-group">
                    <div class="filter-group-title">
                        Loại phòng <i class="fas fa-chevron-up"></i>
                    </div>
                    <?php
                    $types = [
                        ['motel',     'Phòng trọ / Nhà trọ'],
                        ['house',     'Nhà nguyên căn'],
                        ['apt',       'Căn hộ / Chung cư'],
                        ['mini',      'Chung cư mini'],
                        ['dormitory', 'Ký túc xá'],
                    ];
                    foreach ($types as $t):
                    ?>
                    <div class="filter-option">
                        <input type="checkbox" id="type-<?= $t[0] ?>" value="<?= $t[0] ?>">
                        <label for="type-<?= $t[0] ?>"><?= $t[1] ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Diện tích -->
                <div class="filter-group">
                    <div class="filter-group-title">
                        Diện tích <i class="fas fa-chevron-up"></i>
                    </div>
                    <?php
                    $areas = [
                        ['0-20',  'Dưới 20m²'],
                        ['20-30', '20 – 30m²'],
                        ['30-50', '30 – 50m²'],
                        ['50-',   'Trên 50m²'],
                    ];
                    foreach ($areas as $a):
                    ?>
                    <div class="filter-option">
                        <input type="radio" name="area-range" value="<?= $a[0] ?>">
                        <label><?= $a[1] ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tiện nghi -->
                <div class="filter-group">
                    <div class="filter-group-title">
                        Tiện nghi <i class="fas fa-chevron-up"></i>
                    </div>
                    <?php
                    $amenities = [
                        ['fa-wifi',       'Wi-Fi miễn phí'],
                        ['fa-wind',       'Điều hòa'],
                        ['fa-tint',       'Nước nóng 24/7'],
                        ['fa-car',        'Bãi đỗ xe'],
                        ['fa-sun',        'Ban công'],
                        ['fa-shield-alt', 'An ninh 24/7'],
                        ['fa-tshirt',     'Máy giặt'],
                        ['fa-utensils',   'Nhà bếp'],
                    ];
                    foreach ($amenities as $am):
                    ?>
                    <div class="filter-option">
                        <input type="checkbox" value="<?= $am[1] ?>">
                        <label>
                            <i class="fas <?= $am[0] ?>" style="color:var(--primary);width:14px;"></i>
                            <?= $am[1] ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Xác thực -->
                <div class="filter-group">
                    <div class="filter-group-title">Độ tin cậy <i class="fas fa-chevron-up"></i></div>
                    <div class="filter-option">
                        <input type="checkbox" id="verified-only">
                        <label for="verified-only">
                            <i class="fas fa-shield-check" style="color:var(--green);width:14px;"></i>
                            Chỉ hiện đã xác thực eKYC
                        </label>
                    </div>
                </div>

                <div style="padding:16px 20px;">
                    <button class="btn-apply" onclick="applyFilters()">
                        <i class="fas fa-filter"></i> Áp dụng bộ lọc
                    </button>
                </div>
            </div>
        </aside>

        <!-- RESULTS -->
        <div class="results-area">

            <!-- Header kết quả -->
            <div class="results-header">
                <div class="results-count">
                    <i class="fas fa-spinner fa-spin" id="loadingIcon" style="color:var(--primary);margin-right:6px;"></i>
                    Đang tải kết quả...
                </div>
                <div class="results-controls">
                    <select class="sort-select" id="sort-select">
                        <option value="newest">Mới nhất</option>
                        <option value="price_asc">Giá thấp → cao</option>
                        <option value="price_desc">Giá cao → thấp</option>
                        <option value="area_desc">Diện tích lớn nhất</option>
                        <option value="rating">Đánh giá cao</option>
                    </select>
                    <div class="view-btns">
                        <button class="view-btn active" id="btn-grid" onclick="setView('grid')" title="Lưới">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-btn" id="btn-list" onclick="setView('list')" title="Danh sách">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Active filters pills -->
            <div id="activeFilters" style="display:none;margin-bottom:12px;display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                <span style="font-size:12px;color:var(--gray-500);font-weight:600;">Đang lọc:</span>
                <div id="filterPills" style="display:flex;gap:6px;flex-wrap:wrap;"></div>
                <button onclick="resetFilters()" style="font-size:12px;color:var(--red,#ef4444);background:none;border:none;cursor:pointer;font-weight:600;">
                    Xóa tất cả ×
                </button>
            </div>

            <!-- Grid phòng – JS sẽ inject vào đây -->
            <div class="rooms-grid" id="rooms-grid">
                <!-- Skeleton placeholders -->
                <?php for ($i = 0; $i < 6; $i++): ?>
                <div class="room-card" style="pointer-events:none;">
                    <div class="rc-img">
                        <div style="width:100%;height:185px;background:linear-gradient(90deg,#f3f4f6 25%,#e5e7eb 50%,#f3f4f6 75%);background-size:200% 100%;animation:shimmer 1.4s infinite;"></div>
                    </div>
                    <div class="rc-body">
                        <div style="height:14px;background:linear-gradient(90deg,#f3f4f6 25%,#e5e7eb 50%,#f3f4f6 75%);background-size:200% 100%;animation:shimmer 1.4s infinite;border-radius:6px;margin-bottom:8px;width:85%;"></div>
                        <div style="height:20px;background:linear-gradient(90deg,#f3f4f6 25%,#e5e7eb 50%,#f3f4f6 75%);background-size:200% 100%;animation:shimmer 1.4s infinite;border-radius:6px;margin-bottom:8px;width:50%;"></div>
                        <div style="height:12px;background:linear-gradient(90deg,#f3f4f6 25%,#e5e7eb 50%,#f3f4f6 75%);background-size:200% 100%;animation:shimmer 1.4s infinite;border-radius:6px;width:65%;"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination" id="pagination">
                <button class="pg-btn" id="pg-prev" onclick="goPage(currentPage-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pg-nums" style="display:flex;gap:6px;"></div>
                <button class="pg-btn" id="pg-next" onclick="goPage(currentPage+1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>

</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';