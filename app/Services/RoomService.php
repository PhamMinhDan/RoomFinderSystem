<?php

namespace Services;

use Models\Room;
use Repositories\RoomRepository;

class RoomService
{
    private RoomRepository $roomRepo;

    public function __construct()
    {
        $this->roomRepo = new RoomRepository();
    }

    // ── Landlord ──────────────────────────────────────────────────────────

    public function create(string $landlordId, array $data): int
    {
        return $this->roomRepo->createWithRelations($landlordId, $data);
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