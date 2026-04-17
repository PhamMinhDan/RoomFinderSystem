<?php
$title = "Dashboard chủ nhà";
$css = ['landlord-dashboard.css'];
$js  = ['landlord-dashboard.js'];
$showFooter = false;

ob_start();
?>

    <div class="dashboard-container">
        <!-- Status Tabs -->
        <div class="status-tabs">
            <button class="status-tab active" onclick="filterByStatus('all', this)">
                <i class="fas fa-check-circle"></i>
                Đang hiển thị
                <span class="status-count">6</span>
            </button>
            <button class="status-tab" onclick="filterByStatus('expired', this)">
                <i class="fas fa-times-circle"></i>
                Hết hạn
                <span class="status-count">0</span>
            </button>
            <button class="status-tab" onclick="filterByStatus('rejected', this)">
                <i class="fas fa-ban"></i>
                Bị từ chối
                <span class="status-count">0</span>
            </button>
            <button class="status-tab" onclick="filterByStatus('messages', this)">
                <i class="fas fa-envelope"></i>
                Tin nhắn
                <span class="status-count">0</span>
            </button>
            <button class="status-tab" onclick="filterByStatus('pending', this)">
                <i class="fas fa-clock"></i>
                Chờ duyệt
                <span class="status-count">0</span>
            </button>
        </div>

        <!-- Main Content -->
        <div class="dashboard-content">
            <!-- Listings -->
            <div class="listings-section">
                <!-- Listing 1 -->
                <div class="listing-card" data-status="all">
                    <input type="checkbox" class="listing-checkbox">
                    <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=100&h=100&fit=crop" alt="Room" class="listing-image">
                    <div class="listing-main">
                        <div class="listing-title">Gác lửng cho thuê</div>
                        <div class="listing-location">Quận 1, TP.HCM</div>
                        <div class="listing-price">4,000,000 đ/tháng</div>
                        <div class="listing-meta">
                            <div class="listing-meta-item">
                                <i class="fas fa-calendar"></i>
                                Đăng: 14/03/2026
                            </div>
                            <div class="listing-meta-item">
                                <i class="fas fa-timer"></i>
                                Còn 15 ngày
                            </div>
                        </div>
                    </div>
                    <div class="listing-actions">
                        <div class="listing-badge">Đã duyệt</div>
                        <div class="listing-buttons">
                            <button class="btn-small btn-renew" onclick="renewListing()">Gia hạn tin</button>
                            <button class="btn-small btn-edit" onclick="editListing()">Sửa tin</button>
                            <button class="btn-small btn-more" title="Thêm tùy chọn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Listing 2 -->
                <div class="listing-card" data-status="all">
                    <input type="checkbox" class="listing-checkbox">
                    <img src="https://images.unsplash.com/photo-1469022563149-aa64dbd37daa?w=100&h=100&fit=crop" alt="Room" class="listing-image">
                    <div class="listing-main">
                        <div class="listing-title">Phòng 30m² mặt tiền Nguyễn Huệ</div>
                        <div class="listing-location">Quận 1, TP.HCM</div>
                        <div class="listing-price">3,500,000 đ/tháng</div>
                        <div class="listing-meta">
                            <div class="listing-meta-item">
                                <i class="fas fa-calendar"></i>
                                Đăng: 12/03/2026
                            </div>
                            <div class="listing-meta-item">
                                <i class="fas fa-timer"></i>
                                Còn 17 ngày
                            </div>
                        </div>
                    </div>
                    <div class="listing-actions">
                        <div class="listing-badge">Đã duyệt</div>
                        <div class="listing-buttons">
                            <button class="btn-small btn-renew" onclick="renewListing()">Gia hạn tin</button>
                            <button class="btn-small btn-edit" onclick="editListing()">Sửa tin</button>
                            <button class="btn-small btn-more" title="Thêm tùy chọn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Listing 3 -->
                <div class="listing-card" data-status="all">
                    <input type="checkbox" class="listing-checkbox">
                    <img src="https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?w=100&h=100&fit=crop" alt="Room" class="listing-image">
                    <div class="listing-main">
                        <div class="listing-title">Căn hộ studio 25m² view hồ</div>
                        <div class="listing-location">Tây Hồ, Hà Nội</div>
                        <div class="listing-price">2,800,000 đ/tháng</div>
                        <div class="listing-meta">
                            <div class="listing-meta-item">
                                <i class="fas fa-calendar"></i>
                                Đăng: 10/03/2026
                            </div>
                            <div class="listing-meta-item">
                                <i class="fas fa-timer"></i>
                                Còn 20 ngày
                            </div>
                        </div>
                    </div>
                    <div class="listing-actions">
                        <div class="listing-badge">Đã duyệt</div>
                        <div class="listing-buttons">
                            <button class="btn-small btn-renew" onclick="renewListing()">Gia hạn tin</button>
                            <button class="btn-small btn-edit" onclick="editListing()">Sửa tin</button>
                            <button class="btn-small btn-more" title="Thêm tùy chọn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="dash-sidebar">
                <!-- Calendar Widget -->
                <div class="dash-widget">
                    <h3><i class="fas fa-calendar"></i> Tháng 4 2026</h3>
                    <div class="calendar">
                        <div class="calendar-header">
                            <h4>April</h4>
                            <div class="calendar-nav">
                                <button>&lt;</button>
                                <button>&gt;</button>
                            </div>
                        </div>
                        <div class="calendar-grid">
                            <div class="calendar-day header">T2</div>
                            <div class="calendar-day header">T3</div>
                            <div class="calendar-day header">T4</div>
                            <div class="calendar-day header">T5</div>
                            <div class="calendar-day header">T6</div>
                            <div class="calendar-day header">T7</div>
                            <div class="calendar-day header">CN</div>
                            <div class="calendar-day">31</div>
                            <div class="calendar-day">1</div>
                            <div class="calendar-day">2</div>
                            <div class="calendar-day">3</div>
                            <div class="calendar-day">4</div>
                            <div class="calendar-day">5</div>
                            <div class="calendar-day">6</div>
                            <div class="calendar-day">7</div>
                            <div class="calendar-day">8</div>
                            <div class="calendar-day">9</div>
                            <div class="calendar-day">10</div>
                            <div class="calendar-day">11</div>
                            <div class="calendar-day">12</div>
                            <div class="calendar-day">13</div>
                            <div class="calendar-day">14</div>
                            <div class="calendar-day">15</div>
                            <div class="calendar-day">16</div>
                            <div class="calendar-day today">17</div>
                            <div class="calendar-day">18</div>
                            <div class="calendar-day">19</div>
                            <div class="calendar-day">20</div>
                            <div class="calendar-day">21</div>
                            <div class="calendar-day">22</div>
                            <div class="calendar-day">23</div>
                            <div class="calendar-day">24</div>
                            <div class="calendar-day">25</div>
                            <div class="calendar-day">26</div>
                            <div class="calendar-day">27</div>
                            <div class="calendar-day">28</div>
                            <div class="calendar-day">29</div>
                            <div class="calendar-day">30</div>
                        </div>
                    </div>
                </div>

                <!-- Today Schedule Widget -->
                <div class="dash-widget">
                    <h3><i class="fas fa-hourglass-start"></i> Hôm nay</h3>
                    <div>
                        <div class="schedule-time">Hôm nay (16:35)</div>
                        <div class="schedule-item">
                            <div class="schedule-room">Khách hẹn xem: Lê Tuấn Kiệt</div>
                            <div class="schedule-room">Phòng: Gác lửng cho thuê</div>
                        </div>
                        <div class="schedule-item">
                            <div class="schedule-room">Khách hẹn xem: Nguyễn Văn A</div>
                            <div class="schedule-room">Phòng: Căn hộ studio 25m²</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';