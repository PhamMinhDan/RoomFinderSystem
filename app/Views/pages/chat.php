<?php
$title = "Nhắn tin";
$css = ['chat.css'];
$js  = ['chat.js'];
$showFooter = false;
$activeNav = 'chat';

ob_start();
?>

    <!-- Chat Page -->
    <div class="chat-page">
        <!-- Main Wrapper -->
        <div class="chat-wrapper">
            <!-- Left Sidebar - Conversation List -->
            <aside class="chat-sidebar" id="chatSidebar">
                <div class="chat-sidebar-header">
                    <div class="chat-sidebar-title">Tin nhắn</div>
                    <div class="chat-search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Tìm cuộc hội thoại..." id="searchInput" onkeyup="filterConversations()">
                    </div>
                </div>

                <div class="chat-list" id="chatList">
                    <!-- Conversation 1 - Active -->
                    <div class="chat-item active" onclick="selectConversation(this, 1)">
                        <div class="chat-item-avatar">N</div>
                        <div class="chat-item-content">
                            <div class="chat-item-name">Nguyễn Văn A</div>
                            <div class="chat-item-preview">OK, mình sẽ đến lúc 14h</div>
                        </div>
                        <div class="chat-item-meta">
                            <div class="chat-item-time">14:30</div>
                        </div>
                    </div>

                    <!-- Conversation 2 - Unread -->
                    <div class="chat-item unread" onclick="selectConversation(this, 2)">
                        <div class="chat-item-avatar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">T</div>
                        <div class="chat-item-content">
                            <div class="chat-item-name">Trần Thị B</div>
                            <div class="chat-item-preview">Phòng này có điều hòa không?</div>
                        </div>
                        <div class="chat-item-meta">
                            <div class="chat-item-time">10:45</div>
                            <div class="chat-item-badge">2</div>
                        </div>
                    </div>

                    <!-- Conversation 3 -->
                    <div class="chat-item" onclick="selectConversation(this, 3)">
                        <div class="chat-item-avatar" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">L</div>
                        <div class="chat-item-content">
                            <div class="chat-item-name">Lê Văn C</div>
                            <div class="chat-item-preview">Giá thuê có thể thương lượng không?</div>
                        </div>
                        <div class="chat-item-meta">
                            <div class="chat-item-time">09:20</div>
                        </div>
                    </div>

                    <!-- Conversation 4 -->
                    <div class="chat-item" onclick="selectConversation(this, 4)">
                        <div class="chat-item-avatar" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">P</div>
                        <div class="chat-item-content">
                            <div class="chat-item-name">Phạm Thị D</div>
                            <div class="chat-item-preview">Cảm ơn, mình rất thích phòng này!</div>
                        </div>
                        <div class="chat-item-meta">
                            <div class="chat-item-time">Hôm qua</div>
                        </div>
                    </div>

                    <!-- Conversation 5 -->
                    <div class="chat-item" onclick="selectConversation(this, 5)">
                        <div class="chat-item-avatar" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">H</div>
                        <div class="chat-item-content">
                            <div class="chat-item-name">Hoàng Văn E</div>
                            <div class="chat-item-preview">Mình có thể xem phòng được không?</div>
                        </div>
                        <div class="chat-item-meta">
                            <div class="chat-item-time">2 ngày trước</div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Right - Chat Area -->
            <div class="chat-container">
                <!-- Chat Header -->
                <div class="chat-header">
                    <div class="chat-header-info">
                        <div class="chat-header-avatar">N</div>
                        <div class="chat-header-content">
                            <h2>Nguyễn Văn A</h2>
                            <p>Khách tìm trọ | Online</p>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="chat-messages" id="chatMessages">
                    <div class="chat-message-group other">
                        <div class="chat-message">Xin chào, tôi quan tâm đến phòng của bạn</div>
                        <div class="chat-message-time">10:15</div>
                    </div>

                    <div class="chat-message-group other">
                        <div class="chat-message">Phòng này có wifi không?</div>
                        <div class="chat-message-time">10:16</div>
                    </div>

                    <div class="chat-message-group own">
                        <div class="chat-message">Có, wifi miễn phí cho tất cả khách</div>
                        <div class="chat-message-time">10:18</div>
                    </div>

                    <div class="chat-message-group own">
                        <div class="chat-message">Phòng được trang bị đầy đủ nội thất</div>
                        <div class="chat-message-time">10:19</div>
                    </div>

                    <div class="chat-message-group other">
                        <div class="chat-message">Giá thuê bao nhiêu một tháng?</div>
                        <div class="chat-message-time">10:25</div>
                    </div>

                    <div class="chat-message-group own">
                        <div class="chat-message">3.5 triệu đ/tháng, có thể thương lượng với hợp đồng dài hạn</div>
                        <div class="chat-message-time">10:26</div>
                    </div>

                    <div class="chat-message-group other">
                        <div class="chat-message">Được, tôi sẽ đến xem phòng sáng mai lúc 14h được không?</div>
                        <div class="chat-message-time">10:28</div>
                    </div>

                    <div class="chat-message-group own">
                        <div class="chat-message">OK, mình sẽ đợi bạn. Địa chỉ là 123 Nguyễn Huệ, Q.1</div>
                        <div class="chat-message-time">10:30</div>
                    </div>

                    <div class="chat-message-group other">
                        <div class="chat-message">OK, mình sẽ đến lúc 14h</div>
                        <div class="chat-message-time">14:30</div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="chat-input-wrapper">
                    <div class="chat-input-box">
                        <button class="chat-attach-btn" title="Đính kèm file">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <input type="text" class="chat-input" id="messageInput" placeholder="Nhập tin nhắn..." onkeydown="handleMessageKeydown(event)">
                    </div>
                    <button class="chat-send-btn" onclick="sendMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
