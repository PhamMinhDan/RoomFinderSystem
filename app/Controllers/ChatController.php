<?php

namespace Controllers;

use Core\SessionManager;
use Services\ChatService;

class ChatController
{
    private ChatService $chatService;

    public function __construct()
    {
        $this->chatService = new ChatService();
    }

    // ── Pages ──────────────────────────────────────────────────────

    public function index(?int $convId = null): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        if (!$user) {
            header('Location: /?login=1');
            exit;
        }

        $conversations = $this->chatService->getConversations($user['user_id']);
        $activeConv    = null;

        if ($convId) {
            $activeConv = $this->chatService->getConversationDetail($convId, $user['user_id']);
        }

        $GLOBALS['chat_user']          = $user;
        $GLOBALS['chat_conversations'] = $conversations;
        $GLOBALS['chat_active_conv']   = $activeConv;
        $GLOBALS['chat_active_id']     = $convId;

        $GLOBALS['chat_pending_room_id']    = isset($_GET['room_id'])    ? (int) $_GET['room_id'] : null;
        $GLOBALS['chat_pending_landlord_id']= $_GET['landlord_id'] ?? null;
        $GLOBALS['chat_pending_title']      = $_GET['title']       ?? null;
        $GLOBALS['chat_pending_price']      = isset($_GET['price']) ? (int) $_GET['price'] : null;
        $GLOBALS['chat_pending_image']      = $_GET['image']       ?? null;

