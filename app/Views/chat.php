<?php
/**
 * Chat Page - Between Landlord & Tenant
 * Giao diện chat giữa chủ trọ và khách tìm trọ
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin nhắn - Smart Room</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/components.css">
    <style>
        :root {
            --primary: #2b3cf7;
            --primary-dark: #1a2bde;
            --primary-light: #eef0ff;
            --accent: #ff5a3d;
            --green: #10b981;
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: var(--gray-50);
            color: var(--gray-800);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        /* ===== MAIN LAYOUT ===== */
        .chat-page {
            display: grid;
            grid-template-columns: 1fr;
            grid-template-rows: auto 1fr auto;
            flex: 1;
            overflow: hidden;
            background: white;
        }

        .chat-wrapper {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 0;
            flex: 1;
            overflow: hidden;
        }

        /* ===== SIDEBAR (CONVERSATION LIST) ===== */
        .chat-sidebar {
            background: white;
            border-right: 1px solid var(--gray-200);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-sidebar-header {
            padding: 16px;
            border-bottom: 1px solid var(--gray-200);
        }

        .chat-sidebar-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 12px;
        }

        .chat-search-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--gray-100);
            border-radius: 20px;
            padding: 8px 12px;
        }

        .chat-search-box i {
            color: var(--gray-400);
            font-size: 14px;
        }

        .chat-search-box input {
            background: none;
            border: none;
            outline: none;
            flex: 1;
            font-size: 13px;
            font-family: inherit;
            color: var(--gray-700);
        }

        .chat-search-box input::placeholder {
            color: var(--gray-400);
        }

        /* Conversation List */
        .chat-list {
            flex: 1;
            overflow-y: auto;
            padding: 8px 0;
        }

        .chat-item {
            padding: 12px 8px;
            margin: 0 8px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            display: grid;
            grid-template-columns: 48px 1fr auto;
            gap: 12px;
            align-items: flex-start;
        }

        .chat-item:hover {
            background: var(--gray-50);
        }

        .chat-item.active {
            background: var(--primary-light);
        }

        .chat-item-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
        }

        .chat-item-content {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .chat-item-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-900);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-item-preview {
            font-size: 12px;
            color: var(--gray-600);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-item.unread .chat-item-preview {
            color: var(--gray-900);
            font-weight: 600;
        }

        .chat-item-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
            align-items: flex-end;
            flex-shrink: 0;
        }

        .chat-item-time {
            font-size: 11px;
            color: var(--gray-500);
            white-space: nowrap;
        }

        .chat-item-badge {
            background: var(--primary);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
        }

        /* ===== MAIN CHAT AREA ===== */
        .chat-container {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: white;
        }

        /* Chat Header */
        .chat-header {
            padding: 16px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .chat-header-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chat-header-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }

        .chat-header-content h2 {
            font-size: 15px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .chat-header-content p {
            font-size: 11px;
            color: var(--gray-600);
            margin-top: 2px;
        }

        .chat-header-actions {
            display: flex;
            gap: 8px;
        }

        .chat-header-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: var(--gray-100);
            color: var(--gray-600);
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s;
        }

        .chat-header-btn:hover {
            background: var(--gray-200);
            color: var(--gray-900);
        }

        /* Messages Area */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 0;
        }

        .chat-message-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-bottom: 8px;
        }

        .chat-message-group.other {
            align-items: flex-start;
        }

        .chat-message-group.own {
            align-items: flex-end;
        }

        .chat-message {
            max-width: 70%;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.4;
            word-wrap: break-word;
        }

        .chat-message-group.other .chat-message {
            background: var(--gray-100);
            color: var(--gray-900);
        }

        .chat-message-group.own .chat-message {
            background: var(--primary);
            color: white;
        }

        .chat-message-time {
            font-size: 11px;
            color: var(--gray-500);
            margin-top: 2px;
        }

        .chat-message-group.own .chat-message-time {
            color: var(--gray-500);
        }

        /* Typing Indicator */
        .chat-typing {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 10px 14px;
            color: var(--gray-600);
            font-size: 13px;
        }

        .chat-typing-dots {
            display: flex;
            gap: 3px;
        }

        .chat-typing-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--gray-400);
            animation: typing 1.4s infinite;
        }

        .chat-typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .chat-typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {
            0%, 60%, 100% {
                opacity: 0.5;
                transform: translateY(0);
            }
            30% {
                opacity: 1;
                transform: translateY(-6px);
            }
        }

        /* Input Area */
        .chat-input-wrapper {
            width: 100%;
            padding: 16px 16px 20px 16px;
            border-top: 1px solid var(--gray-200);
            display: flex;
            gap: 8px;
            flex-shrink: 0;
            background: white;
            margin-top: auto;
        }

        .chat-input-box {
            flex: 1;
            min-width: 0;
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        .chat-input {
            flex: 1;
            min-width: 0;
            padding: 12px 16px;
            border: 1px solid var(--gray-200);
            border-radius: 20px;
            font-size: 13px;
            font-family: inherit;
            color: var(--gray-800);
            outline: none;
            transition: border-color 0.2s;
            resize: none;
            min-height: 44px;
            max-height: 100px;
        }

        .chat-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .chat-input::placeholder {
            color: var(--gray-400);
        }

        .chat-attach-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: var(--gray-100);
            color: var(--gray-600);
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-attach-btn:hover {
            background: var(--gray-200);
            color: var(--primary);
        }

        .chat-send-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: var(--primary);
            color: white;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-send-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.05);
        }

        .chat-send-btn:active {
            transform: scale(0.95);
        }

        /* Empty State */
        .chat-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--gray-400);
            gap: 12px;
        }

        .chat-empty i {
            font-size: 64px;
            opacity: 0.5;
        }

        .chat-empty p {
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .chat-wrapper {
                grid-template-columns: 280px 1fr;
            }

            .chat-message {
                max-width: 80%;
            }
        }

        @media (max-width: 768px) {
            .chat-wrapper {
                grid-template-columns: 1fr;
            }

            .chat-sidebar {
                position: absolute;
                left: 0;
                top: 0;
                bottom: 0;
                width: 300px;
                z-index: 999;
                box-shadow: 2px 0 8px rgba(0,0,0,0.1);
                display: none;
            }

            .chat-sidebar.open {
                display: flex;
            }

            .chat-message {
                max-width: 85%;
            }

            .chat-container {
                grid-column: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include __DIR__ . '/../../public/assets/components/header.php'; ?>

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

    <script>
        let currentConversationId = 1;

        function selectConversation(element, conversationId) {
            // Remove active class from all items
            document.querySelectorAll('.chat-item').forEach(item => {
                item.classList.remove('active');
                item.classList.remove('unread');
            });

            // Add active class to clicked item
            element.classList.add('active');
            currentConversationId = conversationId;

            // Update chat header based on conversation
            const conversations = {
                1: { name: 'Nguyễn Văn A', role: 'Khách tìm trọ', avatar: 'N', status: 'Online' },
                2: { name: 'Trần Thị B', role: 'Khách tìm trọ', avatar: 'T', status: 'Online' },
                3: { name: 'Lê Văn C', role: 'Khách tìm trọ', avatar: 'L', status: 'Offline' },
                4: { name: 'Phạm Thị D', role: 'Khách tìm trọ', avatar: 'P', status: 'Offline' },
                5: { name: 'Hoàng Văn E', role: 'Khách tìm trọ', avatar: 'H', status: 'Offline' }
            };

            const conv = conversations[conversationId];
            document.querySelector('.chat-header-avatar').textContent = conv.avatar;
            document.querySelector('.chat-header-content h2').textContent = conv.name;
            document.querySelector('.chat-header-content p').textContent = conv.role + ' | ' + conv.status;

            // Load messages for this conversation
            loadConversationMessages(conversationId);
        }

        function loadConversationMessages(conversationId) {
            // Sample messages for each conversation
            const messages = {
                1: [
                    { type: 'other', text: 'Xin chào, tôi quan tâm đến phòng của bạn', time: '10:15' },
                    { type: 'other', text: 'Phòng này có wifi không?', time: '10:16' },
                    { type: 'own', text: 'Có, wifi miễn phí cho tất cả khách', time: '10:18' },
                    { type: 'own', text: 'Phòng được trang bị đầy đủ nội thất', time: '10:19' },
                    { type: 'other', text: 'Giá thuê bao nhiêu một tháng?', time: '10:25' },
                    { type: 'own', text: '3.5 triệu đ/tháng, có thể thương lượng với hợp đồng dài hạn', time: '10:26' },
                    { type: 'other', text: 'Được, tôi sẽ đến xem phòng sáng mai lúc 14h được không?', time: '10:28' },
                    { type: 'own', text: 'OK, mình sẽ đợi bạn. Địa chỉ là 123 Nguyễn Huệ, Q.1', time: '10:30' },
                    { type: 'other', text: 'OK, mình sẽ đến lúc 14h', time: '14:30' }
                ],
                2: [
                    { type: 'other', text: 'Phòng này có điều hòa không?', time: '10:45' },
                    { type: 'own', text: 'Có, phòng được trang bị điều hòa 1 chiều hiệu suất cao', time: '11:00' },
                    { type: 'other', text: 'Giá điện bao nhiêu?', time: '11:05' }
                ],
                3: [
                    { type: 'other', text: 'Giá thuê có thể thương lượng không?', time: '09:20' },
                    { type: 'own', text: 'Có thể thảo luận, nhất là với hợp đồng dài hạn', time: '09:30' }
                ],
                4: [
                    { type: 'other', text: 'Cảm ơn, mình rất thích phòng này!', time: 'Hôm qua' }
                ],
                5: [
                    { type: 'other', text: 'Mình có thể xem phòng được không?', time: '2 ngày trước' }
                ]
            };

            const messagesContainer = document.getElementById('chatMessages');
            messagesContainer.innerHTML = '';

            messages[conversationId]?.forEach(msg => {
                const group = document.createElement('div');
                group.className = `chat-message-group ${msg.type}`;

                const messageEl = document.createElement('div');
                messageEl.className = 'chat-message';
                messageEl.textContent = msg.text;

                const timeEl = document.createElement('div');
                timeEl.className = 'chat-message-time';
                timeEl.textContent = msg.time;

                group.appendChild(messageEl);
                group.appendChild(timeEl);
                messagesContainer.appendChild(group);
            });

            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const text = input.value.trim();

            if (!text) return;

            const messagesContainer = document.getElementById('chatMessages');
            const group = document.createElement('div');
            group.className = 'chat-message-group own';

            const messageEl = document.createElement('div');
            messageEl.className = 'chat-message';
            messageEl.textContent = text;

            const timeEl = document.createElement('div');
            timeEl.className = 'chat-message-time';
            const now = new Date();
            timeEl.textContent = now.getHours() + ':' + String(now.getMinutes()).padStart(2, '0');

            group.appendChild(messageEl);
            group.appendChild(timeEl);
            messagesContainer.appendChild(group);

            // Clear input
            input.value = '';

            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function handleMessageKeydown(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        }

        function filterConversations() {
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const items = document.querySelectorAll('.chat-item');

            items.forEach(item => {
                const name = item.querySelector('.chat-item-name').textContent.toLowerCase();
                const preview = item.querySelector('.chat-item-preview').textContent.toLowerCase();

                if (name.includes(searchText) || preview.includes(searchText)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Initialize
        selectConversation(document.querySelector('.chat-item.active'), 1);
    </script>
</body>
</html>
