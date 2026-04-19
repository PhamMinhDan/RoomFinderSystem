<?php

namespace Services;

use Core\Database;

class AdminService
{
    private \PDO $db;
    private string $encKey;
    private string $encIv;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->encKey = substr(hash('sha256', $_ENV['APP_ENCRYPT_KEY'] ?? 'fallback_key'), 0, 32);
        $this->encIv = substr(hash('sha256', $_ENV['APP_ENCRYPT_IV'] ?? 'fallback_iv'), 0, 16);
    }

    // ── IDENTITY LOGIC ─────────────────────────────────────────────

    public function getPendingIdentities(): array
    {
        $stmt = $this->db->query("
            SELECT iv.*, u.full_name, u.email, BIN_TO_UUID(u.user_id) AS user_uuid
            FROM identity_verifications iv
            JOIN users u ON u.user_id = iv.user_id
            WHERE iv.status = 'pending'
            ORDER BY iv.created_at ASC
        ");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['phone_number']     = $this->decrypt($row['phone_number']);
            $row['front_image_url']  = $this->decrypt($row['front_image_url']);
            $row['back_image_url']   = $this->decrypt($row['back_image_url']);
            $row['selfie_image_url'] = $this->decrypt($row['selfie_image_url']);
        }
        return $rows;
    }

    public function approveIdentity(int $verificationId): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT user_id FROM identity_verifications WHERE verification_id = :id");
            $stmt->execute([':id' => $verificationId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) throw new \RuntimeException('Không tìm thấy bản ghi');

            $this->db->prepare("UPDATE identity_verifications SET status = 'approved', reviewed_at = NOW() WHERE verification_id = :id")
                     ->execute([':id' => $verificationId]);

            $this->db->prepare("UPDATE users SET identity_verified = 1, identity_verified_at = NOW() WHERE user_id = :uid")
                     ->execute([':uid' => $row['user_id']]);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function rejectIdentity(int $verificationId, string $reason): void
    {
        $stmt = $this->db->prepare("UPDATE identity_verifications SET status = 'rejected', reject_reason = :reason, reviewed_at = NOW() WHERE verification_id = :id");
        $stmt->execute([':reason' => $reason, ':id' => $verificationId]);
    }

    // ── ROOM LOGIC ──────────────────────────────────────────────────

    public function getPendingRooms(): array
    {
        $stmt = $this->db->query("
            SELECT r.room_id, r.title, r.price_per_month, r.area_size, r.created_at, 
                   u.full_name AS landlord_name, ra.city_name, ri.image_url AS primary_image
            FROM rooms r
            JOIN users u ON u.user_id = r.landlord_id
            LEFT JOIN room_addresses ra ON ra.room_id = r.room_id
            LEFT JOIN room_images ri ON ri.room_id = r.room_id AND ri.is_primary = 1
            WHERE r.is_approved = 0 AND r.rejected_by_admin = 0 AND r.is_active = 1
            ORDER BY r.created_at ASC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function approveRoom(int $roomId): void
    {
        $stmt = $this->db->prepare("
            UPDATE rooms 
            SET is_approved = 1, is_verified = 1, display_until = DATE_ADD(NOW(), INTERVAL 15 DAY), updated_at = NOW()
            WHERE room_id = :rid
        ");
        $stmt->execute([':rid' => $roomId]);
    }

    public function rejectRoom(int $roomId, string $reason): void
    {
        $stmt = $this->db->prepare("
            UPDATE rooms 
            SET rejected_by_admin = 1, hidden_reason = :reason, hidden_at = NOW(), updated_at = NOW()
            WHERE room_id = :rid
        ");
        $stmt->execute([':reason' => $reason, ':rid' => $roomId]);
    }

    private function decrypt(string $cipher): string 
    {
        if (empty($cipher)) return '';
        $decoded = base64_decode($cipher);
        return openssl_decrypt($decoded, 'AES-256-CBC', $this->encKey, 0, $this->encIv) ?: '';
    }
}