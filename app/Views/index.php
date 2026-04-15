<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoomFinder.vn – Tìm Nhanh, Kiếm Dễ Trọ Mới Toàn Quốc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2b3cf7;
            --primary-dark: #1a2bde;
            --primary-light: #eef0ff;
            --accent: #ff5a3d;
            --accent-light: #fff0ee;
            --gold: #f59e0b;
            --green: #10b981;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.08);
            --shadow-md: 0 4px 16px rgba(0,0,0,.1);
            --shadow-lg: 0 8px 32px rgba(0,0,0,.12);
            --shadow-xl: 0 16px 48px rgba(0,0,0,.15);
            --radius: 12px;
            --radius-lg: 20px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Be Vietnam Pro', sans-serif; background: var(--gray-50); color: var(--gray-800); }
        a { text-decoration: none; color: inherit; }
        img { display: block; }
        .container { max-width: 1280px; margin: 0 auto; padding: 0 20px; }

        /* ===== HEADER ===== */
        .header {
            background: var(--white);
            box-shadow: 0 1px 0 var(--gray-200);
            position: sticky; top: 0; z-index: 1000;
        }
        .header-inner {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 20px; max-width: 1280px; margin: 0 auto; gap: 24px;
        }
        .logo { display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 18px; color: var(--primary); flex-shrink: 0; }
        .logo-icon { width: 36px; height: 36px; background: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--white); font-size: 18px; }
        .nav { display: flex; gap: 28px; align-items: center; }
        .nav a { font-size: 14px; font-weight: 500; color: var(--gray-600); transition: color .2s; }
        .nav a:hover, .nav a.active { color: var(--primary); }
        .nav a.active { font-weight: 600; }
        .header-search {
            flex: 1; max-width: 320px; display: flex; align-items: center; gap: 8px;
            background: var(--gray-100); border: 1px solid var(--gray-200); border-radius: 24px; padding: 8px 16px;
        }
        .header-search i { color: var(--gray-400); font-size: 13px; }
        .header-search input { border: none; background: transparent; outline: none; font-size: 13px; width: 100%; font-family: inherit; color: var(--gray-700); }
        .header-actions { display: flex; gap: 10px; align-items: center; flex-shrink: 0; }
        .btn-post-header {
            background: var(--accent); color: var(--white); border: none;
            padding: 9px 20px; border-radius: 20px; font-size: 13px; font-weight: 700;
            cursor: pointer; font-family: inherit; transition: all .2s;
        }
        .btn-post-header:hover { background: #e04d33; transform: translateY(-1px); }
        .btn-login-header {
            background: transparent; color: var(--primary); border: 2px solid var(--primary);
            padding: 7px 20px; border-radius: 20px; font-size: 13px; font-weight: 700;
            cursor: pointer; font-family: inherit; transition: all .2s;
        }
        .btn-login-header:hover { background: var(--primary); color: var(--white); }

        /* ===== HERO ===== */
        .hero {
            background: linear-gradient(135deg, #0f1b8c 0%, #2b3cf7 45%, #1a2bde 100%);
            min-height: 420px; position: relative; overflow: hidden;
            display: flex; align-items: center;
        }
        .hero::before {
            content: ''; position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='600' height='400' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='500' cy='80' r='200' fill='rgba(255,255,255,0.03)'/%3E%3Ccircle cx='100' cy='350' r='150' fill='rgba(255,255,255,0.03)'/%3E%3C/svg%3E") no-repeat center/cover;
        }
        .hero-content { position: relative; z-index: 2; padding: 60px 0; }
        .hero-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .hero-text h1 {
            font-size: 3rem; font-weight: 900; color: var(--white); line-height: 1.15;
            margin-bottom: 16px; letter-spacing: -1px;
        }
        .hero-text h1 span { color: #7dd3fc; }
        .hero-text p { font-size: 15px; color: rgba(255,255,255,.8); line-height: 1.7; margin-bottom: 28px; max-width: 480px; }
        .hero-stats { display: flex; gap: 32px; }
        .hero-stat { text-align: center; }
        .hero-stat strong { display: block; font-size: 1.6rem; font-weight: 800; color: var(--white); }
        .hero-stat span { font-size: 12px; color: rgba(255,255,255,.7); font-weight: 500; }
        .hero-visual { position: relative; display: flex; justify-content: center; align-items: center; }
        .hero-house-card {
            background: rgba(255,255,255,.15); backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,.2); border-radius: 24px;
            padding: 30px; text-align: center; color: var(--white);
            animation: floatCard 3s ease-in-out infinite;
        }
        .hero-house-card .house-emoji { font-size: 80px; display: block; margin-bottom: 12px; }
        .hero-house-card p { font-size: 14px; opacity: .85; }
        @keyframes floatCard { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }

        /* ===== SEARCH TABS ===== */
        .search-section {
            background: var(--white);
            box-shadow: var(--shadow-md);
            border-bottom: 1px solid var(--gray-200);
        }
        .search-tabs-bar {
            display: flex; gap: 0; border-bottom: 1px solid var(--gray-200); overflow-x: auto;
        }
        .search-tab {
            padding: 16px 28px; font-size: 14px; font-weight: 600; cursor: pointer;
            border: none; background: transparent; color: var(--gray-500); font-family: inherit;
            border-bottom: 3px solid transparent; transition: all .2s; white-space: nowrap;
        }
        .search-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
        .search-tab:hover:not(.active) { color: var(--gray-700); background: var(--gray-50); }
        .search-bar {
            display: grid; grid-template-columns: 2fr 1fr 1fr auto;
            gap: 12px; padding: 20px; align-items: center;
        }
        .search-field {
            display: flex; align-items: center; gap: 10px;
            border: 1.5px solid var(--gray-200); border-radius: 10px; padding: 10px 14px;
            transition: border-color .2s;
        }
        .search-field:focus-within { border-color: var(--primary); }
        .search-field i { color: var(--gray-400); font-size: 14px; flex-shrink: 0; }
        .search-field input, .search-field select {
            border: none; outline: none; font-size: 14px; font-family: inherit;
            color: var(--gray-700); background: transparent; width: 100%;
        }
        .search-field input::placeholder { color: var(--gray-400); }
        .btn-search {
            background: var(--accent); color: var(--white); border: none;
            padding: 12px 28px; border-radius: 10px; font-size: 15px; font-weight: 700;
            cursor: pointer; font-family: inherit; display: flex; align-items: center;
            gap: 8px; transition: all .2s; white-space: nowrap;
        }
        .btn-search:hover { background: #e04d33; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255,90,61,.3); }

        /* ===== SECTION HEADERS ===== */
        .section-header { margin-bottom: 28px; }
        .section-label {
            font-size: 12px; font-weight: 700; color: var(--primary); letter-spacing: 2px;
            text-transform: uppercase; margin-bottom: 6px;
        }
        .section-title-big {
            font-size: 1.75rem; font-weight: 800; color: var(--gray-900); letter-spacing: -0.5px;
        }
        .section-title-big span { color: var(--primary); }

        /* ===== HOT LISTINGS ===== */
        .hot-section { padding: 60px 0; background: var(--white); }
        .rooms-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; }
        .room-card {
            background: var(--white); border-radius: var(--radius); overflow: hidden;
            border: 1px solid var(--gray-200); transition: all .3s; cursor: pointer;
        }
        .room-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-xl); border-color: transparent; }
        .room-card-img { position: relative; overflow: hidden; }
        .room-card-img img { width: 100%; height: 180px; object-fit: cover; transition: transform .4s; }
        .room-card:hover .room-card-img img { transform: scale(1.06); }
        .badge-hot {
            position: absolute; top: 10px; left: 10px;
            background: var(--accent); color: var(--white);
            font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px;
        }
        .badge-new {
            position: absolute; top: 10px; left: 10px;
            background: var(--green); color: var(--white);
            font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px;
        }
        .btn-review {
            position: absolute; bottom: 10px; left: 10px;
            background: rgba(0,0,0,.6); color: var(--white); border: none;
            font-size: 11px; font-weight: 600; padding: 5px 12px; border-radius: 20px;
            cursor: pointer; font-family: inherit; display: flex; align-items: center; gap: 5px;
            backdrop-filter: blur(4px);
        }
        .btn-review:hover { background: var(--primary); }
        .btn-fav {
            position: absolute; bottom: 10px; right: 10px;
            background: rgba(255,255,255,.9); border: none; width: 32px; height: 32px;
            border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;
            font-size: 14px; color: var(--gray-500); transition: all .2s;
        }
        .btn-fav:hover { color: var(--accent); background: var(--white); }
        .room-card-body { padding: 14px; }
        .room-card-name {
            font-size: 13px; font-weight: 600; color: var(--gray-800); line-height: 1.4;
            margin-bottom: 6px; overflow: hidden; display: -webkit-box;
            -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        }
        .room-card-price { font-size: 15px; font-weight: 800; color: var(--accent); margin-bottom: 6px; }
        .room-card-type { font-size: 11px; color: var(--gray-500); margin-bottom: 6px; }
        .room-card-loc {
            font-size: 12px; color: var(--gray-500); display: flex; align-items: center; gap: 4px;
        }
        .room-card-loc i { color: var(--primary); font-size: 11px; }

        /* ===== FEATURED CITIES ===== */
        .cities-section { padding: 60px 0; background: var(--gray-50); }
        .cities-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 16px; }
        .city-card {
            position: relative; border-radius: var(--radius); overflow: hidden;
            height: 200px; cursor: pointer; transition: all .3s;
        }
        .city-card img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
        .city-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-xl); }
        .city-card:hover img { transform: scale(1.08); }
        .city-card::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,.75) 0%, rgba(0,0,0,.1) 60%, transparent 100%);
        }
        .city-name {
            position: absolute; bottom: 14px; left: 0; right: 0; z-index: 2;
            text-align: center; color: var(--white); font-weight: 700; font-size: 14px;
        }
        .city-count {
            display: block; font-size: 11px; font-weight: 400; opacity: .85; margin-top: 2px;
        }

        /* ===== DISCOVER MORE ===== */
        .discover-section { padding: 60px 0; background: var(--white); }
        .discover-inner { background: var(--gray-50); border-radius: var(--radius-lg); padding: 40px; }
        .discover-title { font-size: 1.2rem; font-weight: 800; color: var(--primary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 1px; }
        .discover-sub { font-size: 13px; color: var(--gray-500); margin-bottom: 28px; }
        .discover-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; }
        .discover-col { padding-right: 20px; }
        .discover-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 0; border-bottom: 1px solid var(--gray-200);
            font-size: 14px; color: var(--gray-700); transition: all .2s; cursor: pointer;
        }
        .discover-item:hover { color: var(--primary); padding-left: 6px; }
        .discover-item:last-child { border-bottom: none; }
        .discover-item strong { font-weight: 600; }
        .discover-item span { font-size: 12px; color: var(--gray-400); }

        /* ===== FOOTER ===== */
        footer { background: var(--gray-900); color: rgba(255,255,255,.7); padding: 60px 0 0; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 40px; padding-bottom: 40px; border-bottom: 1px solid rgba(255,255,255,.1); }
        .footer-brand .logo { color: var(--white); margin-bottom: 14px; }
        .footer-brand .logo-icon { background: var(--primary); }
        .footer-brand p { font-size: 13px; line-height: 1.7; max-width: 260px; }
        .footer-social { display: flex; gap: 12px; margin-top: 20px; }
        .footer-social a {
            width: 36px; height: 36px; background: rgba(255,255,255,.1); border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; color: rgba(255,255,255,.7); transition: all .2s;
        }
        .footer-social a:hover { background: var(--primary); color: var(--white); }
        .footer-col h5 { color: var(--white); font-size: 14px; font-weight: 700; margin-bottom: 16px; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 10px; }
        .footer-col ul li a { font-size: 13px; color: rgba(255,255,255,.6); transition: color .2s; }
        .footer-col ul li a:hover { color: var(--white); }
        .footer-col .contact-item { display: flex; align-items: center; gap: 10px; font-size: 13px; margin-bottom: 10px; }
        .footer-col .contact-item i { color: var(--primary); width: 16px; }
        .footer-bottom { text-align: center; padding: 20px; font-size: 12px; color: rgba(255,255,255,.4); }

        /* ===== LOGIN MODAL ===== */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.55); backdrop-filter: blur(4px);
            z-index: 9999; align-items: center; justify-content: center; padding: 20px;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: var(--white); border-radius: 20px; width: 100%; max-width: 440px;
            padding: 40px; position: relative; animation: modalIn .3s cubic-bezier(.34,1.56,.64,1);
        }
        @keyframes modalIn { from { opacity: 0; transform: scale(.9) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
        .modal-close {
            position: absolute; top: 16px; right: 16px;
            background: var(--gray-100); border: none; width: 32px; height: 32px; border-radius: 50%;
            cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center;
            color: var(--gray-600); transition: all .2s;
        }
        .modal-close:hover { background: var(--gray-200); }
        .modal-logo { display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 16px; color: var(--primary); margin-bottom: 24px; }
        .modal-logo .logo-icon { width: 32px; height: 32px; font-size: 16px; }
        .modal-title { font-size: 22px; font-weight: 800; color: var(--gray-900); margin-bottom: 6px; }
        .modal-sub { font-size: 13px; color: var(--gray-500); margin-bottom: 28px; }
        .social-login { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
        .social-btn {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            padding: 12px; border: 1.5px solid var(--gray-200); border-radius: 10px;
            background: var(--white); font-size: 14px; font-weight: 600; cursor: pointer;
            font-family: inherit; color: var(--gray-700); transition: all .2s;
        }
        .social-btn:hover { background: var(--gray-50); border-color: var(--gray-300); }
        .social-btn svg { flex-shrink: 0; }
        .divider { display: flex; align-items: center; gap: 12px; margin: 20px 0; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--gray-200); }
        .divider span { font-size: 12px; color: var(--gray-400); white-space: nowrap; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--gray-700); margin-bottom: 6px; }
        .form-input {
            width: 100%; border: 1.5px solid var(--gray-200); border-radius: 10px;
            padding: 12px 14px; font-size: 14px; font-family: inherit; color: var(--gray-800);
            outline: none; transition: border-color .2s;
        }
        .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(43,60,247,.1); }
        .btn-submit {
            width: 100%; background: var(--primary); color: var(--white); border: none;
            padding: 14px; border-radius: 10px; font-size: 15px; font-weight: 700;
            cursor: pointer; font-family: inherit; margin-top: 16px; transition: all .2s;
        }
        .btn-submit:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .modal-footer-text { text-align: center; font-size: 13px; color: var(--gray-500); margin-top: 20px; }
        .modal-footer-text a { color: var(--primary); font-weight: 600; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1200px) {
            .rooms-grid { grid-template-columns: repeat(4, 1fr); }
            .cities-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 1024px) {
            .hero-inner { grid-template-columns: 1fr; }
            .hero-visual { display: none; }
            .hero-text h1 { font-size: 2.4rem; }
            .search-bar { grid-template-columns: 1fr 1fr; }
            .discover-grid { grid-template-columns: repeat(2, 1fr); }
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 768px) {
            .nav { display: none; }
            .header-search { display: none; }
            .rooms-grid { grid-template-columns: repeat(2, 1fr); }
            .cities-grid { grid-template-columns: repeat(2, 1fr); }
            .search-bar { grid-template-columns: 1fr; }
            .hero-text h1 { font-size: 2rem; }
        }
        @media (max-width: 480px) {
            .rooms-grid { grid-template-columns: repeat(2, 1fr); }
            .discover-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="header">
    <div class="header-inner">
        <a href="/" class="logo">
            <div class="logo-icon">🏠</div>
            RoomFinder.vn
        </a>
        <nav class="nav">
            <a href="/" class="active">Trang chủ</a>
            <a href="/search">Tìm phòng</a>
            <a href="#">Cho thuê</a>
            <a href="#">Blog</a>
            <a href="#contact">Liên hệ</a>
        </nav>
        <div class="header-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Tìm kiếm nhanh..." onkeydown="if(event.key==='Enter') window.location.href='/search?keyword='+encodeURIComponent(this.value)">
        </div>
        <div class="header-actions">
            <button class="btn-post-header">+ Đăng tin</button>
            <button class="btn-login-header" onclick="openModal()">Đăng nhập</button>
        </div>
    </div>
</header>

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

<!-- FOOTER -->
<footer id="contact">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="logo">
                    <div class="logo-icon">🏠</div> RoomFinder.vn
                </div>
                <p>Nền tảng tìm kiếm phòng trọ hàng đầu Việt Nam với đa dạng lựa chọn và an toàn tuyệt đối.</p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h5>Khám Phá</h5>
                <ul>
                    <li><a href="/search">Tất cả phòng trọ</a></li>
                    <li><a href="/search?sort=new">Phòng mới nhất</a></li>
                    <li><a href="/search?sort=popular">Phòng yêu thích nhất</a></li>
                    <li><a href="#">Hướng dẫn thuê trọ</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>Cho Chủ Trọ</h5>
                <ul>
                    <li><a href="#">Đăng tin miễn phí</a></li>
                    <li><a href="#">Quản lý tin đăng</a></li>
                    <li><a href="#">Xác thực danh tính</a></li>
                    <li><a href="#">Trợ giúp</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>Liên Hệ</h5>
                <div class="contact-item"><i class="fas fa-phone"></i> 1900 xxxx</div>
                <div class="contact-item"><i class="fas fa-envelope"></i> support@roomfinder.vn</div>
                <div class="contact-item"><i class="fas fa-map-marker-alt"></i> TP. Hồ Chí Minh</div>
            </div>
        </div>
        <div class="footer-bottom">&copy; 2026 RoomFinder.vn. Tất cả quyền được bảo lưu.</div>
    </div>
</footer>

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
</body>
</html>