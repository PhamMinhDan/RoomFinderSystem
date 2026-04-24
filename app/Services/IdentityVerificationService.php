<?php

namespace Services;

use Models\IdentityVerification;
use Repositories\IdentityVerificationRepository;
use Repositories\AdminRepository;
use Services\NotificationService;

class IdentityVerificationService
{
    private IdentityVerificationRepository $verificationRepo;
    private AdminRepository $adminRepo;
    private NotificationService $notifService;
    private string $encKey;
    private string $encIv;

    public function __construct()
    {
        $this->verificationRepo = new IdentityVerificationRepository();
        $this->adminRepo = new AdminRepository();
        $this->notifService = new NotificationService();
        $this->encKey = substr(hash('sha256', $_ENV['APP_ENCRYPT_KEY'] ?? 'fallback_key'), 0, 32);
        $this->encIv  = substr(hash('sha256', $_ENV['APP_ENCRYPT_IV']  ?? 'fallback_iv'),  0, 16);
    }

    public function submit(string $userId, array $data): array
    {
        $existing = $this->verificationRepo->findLatestByUserId($userId);

        if ($existing) {
            if ($existing->isPending()) {
                throw new \RuntimeException('Bạn đã gửi yêu cầu xác thực, vui lòng chờ phê duyệt.');
            }
            if ($existing->isApproved()) {
                throw new \RuntimeException('Danh tính của bạn đã được xác thực.');
            }
        }

        $encryptedData = [
            'phone_number'     => $this->encrypt($data['phone_number']),
            'document_type'    => $data['document_type'],
            'front_image_url'  => $this->encrypt($data['front_image_url']),
            'back_image_url'   => $this->encrypt($data['back_image_url']),
            'selfie_image_url' => $this->encrypt($data['selfie_image_url']),
        ];

        if ($existing && $existing->isRejected()) {
            $this->verificationRepo->resubmit($userId, $encryptedData);
        } else {
            $this->verificationRepo->create($userId, $encryptedData);
        }
        try {
            $adminIds = $this->adminRepo->getAllAdminIds();
            foreach ($adminIds as $adminIdBin) {
                $this->notifService->send(
                    $adminIdBin,
                    "Yêu cầu xác thực mới ",
                    "Người dùng vừa gửi yêu cầu xác thực danh tính mới cần bạn phê duyệt.",
                    "identity_request",
                    "/admin/dashboard#verify-users"
                );
            }
        } catch (\Exception $e) {
            error_log("Notification Error: " . $e->getMessage());
        }

        return ['status' => IdentityVerification::STATUS_PENDING];
    }


    public function getStatus(string $userId): ?array
    {
        $record = $this->verificationRepo->findLatestByUserId($userId);
        if (!$record) return null;
        return [
            'verification_id' => $record->verification_id,
            'status'          => $record->status,
            'document_type'   => $record->document_type,
            'reject_reason'   => $record->reject_reason,
            'created_at'      => $record->created_at,
            'reviewed_at'     => $record->reviewed_at,
        ];
    }


    public function encrypt(string $plain): string
    {
        if (empty($plain)) return '';
        return base64_encode(openssl_encrypt($plain, 'AES-256-CBC', $this->encKey, 0, $this->encIv));
    }

    public function decrypt(string $cipher): string
    {
        if (empty($cipher)) return '';
        return openssl_decrypt(base64_decode($cipher), 'AES-256-CBC', $this->encKey, 0, $this->encIv) ?: '';
    }
}