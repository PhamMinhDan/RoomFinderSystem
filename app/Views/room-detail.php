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
    <title>Chi Tiết Phòng Trọ – RoomFinder.vn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/room-detail.css">
</head>
<body>
<?php include __DIR__ . '/../../public/assets/components/header.php'; ?>

<div class="container">
    <div class="breadcrumb">
        <a href="/">Trang chủ</a>
        <i class="fas fa-chevron-right"></i>
        <a href="/search">Tìm phòng</a>
        <i class="fas fa-chevron-right"></i>
        <span>Phòng 30m² mặt tiền đường Nguyễn Huệ</span>
    </div>

    <div class="detail-grid">
        <!-- LEFT COLUMN -->
        <div>
            <!-- GALLERY -->
            <div class="gallery-wrap">
                <div class="gallery-main-wrap">
                    <img id="main-img" class="gallery-main" src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=900&h=500&fit=crop" alt="Phòng trọ">
                    <span class="gallery-badge">HOT</span>
                    <button class="gallery-fav" id="fav-btn" onclick="toggleFav()"><i class="far fa-heart"></i></button>
                    <button class="gallery-share" onclick="alert('Chia sẻ link')"><i class="fas fa-share-alt"></i></button>
                </div>
                <div class="thumbs">
                    <?php
                    $imgs = [
                        'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=200&h=140&fit=crop',
                        'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=200&h=140&fit=crop',
                        'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=200&h=140&fit=crop',
                        'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=200&h=140&fit=crop',
                        'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?w=200&h=140&fit=crop',
                    ];
                    foreach($imgs as $i => $img):
                    ?>
                    <img src="<?= $img ?>" class="thumb <?= $i===0?'active':'' ?>" alt="thumb" onclick="changeImg(this)">
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ROOM INFO -->
            <div class="content-card">
                <div class="content-title">Thông tin phòng trọ</div>
                <div class="address-row">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <div class="address-text">30 Nguyễn Huệ, Phường Bến Nghé, Quận 1</div>
                        <div class="address-sub">TP. Hồ Chí Minh · Cập nhật 2 ngày trước</div>
                    </div>
                </div>
                <div class="amenities-grid">
                    <?php
                    $amenities = [
                        ['fa-wifi','Wi-Fi miễn phí'],['fa-wind','Điều hòa riêng'],['fa-tint','Nước nóng 24/7'],
                        ['fa-car','Bãi đỗ xe'],['fa-sun','Ban công riêng'],['fa-shield-alt','An ninh 24/7'],
                        ['fa-tv','TV cáp'],['fa-box','Tủ lạnh'],['fa-couch','Đầy đủ nội thất'],
                    ];
                    foreach($amenities as $a):
                    ?>
                    <div class="amenity">
                        <i class="fas <?= $a[0] ?>"></i>
                        <span><?= $a[1] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- TABS -->
            <div class="content-card">
                <div class="tab-bar">
                    <button class="tab-btn active" onclick="showTab('desc',this)">Mô tả</button>
                    <button class="tab-btn" onclick="showTab('map',this)">Bản đồ</button>
                    <button class="tab-btn" onclick="showTab('reviews',this)">Đánh giá (89)</button>
                    <button class="tab-btn" onclick="showTab('similar',this)">Phòng tương tự</button>
                </div>

                <!-- DESC -->
                <div id="tab-desc">
                    <p class="desc-text">
                        Phòng trọ 30m² mặt tiền đường Nguyễn Huệ, trung tâm Quận 1, TP. Hồ Chí Minh. Phòng được thiết kế hiện đại, thoáng mát với đầy đủ nội thất cao cấp gồm: giường, tủ quần áo, bàn làm việc, điều hòa, tủ lạnh, máy nước nóng.
                        <br><br>
                        Vị trí đắc địa, cách Bến Thành 500m, gần các trung tâm thương mại, siêu thị, nhà hàng. An ninh nghiêm ngặt với camera giám sát 24/7 và bảo vệ thường trực. Chủ nhà thân thiện, nhiệt tình hỗ trợ.
                        <br><br>
                        <strong>Giá điện:</strong> 3.500đ/kWh &nbsp;|&nbsp; <strong>Giá nước:</strong> 80.000đ/người/tháng &nbsp;|&nbsp; <strong>Phí dịch vụ:</strong> 100.000đ/tháng
                    </p>
                </div>

                <!-- MAP -->
                <div id="tab-map" style="display:none;">
                    <div class="map-placeholder">
                        <i class="fas fa-map-marked-alt"></i>
                        <span>Bản đồ Google Maps</span>
                        <small>30 Nguyễn Huệ, Q.1, TP.HCM</small>
                    </div>
                </div>

                <!-- REVIEWS -->
                <div id="tab-reviews" style="display:none;">
                    <div class="review-form">
                        <h4>✍️ Viết đánh giá của bạn</h4>
                        <div class="star-row" id="star-row">
                            <?php for($i=1;$i<=5;$i++): ?>
                            <button class="star-btn" onclick="setRating(<?= $i ?>)">★</button>
                            <?php endfor; ?>
                        </div>
                        <textarea class="review-textarea" placeholder="Chia sẻ trải nghiệm của bạn về phòng này..."></textarea>
                        <button class="btn-review-submit">Gửi đánh giá</button>
                    </div>

                    <?php
                    $reviews = [
                        ['👨‍💼','Nguyễn Văn A','2 tuần trước',5,'Phòng rất sạch sẽ, chủ nhà thân thiện. An ninh tốt, có camera giám sát. Giá hợp lý với chất lượng. Tôi rất hài lòng với lựa chọn này.'],
                        ['👩‍💻','Trần Thị B','1 tháng trước',4,'Phòng đẹp, tiện nghi đầy đủ. Chỉ có điều là nước nóng đôi khi yếu, nhưng chủ nhà đã hỗ trợ kịp thời. Nhân viên quản lý lịch sự và nhiệt tình.'],
                        ['🧑‍🎓','Lê Văn C','1 tháng trước',5,'Vị trí tuyệt vời, gần ga tàu và các cơ sở thương mại. Phòng rộng, nóm xanh, điều hòa mạnh. Chủ nhà rất tử tế và dễ tương tác.'],
                    ];
                    foreach($reviews as $r):
                    ?>
                    <div class="review-item">
                        <div class="review-avatar"><?= $r[0] ?></div>
                        <div>
                            <div><span class="review-name"><?= $r[1] ?></span><span class="review-date"><?= $r[2] ?></span></div>
                            <div class="review-stars"><?= str_repeat('★',$r[3]).str_repeat('☆',5-$r[3]) ?> (<?= $r[3] ?>.0)</div>
                            <div class="review-text"><?= $r[4] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- SIMILAR -->
                <div id="tab-similar" style="display:none;">
                    <div class="similar-grid">
                        <?php
                        $sims = [
                            ['Phòng 28m² gần Bến Thành','3.200.000 đ/tháng','Q.1, TP.HCM','https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=300&h=160&fit=crop'],
                            ['Studio 32m² full nội thất','3.800.000 đ/tháng','Q.3, TP.HCM','https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=300&h=160&fit=crop'],
                            ['CCMN 25m² an ninh 24/7','2.500.000 đ/tháng','Q.Bình Thạnh','https://images.unsplash.com/photo-1536376072261-38c75010e6c9?w=300&h=160&fit=crop'],
                        ];
                        foreach($sims as $s):
                        ?>
                        <div class="sim-card" onclick="window.location.href='/room/1'">
                            <img src="<?= $s[3] ?>" alt="<?= htmlspecialchars($s[0]) ?>">
                            <div class="sim-body">
                                <div class="sim-title"><?= htmlspecialchars($s[0]) ?></div>
                                <div class="sim-price"><?= $s[1] ?></div>
                                <div class="sim-loc"><i class="fas fa-location-dot" style="color:var(--primary);font-size:10px"></i> <?= $s[2] ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN - STICKY INFO CARD -->
        <div>
            <div class="info-card">
                <div class="info-top">
                    <div class="info-badge-row">
                        <span class="info-badge">Phòng đơn</span>
                        <span class="info-verified"><i class="fas fa-check-circle"></i> Đã xác thực eKYC</span>
                    </div>
                    <div class="info-price">3.500.000 đ</div>
                    <div class="info-price-sub">mỗi tháng · tiền cọc 3.500.000 đ</div>
                    <div class="info-title">Phòng 30m² mặt tiền đường Nguyễn Huệ, full nội thất cao cấp</div>
                    <div class="info-rating">
                        <span class="stars">★★★★★</span>
                        <span class="rating-num">4.8</span>
                        <span class="rating-cnt">· 89 đánh giá</span>
                    </div>
                </div>

                <div class="meta-grid">
                    <div class="meta-item">
                        <div class="meta-icon">📐</div>
                        <div class="meta-label">Diện tích</div>
                        <div class="meta-value">30m²</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-icon">👥</div>
                        <div class="meta-label">Sức chứa</div>
                        <div class="meta-value">1-2 người</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-icon">🔑</div>
                        <div class="meta-label">Tiền cọc</div>
                        <div class="meta-value">3,5 triệu</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-icon">⚡</div>
                        <div class="meta-label">Tiền điện</div>
                        <div class="meta-value">3.500đ/kWh</div>
                    </div>
                </div>

                <div class="cta-area">
                    <button class="btn-main btn-accent-cta" onclick="alert('Mở chat với chủ nhà')">
                        <i class="fas fa-comment-dots"></i> Nhắn tin ngay
                    </button>
                    <button class="btn-main btn-outline-cta" id="phone-btn" onclick="showPhone()">
                        <i class="fas fa-phone"></i> Xem số điện thoại
                    </button>
                    <div class="phone-reveal" id="phone-reveal" style="display:none;">
                        <p>Số điện thoại chủ nhà</p>
                        <strong>0901 234 567</strong>
                    </div>
                    <button class="btn-main btn-primary-cta" onclick="toggleFav()">
                        <i class="far fa-heart" id="fav-icon-card"></i> Lưu yêu thích
                    </button>
                </div>

                <!-- LANDLORD -->
                <div class="landlord-area">
                    <div style="font-size:12px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.5px;margin-bottom:14px;">Thông tin chủ nhà</div>
                    <div class="landlord-header">
                        <div class="landlord-avatar">👨‍💼</div>
                        <div>
                            <div class="landlord-name">Nguyễn Văn An</div>
                            <div class="landlord-meta">Thành viên từ 2021 · 12 tin đăng</div>
                            <div class="landlord-verified"><i class="fas fa-shield-check"></i> Đã xác thực eKYC</div>
                        </div>
                    </div>
                    <div class="landlord-stats">
                        <div class="l-stat"><strong>4.8 ★</strong><span>Đánh giá</span></div>
                        <div class="l-stat"><strong>89</strong><span>Nhận xét</span></div>
                    </div>
                    <button class="btn-main btn-outline-cta" style="font-size:13px;padding:11px;" onclick="alert('Chat với chủ nhà')">
                        <i class="fas fa-comments"></i> Nhắn tin cho chủ nhà
                    </button>
                </div>

                <div class="report-link">
                    <i class="fas fa-flag"></i> Có vấn đề với bài đăng? <a href="#">Báo cáo</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../public/assets/components/footer.php'; ?>

