<?php

namespace Services;

use Models\Room;
use Models\IdentityVerification;
use Repositories\AdminRepository;

class AdminService
{
    private AdminRepository $adminRepo;
    private string $encKey;
    private string $encIv;

    public function __construct()
    {
        $this->adminRepo = new AdminRepository();
        $this->encKey = substr(hash('sha256', $_ENV['APP_ENCRYPT_KEY'] ?? 'fallback_key'), 0, 32);
        $this->encIv  = substr(hash('sha256', $_ENV['APP_ENCRYPT_IV']  ?? 'fallback_iv'),  0, 16);
    }

    // ── Identity ──────────────────────────────────────────────────────────

    /**
     * Danh sách xác thực đang pending – giải mã dữ liệu nhạy cảm trước khi trả ra.
     */
    public function getPendingIdentities(): array
    {
        $records = $this->adminRepo->findPendingIdentities();

        return array_map(function (IdentityVerification $iv) {
            $arr = $iv->toArray();
            $arr['phone_number']     = $this->decrypt($arr['phone_number']);
            $arr['front_image_url']  = $this->decrypt($arr['front_image_url']);
            $arr['back_image_url']   = $this->decrypt($arr['back_image_url']);
            $arr['selfie_image_url'] = $this->decrypt($arr['selfie_image_url']);
            return $arr;
        }, $records);
    }

    public function approveIdentity(int $verificationId): void
    {
        // Lấy user_id trước khi update (AdminRepository trả raw binary)
        $userIdBin = $this->adminRepo->findUserIdByVerificationId($verificationId);

        $this->adminRepo->approveIdentity($verificationId);
        $this->adminRepo->markUserIdentityVerified($userIdBin);
    }

    /**
     * Từ chối xác thực danh tính.
     */
    public function rejectIdentity(int $verificationId, string $reason): void
    {
        $this->adminRepo->rejectIdentity($verificationId, $reason);
    }

    // ── Rooms ─────────────────────────────────────────────────────────────

    /**
     * Danh sách phòng đang chờ duyệt.
     */
    public function getPendingRooms(): array
    {
        $rooms = $this->adminRepo->findPendingRooms();
        return array_map(fn(Room $r) => $r->toArray(), $rooms);
    }

    /**
     * Duyệt phòng.
     */
    public function approveRoom(int $roomId): void
    {
        $this->adminRepo->approveRoom($roomId);
    }

    /**
     * Từ chối phòng.
     */
    public function rejectRoom(int $roomId, string $reason): void
    {
        $this->adminRepo->rejectRoom($roomId, $reason);
    }

    // ── Encryption helpers ────────────────────────────────────────────────

    private function decrypt(string $cipher): string
    {
        if (empty($cipher)) return '';
        return openssl_decrypt(base64_decode($cipher), 'AES-256-CBC', $this->encKey, 0, $this->encIv) ?: '';
    }
}