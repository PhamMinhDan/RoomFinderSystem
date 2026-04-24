<?php

namespace Repositories;

use Models\Room;
use Models\IdentityVerification;

class AdminRepository extends BaseRepository
{

    public function findPendingIdentities(): array
    {
        $stmt = $this->db->query("
            SELECT iv.verification_id, BIN_TO_UUID(iv.user_id) AS user_id, iv.phone_number, iv.document_type,
                   iv.front_image_url, iv.back_image_url, iv.selfie_image_url,
                   iv.status, iv.reject_reason, iv.created_at, iv.reviewed_at,
                   u.full_name, u.email, BIN_TO_UUID(u.user_id) AS user_uuid
            FROM identity_verifications iv
            JOIN users u ON u.user_id = iv.user_id
            WHERE iv.status = 'pending'
            ORDER BY iv.created_at ASC
        ");
        return array_map(
            fn($row) => new IdentityVerification($row),
            $stmt->fetchAll(\PDO::FETCH_ASSOC)
        );
    }


    public function findUserIdByVerificationId(int $verificationId): string
    {
        $stmt = $this->db->prepare(
            "SELECT user_id FROM identity_verifications WHERE verification_id = :id"
        );
        $stmt->execute([':id' => $verificationId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            throw new \RuntimeException('Không tìm thấy bản ghi xác thực');
        }
        return $row['user_id']; 
    }


    public function approveIdentity(int $verificationId): void
    {
        $this->db->prepare("
            UPDATE identity_verifications
            SET status = 'approved', reviewed_at = NOW()
            WHERE verification_id = :id
        ")->execute([':id' => $verificationId]);
    }


    public function rejectIdentity(int $verificationId, string $reason): void
    {
        $this->db->prepare("
            UPDATE identity_verifications
            SET status = 'rejected', reject_reason = :reason, reviewed_at = NOW()
            WHERE verification_id = :id
        ")->execute([':reason' => $reason, ':id' => $verificationId]);
    }

 
    public function markUserIdentityVerified(string $userIdBin): void
    {
        $this->db->prepare("
            UPDATE users
            SET identity_verified = 1, identity_verified_at = NOW()
            WHERE user_id = :uid
        ")->execute([':uid' => $userIdBin]);
    }

    public function findPendingRooms(): array
    {
        $stmt = $this->db->query("
            SELECT r.room_id, r.title, r.price_per_month, r.area_size, r.created_at,
                   u.full_name AS landlord_name,
                   ra.city_name,
                   ri.image_url AS primary_image
            FROM rooms r
            JOIN users u           ON u.user_id  = r.landlord_id
            LEFT JOIN room_addresses ra ON ra.room_id = r.room_id
            LEFT JOIN room_images ri    ON ri.room_id = r.room_id AND ri.is_primary = 1
            WHERE r.is_approved = 0 AND r.rejected_by_admin = 0 AND r.is_active = 1
            ORDER BY r.created_at ASC
        ");
        return array_map(
            fn($row) => new Room($row),
            $stmt->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    public function approveRoom(int $roomId): void
    {
        $this->db->prepare("
            UPDATE rooms
            SET is_approved   = 1,
                is_verified   = 1,
                display_until = DATE_ADD(NOW(), INTERVAL 15 DAY),
                updated_at    = NOW()
            WHERE room_id = :rid
        ")->execute([':rid' => $roomId]);
    }

    public function rejectRoom(int $roomId, string $reason): void
    {
        $this->db->prepare("
            UPDATE rooms
            SET rejected_by_admin = 1,
                hidden_reason     = :reason,
                hidden_at         = NOW(),
                updated_at        = NOW()
            WHERE room_id = :rid
        ")->execute([':reason' => $reason, ':rid' => $roomId]);
    }

    public function findRoomById(int $roomId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                room_id, 
                landlord_id, 
                title 
            FROM rooms 
            WHERE room_id = :rid
        ");
        $stmt->execute([':rid' => $roomId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $row ?: null;
    }

    public function getAllAdminIds(): array
    {
        $sql = "SELECT user_id FROM users 
                WHERE role_id = (SELECT role_id FROM roles WHERE role_name = 'ADMIN' LIMIT 1)";
        
        $stmt = $this->db->query($sql);
        
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}