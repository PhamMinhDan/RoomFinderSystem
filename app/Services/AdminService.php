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
        $userIdBin = $this->adminRepo->findUserIdByVerificationId($verificationId);

        $this->adminRepo->approveIdentity($verificationId);
        $this->adminRepo->markUserIdentityVerified($userIdBin);
    }

    public function rejectIdentity(int $verificationId, string $reason): void
    {
        $this->adminRepo->rejectIdentity($verificationId, $reason);
    }


    public function getPendingRooms(): array
    {
        $rooms = $this->adminRepo->findPendingRooms();
        return array_map(fn(Room $r) => $r->toArray(), $rooms);
    }


    public function approveRoom(int $roomId): void
    {
        $this->adminRepo->approveRoom($roomId);
        $room = $this->adminRepo->findRoomById($roomId); 
        $notif = new NotificationService();
        $notif->send(
            $room['landlord_id'], 
            "Tin đăng đã được duyệt! ", 
            "Tin '{$room['title']}' của bạn đã được phê duyệt và hiển thị.", 
            'room_approved', 
            "/room/{$roomId}"
        );
    }

    public function rejectRoom(int $roomId, string $reason): void
    {
        $this->adminRepo->rejectRoom($roomId, $reason);
        $room = $this->adminRepo->findRoomById($roomId);
        $notif = new NotificationService();
        $notif->send(
            $room['landlord_id'], 
            "Tin đăng bị từ chối ", 
            "Lý do: $reason", 
            'room_rejected', 
            "/landlord/dashboard"
        );
    }

    private function decrypt(string $cipher): string
    {
        if (empty($cipher)) return '';
        return openssl_decrypt(base64_decode($cipher), 'AES-256-CBC', $this->encKey, 0, $this->encIv) ?: '';
    }
}