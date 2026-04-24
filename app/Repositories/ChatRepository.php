<?php

namespace Repositories;

use Core\MessageCipher;
use Models\Conversation;
use Models\Message;

class ChatRepository extends BaseRepository
{
    // ══════════════════════════════════════════════════════════════
    // CONVERSATIONS
    // ══════════════════════════════════════════════════════════════

    /**
     * Kiểm tra nhanh user có phải participant không.
     */
    public function isParticipant(int $convId, string $userId): bool
    {
        $bin  = $this->uuidToBin($userId);
        $stmt = $this->db->prepare("
            SELECT 1 FROM chat_conversations
            WHERE conversation_id = ?
              AND (participant_a = ? OR participant_b = ?)
              AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$convId, $bin, $bin]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Tìm conversation đã tồn tại (không tạo mới).
     */
    public function findExistingConversation(
        string $participantA,
        string $participantB,
        ?int   $roomId = null
    ): ?int {
        [$binA, $binB] = $this->sortedBins($participantA, $participantB);

        if ($roomId !== null) {
            $stmt = $this->db->prepare("
                SELECT conversation_id FROM chat_conversations
                WHERE participant_a = ? AND participant_b = ?
                  AND room_id = ?
                  AND is_active = 1
                LIMIT 1
            ");
            $stmt->execute([$binA, $binB, $roomId]);
        } else {
            $stmt = $this->db->prepare("
                SELECT conversation_id FROM chat_conversations
                WHERE participant_a = ? AND participant_b = ?
                  AND room_id IS NULL
                  AND is_active = 1
                LIMIT 1
            ");
            $stmt->execute([$binA, $binB]);
        }

        $id = $stmt->fetchColumn();
        return $id !== false ? (int) $id : null;
    }

    /**
     * Tìm hoặc tạo conversation.
     */
    public function findOrCreateConversation(
        string  $participantA,
        string  $participantB,
        ?int    $roomId       = null,
        ?array  $roomSnapshot = null
    ): int {
        [$binA, $binB] = $this->sortedBins($participantA, $participantB);

        if ($roomId !== null) {
            $stmt = $this->db->prepare("
                SELECT conversation_id FROM chat_conversations
                WHERE participant_a = ? AND participant_b = ? AND room_id = ?
                LIMIT 1
            ");
            $stmt->execute([$binA, $binB, $roomId]);
        } else {
            $stmt = $this->db->prepare("
                SELECT conversation_id FROM chat_conversations
                WHERE participant_a = ? AND participant_b = ? AND room_id IS NULL
                LIMIT 1
            ");
            $stmt->execute([$binA, $binB]);
        }

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) return (int) $row['conversation_id'];

        $this->db->prepare("
            INSERT INTO chat_conversations
                (participant_a, participant_b, room_id, room_snapshot, last_message_at)
            VALUES (?, ?, ?, ?, NOW())
        ")->execute([
            $binA,
            $binB,
            $roomId,
            $roomSnapshot ? json_encode($roomSnapshot, JSON_UNESCAPED_UNICODE) : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Danh sách conversations của một user (theo last activity).
     * Dùng positional params (?) để tránh lỗi "invalid parameter number" với PDO MySQL.
     */
    public function findConversationsForUser(string $userId): array
    {
        $binId = $this->uuidToBin($userId);

        $stmt = $this->db->prepare("
            SELECT
                c.conversation_id,
                c.room_id,
                c.room_snapshot,
                c.last_message_at,
                c.unread_a,
                c.unread_b,
                BIN_TO_UUID(
                    CASE WHEN c.participant_a = ?
                         THEN c.participant_b
                         ELSE c.participant_a END
                ) AS other_id,
                u.full_name          AS other_name,
                u.avatar_url         AS other_avatar,
                u.identity_verified  AS other_verified,
                (c.participant_a = ?) AS i_am_a,
                m.content_enc  AS last_enc,
                m.content_iv   AS last_iv,
                m.content_tag  AS last_tag,
                m.msg_type     AS last_type,
                m.created_at   AS last_created
            FROM chat_conversations c
            JOIN users u
              ON u.user_id = CASE WHEN c.participant_a = ?
                                  THEN c.participant_b
                                  ELSE c.participant_a END
            LEFT JOIN chat_messages m
              ON m.message_id = (
                  SELECT message_id FROM chat_messages
                  WHERE  conversation_id  = c.conversation_id
                    AND  recalled_for_all = 0
                    AND  is_deleted       = 0
                  ORDER BY created_at DESC
                  LIMIT 1
              )
            WHERE (c.participant_a = ? OR c.participant_b = ?)
              AND c.is_active = 1
            ORDER BY c.last_message_at DESC
        ");

        // 5 vị trí ?, tất cả đều là cùng binId
        $stmt->execute([$binId, $binId, $binId, $binId, $binId]);

        return array_map(function (array $row) {
            $row['room_snapshot'] = $row['room_snapshot']
                ? json_decode($row['room_snapshot'], true)
                : null;
            $row['unread']       = (int) ($row['i_am_a'] ? $row['unread_a'] : $row['unread_b']);
            $row['last_preview'] = $this->previewText(
                $row['last_type'] ?? null,
                $row['last_enc']  ?? null,
                $row['last_iv']   ?? null,
                $row['last_tag']  ?? null,
            );
            unset($row['last_enc'], $row['last_iv'], $row['last_tag']);
            return $row;
        }, $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * Chi tiết một conversation (kèm kiểm tra user là participant).
     */
    public function findConversationById(int $convId, string $userId): ?array
    {
        $binId = $this->uuidToBin($userId);

        $stmt = $this->db->prepare("
            SELECT c.*,
                   BIN_TO_UUID(c.participant_a) AS uuid_a,
                   BIN_TO_UUID(c.participant_b) AS uuid_b,
                   BIN_TO_UUID(
                       CASE WHEN c.participant_a = ?
                            THEN c.participant_b
                            ELSE c.participant_a END
                   ) AS other_id,
                   u.full_name          AS other_name,
                   u.avatar_url         AS other_avatar,
                   u.identity_verified  AS other_verified,
                   (c.participant_a = ?) AS i_am_a
            FROM chat_conversations c
            JOIN users u
              ON u.user_id = CASE WHEN c.participant_a = ?
                                  THEN c.participant_b
                                  ELSE c.participant_a END
            WHERE c.conversation_id = ?
              AND (c.participant_a = ? OR c.participant_b = ?)
              AND c.is_active = 1
        ");

        $stmt->execute([$binId, $binId, $binId, $convId, $binId, $binId]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            error_log('[ChatRepo::findConversationById] NOT FOUND | convId=' . $convId . ' | userId=' . $userId);
            return null;
        }

        $row['room_snapshot'] = $row['room_snapshot']
            ? json_decode($row['room_snapshot'], true)
            : null;

        return $row;
    }

    // ══════════════════════════════════════════════════════════════
    // MESSAGES
    // ══════════════════════════════════════════════════════════════

    /**
     * Insert text / system message. Returns new message_id.
     */
    public function insertMessage(
        int     $convId,
        string  $senderId,
        string  $plaintext,
        string  $msgType    = Message::TYPE_TEXT,
        ?array  $systemData = null
    ): int {
        $enc = MessageCipher::encrypt($plaintext);

        $this->db->prepare("
            INSERT INTO chat_messages
                (conversation_id, sender_id, content_enc, content_iv, content_tag,
                 msg_type, system_data)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $convId,
            $this->uuidToBin($senderId),
            $enc['enc'],
            $enc['iv'],
            $enc['tag'],
            $msgType,
            $systemData ? json_encode($systemData, JSON_UNESCAPED_UNICODE) : null,
        ]);

        $msgId = (int) $this->db->lastInsertId();
        $this->touchConversation($convId, $senderId);
        return $msgId;
    }

    /**
     * Insert file/image message + attachments. Returns new message_id.
     */
    public function insertFileMessage(
        int    $convId,
        string $senderId,
        string $msgType,
        array  $attachments
    ): int {
        $enc = MessageCipher::encrypt($attachments[0]['file_name'] ?? 'file');

        $this->db->prepare("
            INSERT INTO chat_messages
                (conversation_id, sender_id, content_enc, content_iv, content_tag, msg_type)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([
            $convId,
            $this->uuidToBin($senderId),
            $enc['enc'],
            $enc['iv'],
            $enc['tag'],
            $msgType,
        ]);

        $msgId     = (int) $this->db->lastInsertId();
        $insertAtt = $this->db->prepare("
            INSERT INTO chat_attachments
                (message_id, file_url, file_name, file_type, file_size,
                 mime_type, thumbnail_url, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($attachments as $i => $att) {
            $insertAtt->execute([
                $msgId,
                $att['file_url'],
                $att['file_name'],
                $att['file_type'],
                (int) ($att['file_size'] ?? 0),
                $att['mime_type'],
                $att['thumbnail_url'] ?? null,
                $i,
            ]);
        }

        $this->touchConversation($convId, $senderId);
        return $msgId;
    }

    /**
     * Paginated messages (oldest-first, cursor = before_id).
     * Dùng positional params + bindValue riêng cho LIMIT để tránh PDO cast string.
     */
    public function findMessages(int $convId, int $limit = 40, ?int $beforeId = null): array
    {
        if ($beforeId) {
            $sql = "
                SELECT
                    m.message_id, m.conversation_id,
                    BIN_TO_UUID(m.sender_id) AS sender_id,
                    m.content_enc, m.content_iv, m.content_tag,
                    m.msg_type, m.system_data,
                    m.is_read, m.read_at,
                    m.recalled_for_all, m.recalled_by_sender,
                    m.created_at,
                    u.full_name  AS sender_name,
                    u.avatar_url AS sender_avatar,
                    GROUP_CONCAT(
                        JSON_OBJECT(
                            'url',  a.file_url,
                            'name', a.file_name,
                            'type', a.file_type,
                            'size', a.file_size,
                            'mime', a.mime_type,
                            'thumb',a.thumbnail_url
                        ) ORDER BY a.sort_order SEPARATOR '||'
                    ) AS attachments_raw
                FROM chat_messages m
                JOIN  users u ON u.user_id = m.sender_id
                LEFT JOIN chat_attachments a ON a.message_id = m.message_id
                WHERE m.conversation_id = ?
                  AND m.is_deleted = 0
                  AND m.message_id < ?
                GROUP BY m.message_id
                ORDER BY m.message_id DESC
                LIMIT ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(1, $convId,   \PDO::PARAM_INT);
            $stmt->bindValue(2, $beforeId, \PDO::PARAM_INT);
            $stmt->bindValue(3, $limit,    \PDO::PARAM_INT);
        } else {
            $sql = "
                SELECT
                    m.message_id, m.conversation_id,
                    BIN_TO_UUID(m.sender_id) AS sender_id,
                    m.content_enc, m.content_iv, m.content_tag,
                    m.msg_type, m.system_data,
                    m.is_read, m.read_at,
                    m.recalled_for_all, m.recalled_by_sender,
                    m.created_at,
                    u.full_name  AS sender_name,
                    u.avatar_url AS sender_avatar,
                    GROUP_CONCAT(
                        JSON_OBJECT(
                            'url',  a.file_url,
                            'name', a.file_name,
                            'type', a.file_type,
                            'size', a.file_size,
                            'mime', a.mime_type,
                            'thumb',a.thumbnail_url
                        ) ORDER BY a.sort_order SEPARATOR '||'
                    ) AS attachments_raw
                FROM chat_messages m
                JOIN  users u ON u.user_id = m.sender_id
                LEFT JOIN chat_attachments a ON a.message_id = m.message_id
                WHERE m.conversation_id = ?
                  AND m.is_deleted = 0
                GROUP BY m.message_id
                ORDER BY m.message_id DESC
                LIMIT ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(1, $convId, \PDO::PARAM_INT);
            $stmt->bindValue(2, $limit,  \PDO::PARAM_INT);
        }

        $stmt->execute();
        return array_map(
            [$this, 'decryptMessageRow'],
            array_reverse($stmt->fetchAll(\PDO::FETCH_ASSOC))
        );
    }

    /**
     * Messages mới hơn $afterId (cho SSE & fetchSingleMessage).
     */
    public function findNewMessages(int $convId, int $afterId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                m.message_id, m.conversation_id,
                BIN_TO_UUID(m.sender_id) AS sender_id,
                m.content_enc, m.content_iv, m.content_tag,
                m.msg_type, m.system_data,
                m.is_read, m.recalled_for_all, m.created_at,
                u.full_name  AS sender_name,
                u.avatar_url AS sender_avatar,
                GROUP_CONCAT(
                    JSON_OBJECT(
                        'url',  a.file_url,
                        'name', a.file_name,
                        'type', a.file_type,
                        'size', a.file_size,
                        'mime', a.mime_type,
                        'thumb',a.thumbnail_url
                    ) ORDER BY a.sort_order SEPARATOR '||'
                ) AS attachments_raw
            FROM chat_messages m
            JOIN  users u ON u.user_id = m.sender_id
            LEFT JOIN chat_attachments a ON a.message_id = m.message_id
            WHERE m.conversation_id = ?
              AND m.message_id > ?
              AND m.is_deleted = 0
            GROUP BY m.message_id
            ORDER BY m.message_id ASC
            LIMIT 50
        ");
        $stmt->execute([$convId, $afterId]);
        return array_map([$this, 'decryptMessageRow'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * Last message_id trong conversation (cursor khởi tạo SSE).
     */
    public function getLastMessageId(int $convId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(MAX(message_id), 0) FROM chat_messages WHERE conversation_id = ?"
        );
        $stmt->execute([$convId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Đánh dấu đã đọc + reset unread counter.
     */
    public function markRead(int $convId, string $readerId): void
    {
        $binReader = $this->uuidToBin($readerId);

        $this->db->prepare("
            UPDATE chat_messages
            SET is_read = 1, read_at = NOW()
            WHERE conversation_id = ?
              AND sender_id != ?
              AND is_read = 0
        ")->execute([$convId, $binReader]);

        $this->db->prepare("
            UPDATE chat_conversations
            SET unread_a = CASE WHEN participant_a = ? THEN 0 ELSE unread_a END,
                unread_b = CASE WHEN participant_b = ? THEN 0 ELSE unread_b END
            WHERE conversation_id = ?
        ")->execute([$binReader, $binReader, $convId]);
    }

    /**
     * Thu hồi tin nhắn (sender only).
     */
    public function recallMessage(int $msgId, string $senderId, bool $forAll = false): bool
    {
        $stmt = $this->db->prepare("
            UPDATE chat_messages
            SET recalled_by_sender = 1,
                recalled_for_all   = ?,
                recalled_at        = NOW()
            WHERE message_id       = ?
              AND sender_id        = ?
              AND recalled_for_all = 0
        ");
        $stmt->execute([
            $forAll ? 1 : 0,
            $msgId,
            $this->uuidToBin($senderId),
        ]);
        return $stmt->rowCount() > 0;
    }

    // ══════════════════════════════════════════════════════════════
    // PUBLIC HELPERS
    // ══════════════════════════════════════════════════════════════

    public function binToUuidPublic(string $bin): string
    {
        $hex = bin2hex($bin);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex,  0, 8),
            substr($hex,  8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20)
        );
    }

    public function getLandlordUuidByRoom(int $roomId): string
    {
        $stmt = $this->db->prepare(
            "SELECT BIN_TO_UUID(landlord_id) FROM rooms WHERE room_id = ? LIMIT 1"
        );
        $stmt->execute([$roomId]);
        return (string) ($stmt->fetchColumn() ?: '');
    }

    // ══════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════

    private function sortedBins(string $a, string $b): array
    {
        $binA = $this->uuidToBin($a);
        $binB = $this->uuidToBin($b);
        return $a > $b ? [$binB, $binA] : [$binA, $binB];
    }

    private function touchConversation(int $convId, string $senderId): void
    {
        $this->db->prepare("
            UPDATE chat_conversations
            SET last_message_at = NOW(),
                unread_a = CASE WHEN participant_b = ? THEN unread_a + 1 ELSE unread_a END,
                unread_b = CASE WHEN participant_a = ? THEN unread_b + 1 ELSE unread_b END
            WHERE conversation_id = ?
        ")->execute([
            $this->uuidToBin($senderId),
            $this->uuidToBin($senderId),
            $convId,
        ]);
    }

    private function decryptMessageRow(array $row): array
    {
        if (!empty($row['recalled_for_all'])) {
            $row['content'] = null;
            $row['recalled'] = true;
        } else {
            try {
                $row['content'] = MessageCipher::decrypt(
                    $row['content_enc'],
                    $row['content_iv'],
                    $row['content_tag']
                );
            } catch (\Throwable) {
                $row['content'] = null;
            }
            $row['recalled'] = false;
        }
        unset($row['content_enc'], $row['content_iv'], $row['content_tag']);

        if (!empty($row['attachments_raw'])) {
            $row['attachments'] = array_values(array_filter(
                array_map(
                    fn($s) => json_decode(trim($s), true),
                    explode('||', $row['attachments_raw'])
                )
            ));
        } else {
            $row['attachments'] = [];
        }
        unset($row['attachments_raw']);

        if (!empty($row['system_data']) && is_string($row['system_data'])) {
            $row['system_data'] = json_decode($row['system_data'], true);
        }

        return $row;
    }

    private function previewText(
        ?string $type,
        ?string $enc,
        ?string $iv,
        ?string $tag
    ): string {
        if ($type === 'image')  return '🖼 Đã gửi ảnh';
        if ($type === 'file')   return '📎 Đã gửi file';
        if ($type === 'system') return '🏠 Thông tin phòng';
        if ($type === 'text' && $enc && $iv && $tag) {
            try {
                $plain = MessageCipher::decrypt($enc, $iv, $tag);
                return mb_substr($plain, 0, 60) . (mb_strlen($plain) > 60 ? '…' : '');
            } catch (\Throwable) {
                return 'Tin nhắn được mã hóa';
            }
        }
        return '';
    }
}