<?php

namespace Services;

use Models\Message;
use Repositories\ChatRepository;
use Repositories\RoomRepository;

class ChatService
{
    private ChatRepository $chatRepo;
    private RoomRepository $roomRepo;

    public function __construct()
    {
        $this->chatRepo = new ChatRepository();
        $this->roomRepo = new RoomRepository();
    }

    // ── Preview (không tạo conversation) ──────────────────────────

    /**
     * Trả về thông tin để redirect sang /chat?room_id=xxx.
     * KHÔNG tạo conversation. Dùng cho nút "Nhắn tin" ở room-detail.
     */
    public function getRoomChatPreview(string $renterId, int $roomId): array
    {
        $room = $this->roomRepo->findPublicById($roomId);
        if (!$room) {
            throw new \RuntimeException('Phòng không tồn tại');
        }

        $roomArr    = $room->toArray();
        $landlordId = $this->resolveLandlordId($roomArr, $roomId);

        if (strtolower(trim($renterId)) === strtolower(trim($landlordId))) {
            throw new \RuntimeException('Bạn không thể nhắn tin với chính mình');
        }

        // Nếu đã có conversation sẵn → redirect thẳng vào đó
        $existingConvId = $this->chatRepo->findExistingConversation($renterId, $landlordId, $roomId);
        if ($existingConvId) {
            return ['redirect_url' => '/chat/' . $existingConvId];
        }

        // Chưa có → redirect sang /chat với query param để hiện preview sidebar
        return [
            'redirect_url' => '/chat?room_id=' . $roomId
                            . '&landlord_id=' . urlencode($landlordId)
                            . '&title='       . urlencode($roomArr['title'] ?? '')
                            . '&price='       . (int)($roomArr['price_per_month'] ?? 0)
                            . '&image='       . urlencode($roomArr['primary_image'] ?? ''),
        ];
    }

    // ── Open / init a conversation ─────────────────────────────────

    /**
     * Tạo (hoặc lấy) conversation khi người dùng gửi tin nhắn đầu tiên.
     * Đây là điểm DUY NHẤT tạo conversation từ room context.
     */
    public function openConversationForRoom(string $renterId, int $roomId): array
    {
        $room = $this->roomRepo->findPublicById($roomId);
        if (!$room) {
            throw new \RuntimeException('Phòng không tồn tại');
        }

        $roomArr    = $room->toArray();
        $landlordId = $this->resolveLandlordId($roomArr, $roomId);

        if (strtolower(trim($renterId)) === strtolower(trim($landlordId))) {
            throw new \RuntimeException('Bạn không thể nhắn tin với chính mình');
        }

        $snapshot = [
            'room_id'       => $roomId,
            'title'         => $roomArr['title']           ?? '',
            'price'         => $roomArr['price_per_month'] ?? 0,
            'image'         => $roomArr['primary_image']   ?? null,
            'city'          => $roomArr['city_name']       ?? '',
            'district'      => $roomArr['district_name']   ?? '',
            'street'        => $roomArr['street_address']  ?? '',
            'landlord_name' => $roomArr['landlord_name']   ?? '',
        ];

        $convId = $this->chatRepo->findOrCreateConversation(
            $renterId,
            $landlordId,
            $roomId,
            $snapshot
        );

        return [
            'conversation_id' => $convId,
            'landlord_id'     => $landlordId,
            'landlord_name'   => $roomArr['landlord_name']   ?? null,
            'landlord_avatar' => $roomArr['landlord_avatar'] ?? null,
        ];
    }

    /**
     * Mở (hoặc lấy) direct conversation không gắn phòng.
     */
    public function openDirectConversation(string $userA, string $userB): int
    {
        return $this->chatRepo->findOrCreateConversation($userA, $userB);
    }

    // ── Conversation list ──────────────────────────────────────────

    public function getConversations(string $userId): array
    {
        return $this->chatRepo->findConversationsForUser($userId);
    }

    public function getConversationDetail(int $convId, string $userId): ?array
    {
        return $this->chatRepo->findConversationById($convId, $userId);
    }

    // ── Messages ───────────────────────────────────────────────────

    public function getMessages(
        int     $convId,
        string  $userId,
        int     $limit    = 40,
        ?int    $beforeId = null
    ): array {
        $conv = $this->chatRepo->findConversationById($convId, $userId);
        if (!$conv) {
            throw new \RuntimeException('Không có quyền truy cập');
        }

        $this->chatRepo->markRead($convId, $userId);
        return $this->chatRepo->findMessages($convId, $limit, $beforeId);
    }

    public function sendTextMessage(int $convId, string $senderId, string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            throw new \InvalidArgumentException('Tin nhắn không được để trống');
        }
        if (mb_strlen($text) > 5000) {
            throw new \InvalidArgumentException('Tin nhắn quá dài (tối đa 5000 ký tự)');
        }

        if (!$this->chatRepo->isParticipant($convId, $senderId)) {
            throw new \RuntimeException('Không có quyền gửi tin nhắn');
        }

        $msgId = $this->chatRepo->insertMessage($convId, $senderId, $text, Message::TYPE_TEXT);
        return $this->fetchSingleMessage($convId, $msgId);
    }

    public function sendFileMessage(int $convId, string $senderId, array $attachments): array
    {
        if (!$this->chatRepo->isParticipant($convId, $senderId)) {
            throw new \RuntimeException('Không có quyền gửi tin nhắn');
        }

        $isImage = str_starts_with($attachments[0]['mime_type'] ?? '', 'image/');
        $msgType = $isImage ? Message::TYPE_IMAGE : Message::TYPE_FILE;
        $msgId   = $this->chatRepo->insertFileMessage($convId, $senderId, $msgType, $attachments);

        return $this->fetchSingleMessage($convId, $msgId);
    }

    public function recallMessage(int $msgId, string $senderId): bool
    {
        return $this->chatRepo->recallMessage($msgId, $senderId, false);
    }

    // ── SSE polling ────────────────────────────────────────────────

    public function pollNewMessages(int $convId, string $userId, int $afterId): array
    {
        $conv = $this->chatRepo->findConversationById($convId, $userId);
        if (!$conv) return [];

        $messages = $this->chatRepo->findNewMessages($convId, $afterId);

        if (!empty($messages)) {
            $this->chatRepo->markRead($convId, $userId);
        }

        return $messages;
    }

    public function getLastMessageId(int $convId): int
    {
        return $this->chatRepo->getLastMessageId($convId);
    }

    private function fetchSingleMessage(int $convId, int $msgId): array
    {
        // findNewMessages dùng > afterId, nên truyền msgId - 1
        $rows = $this->chatRepo->findNewMessages($convId, $msgId - 1);

        foreach ($rows as $row) {
            if ((int) $row['message_id'] === $msgId) {
                return $row;
            }
        }

        return [
            'message_id'      => $msgId,
            'conversation_id' => $convId,
            'msg_type'        => Message::TYPE_TEXT,
            'content'         => null,
            'attachments'     => [],
            'recalled'        => false,
            'created_at'      => date('Y-m-d H:i:s'),
        ];
    }

    private function resolveLandlordId(array $roomArr, int $roomId): string
    {
        $landlordId = $roomArr['landlord_id'] ?? '';

        if (!empty($landlordId) && strlen($landlordId) === 16) {
            $landlordId = $this->chatRepo->binToUuidPublic($landlordId);
        }

        if (empty($landlordId)) {
            $landlordId = $this->chatRepo->getLandlordUuidByRoom($roomId);
        }

        if (empty($landlordId)) {
            throw new \RuntimeException('Không tìm thấy thông tin chủ nhà');
        }

        return $landlordId;
    }
}