<?php

use Core\SessionManager;

SessionManager::start();
$currentUser = SessionManager::getUser();

// Nếu chưa đăng nhập → redirect
if (!$currentUser) {
    header('Location: /?login=1');
    exit;
}

// Lấy data từ GLOBALS (nếu đi qua ChatController) hoặc tự load
if (isset($GLOBALS['chat_user'])) {
    $me            = $GLOBALS['chat_user'];
    $conversations = $GLOBALS['chat_conversations'] ?? [];
    $activeConv    = $GLOBALS['chat_active_conv']   ?? null;
    $activeId      = (int)($GLOBALS['chat_active_id'] ?? 0);
} else {
    $me            = $currentUser;
    $activeId      = 0;
    $activeConv    = null;
    try {
        $chatService   = new \Services\ChatService();
        $conversations = $chatService->getConversations($me['user_id']);

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (preg_match('#^/chat/(\d+)$#', $uri, $m)) {
            $activeId   = (int)$m[1];
            $activeConv = $chatService->getConversationDetail($activeId, $me['user_id']);
        }
    } catch (\Throwable $e) {
        $conversations = [];
    }
}

// Pending room (khi đến từ room-detail, chưa có conversation)
$pendingRoomId    = isset($_GET['room_id'])    ? (int) $_GET['room_id'] : ($GLOBALS['chat_pending_room_id']    ?? null);
$pendingLandlordId= $_GET['landlord_id']       ?? ($GLOBALS['chat_pending_landlord_id'] ?? null);
$pendingTitle     = $_GET['title']             ?? ($GLOBALS['chat_pending_title']       ?? null);
$pendingPrice     = isset($_GET['price'])      ? (int) $_GET['price']  : ($GLOBALS['chat_pending_price']      ?? null);
$pendingImage     = $_GET['image']             ?? ($GLOBALS['chat_pending_image']       ?? null);

// Nếu vào /chat?room_id=xxx nhưng conversation đã tồn tại → redirect thẳng
if ($pendingRoomId && !$activeId && !empty($pendingLandlordId)) {
    // Không cần redirect phía server — JS sẽ xử lý qua CHAT_PENDING_ROOM
    // (giữ nguyên để JS render pending conv item)
}

$title      = 'Nhắn tin – RoomFinder.vn';
$css        = ['chat.css'];
$js         = ['chat-init.js', 'chat.js'];
$showFooter = false;
$activeNav  = 'chat';

ob_start();
?>

