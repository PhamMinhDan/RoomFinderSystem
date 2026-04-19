<?php

namespace Services;

use Core\Database;

class RoomService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Landlord ──────────────────────────────────────────────────────────

    /**
     * Tạo bài đăng mới (is_approved = false, trạng thái chờ duyệt)
     */
    public function create(string $landlordId, array $data): int
    {
        $this->db->beginTransaction();
        try {
            $landlordBin = hex2bin(str_replace('-', '', $landlordId));

            // 1. Insert room
            $stmt = $this->db->prepare("
                INSERT INTO rooms
                    (landlord_id, title, description, area_size, price_per_month,
                     deposit_amount, capacity, room_type, furnish_level,
                     availability_status, available_from,
                     is_verified, is_approved, is_active)
                VALUES
                    (:lid, :title, :desc, :area, :price,
                     :deposit, :capacity, :room_type, :furnish,
                     'available', :avail_from,
                     0, 0, 1)
            ");
            $stmt->execute([
                ':lid'       => $landlordBin,
                ':title'     => $data['title'],
                ':desc'      => $data['description'] ?? null,
                ':area'      => $data['area_size'],
                ':price'     => $data['price_per_month'],
                ':deposit'   => $data['deposit_amount'] ?? null,
                ':capacity'  => $data['capacity'],
                ':room_type' => $data['room_type'],
                ':furnish'   => $data['furnish_level'] ?? null,
                ':avail_from'=> $data['available_from'] ?? date('Y-m-d'),
            ]);
            $roomId = (int)$this->db->lastInsertId();

            // 2. Insert room_address
            $addr = $data['address'];
            $stmtAddr = $this->db->prepare("
                INSERT INTO room_addresses
                    (room_id, street_address, city_name, district_name, ward_name, latitude, longitude)
                VALUES
                    (:rid, :street, :city, :district, :ward, :lat, :lng)
            ");
            $stmtAddr->execute([
                ':rid'      => $roomId,
                ':street'   => $addr['street_address'],
                ':city'     => $addr['city_name'],
                ':district' => $addr['district_name'],
                ':ward'     => $addr['ward_name'],
                ':lat'      => $addr['latitude'] ?? null,
                ':lng'      => $addr['longitude'] ?? null,
            ]);

            // 3. Insert room_images
            foreach ($data['images'] as $i => $imgUrl) {
                $stmtImg = $this->db->prepare("
                    INSERT INTO room_images (room_id, image_url, image_order, is_primary, uploaded_by)
                    VALUES (:rid, :url, :ord, :primary, :uid)
                ");
                $stmtImg->execute([
                    ':rid'     => $roomId,
                    ':url'     => $imgUrl,
                    ':ord'     => $i,
                    ':primary' => $i === 0 ? 1 : 0,
                    ':uid'     => $landlordBin,
                ]);
            }

            // 4. Insert amenities
            if (!empty($data['amenity_ids']) && is_array($data['amenity_ids'])) {
                foreach ($data['amenity_ids'] as $amenityId) {
                    $stmtAm = $this->db->prepare("
                        INSERT IGNORE INTO room_amenities (room_id, amenity_id) VALUES (:rid, :aid)
                    ");
                    $stmtAm->execute([':rid' => $roomId, ':aid' => (int)$amenityId]);
                }
            }

            $this->db->commit();
            return $roomId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Danh sách phòng của landlord, phân loại theo status
     */
    public function getLandlordRooms(string $landlordId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.room_id, r.title, r.price_per_month, r.is_approved,
                   r.rejected_by_admin, r.display_until, r.created_at,
                   r.view_count, r.average_rating, r.total_reviews,
                   ra.city_name, ra.district_name,
                   ri.image_url AS primary_image,
                   CASE
                       WHEN r.rejected_by_admin = 1                         THEN 'rejected'
                       WHEN r.is_approved = 0                               THEN 'pending'
                       WHEN r.display_until IS NOT NULL
                            AND r.display_until < NOW()                     THEN 'expired'
                       ELSE 'active'
                   END AS display_status
            FROM rooms r
            LEFT JOIN room_addresses ra ON ra.room_id = r.room_id
            LEFT JOIN room_images ri ON ri.room_id = r.room_id AND ri.is_primary = 1
            WHERE r.landlord_id = :lid AND r.is_active = 1
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([':lid' => hex2bin(str_replace('-', '', $landlordId))]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── Public (homepage, search, detail) ────────────────────────────────

    /**
     * Danh sách phòng đã duyệt, còn hạn hiển thị
     */
    public function getPublicList(array $params = []): array
    {
        $where  = ["r.is_approved = 1", "r.is_active = 1", "r.rejected_by_admin = 0",
                   "(r.display_until IS NULL OR r.display_until > NOW())"];
        $binds  = [];

        if (!empty($params['keyword'])) {
            $where[] = "(r.title LIKE :kw OR ra.city_name LIKE :kw OR ra.district_name LIKE :kw OR ra.street_address LIKE :kw)";
            $binds[':kw'] = '%' . $params['keyword'] . '%';
        }
        if (!empty($params['city'])) {
            $where[] = "ra.city_name LIKE :city";
            $binds[':city'] = '%' . $params['city'] . '%';
        }
        if (!empty($params['price_min'])) {
            $where[] = "r.price_per_month >= :pmin";
            $binds[':pmin'] = (float)$params['price_min'];
        }
        if (!empty($params['price_max'])) {
            $where[] = "r.price_per_month <= :pmax";
            $binds[':pmax'] = (float)$params['price_max'];
        }
        if (!empty($params['room_type'])) {
            $where[] = "r.room_type = :rtype";
            $binds[':rtype'] = $params['room_type'];
        }
        if (!empty($params['area_min'])) {
            $where[] = "r.area_size >= :amin";
            $binds[':amin'] = (float)$params['area_min'];
        }
        if (!empty($params['area_max'])) {
            $where[] = "r.area_size <= :amax";
            $binds[':amax'] = (float)$params['area_max'];
        }

        $limit  = min((int)($params['limit'] ?? 20), 100);
        $offset = (int)($params['offset'] ?? 0);
        $sort   = match($params['sort'] ?? 'newest') {
            'price_asc'  => 'r.price_per_month ASC',
            'price_desc' => 'r.price_per_month DESC',
            'area_desc'  => 'r.area_size DESC',
            'rating'     => 'r.average_rating DESC',
            default      => 'r.created_at DESC',
        };

        $sql = "
            SELECT r.room_id, r.title, r.price_per_month, r.area_size, r.capacity,
                   r.room_type, r.furnish_level, r.average_rating, r.total_reviews,
                   r.view_count, r.created_at, r.display_until,
                   u.full_name AS landlord_name, u.identity_verified AS landlord_verified,
                   ra.city_name, ra.district_name, ra.ward_name, ra.street_address,
                   ri.image_url AS primary_image
            FROM rooms r
            JOIN users u ON u.user_id = r.landlord_id
            LEFT JOIN room_addresses ra ON ra.room_id = r.room_id
            LEFT JOIN room_images ri ON ri.room_id = r.room_id AND ri.is_primary = 1
            WHERE " . implode(' AND ', $where) . "
            ORDER BY $sort
            LIMIT $limit OFFSET $offset
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($binds);
        $rooms = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Gắn thêm amenities cho mỗi phòng
        foreach ($rooms as &$room) {
            $room['amenities'] = $this->getRoomAmenities((int)$room['room_id']);
        }

        return $rooms;
    }

    /**
     * Chi tiết phòng (tăng view_count)
     */
    public function getPublicDetail(int $roomId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT r.room_id, r.title, r.description, r.price_per_month, r.deposit_amount,
                   r.area_size, r.capacity, r.room_type, r.furnish_level,
                   r.availability_status, r.available_from,
                   r.average_rating, r.total_reviews, r.view_count, r.created_at, r.display_until,
                   u.full_name AS landlord_name, u.identity_verified AS landlord_verified,
                   u.created_at AS landlord_since, u.avatar_url AS landlord_avatar,
                   BIN_TO_UUID(u.user_id) AS landlord_id,
                   ra.street_address, ra.city_name, ra.district_name, ra.ward_name,
                   ra.latitude, ra.longitude
            FROM rooms r
            JOIN users u ON u.user_id = r.landlord_id
            LEFT JOIN room_addresses ra ON ra.room_id = r.room_id
            WHERE r.room_id = :rid
              AND r.is_approved = 1
              AND r.is_active = 1
              AND r.rejected_by_admin = 0
              AND (r.display_until IS NULL OR r.display_until > NOW())
        ");
        $stmt->execute([':rid' => $roomId]);
        $room = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$room) return null;

        // Tăng view count
        $this->db->prepare("UPDATE rooms SET view_count = view_count + 1 WHERE room_id = :rid")
                 ->execute([':rid' => $roomId]);

        // Ảnh
        $imgStmt = $this->db->prepare("SELECT image_url, is_primary FROM room_images WHERE room_id = :rid ORDER BY image_order");
        $imgStmt->execute([':rid' => $roomId]);
        $room['images'] = $imgStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Tiện ích
        $room['amenities'] = $this->getRoomAmenities($roomId);

        // Reviews
        $revStmt = $this->db->prepare("
            SELECT rv.rating, rv.comment, rv.created_at,
                   u.full_name AS reviewer_name, u.avatar_url AS reviewer_avatar
            FROM reviews rv
            JOIN users u ON u.user_id = rv.user_id
            WHERE rv.room_id = :rid AND rv.is_active = 1
            ORDER BY rv.created_at DESC
            LIMIT 10
        ");
        $revStmt->execute([':rid' => $roomId]);
        $room['reviews'] = $revStmt->fetchAll(\PDO::FETCH_ASSOC);

        return $room;
    }

    // ── Admin ─────────────────────────────────────────────────────────────

    public function getPendingRooms(): array
    {
        $stmt = $this->db->query("
            SELECT r.room_id, r.title, r.price_per_month, r.area_size,
                   r.created_at, u.full_name AS landlord_name, u.email AS landlord_email,
                   ra.city_name, ra.district_name,
                   ri.image_url AS primary_image
            FROM rooms r
            JOIN users u ON u.user_id = r.landlord_id
            LEFT JOIN room_addresses ra ON ra.room_id = r.room_id
            LEFT JOIN room_images ri ON ri.room_id = r.room_id AND ri.is_primary = 1
            WHERE r.is_approved = 0 AND r.rejected_by_admin = 0 AND r.is_active = 1
            ORDER BY r.created_at ASC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Duyệt bài đăng: set is_approved=1, display_until = NOW()+15 ngày
     */
    public function approve(int $roomId, string $adminId): void
    {
        $stmt = $this->db->prepare("
            UPDATE rooms
            SET is_approved   = 1,
                is_verified   = 1,
                display_until = DATE_ADD(NOW(), INTERVAL 15 DAY),
                updated_at    = NOW()
            WHERE room_id = :rid
        ");
        $stmt->execute([':rid' => $roomId]);
    }

    /**
     * Từ chối bài đăng
     */
    public function reject(int $roomId, string $adminId, string $reason): void
    {
        $stmt = $this->db->prepare("
            UPDATE rooms
            SET rejected_by_admin = 1,
                hidden_reason     = :reason,
                hidden_at         = NOW(),
                updated_at        = NOW()
            WHERE room_id = :rid
        ");
        $stmt->execute([':reason' => $reason, ':rid' => $roomId]);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function getRoomAmenities(int $roomId): array
    {
        $stmt = $this->db->prepare("
            SELECT a.amenity_id, a.amenity_name, a.icon_url, a.category
            FROM room_amenities ra
            JOIN amenities a ON a.amenity_id = ra.amenity_id
            WHERE ra.room_id = :rid AND a.is_active = 1
        ");
        $stmt->execute([':rid' => $roomId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}