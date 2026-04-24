<?php

declare(strict_types=1);

namespace Repositories;

use Core\Database;

class FavouriteRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function exists(string $userId, int $roomId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM favourites WHERE user_id = UUID_TO_BIN(?) AND room_id = ? LIMIT 1'
        );
        $stmt->execute([$userId, $roomId]);
        return (bool) $stmt->fetchColumn();
    }

    public function add(string $userId, int $roomId): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO favourites (user_id, room_id)
             VALUES (UUID_TO_BIN(?), ?)'
        );
        $stmt->execute([$userId, $roomId]);
    }

    public function remove(string $userId, int $roomId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM favourites WHERE user_id = UUID_TO_BIN(?) AND room_id = ?'
        );
        $stmt->execute([$userId, $roomId]);
    }

    public function idsByUser(string $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT room_id FROM favourites WHERE user_id = UUID_TO_BIN(?)'
        );
        $stmt->execute([$userId]);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function listByUser(string $userId): array
{
    $sql = "
        SELECT
            r.room_id,
            r.title,
            r.price_per_month,
            r.area_size,
            r.capacity,
            r.room_type,
            r.availability_status AS status,
            r.created_at,
            r.display_until AS expires_at,
            -- Địa chỉ lấy từ bảng room_addresses
            ra.street_address,
            ra.ward_name,
            ra.district_name,
            ra.city_name,
            -- Ảnh đại diện
            (
                SELECT ri.image_url
                FROM   room_images ri
                WHERE  ri.room_id = r.room_id
                ORDER  BY ri.is_primary DESC, ri.image_id ASC
                LIMIT  1
            ) AS primary_image,
            -- Xác thực chủ nhà
            (
                SELECT iv.status = 'approved'
                FROM   identity_verifications iv
                WHERE  iv.user_id = r.landlord_id
                LIMIT  1
            ) AS landlord_verified,
            -- Đánh giá
            IFNULL(ROUND(AVG(rv.rating), 1), 0) AS average_rating,
            COUNT(rv.review_id)       AS total_reviews,
            -- Thời điểm lưu
            f.created_at              AS saved_at
        FROM       favourites f
        JOIN       rooms      r  ON r.room_id = f.room_id
        LEFT JOIN  room_addresses ra ON ra.room_id = r.room_id
        LEFT JOIN  reviews    rv ON rv.room_id = r.room_id
        WHERE      f.user_id = UUID_TO_BIN(?)
        GROUP BY
            r.room_id, r.title, r.price_per_month, r.area_size,
            r.capacity, r.room_type, r.availability_status, r.created_at, r.display_until,
            ra.street_address, ra.ward_name, ra.district_name, ra.city_name,
            f.created_at, r.landlord_id
        ORDER BY   f.created_at DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
}