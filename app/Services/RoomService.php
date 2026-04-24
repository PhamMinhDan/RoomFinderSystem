<?php

namespace Services;

use Models\Room;
use Repositories\RoomRepository;
use Repositories\AdminRepository;

class RoomService
{
    private RoomRepository $roomRepo;
    private AdminRepository $adminRepo;

    public function __construct()
    {
        $this->roomRepo = new RoomRepository();
        $this->adminRepo = new AdminRepository();
    }


    public function create(string $landlordId, array $data): int
    {
        $roomId = $this->roomRepo->createWithRelations($landlordId, $data);
    
        $admins = $this->adminRepo->getAllAdminIds();
        $notif = new NotificationService();
        foreach($admins as $adminId) {
            $notif->send($adminId, "Có tin mới chờ duyệt", "Tin: {$data['title']}", 'admin_new_room', "/admin/dashboard#pending");
        }
        return $roomId;
    }

    public function getLandlordRooms(string $landlordId): array
    {
        $rooms = $this->roomRepo->findByLandlord($landlordId);
        return array_map(fn(Room $r) => $r->toArray(), $rooms);
    }

    // ── Public (homepage / search / detail) ──────────────────────────────

    public function getPublicList(array $params = []): array
    {
        $rooms = $this->roomRepo->findPublic($params);

        return array_map(function (Room $room) {
            $arr = $room->toArray();
            $arr['amenities'] = $this->roomRepo->findAmenities((int) $arr['room_id']);
            return $arr;
        }, $rooms);
    }

    public function getPublicDetail(int $roomId): ?array
    {
        $room = $this->roomRepo->findPublicById($roomId);
        if (!$room) return null;

        $this->roomRepo->incrementViewCount($roomId);

        $arr = $room->toArray();
        $arr['images']    = $this->roomRepo->findImages($roomId);
        $arr['amenities'] = $this->roomRepo->findAmenities($roomId);
        $arr['reviews']   = $this->roomRepo->findReviews($roomId);

        return $arr;
    }
}