<div class="chat-page" id="chatPage">

    <!-- ══════════════════════════════════════════════════════════
         SIDEBAR – danh sách cuộc hội thoại
         ══════════════════════════════════════════════════════════ -->
    <aside class="chat-sidebar" id="chatSidebar">

        <div class="chs-header">
            <div class="chs-title">
                <span class="chs-title-dot"></span>
                Tin nhắn
            </div>
            <div class="chs-search">
                <i class="fas fa-search"></i>
                <input type="text" id="convSearch"
                       placeholder="Tìm cuộc hội thoại..."
                       oninput="filterConvs(this.value)">
            </div>
        </div>

        <div class="chs-list" id="convList">

            <?php
            // ── Pending room: hiện conv item ảo ở đầu sidebar ──────────────
            if ($pendingRoomId && !$activeId):
                $pendingName  = htmlspecialchars($pendingTitle ?: 'Phòng trọ');
                $pendingPriceF = $pendingPrice ? number_format($pendingPrice, 0, ',', '.') . ' đ/tháng' : '';
            ?>
            <div class="conv-item active" id="pendingConvItem"
                 data-conv-id="pending"
                 data-pending="1">
                <div class="conv-avatar">
                    <?php if ($pendingImage): ?>
                    <img class="conv-avatar-img"
                         src="<?= htmlspecialchars($pendingImage) ?>"
                         referrerpolicy="no-referrer"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                         alt="room">
                    <div class="conv-avatar-img" style="display:none;background:#c7d2fe;align-items:center;justify-content:center;">
                        <i class="fas fa-home" style="color:#6366f1;font-size:14px;"></i>
                    </div>
                    <?php else: ?>
                    <div class="conv-avatar-img" style="background:#c7d2fe;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-home" style="color:#6366f1;font-size:14px;"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="conv-body">
                    <div class="conv-name"><?= $pendingName ?></div>
                    <div class="conv-preview" style="color:#6366f1;font-size:11.5px;">
                        <?= $pendingPriceF ?: 'Nhắn tin để bắt đầu' ?>
                    </div>
                </div>
                <div class="conv-meta">
                    <div class="conv-time" style="color:#6366f1;font-size:10px;">Mới</div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($conversations) && !$pendingRoomId): ?>
            <div class="chs-empty">
                <i class="fas fa-comment-slash"></i>
                Chưa có cuộc hội thoại nào.<br>
                Hãy nhắn tin từ trang tìm phòng!
            </div>
            <?php else: ?>
            <?php foreach ($conversations as $conv):
                $unread       = (int)($conv['unread'] ?? 0);
                $isActive     = $activeId && $activeId === (int)$conv['conversation_id'];
                $avatarLetter = mb_strtoupper(mb_substr($conv['other_name'] ?? 'U', 0, 1));
            ?>
            <div class="conv-item <?= $unread > 0 ? 'unread' : '' ?> <?= $isActive ? 'active' : '' ?>"
                 data-conv-id="<?= (int)$conv['conversation_id'] ?>"
                 onclick="selectConv(this, <?= (int)$conv['conversation_id'] ?>)">

                <div class="conv-avatar">
                    <?php if (!empty($conv['other_avatar'])): ?>
                    <img class="conv-avatar-img"
                         src="<?= htmlspecialchars($conv['other_avatar']) ?>"
                         referrerpolicy="no-referrer"
                         alt="<?= htmlspecialchars($conv['other_name'] ?? '') ?>">
                    <?php else: ?>
                    <div class="conv-avatar-img"><?= $avatarLetter ?></div>
                    <?php endif; ?>
                    <span class="conv-online-dot" style="display:none;"></span>
                </div>

                <div class="conv-body">
                    <div class="conv-name"><?= htmlspecialchars($conv['other_name'] ?? 'Người dùng') ?></div>
                    <div class="conv-preview"><?= htmlspecialchars($conv['last_preview'] ?? 'Nhấn để xem') ?></div>
                </div>

                <div class="conv-meta">
                    <div class="conv-time"><?= chatFormatTime($conv['last_message_at'] ?? null) ?></div>
                    <?php if ($unread > 0): ?>
                    <div class="conv-badge"><?= $unread > 99 ? '99+' : $unread ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </aside>

    <!-- ══════════════════════════════════════════════════════════
         MAIN – khu vực chat
         ══════════════════════════════════════════════════════════ -->
    <div class="chat-main" id="chatMain">

        <?php if ($activeConv): ?>
        <?php
            $snap         = $activeConv['room_snapshot'] ?? null;
            $avatarLetter = mb_strtoupper(mb_substr($activeConv['other_name'] ?? 'U', 0, 1));
        ?>

        <!-- Header -->
        <div class="chat-header" id="chatHeader">
            <div class="ch-info">
                <button class="ch-btn mob-back-btn" onclick="showSidebar()" title="Quay lại">
                    <i class="fas fa-arrow-left"></i>
                </button>

                <div class="ch-avatar">
                    <?php if (!empty($activeConv['other_avatar'])): ?>
                    <img class="ch-avatar-img"
                         src="<?= htmlspecialchars($activeConv['other_avatar']) ?>"
                         referrerpolicy="no-referrer" alt="">
                    <?php else: ?>
                    <div class="ch-avatar-img"><?= $avatarLetter ?></div>
                    <?php endif; ?>
                    <span class="ch-online-ring" style="display:none;" id="headerOnlineRing"></span>
                </div>

                <div>
                    <div class="ch-name"><?= htmlspecialchars($activeConv['other_name'] ?? 'Người dùng') ?></div>
                    <div class="ch-status" id="headerStatus">Đang hoạt động</div>
                </div>
            </div>

            <?php if ($snap): ?>
            <a class="ch-room-context"
               href="/room/<?= (int)($snap['room_id'] ?? 0) ?>"
               title="Xem phòng" target="_blank" rel="noopener">
                <?php if (!empty($snap['image'])): ?>
                <img class="ch-room-thumb"
                     src="<?= htmlspecialchars($snap['image']) ?>"
                     alt="room"
                     onerror="this.style.display='none'">
                <?php else: ?>
                <div class="ch-room-thumb" style="background:#c7d2fe;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-home" style="color:#6366f1;font-size:18px;"></i>
                </div>
                <?php endif; ?>
                <div class="ch-room-info">
                    <div class="ch-room-title"><?= htmlspecialchars($snap['title'] ?? '') ?></div>
                    <div class="ch-room-price">
                        <?= number_format((float)($snap['price'] ?? 0), 0, ',', '.') ?> đ/tháng
                    </div>
                </div>
                <i class="fas fa-external-link-alt" style="font-size:11px;color:#9ca3af;margin-left:4px;"></i>
            </a>
            <?php endif; ?>

            <div class="ch-actions">
                <button class="ch-btn" title="Tìm kiếm" onclick="chatToast('Chức năng đang phát triển')">
                    <i class="fas fa-search"></i>
                </button>
                <button class="ch-btn" title="Thông tin" onclick="chatToast('Chức năng đang phát triển')">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>
        </div>

        <!-- Messages -->
        <div class="chat-messages" id="chatMessages">
            <div class="load-more-wrap" id="loadMoreWrap" style="display:none;">
                <button class="load-more-btn" onclick="loadMoreMessages()">
                    <i class="fas fa-chevron-up" style="margin-right:6px;"></i> Tải thêm tin nhắn cũ
                </button>
            </div>

            <div id="msgSkeleton" style="display:flex;flex-direction:column;gap:16px;padding:10px 0;">
                <?php
                $skeletonWidths = [160, 220, 130, 195, 100];
                foreach ($skeletonWidths as $i => $w): ?>
                <div style="display:flex;gap:10px;align-items:flex-end;<?= $i % 2 === 0 ? '' : 'flex-direction:row-reverse;' ?>">
                    <div style="width:28px;height:28px;border-radius:50%;background:#e5e7eb;flex-shrink:0;"></div>
                    <div style="height:42px;background:#e5e7eb;border-radius:14px;width:<?= $w ?>px;animation:pulse 1.5s infinite;"></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Typing indicator -->
        <div class="typing-indicator" id="typingIndicator" style="padding:0 24px 6px;">
            <div class="msg-mini-avatar" id="typingAvatar"><?= $avatarLetter ?></div>
            <div class="typing-bubble">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        </div>

        <!-- Input -->
        <div class="chat-input-area">
            <div class="file-preview-strip" id="filePreviewStrip"></div>
            <div class="chat-input-row">
                <div class="ci-left-btns">
                    <label class="ci-icon-btn" title="Gửi file">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" id="fileInput" multiple
                               accept=".pdf,.doc,.docx,.zip,.txt,image/*"
                               style="display:none;"
                               onchange="handleFileSelect(this)">
                    </label>
                    <label class="ci-icon-btn" title="Gửi ảnh">
                        <i class="fas fa-image"></i>
                        <input type="file" id="imageInput" multiple
                               accept="image/*"
                               style="display:none;"
                               onchange="handleFileSelect(this)">
                    </label>
                </div>

                <div class="ci-textarea-wrap">
                    <textarea class="ci-textarea" id="msgInput"
                              placeholder="Nhắn tin..."
                              rows="1"
                              onkeydown="handleMsgKeydown(event)"
                              oninput="autoResize(this)"></textarea>
                </div>

                <button class="ci-send-btn" id="sendBtn" onclick="sendMessage()" title="Gửi (Enter)">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>

        <?php elseif ($pendingRoomId): ?>
        <?php
            // Hiện chat panel cho pending room (chưa có conv)
            $pTitle  = htmlspecialchars($pendingTitle ?: 'Phòng trọ');
            $pPrice  = $pendingPrice ? number_format($pendingPrice, 0, ',', '.') . ' đ/tháng' : '';
            $pImage  = htmlspecialchars($pendingImage ?? '');
        ?>
        <!-- Header (pending) -->
        <div class="chat-header" id="chatHeader">
            <div class="ch-info">
                <button class="ch-btn mob-back-btn" onclick="showSidebar()" title="Quay lại">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="ch-avatar">
                    <?php if ($pendingImage): ?>
                    <img class="ch-avatar-img" src="<?= $pImage ?>" referrerpolicy="no-referrer"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" alt="">
                    <div class="ch-avatar-img" style="display:none;background:#c7d2fe;align-items:center;justify-content:center;">
                        <i class="fas fa-home" style="color:#6366f1;"></i>
                    </div>
                    <?php else: ?>
                    <div class="ch-avatar-img" style="background:#c7d2fe;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-home" style="color:#6366f1;"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="ch-name"><?= $pTitle ?></div>
                    <div class="ch-status" style="color:#6366f1;"><?= $pPrice ?></div>
                </div>
            </div>
            <!-- Room context link -->
            <a class="ch-room-context" href="/room/<?= (int)$pendingRoomId ?>"
               title="Xem phòng" target="_blank" rel="noopener">
                <?php if ($pendingImage): ?>
                <img class="ch-room-thumb" src="<?= $pImage ?>" alt="room" onerror="this.style.display='none'">
                <?php else: ?>
                <div class="ch-room-thumb" style="background:#c7d2fe;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-home" style="color:#6366f1;font-size:18px;"></i>
                </div>
                <?php endif; ?>
                <div class="ch-room-info">
                    <div class="ch-room-title"><?= $pTitle ?></div>
                    <?php if ($pPrice): ?>
                    <div class="ch-room-price"><?= $pPrice ?></div>
                    <?php endif; ?>
                </div>
                <i class="fas fa-external-link-alt" style="font-size:11px;color:#9ca3af;margin-left:4px;"></i>
            </a>
        </div>

        <!-- Messages (empty state) -->
        <div class="chat-messages" id="chatMessages">
            <div id="msgSkeleton"></div><!-- JS sẽ xóa ngay -->
        </div>

        <!-- Typing indicator placeholder -->
        <div class="typing-indicator" id="typingIndicator" style="padding:0 24px 6px;display:none;"></div>

        <!-- Input -->
        <div class="chat-input-area">
            <div class="file-preview-strip" id="filePreviewStrip"></div>
            <div class="chat-input-row">
                <div class="ci-left-btns">
                    <label class="ci-icon-btn" title="Gửi file">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" id="fileInput" multiple
                               accept=".pdf,.doc,.docx,.zip,.txt,image/*"
                               style="display:none;"
                               onchange="handleFileSelect(this)">
                    </label>
                    <label class="ci-icon-btn" title="Gửi ảnh">
                        <i class="fas fa-image"></i>
                        <input type="file" id="imageInput" multiple
                               accept="image/*"
                               style="display:none;"
                               onchange="handleFileSelect(this)">
                    </label>
                </div>
                <div class="ci-textarea-wrap">
                    <textarea class="ci-textarea" id="msgInput"
                              placeholder="Nhắn tin để bắt đầu cuộc trò chuyện..."
                              rows="1"
                              onkeydown="handleMsgKeydown(event)"
                              oninput="autoResize(this)"></textarea>
                </div>
                <button class="ci-send-btn" id="sendBtn" onclick="sendMessage()" title="Gửi (Enter)">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>

        <?php else: ?>
        <!-- Chưa chọn cuộc hội thoại -->
        <div class="chat-empty-state">
            <div class="big-icon">💬</div>
            <h3>Chào, <?= htmlspecialchars($me['full_name'] ?? 'bạn') ?>!</h3>
            <p>Chọn một cuộc hội thoại bên trái,<br>hoặc tìm phòng và bắt đầu nhắn tin.</p>
            <a href="/search"
               style="margin-top:8px;display:inline-flex;align-items:center;gap:8px;
                      background:#5b63f5;color:#fff;padding:11px 24px;border-radius:12px;
                      font-weight:700;font-size:14px;text-decoration:none;">
                <i class="fas fa-search"></i> Tìm phòng trọ
            </a>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Lightbox -->
