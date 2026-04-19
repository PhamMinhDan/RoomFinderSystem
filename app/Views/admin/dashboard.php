<?php
use Core\SessionManager;

SessionManager::start();
$currentUser = SessionManager::getUser();

if (!$currentUser || strtoupper($currentUser['role'] ?? '') !== 'ADMIN') {
    $redirect = $currentUser ? '/' : '/?redirect=/admin/dashboard';
    header('Location: ' . $redirect, true, 302);
    exit;
}

$adminName   = htmlspecialchars($currentUser['full_name'] ?? $currentUser['username'] ?? 'Admin');
$adminEmail  = htmlspecialchars($currentUser['email'] ?? '');
$adminAvatar = htmlspecialchars($currentUser['avatar_url'] ?? '');
$adminInitial = mb_strtoupper(mb_substr($currentUser['full_name'] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – RoomFinder</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin-dashboard.css">
</head>
<body>

<div class="admin-shell">

    <!-- ══════════ SIDEBAR ══════════ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-top">
            <a href="/" class="sidebar-logo">
                <div class="sidebar-logo-icon">🏠</div>
                <span>RoomFinder</span>
            </a>
            <button class="sidebar-collapse-btn" id="collapseBtn" title="Thu gọn">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Tổng quan</div>
            <a href="/admin/dashboard" class="nav-link active" data-page="dashboard">
                <i class="fas fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>

            <div class="nav-section-label">Quản lý tin</div>
            <a href="/admin/pending" class="nav-link" data-page="pending">
                <i class="fas fa-hourglass-half"></i>
                <span>Chờ duyệt</span>
                <span class="nav-badge">48</span>
            </a>
            <a href="/admin/approved" class="nav-link" data-page="approved">
                <i class="fas fa-check-circle"></i>
                <span>Đã duyệt</span>
            </a>
            <a href="/admin/listings" class="nav-link" data-page="listings">
                <i class="fas fa-list-ul"></i>
                <span>Tất cả tin</span>
            </a>
            <a href="/admin/rejected" class="nav-link" data-page="rejected">
                <i class="fas fa-ban"></i>
                <span>Bị từ chối</span>
            </a>

            <div class="nav-section-label">Người dùng</div>
            <a href="/admin/users" class="nav-link" data-page="users">
                <i class="fas fa-users"></i>
                <span>Danh sách</span>
            </a>
            <a href="/admin/verify-users" class="nav-link" data-page="verify-users">
                <i class="fas fa-user-check"></i>
                <span>Chờ xác thực</span>
                <span class="nav-badge warn">124</span>
            </a>
            <a href="/admin/banned" class="nav-link" data-page="banned">
                <i class="fas fa-user-slash"></i>
                <span>Bị khoá</span>
            </a>

            <div class="nav-section-label">Hệ thống</div>
            <a href="/admin/settings" class="nav-link" data-page="settings">
                <i class="fas fa-cog"></i>
                <span>Cài đặt</span>
            </a>
        </nav>

        <!-- User card at bottom -->
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <?php if ($adminAvatar): ?>
                    <img src="<?= $adminAvatar ?>" alt="Avatar" referrerpolicy="no-referrer">
                <?php else: ?>
                    <span><?= $adminInitial ?></span>
                <?php endif; ?>
                <span class="online-dot"></span>
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= $adminName ?></div>
                <div class="sidebar-user-role">Administrator</div>
            </div>
            <div class="sidebar-user-actions">
                <a href="/" title="Về trang chủ" class="sidebar-action-btn">
                    <i class="fas fa-external-link-alt"></i>
                </a>
                <form method="POST" action="/auth/logout" style="margin:0">
                    <button type="submit" title="Đăng xuất" class="sidebar-action-btn danger">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- ══════════ MAIN ══════════ -->
    <div class="main-wrap">

        <!-- Top Bar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="topbar-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Tìm kiếm tin đăng, người dùng...">
                    <span class="search-shortcut">⌘K</span>
                </div>
            </div>
            <div class="topbar-right">
                <button class="topbar-icon-btn" id="notifBtn">
                    <i class="fas fa-bell"></i>
                    <span class="topbar-badge">3</span>
                </button>

                <!-- Avatar Dropdown -->
                <div class="topbar-user" id="topbarUser">
                    <div class="topbar-avatar" onclick="toggleUserMenu()">
                        <?php if ($adminAvatar): ?>
                            <img src="<?= $adminAvatar ?>" alt="Avatar" referrerpolicy="no-referrer">
                        <?php else: ?>
                            <span><?= $adminInitial ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="topbar-user-info" onclick="toggleUserMenu()">
                        <span class="topbar-name"><?= $adminName ?></span>
                        <span class="topbar-role">Admin</span>
                    </div>
                    <i class="fas fa-chevron-down topbar-chevron" id="topbarChevron" onclick="toggleUserMenu()"></i>

                    <!-- Dropdown Menu -->
                    <div class="user-dropdown-menu" id="userDropdownMenu">
                        <div class="udm-header">
                            <div class="udm-avatar">
                                <?php if ($adminAvatar): ?>
                                    <img src="<?= $adminAvatar ?>" alt="Avatar" referrerpolicy="no-referrer">
                                <?php else: ?>
                                    <span><?= $adminInitial ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="udm-name"><?= $adminName ?></div>
                                <div class="udm-email"><?= $adminEmail ?></div>
                                <span class="udm-badge">ADMIN</span>
                            </div>
                        </div>
                        <div class="udm-divider"></div>
                        <a href="/admin/settings" class="udm-item">
                            <i class="fas fa-cog"></i> Cài đặt hệ thống
                        </a>
                        <a href="/" class="udm-item">
                            <i class="fas fa-home"></i> Về trang chủ
                        </a>
                        <div class="udm-divider"></div>
                        <form method="POST" action="/auth/logout" style="margin:0">
                            <button type="submit" class="udm-item danger">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content" id="pageContent">

            <!-- ── DASHBOARD PAGE ── -->
            <div class="page" id="page-dashboard">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Dashboard</h1>
                        <p class="page-subtitle">Chào buổi sáng, <?= $adminName ?>! Đây là tổng quan hôm nay.</p>
                    </div>
                    <div class="page-header-actions">
                        <button class="btn-secondary"><i class="fas fa-download"></i> Xuất báo cáo</button>
                        <button class="btn-primary"><i class="fas fa-plus"></i> Thêm tin</button>
                    </div>
                </div>

                <!-- Stat Cards -->
                <div class="stat-grid">
                    <div class="stat-card" style="--accent-color: #2b3cf7;">
                        <div class="stat-card-icon" style="background: linear-gradient(135deg,#dbeafe,#bfdbfe);">
                            <i class="fas fa-file-alt" style="color:#2b3cf7"></i>
                        </div>
                        <div class="stat-card-body">
                            <div class="stat-card-label">Tổng tin đăng</div>
                            <div class="stat-card-value">1,284</div>
                            <div class="stat-card-growth up"><i class="fas fa-arrow-up"></i> 12% so với tuần trước</div>
                        </div>
                    </div>
                    <div class="stat-card" style="--accent-color: #f59e0b;">
                        <div class="stat-card-icon" style="background: linear-gradient(135deg,#fef3c7,#fde68a);">
                            <i class="fas fa-hourglass-half" style="color:#d97706"></i>
                        </div>
                        <div class="stat-card-body">
                            <div class="stat-card-label">Chờ duyệt</div>
                            <div class="stat-card-value">48</div>
                            <div class="stat-card-growth warn"><i class="fas fa-arrow-up"></i> 5% cần xử lý</div>
                        </div>
                    </div>
                    <div class="stat-card" style="--accent-color: #10b981;">
                        <div class="stat-card-icon" style="background: linear-gradient(135deg,#d1fae5,#a7f3d0);">
                            <i class="fas fa-check-circle" style="color:#059669"></i>
                        </div>
                        <div class="stat-card-body">
                            <div class="stat-card-label">Đã duyệt</div>
                            <div class="stat-card-value">1,236</div>
                            <div class="stat-card-growth up"><i class="fas fa-arrow-up"></i> 8% tháng này</div>
                        </div>
                    </div>
                    <div class="stat-card" style="--accent-color: #8b5cf6;">
                        <div class="stat-card-icon" style="background: linear-gradient(135deg,#ede9fe,#ddd6fe);">
                            <i class="fas fa-users" style="color:#7c3aed"></i>
                        </div>
                        <div class="stat-card-body">
                            <div class="stat-card-label">Người dùng</div>
                            <div class="stat-card-value">856</div>
                            <div class="stat-card-growth up"><i class="fas fa-arrow-up"></i> 3% mới đăng ký</div>
                        </div>
                    </div>
                    <div class="stat-card" style="--accent-color: #06b6d4;">
                        <div class="stat-card-icon" style="background: linear-gradient(135deg,#cffafe,#a5f3fc);">
                            <i class="fas fa-user-check" style="color:#0891b2"></i>
                        </div>
                        <div class="stat-card-body">
                            <div class="stat-card-label">Chờ xác thực</div>
                            <div class="stat-card-value">124</div>
                            <div class="stat-card-growth warn"><i class="fas fa-arrow-up"></i> 2% chờ duyệt</div>
                        </div>
                    </div>
                    <div class="stat-card" style="--accent-color: #2b3cf7;">
                        <div class="stat-card-icon" style="background: linear-gradient(135deg,#dbeafe,#bfdbfe);">
                            <i class="fas fa-percentage" style="color:#2b3cf7"></i>
                        </div>
                        <div class="stat-card-body">
                            <div class="stat-card-label">Tỷ lệ duyệt</div>
                            <div class="stat-card-value">96.3%</div>
                            <div class="stat-card-growth up"><i class="fas fa-arrow-up"></i> 2.1% cải thiện</div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="charts-row">
                    <div class="chart-card wide">
                        <div class="card-hdr">
                            <h3>Tin đăng theo tuần</h3>
                            <div class="card-hdr-tabs">
                                <button class="tab-btn active">Tuần</button>
                                <button class="tab-btn">Tháng</button>
                            </div>
                        </div>
                        <div class="bar-chart-wrap">
                            <div class="bar-chart-container" id="barChart"></div>
                        </div>
                    </div>
                    <div class="chart-card">
                        <div class="card-hdr"><h3>Quận hot nhất</h3></div>
                        <div class="district-list">
                            <?php
                            $districts = [
                                ['Quận 1', 324, 12, '#2b3cf7'],
                                ['Quận 3', 287, 8, '#8b5cf6'],
                                ['Quận 5', 256, 5, '#06b6d4'],
                                ['Quận 7', 198, 3, '#10b981'],
                                ['Quận 11', 176, 7, '#f59e0b'],
                            ];
                            foreach ($districts as $i => [$name, $count, $pct, $color]):
                            ?>
                            <div class="district-item">
                                <div class="district-rank" style="background:<?= $color ?>20;color:<?= $color ?>"><?= $i+1 ?></div>
                                <div class="district-info">
                                    <div class="district-name"><?= $name ?></div>
                                    <div class="district-bar-wrap">
                                        <div class="district-bar" style="width:<?= round($count/324*100) ?>%;background:<?= $color ?>"></div>
                                    </div>
                                </div>
                                <div class="district-stats">
                                    <span class="district-count"><?= $count ?></span>
                                    <span class="district-pct up">↑<?= $pct ?>%</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Approval Queue -->
                <div class="section-card">
                    <div class="section-card-hdr">
                        <h3>Tin mới chờ duyệt</h3>
                        <a href="/admin/pending" class="btn-link">Xem tất cả <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="approval-list">
                        <?php
                        $listings = [
                            ['Phòng 25m² gác lửng đầy đủ tiện nghi', 'Quận 1, TP.HCM', '3,500,000', '19/04/2026', 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=120&h=120&fit=crop'],
                            ['Studio 30m² mặt tiền Nguyễn Huệ', 'Quận 1, TP.HCM', '4,200,000', '18/04/2026', 'https://images.unsplash.com/photo-1469022563149-aa64dbd37daa?w=120&h=120&fit=crop'],
                            ['Căn hộ mini full nội thất Bình Thạnh', 'Quận Bình Thạnh, TP.HCM', '5,000,000', '17/04/2026', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=120&h=120&fit=crop'],
                        ];
                        foreach ($listings as $l):
                        ?>
                        <div class="approval-row">
                            <input type="checkbox" class="approval-cb">
                            <img src="<?= $l[4] ?>" class="approval-img" alt="">
                            <div class="approval-info">
                                <div class="approval-title"><?= $l[0] ?></div>
                                <div class="approval-meta-row">
                                    <span><i class="fas fa-map-marker-alt"></i> <?= $l[1] ?></span>
                                    <span><i class="fas fa-calendar"></i> <?= $l[3] ?></span>
                                </div>
                                <div class="approval-price"><?= $l[2] ?> đ/tháng</div>
                            </div>
                            <div class="approval-actions-col">
                                <span class="status-badge pending">Chờ duyệt</span>
                                <div class="approval-btns">
                                    <button class="btn-approve-sm" onclick="approveListing(this)"><i class="fas fa-check"></i> Duyệt</button>
                                    <button class="btn-reject-sm" onclick="rejectListing(this)"><i class="fas fa-times"></i> Từ chối</button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="approval-bulk-actions">
                        <button class="btn-primary" onclick="approveSelected()"><i class="fas fa-check-double"></i> Duyệt đã chọn</button>
                        <button class="btn-danger" onclick="rejectSelected()"><i class="fas fa-times-circle"></i> Từ chối đã chọn</button>
                    </div>
                </div>
            </div>

            <!-- ── PENDING PAGE ── -->
            <div class="page hidden" id="page-pending">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Tin chờ duyệt</h1>
                        <p class="page-subtitle">48 tin đang chờ xét duyệt</p>
                    </div>
                    <div class="page-header-actions">
                        <button class="btn-primary" onclick="approveSelected()"><i class="fas fa-check-double"></i> Duyệt đã chọn</button>
                    </div>
                </div>
                <div class="filter-bar">
                    <div class="filter-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Tìm kiếm tin...">
                    </div>
                    <select class="filter-select">
                        <option>Tất cả khu vực</option>
                        <option>TP.HCM</option>
                        <option>Hà Nội</option>
                        <option>Đà Nẵng</option>
                    </select>
                    <select class="filter-select">
                        <option>Mức giá</option>
                        <option>Dưới 3 triệu</option>
                        <option>3–5 triệu</option>
                        <option>Trên 5 triệu</option>
                    </select>
                </div>
                <div class="section-card">
                    <div class="approval-list" id="pendingList">
                        <?php for($i=0;$i<6;$i++): ?>
                        <div class="approval-row">
                            <input type="checkbox" class="approval-cb">
                            <img src="https://images.unsplash.com/photo-150267226026<?=$i?>-1c1ef2d93688?w=120&h=120&fit=crop" class="approval-img" alt="" onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=120&h=120&fit=crop'">
                            <div class="approval-info">
                                <div class="approval-title">Phòng trọ <?= $i+1 ?> – Khu vực trung tâm</div>
                                <div class="approval-meta-row">
                                    <span><i class="fas fa-map-marker-alt"></i> Quận <?= $i+1 ?>, TP.HCM</span>
                                    <span><i class="fas fa-calendar"></i> <?= 19-$i ?>/04/2026</span>
                                    <span><i class="fas fa-user"></i> Nguyễn Văn A</span>
                                </div>
                                <div class="approval-price"><?= number_format((3+$i*0.5)*1000000,0,'.',',') ?> đ/tháng</div>
                            </div>
                            <div class="approval-actions-col">
                                <span class="status-badge pending">Chờ duyệt</span>
                                <div class="approval-btns">
                                    <button class="btn-approve-sm" onclick="approveListing(this)"><i class="fas fa-check"></i> Duyệt</button>
                                    <button class="btn-reject-sm" onclick="rejectListing(this)"><i class="fas fa-times"></i> Từ chối</button>
                                </div>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- ── APPROVED PAGE ── -->
            <div class="page hidden" id="page-approved">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Tin đã duyệt</h1>
                        <p class="page-subtitle">1,236 tin đang hiển thị</p>
                    </div>
                </div>
                <div class="section-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox"></th>
                                <th>Tin đăng</th>
                                <th>Khu vực</th>
                                <th>Giá</th>
                                <th>Người đăng</th>
                                <th>Ngày duyệt</th>
                                <th>Hết hạn</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($i=0;$i<8;$i++): ?>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>
                                    <div class="table-listing-cell">
                                        <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=50&h=50&fit=crop" alt="">
                                        <span>Phòng trọ đã duyệt #<?= $i+1 ?></span>
                                    </div>
                                </td>
                                <td>Quận <?= $i+1 ?>, TP.HCM</td>
                                <td><?= number_format((3+$i*0.3)*1000000,0,'.',',') ?> đ</td>
                                <td>Nguyễn Văn <?= chr(65+$i) ?></td>
                                <td><?= 10+$i ?>/04/2026</td>
                                <td><?= 10+$i ?>/05/2026</td>
                                <td>
                                    <div class="table-actions">
                                        <button class="tbl-btn view" title="Xem"><i class="fas fa-eye"></i></button>
                                        <button class="tbl-btn edit" title="Sửa"><i class="fas fa-edit"></i></button>
                                        <button class="tbl-btn del" title="Xoá" onclick="if(confirm('Xoá tin này?')) this.closest('tr').remove()"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                    <div class="table-pagination">
                        <span class="pagination-info">Hiển thị 1–8 trong 1,236 tin</span>
                        <div class="pagination-btns">
                            <button class="pag-btn" disabled><i class="fas fa-chevron-left"></i></button>
                            <button class="pag-btn active">1</button>
                            <button class="pag-btn">2</button>
                            <button class="pag-btn">3</button>
                            <span>...</span>
                            <button class="pag-btn">155</button>
                            <button class="pag-btn"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── ALL LISTINGS PAGE ── -->
            <div class="page hidden" id="page-listings">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Tất cả tin đăng</h1>
                        <p class="page-subtitle">Quản lý toàn bộ 1,284 tin</p>
                    </div>
                </div>
                <div class="filter-bar">
                    <div class="filter-search"><i class="fas fa-search"></i><input type="text" placeholder="Tìm kiếm..."></div>
                    <select class="filter-select">
                        <option>Tất cả trạng thái</option>
                        <option>Chờ duyệt</option>
                        <option>Đã duyệt</option>
                        <option>Từ chối</option>
                        <option>Hết hạn</option>
                    </select>
                </div>
                <div class="section-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox"></th>
                                <th>Tin đăng</th>
                                <th>Khu vực</th>
                                <th>Giá</th>
                                <th>Người đăng</th>
                                <th>Trạng thái</th>
                                <th>Ngày đăng</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $statuses = [['Đã duyệt','approved'],['Chờ duyệt','pending'],['Đã duyệt','approved'],['Từ chối','rejected'],['Đã duyệt','approved'],['Chờ duyệt','pending'],['Hết hạn','expired'],['Đã duyệt','approved']];
                            foreach($statuses as $i=>[$sl,$sc]):
                            ?>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>
                                    <div class="table-listing-cell">
                                        <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=50&h=50&fit=crop" alt="">
                                        <span>Tin đăng #<?= $i+1 ?></span>
                                    </div>
                                </td>
                                <td>Quận <?= $i+1 ?>, TP.HCM</td>
                                <td><?= number_format((2.5+$i*0.4)*1000000,0,'.',',') ?> đ</td>
                                <td>User <?= $i+1 ?></td>
                                <td><span class="status-badge <?= $sc ?>"><?= $sl ?></span></td>
                                <td><?= 12+$i ?>/04/2026</td>
                                <td>
                                    <div class="table-actions">
                                        <button class="tbl-btn view"><i class="fas fa-eye"></i></button>
                                        <button class="tbl-btn edit"><i class="fas fa-edit"></i></button>
                                        <button class="tbl-btn del" onclick="if(confirm('Xoá?')) this.closest('tr').remove()"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── REJECTED PAGE ── -->
            <div class="page hidden" id="page-rejected">
                <div class="page-header">
                    <div><h1 class="page-title">Tin bị từ chối</h1><p class="page-subtitle">12 tin đã bị từ chối</p></div>
                </div>
                <div class="section-card">
                    <div class="empty-state">
                        <i class="fas fa-ban"></i>
                        <p>Hiển thị các tin bị từ chối ở đây</p>
                        <span>Tính năng đang được phát triển</span>
                    </div>
                </div>
            </div>

            <!-- ── USERS PAGE ── -->
            <div class="page hidden" id="page-users">
                <div class="page-header">
                    <div><h1 class="page-title">Người dùng</h1><p class="page-subtitle">856 tài khoản đã đăng ký</p></div>
                    <div class="page-header-actions">
                        <button class="btn-secondary"><i class="fas fa-download"></i> Xuất Excel</button>
                    </div>
                </div>
                <div class="filter-bar">
                    <div class="filter-search"><i class="fas fa-search"></i><input type="text" placeholder="Tìm tên, email..."></div>
                    <select class="filter-select">
                        <option>Tất cả vai trò</option>
                        <option>ADMIN</option>
                        <option>LANDLORD</option>
                        <option>RENTER</option>
                    </select>
                    <select class="filter-select">
                        <option>Tất cả trạng thái</option>
                        <option>Hoạt động</option>
                        <option>Bị khoá</option>
                    </select>
                </div>
                <div class="section-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox"></th>
                                <th>Người dùng</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Đăng nhập lần cuối</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $roles = [['ADMIN','admin'],['LANDLORD','landlord'],['RENTER','renter'],['RENTER','renter'],['LANDLORD','landlord'],['RENTER','renter'],['RENTER','renter'],['LANDLORD','landlord']];
                            foreach($roles as $i=>[$rl,$rc]):
                            ?>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-cell-avatar"><?= chr(65+$i) ?></div>
                                        <div>
                                            <div class="user-cell-name">Người dùng <?= $i+1 ?></div>
                                            <div class="user-cell-id">#USER-<?= str_pad($i+1,4,'0',STR_PAD_LEFT) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>user<?= $i+1 ?>@example.com</td>
                                <td><span class="role-badge <?= $rc ?>"><?= $rl ?></span></td>
                                <td><?= 15+$i ?>/04/2026</td>
                                <td><span class="status-badge approved">Hoạt động</span></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="tbl-btn view"><i class="fas fa-eye"></i></button>
                                        <button class="tbl-btn edit"><i class="fas fa-edit"></i></button>
                                        <button class="tbl-btn del" onclick="if(confirm('Khoá user này?')) alert('Đã khoá')"><i class="fas fa-user-slash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── VERIFY USERS PAGE ── -->
            <div class="page hidden" id="page-verify-users">
                <div class="page-header">
                    <div><h1 class="page-title">Chờ xác thực danh tính</h1><p class="page-subtitle">124 người dùng đang chờ xác thực</p></div>
                </div>
                <div class="section-card">
                    <div class="empty-state">
                        <i class="fas fa-id-card"></i>
                        <p>Danh sách người dùng chờ xác thực CCCD/CMND</p>
                        <span>Tính năng đang được phát triển</span>
                    </div>
                </div>
            </div>

            <!-- ── BANNED PAGE ── -->
            <div class="page hidden" id="page-banned">
                <div class="page-header">
                    <div><h1 class="page-title">Tài khoản bị khoá</h1><p class="page-subtitle">Quản lý tài khoản vi phạm</p></div>
                </div>
                <div class="section-card">
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <p>Không có tài khoản nào đang bị khoá</p>
                        <span>Tất cả người dùng đang hoạt động bình thường</span>
                    </div>
                </div>
            </div>

            <!-- ── SETTINGS PAGE ── -->
            <div class="page hidden" id="page-settings">
                <div class="page-header">
                    <div><h1 class="page-title">Cài đặt hệ thống</h1><p class="page-subtitle">Quản lý cấu hình RoomFinder</p></div>
                </div>
                <div class="settings-grid">
                    <div class="section-card">
                        <div class="section-card-hdr"><h3><i class="fas fa-globe"></i> Thông tin website</h3></div>
                        <div class="settings-form">
                            <div class="form-group">
                                <label>Tên website</label>
                                <input type="text" value="RoomFinder.vn" class="form-input">
                            </div>
                            <div class="form-group">
                                <label>Email liên hệ</label>
                                <input type="email" value="admin@roomfinder.vn" class="form-input">
                            </div>
                            <div class="form-group">
                                <label>Mô tả</label>
                                <textarea class="form-input" rows="3">Trang tìm phòng trọ hàng đầu Việt Nam</textarea>
                            </div>
                            <button class="btn-primary">Lưu thay đổi</button>
                        </div>
                    </div>
                    <div class="section-card">
                        <div class="section-card-hdr"><h3><i class="fas fa-shield-alt"></i> Bảo mật</h3></div>
                        <div class="settings-form">
                            <div class="form-group">
                                <label>Thời hạn session (giây)</label>
                                <input type="number" value="3600" class="form-input">
                            </div>
                            <div class="form-group">
                                <label>Yêu cầu xác thực 2 bước</label>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <button class="btn-primary">Cập nhật</button>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /page-content -->
    </div><!-- /main-wrap -->
</div><!-- /admin-shell -->

<script src="/assets/js/admin-dashboard.js"></script>
</body>
</html>