        require __DIR__ . '/../Views/pages/chat.php';
    }

  
    public function roomPreview(): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        if (!$user) { $this->json(['error' => 'Chưa đăng nhập'], 401); return; }

        $body   = $this->parseJson();
        $roomId = (int) ($body['room_id'] ?? 0);
        if (!$roomId) { $this->json(['error' => 'Thiếu room_id'], 422); return; }

        try {
            $result = $this->chatService->getRoomChatPreview($user['user_id'], $roomId);
            $this->json($result);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    // ── API: Open room (tạo conv + gửi room-card, dùng khi gửi tin đầu tiên) ──

    public function openRoom(): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        if (!$user) { $this->json(['error' => 'Chưa đăng nhập'], 401); return; }

        $body   = $this->parseJson();
        $roomId = (int) ($body['room_id'] ?? 0);
        if (!$roomId) { $this->json(['error' => 'Thiếu room_id'], 422); return; }

        try {
            $result = $this->chatService->openConversationForRoom($user['user_id'], $roomId);
            $this->json([
                'conversation_id' => $result['conversation_id'],
                'landlord_name'   => $result['landlord_name']   ?? null,
                'landlord_avatar' => $result['landlord_avatar'] ?? null,
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function openDirect(): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        if (!$user) { $this->json(['error' => 'Chưa đăng nhập'], 401); return; }

        $body     = $this->parseJson();
        $targetId = $body['user_id'] ?? '';
        if (!$targetId) { $this->json(['error' => 'Thiếu user_id'], 422); return; }

        try {
            $convId = $this->chatService->openDirectConversation($user['user_id'], $targetId);
            $this->json(['conversation_id' => $convId]);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function conversations(): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        if (!$user) { $this->json(['error' => 'Chưa đăng nhập'], 401); return; }

        $this->json(['data' => $this->chatService->getConversations($user['user_id'])]);
    }

    public function messages(int $convId): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        if (!$user) { $this->json(['error' => 'Chưa đăng nhập'], 401); return; }

        $limit    = min((int) ($_GET['limit']    ?? 40), 100);
        $beforeId = isset($_GET['before_id']) ? (int) $_GET['before_id'] : null;

        try {
            $msgs = $this->chatService->getMessages($convId, $user['user_id'], $limit, $beforeId);
            $this->json(['data' => $msgs]);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 403);
        }
    }

    public function sendMessage(int $convId): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        if (!$user) { $this->json(['error' => 'Chưa đăng nhập'], 401); return; }

        $body = $this->parseJson();
        $text = trim($body['text'] ?? '');
        if ($text === '') { $this->json(['error' => 'Tin nhắn rỗng'], 422); return; }

        // Nếu kèm room_id → đảm bảo conversation đã tồn tại (lazy creation)
        if (!empty($body['room_id'])) {
            try {
                $this->chatService->openConversationForRoom(
                    $user['user_id'],
                    (int) $body['room_id']
                );
            } catch (\Throwable $e) {
                $this->json(['error' => $e->getMessage()], 400);
                return;
            }
        }

        try {
            $msg = $this->chatService->sendTextMessage($convId, $user['user_id'], $text);
            $this->json(['data' => $msg]);
        } catch (\Throwable $e) {
            error_log('[ChatController::sendMessage] ' . $e->getMessage() . ' | convId=' . $convId . ' | userId=' . $user['user_id']);
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function upload(int $convId): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        if (!$user) { $this->json(['error' => 'Chưa đăng nhập'], 401); return; }

        if (empty($_FILES['files'])) {
            $this->json(['error' => 'Không có file'], 422);
            return;
        }

        $allowed   = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf', 'application/zip',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
        ];
        $maxSize   = 20 * 1024 * 1024; // 20 MB
        $uploadDir = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/') . '/../storage/uploads/chat/';
        @mkdir($uploadDir, 0755, true);

        $attachments = [];
        $files       = $_FILES['files'];
        $count       = is_array($files['name']) ? count($files['name']) : 1;

        for ($i = 0; $i < min($count, 5); $i++) {
            $name = is_array($files['name'])     ? $files['name'][$i]     : $files['name'];
            $tmp  = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $size = is_array($files['size'])     ? $files['size'][$i]     : $files['size'];

            if (empty($tmp) || !is_uploaded_file($tmp)) continue;

            $mime = mime_content_type($tmp);
            if (!in_array($mime, $allowed, true) || $size > $maxSize) continue;

            $ext      = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $filename = bin2hex(random_bytes(8)) . '.' . $ext;
            $dest     = $uploadDir . $filename;

            if (!move_uploaded_file($tmp, $dest)) continue;

            $webUrl  = '/storage/uploads/chat/' . $filename;
            $isImage = str_starts_with($mime, 'image/');

            $attachments[] = [
                'file_url'      => $webUrl,
                'file_name'     => $name,
                'file_type'     => $isImage ? 'image' : $ext,
                'file_size'     => (int) $size,
                'mime_type'     => $mime,
                'thumbnail_url' => $isImage ? $webUrl : null,
            ];
        }

        if (empty($attachments)) {
            $this->json(['error' => 'File không hợp lệ hoặc vượt quá 20 MB'], 422);
            return;
        }

        try {
            $msg = $this->chatService->sendFileMessage($convId, $user['user_id'], $attachments);
            $this->json(['data' => $msg]);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    // ── API: Recall / Read ─────────────────────────────────────────

    public function recall(int $convId, int $msgId): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        if (!$user) { $this->json(['error' => 'Chưa đăng nhập'], 401); return; }

        $ok = $this->chatService->recallMessage($msgId, $user['user_id']);
        $this->json(['success' => $ok]);
    }

    public function markRead(int $convId): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        if (!$user) { $this->json(['error' => 'Chưa đăng nhập'], 401); return; }

        (new \Repositories\ChatRepository())->markRead($convId, $user['user_id']);
        $this->json(['success' => true]);
    }

    public function sse(int $convId): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        if (!$user) { http_response_code(401); exit; }

        $conv = $this->chatService->getConversationDetail($convId, $user['user_id']);
        if (!$conv) { http_response_code(403); exit; }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        header('Connection: keep-alive');

        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', false);
        set_time_limit(0);

        $afterId   = (int) ($_GET['after_id'] ?? $this->chatService->getLastMessageId($convId));
        $startTime = time();
        $maxTime   = 25;
        $pollEvery = 1_500_000; // 1.5s

        echo "event: ping\ndata: " . json_encode(['ts' => time()]) . "\n\n";
        flush();

        $heartbeat = 0;
        while (true) {
            if (connection_aborted() || (time() - $startTime) >= $maxTime) {
                echo "event: reconnect\ndata: {}\n\n";
                flush();
                break;
            }

            $newMsgs = $this->chatService->pollNewMessages($convId, $user['user_id'], $afterId);

            if (!empty($newMsgs)) {
                foreach ($newMsgs as $msg) {
                    echo "event: message\ndata: "
                        . json_encode($msg, JSON_UNESCAPED_UNICODE)
                        . "\n\n";
                    $afterId = max($afterId, (int) $msg['message_id']);
                }
                flush();
            }

            if (++$heartbeat % 5 === 0) {
                echo "event: heartbeat\ndata: " . json_encode(['ts' => time()]) . "\n\n";
                flush();
            }

            usleep($pollEvery);
        }

        exit;
    }

    private function parseJson(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}