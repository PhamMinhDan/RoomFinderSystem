<?php

namespace Repositories;

use Models\IdentityVerification;

class IdentityVerificationRepository extends BaseRepository
{
    /**
     * Lấy bản ghi mới nhất của user (dữ liệu raw – còn mã hóa).
     */
    public function findLatestByUserId(string $userId): ?IdentityVerification
    {
        $stmt = $this->db->prepare("
            SELECT verification_id, user_id, phone_number, document_type,
                   front_image_url, back_image_url, selfie_image_url,
                   status, reject_reason, created_at, reviewed_at
            FROM identity_verifications
            WHERE user_id = :uid
            ORDER BY verification_id DESC
            LIMIT 1
        ");
        $stmt->execute([':uid' => $this->uuidToBin($userId)]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? new IdentityVerification($row) : null;
    }

    /**
     * Tạo mới bản ghi (dữ liệu nhạy cảm đã được mã hóa trước khi truyền vào).
     */
    public function create(string $userId, array $encryptedData): void
    {
        $this->db->prepare("
            INSERT INTO identity_verifications
                (user_id, phone_number, document_type, front_image_url, back_image_url, selfie_image_url, status)
            VALUES
                (:uid, :phone, :doc_type, :front, :back, :selfie, 'pending')
        ")->execute([
            ':uid'      => $this->uuidToBin($userId),
            ':phone'    => $encryptedData['phone_number'],
            ':doc_type' => $encryptedData['document_type'],
            ':front'    => $encryptedData['front_image_url'],
            ':back'     => $encryptedData['back_image_url'],
            ':selfie'   => $encryptedData['selfie_image_url'],
        ]);
    }

    /**
     * Gửi lại sau khi bị rejected (reset về pending).
     */
    public function resubmit(string $userId, array $encryptedData): void
    {
        $this->db->prepare("
            UPDATE identity_verifications
            SET phone_number     = :phone,
                document_type    = :doc_type,
                front_image_url  = :front,
                back_image_url   = :back,
                selfie_image_url = :selfie,
                status           = 'pending',
                reject_reason    = NULL,
                reviewed_at      = NULL,
                updated_at       = NOW()
            WHERE user_id = :uid
            ORDER BY verification_id DESC
            LIMIT 1
        ")->execute([
            ':uid'      => $this->uuidToBin($userId),
            ':phone'    => $encryptedData['phone_number'],
            ':doc_type' => $encryptedData['document_type'],
            ':front'    => $encryptedData['front_image_url'],
            ':back'     => $encryptedData['back_image_url'],
            ':selfie'   => $encryptedData['selfie_image_url'],
        ]);
    }
}