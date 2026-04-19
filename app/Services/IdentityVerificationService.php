<?php

namespace Services;

use Core\Database;

class IdentityVerificationService
{
    private \PDO $db;
    private string $encKey;
    private string $encIv;

    public function __construct()
    {
         $this->db = Database::getInstance();

        // Key 32 bytes (256-bit), IV 16 bytes (128-bit)
        $this->encKey = substr(
            hash('sha256', $_ENV['APP_ENCRYPT_KEY'] ?? 'fallback_key'),
            0,
            32
        );

        $this->encIv = substr(
            hash('sha256', $_ENV['APP_ENCRYPT_IV'] ?? 'fallback_iv'),
            0,
            16
        );

    }

    public function submit(string $userId, array $data): array
    {
        // Kiểm tra xem đã có pending / approved chưa
        $existing = $this->getStatus($userId);
        if ($existing) {
            if ($existing['status'] === 'pending') {
                throw new \RuntimeException('Bạn đã gửi yêu cầu xác thực, vui lòng chờ phê duyệt.');
            }
            if ($existing['status'] === 'approved') {
                throw new \RuntimeException('Danh tính của bạn đã được xác thực.');
            }
        }

        // Mã hóa số điện thoại & URL ảnh
        $encPhone   = $this->encrypt($data['phone_number']);
        $encFront   = $this->encrypt($data['front_image_url']);
        $encBack    = $this->encrypt($data['back_image_url']);
        $encSelfie  = $this->encrypt($data['selfie_image_url']);

        if ($existing && $existing['status'] === 'rejected') {
            // Cho phép gửi lại
            $stmt = $this->db->prepare("
                UPDATE identity_verifications
                SET phone_number      = :phone,
                    document_type     = :doc_type,
                    front_image_url   = :front,
                    back_image_url    = :back,
                    selfie_image_url  = :selfie,
                    status            = 'pending',
                    reject_reason     = NULL,
                    reviewed_at       = NULL,
                    updated_at        = NOW()
                WHERE user_id = :uid
                ORDER BY verification_id DESC
                LIMIT 1
            ");
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO identity_verifications
                    (user_id, phone_number, document_type, front_image_url, back_image_url, selfie_image_url, status)
                VALUES
                    (:uid, :phone, :doc_type, :front, :back, :selfie, 'pending')
            ");
        }

        $stmt->execute([
            ':uid'      => hex2bin(str_replace('-', '', $userId)),
            ':phone'    => $encPhone,
            ':doc_type' => $data['document_type'],
            ':front'    => $encFront,
            ':back'     => $encBack,
            ':selfie'   => $encSelfie,
        ]);

        return ['status' => 'pending'];
    }

    public function getStatus(string $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT verification_id, status, document_type, reject_reason, created_at, reviewed_at
            FROM identity_verifications
            WHERE user_id = :uid
            ORDER BY verification_id DESC
            LIMIT 1
        ");
        $stmt->execute([':uid' => hex2bin(str_replace('-', '', $userId))]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Duyệt xác thực (Admin)
     */
    public function approve(int $verificationId): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT user_id FROM identity_verifications WHERE verification_id = :id");
            $stmt->execute([':id' => $verificationId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) throw new \RuntimeException('Không tìm thấy bản ghi xác thực');

            // Cập nhật trạng thái xác thực
            $upd = $this->db->prepare("
                UPDATE identity_verifications
                SET status      = 'approved',
                    reviewed_at = NOW(),
                    updated_at  = NOW()
                WHERE verification_id = :id
            ");
            $upd->execute([':id' => $verificationId]);

            // Cập nhật user: identity_verified = true
            $updUser = $this->db->prepare("
                UPDATE users
                SET identity_verified    = 1,
                    identity_verified_at = NOW(),
                    updated_at           = NOW()
                WHERE user_id = :uid
            ");
            $updUser->execute([':uid' => $row['user_id']]);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Từ chối xác thực (Admin)
     */
    public function reject(int $verificationId, string $reason): void
    {
        $stmt = $this->db->prepare("
            UPDATE identity_verifications
            SET status        = 'rejected',
                reject_reason = :reason,
                reviewed_at   = NOW(),
                updated_at    = NOW()
            WHERE verification_id = :id
        ");
        $stmt->execute([':reason' => $reason, ':id' => $verificationId]);
    }

    /**
     * Danh sách chờ duyệt (Admin) – giải mã để admin xem
     */
    public function getPendingList(): array
    {
        $stmt = $this->db->query("
            SELECT iv.verification_id, iv.document_type, iv.status,
                   iv.phone_number, iv.front_image_url, iv.back_image_url, iv.selfie_image_url,
                   iv.reject_reason, iv.created_at,
                   u.full_name, u.email,
                   BIN_TO_UUID(u.user_id) AS user_uuid
            FROM identity_verifications iv
            JOIN users u ON u.user_id = iv.user_id
            WHERE iv.status = 'pending'
            ORDER BY iv.created_at ASC
        ");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Giải mã
        foreach ($rows as &$row) {
            $row['phone_number']     = $this->decrypt($row['phone_number']);
            $row['front_image_url']  = $this->decrypt($row['front_image_url']);
            $row['back_image_url']   = $this->decrypt($row['back_image_url']);
            $row['selfie_image_url'] = $this->decrypt($row['selfie_image_url']);
        }

        return $rows;
    }

    // ── Encryption helpers ────────────────────────────────────────────────

    public function encrypt(string $plain): string
    {
        if (empty($plain)) return '';
        $encrypted = openssl_encrypt($plain, 'AES-256-CBC', $this->encKey, 0, $this->encIv);
        return base64_encode($encrypted);
    }

    public function decrypt(string $cipher): string
    {
        if (empty($cipher)) return '';
        $decoded = base64_decode($cipher);
        return openssl_decrypt($decoded, 'AES-256-CBC', $this->encKey, 0, $this->encIv) ?: '';
    }
}