<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Phòng Trọ – RoomFinder.vn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:#2b3cf7;--primary-dark:#1a2bde;--primary-light:#eef0ff;
            --accent:#ff5a3d;--green:#10b981;--gold:#f59e0b;--red:#ef4444;
            --white:#fff;--gray-50:#f9fafb;--gray-100:#f3f4f6;--gray-200:#e5e7eb;
            --gray-300:#d1d5db;--gray-400:#9ca3af;--gray-500:#6b7280;
            --gray-600:#4b5563;--gray-700:#374151;--gray-800:#1f2937;--gray-900:#111827;
            --shadow-sm:0 1px 3px rgba(0,0,0,.06);--shadow-md:0 4px 16px rgba(0,0,0,.1);
            --shadow-lg:0 8px 32px rgba(0,0,0,.12);--shadow-xl:0 16px 48px rgba(0,0,0,.15);
            --radius:12px;--radius-lg:20px;
        }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Be Vietnam Pro',sans-serif;background:var(--gray-50);color:var(--gray-800);}
        a{text-decoration:none;color:inherit;}
        .container{max-width:1280px;margin:0 auto;padding:0 20px;}

        /* HEADER */
        .header{background:var(--white);box-shadow:0 1px 0 var(--gray-200);position:sticky;top:0;z-index:1000;}
        .header-inner{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;max-width:1280px;margin:0 auto;gap:24px;}
        .logo{display:flex;align-items:center;gap:8px;font-weight:800;font-size:18px;color:var(--primary);}
        .logo-icon{width:36px;height:36px;background:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--white);font-size:18px;}
        .nav{display:flex;gap:28px;align-items:center;}
        .nav a{font-size:14px;font-weight:500;color:var(--gray-600);transition:color .2s;}
        .nav a:hover{color:var(--primary);}
        .header-actions{display:flex;gap:10px;align-items:center;}
        .btn-post-h{background:var(--accent);color:var(--white);border:none;padding:9px 20px;border-radius:20px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;}
        .btn-login-h{background:transparent;color:var(--primary);border:2px solid var(--primary);padding:7px 20px;border-radius:20px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;}
        .btn-login-h:hover{background:var(--primary);color:var(--white);}

        /* BREADCRUMB */
        .breadcrumb{padding:16px 0;display:flex;align-items:center;gap:8px;font-size:13px;color:var(--gray-500);}
        .breadcrumb a{color:var(--primary);font-weight:500;}
        .breadcrumb i{font-size:10px;}

        /* MAIN GRID */
        .detail-grid{display:grid;grid-template-columns:1fr 380px;gap:28px;padding-bottom:60px;}

        /* GALLERY */
        .gallery-wrap{background:var(--white);border-radius:var(--radius-lg);overflow:hidden;border:1px solid var(--gray-200);margin-bottom:24px;}
        .gallery-main-wrap{position:relative;}
        .gallery-main{width:100%;height:440px;object-fit:cover;display:block;cursor:pointer;}
        .gallery-badge{position:absolute;top:16px;left:16px;background:var(--accent);color:var(--white);font-size:12px;font-weight:700;padding:6px 14px;border-radius:20px;}
        .gallery-fav{position:absolute;top:16px;right:16px;background:rgba(255,255,255,.95);border:none;width:40px;height:40px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--gray-500);transition:all .2s;box-shadow:var(--shadow-sm);}
        .gallery-fav:hover{color:var(--accent);}
        .gallery-fav.faved{color:var(--accent);}
        .gallery-share{position:absolute;top:64px;right:16px;background:rgba(255,255,255,.95);border:none;width:40px;height:40px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--gray-500);transition:all .2s;box-shadow:var(--shadow-sm);}
        .gallery-share:hover{color:var(--primary);}
        .thumbs{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;padding:12px;}
        .thumb{width:100%;aspect-ratio:1.2;object-fit:cover;border-radius:8px;cursor:pointer;border:2px solid transparent;transition:all .2s;opacity:.75;}
        .thumb:hover{opacity:1;}
        .thumb.active{border-color:var(--primary);opacity:1;}

        /* ROOM INFO CARD */
        .info-card{background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--gray-200);overflow:hidden;position:sticky;top:80px;}
        .info-top{padding:24px;border-bottom:1px solid var(--gray-100);}
        .info-badge-row{display:flex;align-items:center;gap:8px;margin-bottom:12px;}
        .info-badge{background:var(--primary-light);color:var(--primary);font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;}
        .info-verified{background:#f0fdf4;color:var(--green);font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;display:flex;align-items:center;gap:4px;}
        .info-price{font-size:2rem;font-weight:900;color:var(--accent);margin-bottom:4px;}
        .info-price-sub{font-size:12px;color:var(--gray-500);margin-bottom:16px;}
        .info-title{font-size:15px;font-weight:700;color:var(--gray-800);line-height:1.5;margin-bottom:14px;}
        .info-rating{display:flex;align-items:center;gap:8px;}
        .stars{color:var(--gold);font-size:14px;}
        .rating-num{font-size:13px;font-weight:700;color:var(--gray-700);}
        .rating-cnt{font-size:13px;color:var(--gray-500);}
        .meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:20px 24px;border-bottom:1px solid var(--gray-100);}
        .meta-item{background:var(--gray-50);border-radius:10px;padding:14px;text-align:center;}
        .meta-icon{font-size:18px;margin-bottom:6px;}
        .meta-label{font-size:11px;color:var(--gray-500);margin-bottom:3px;}
        .meta-value{font-size:14px;font-weight:700;color:var(--gray-800);}
        .cta-area{padding:20px 24px;border-bottom:1px solid var(--gray-100);display:flex;flex-direction:column;gap:10px;}
        .btn-main{width:100%;padding:14px;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .2s;}
        .btn-primary-cta{background:var(--primary);color:var(--white);}
        .btn-primary-cta:hover{background:var(--primary-dark);}
        .btn-accent-cta{background:var(--accent);color:var(--white);}
        .btn-accent-cta:hover{background:#e04d33;}
        .btn-outline-cta{background:var(--white);color:var(--gray-700);border:1.5px solid var(--gray-200);}
        .btn-outline-cta:hover{border-color:var(--primary);color:var(--primary);}
        .phone-reveal{background:var(--gray-50);border-radius:10px;padding:14px;text-align:center;border:1.5px dashed var(--gray-300);}
        .phone-reveal p{font-size:11px;color:var(--gray-500);margin-bottom:6px;}
        .phone-reveal strong{font-size:1.25rem;font-weight:800;color:var(--gray-900);}
        .landlord-area{padding:20px 24px;}
        .landlord-header{display:flex;align-items:center;gap:14px;margin-bottom:16px;}
        .landlord-avatar{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:var(--white);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;}
        .landlord-name{font-weight:700;font-size:14px;color:var(--gray-900);margin-bottom:3px;}
        .landlord-meta{font-size:12px;color:var(--gray-500);}
        .landlord-verified{display:flex;align-items:center;gap:5px;font-size:12px;color:var(--green);font-weight:600;margin-top:4px;}
        .landlord-verified i{font-size:11px;}
        .landlord-stats{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;}
        .l-stat{text-align:center;background:var(--gray-50);border-radius:8px;padding:10px;}
        .l-stat strong{display:block;font-size:1rem;font-weight:800;color:var(--gray-800);}
        .l-stat span{font-size:11px;color:var(--gray-500);}
        .report-link{text-align:center;font-size:12px;color:var(--gray-400);padding:0 24px 16px;}
        .report-link a{color:var(--red);}

        /* LEFT CONTENT */
        .content-card{background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--gray-200);padding:28px;margin-bottom:20px;}
        .content-title{font-size:1.1rem;font-weight:800;color:var(--gray-900);margin-bottom:20px;display:flex;align-items:center;gap:10px;}
        .content-title::before{content:'';width:4px;height:20px;background:var(--primary);border-radius:4px;flex-shrink:0;}
        .address-row{display:flex;align-items:flex-start;gap:12px;padding:14px;background:var(--primary-light);border-radius:10px;margin-bottom:20px;}
        .address-row i{color:var(--primary);font-size:18px;margin-top:2px;flex-shrink:0;}
        .address-text{font-size:14px;color:var(--primary-dark);font-weight:600;}
        .address-sub{font-size:12px;color:var(--primary);margin-top:3px;}
        .amenities-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px;}
        .amenity{display:flex;align-items:center;gap:10px;padding:12px;border:1px solid var(--gray-200);border-radius:10px;transition:all .2s;}
        .amenity:hover{border-color:var(--primary);background:var(--primary-light);}
        .amenity i{color:var(--primary);font-size:16px;width:20px;text-align:center;}
        .amenity span{font-size:13px;font-weight:500;color:var(--gray-700);}
        .desc-text{font-size:14px;color:var(--gray-700);line-height:1.8;}
        .map-placeholder{background:var(--gray-100);border-radius:10px;height:240px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:8px;color:var(--gray-500);font-size:14px;border:2px dashed var(--gray-300);margin-top:16px;}
        .map-placeholder i{font-size:32px;color:var(--gray-400);}

        /* TABS */
        .tab-bar{display:flex;gap:0;border-bottom:1px solid var(--gray-200);margin-bottom:20px;overflow-x:auto;}
        .tab-btn{padding:12px 20px;font-size:13px;font-weight:600;cursor:pointer;border:none;background:transparent;color:var(--gray-500);font-family:inherit;border-bottom:3px solid transparent;transition:all .2s;white-space:nowrap;}
        .tab-btn.active{color:var(--primary);border-bottom-color:var(--primary);}

        /* REVIEW */
        .review-form{background:var(--gray-50);border-radius:10px;padding:20px;margin-bottom:24px;}
        .review-form h4{font-size:14px;font-weight:700;margin-bottom:14px;color:var(--gray-800);}
        .star-row{display:flex;gap:4px;margin-bottom:14px;}
        .star-btn{font-size:22px;color:var(--gray-300);cursor:pointer;transition:color .15s;background:none;border:none;}
        .star-btn.active,.star-btn:hover~.star-btn{color:var(--gold);}
        .review-textarea{width:100%;border:1.5px solid var(--gray-200);border-radius:8px;padding:12px;font-size:13px;font-family:inherit;resize:vertical;min-height:90px;outline:none;}
        .review-textarea:focus{border-color:var(--primary);}
        .btn-review-submit{background:var(--primary);color:var(--white);border:none;padding:10px 24px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;margin-top:10px;}
        .review-item{display:flex;gap:14px;padding:16px 0;border-bottom:1px solid var(--gray-100);}
        .review-item:last-child{border-bottom:none;}
        .review-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);color:var(--white);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
        .review-name{font-size:13px;font-weight:700;color:var(--gray-800);}
        .review-date{font-size:11px;color:var(--gray-400);margin-left:10px;}
        .review-stars{color:var(--gold);font-size:12px;margin:4px 0;}
        .review-text{font-size:13px;color:var(--gray-700);line-height:1.6;}

        /* SIMILAR ROOMS */
        .similar-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;}
        .sim-card{border-radius:var(--radius);overflow:hidden;border:1px solid var(--gray-200);cursor:pointer;transition:all .2s;}
        .sim-card:hover{transform:translateY(-3px);box-shadow:var(--shadow-lg);border-color:transparent;}
        .sim-card img{width:100%;height:130px;object-fit:cover;display:block;}
        .sim-body{padding:12px;}
        .sim-title{font-size:12px;font-weight:600;color:var(--gray-800);margin-bottom:5px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;}
        .sim-price{font-size:13px;font-weight:800;color:var(--accent);margin-bottom:4px;}
        .sim-loc{font-size:11px;color:var(--gray-500);}

        @media(max-width:1024px){.detail-grid{grid-template-columns:1fr;}.info-card{position:static;}.amenities-grid{grid-template-columns:repeat(2,1fr);}}
        @media(max-width:768px){.nav{display:none;}.gallery-main{height:280px;}.thumbs{grid-template-columns:repeat(4,1fr);}.similar-grid{grid-template-columns:repeat(2,1fr);}}
    </style>
</head>
<body>

<header class="header">
    <div class="header-inner">
        <a href="/" class="logo"><div class="logo-icon">🏠</div> RoomFinder.vn</a>
        <nav class="nav">
            <a href="/">Trang chủ</a>
            <a href="/search">Tìm phòng</a>
            <a href="#">Cho thuê</a>
            <a href="#">Blog</a>
        </nav>
        <div class="header-actions">
            <button class="btn-post-h">+ Đăng tin</button>
            <button class="btn-login-h">Đăng nhập</button>
        </div>
    </div>
</header>

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

<footer style="background:var(--gray-900);color:rgba(255,255,255,.6);text-align:center;padding:24px;font-size:13px;">
    &copy; 2026 RoomFinder.vn. Tất cả quyền được bảo lưu.
</footer>

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