<script>
    function changeImg(el) {
        const full = el.src.replace('w=200&h=140','w=900&h=500');
        document.getElementById('main-img').src = full;
        document.querySelectorAll('.thumb').forEach(t=>t.classList.remove('active'));
        el.classList.add('active');
    }

    function toggleFav() {
        const btn = document.getElementById('fav-btn');
        const icon = document.getElementById('fav-icon-card');
        btn.classList.toggle('faved');
        if(btn.classList.contains('faved')) {
            btn.innerHTML = '<i class="fas fa-heart" style="color:var(--accent)"></i>';
            icon.className = 'fas fa-heart';
            // Show message and redirect
            alert('Đã lưu phòng vào danh sách yêu thích!');
            setTimeout(() => {
                window.location.href = '/saved-rooms';
            }, 500);
        } else {
            btn.innerHTML = '<i class="far fa-heart"></i>';
            icon.className = 'far fa-heart';
        }
    }

    function showPhone() {
        const reveal = document.getElementById('phone-reveal');
        const btn = document.getElementById('phone-btn');
        reveal.style.display = reveal.style.display==='none' ? 'block' : 'none';
        btn.style.display = reveal.style.display==='block' ? 'none' : 'flex';
    }

    function showTab(name, el) {
        ['desc','map','reviews','similar'].forEach(t => {
            document.getElementById('tab-'+t).style.display = t===name ? 'block' : 'none';
        });
        document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
        el.classList.add('active');
    }

    let rating = 0;
    function setRating(n) {
        rating = n;
        document.querySelectorAll('#star-row .star-btn').forEach((s,i) => {
            s.style.color = i < n ? 'var(--gold)' : 'var(--gray-300)';
        });
    }
</script>
</body>
</html>