<div class="chat-lightbox" id="chatLightbox" onclick="closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()"><i class="fas fa-times"></i></button>
    <img src="" alt="preview" id="lightboxImg" onclick="event.stopPropagation()">
</div>

<!-- Toast -->
<div class="chat-toast" id="chatToast"></div>

<script>
const CHAT_ME = <?= json_encode([
    'user_id'   => $me['user_id']    ?? '',
    'full_name' => $me['full_name']  ?? '',
    'avatar'    => $me['avatar_url'] ?? null,
], JSON_UNESCAPED_UNICODE) ?>;

const CHAT_ACTIVE_CONV_ID = <?= $activeId ?: 'null' ?>;
const CHAT_ACTIVE_CONV    = <?= $activeConv ? json_encode([
    'conversation_id' => $activeConv['conversation_id'],
    'other_id'        => $activeConv['other_id']      ?? '',
    'other_name'      => $activeConv['other_name']    ?? '',
    'room_snapshot'   => $activeConv['room_snapshot'] ?? null,
], JSON_UNESCAPED_UNICODE) : 'null' ?>;

const CHAT_PENDING_ROOM = <?= ($pendingRoomId && !$activeId)
    ? json_encode([
        'room_id'     => (int) $pendingRoomId,
        'landlord_id' => $pendingLandlordId ?? '',
        'title'       => $pendingTitle      ?? '',
        'price'       => (int) ($pendingPrice ?? 0),
        'image'       => $pendingImage      ?? null,
    ], JSON_UNESCAPED_UNICODE)
    : 'null' ?>;
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';

// ── Helper ─────────────────────────────────────────────────────────────────
function chatFormatTime(?string $dt): string
{
    if (!$dt) return '';
    $ts   = strtotime($dt);
    $diff = time() - $ts;
    if ($diff < 60)        return 'vừa xong';
    if ($diff < 3600)      return floor($diff / 60) . ' phút';
    if ($diff < 86400)     return date('H:i', $ts);
    if ($diff < 86400 * 2) return 'Hôm qua';
    return date('d/m', $ts);
}