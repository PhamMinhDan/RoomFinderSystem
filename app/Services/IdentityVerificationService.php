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