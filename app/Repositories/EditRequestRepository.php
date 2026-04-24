<?php

namespace Repositories;

class EditRequestRepository extends BaseRepository
{

    public function hasPendingRequest(int $roomId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM room_edit_requests
             WHERE room_id = :rid AND status = 'pending'"
        );
        $stmt->execute([':rid' => $roomId]);
        return (int) $stmt->fetchColumn() > 0;
    }


    public function create(int $roomId, string $landlordId, array $newData): int
    {
        $this->db->prepare("
            INSERT INTO room_edit_requests (room_id, requested_by, new_data)
            VALUES (:rid, :lid, :data)
        ")->execute([
            ':rid'  => $roomId,
            ':lid'  => $this->uuidToBin($landlordId),
            ':data' => json_encode($newData, JSON_UNESCAPED_UNICODE),
        ]);
        return (int) $this->db->lastInsertId();
    }


    public function findPending(): array
    {
        $stmt = $this->db->query("
            SELECT
                er.request_id,
                er.room_id,
                er.new_data,
                er.created_at,

                -- Dữ liệu hiện tại của phòng (để so sánh)
                r.title              AS current_title,
                r.description        AS current_description,
                r.price_per_month    AS current_price,
                r.deposit_amount     AS current_deposit,
                r.area_size          AS current_area,
                r.room_type          AS current_room_type,
                r.furnish_level      AS current_furnish,
                r.capacity           AS current_capacity,
                r.available_from     AS current_available_from,
                ra.city_name         AS current_city,
                ra.district_name     AS current_district,
                ra.ward_name         AS current_ward,
                ra.street_address    AS current_street,
                ri.image_url         AS current_primary_image,

                -- Thông tin chủ nhà
                u.full_name          AS landlord_name,
                u.email              AS landlord_email,
                BIN_TO_UUID(er.landlord_id) AS landlord_uuid

            FROM room_edit_requests er
            JOIN rooms r           ON r.room_id   = er.room_id
            JOIN users u           ON u.user_id   = er.landlord_id
            LEFT JOIN room_addresses ra ON ra.room_id = r.room_id
            LEFT JOIN room_images ri    ON ri.room_id = r.room_id AND ri.is_primary = 1

            WHERE er.status = 'pending'
            ORDER BY er.created_at ASC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function findById(int $requestId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                er.*,
                BIN_TO_UUID(er.landlord_id) AS landlord_uuid,
                r.landlord_id               AS room_landlord_bin,
                BIN_TO_UUID(r.landlord_id)  AS room_landlord_uuid,
                r.title                     AS current_title
            FROM room_edit_requests er
            JOIN rooms r ON r.room_id = er.room_id
            WHERE er.request_id = :id
        ");
        $stmt->execute([':id' => $requestId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }


    public function findRoomForEdit(int $roomId, string $landlordId): ?array
    {
        $stmt = $this->db->prepare("
        SELECT
            r.room_id, r.title, r.description, r.price_per_month, r.deposit_amount,
            r.area_size, r.capacity, r.room_type, r.furnish_level, r.available_from,
            -- r.edit_pending,   
            -- r.is_approved,   
            -- r.rejected_by_admin,
            ra.street_address, ra.city_name, ra.district_name, ra.ward_name,
            ra.latitude, ra.longitude
        FROM rooms r
        LEFT JOIN room_addresses ra ON ra.room_id = r.room_id
        WHERE r.room_id    = :rid
          AND r.landlord_id = :lid
          AND r.is_active   = 1
    ");
        $stmt->execute([
            ':rid' => $roomId,
            ':lid' => $this->uuidToBin($landlordId),
        ]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }


    public function setStatus(int $requestId, string $status, ?string $reason = null): void
    {
        $this->db->prepare("
            UPDATE room_edit_requests
            SET status        = :status,
                reject_reason = :reason,
                reviewed_at   = NOW()
            WHERE request_id = :id
        ")->execute([
            ':status' => $status,
            ':reason' => $reason,
            ':id'     => $requestId,
        ]);
    }


    public function setRoomEditPending(int $roomId, bool $pending): void
    {
        $this->db->prepare("
            UPDATE rooms
            SET edit_pending = :ep,
                is_approved  = :ap,
                updated_at   = NOW()
            WHERE room_id = :rid
        ")->execute([
            ':ep'  => $pending ? 1 : 0,
            ':ap'  => $pending ? 0 : 1,
            ':rid' => $roomId,
        ]);
    }


    public function applyToRoom(int $roomId, array $data): void
    {
        // 1. rooms
        $this->db->prepare("
            UPDATE rooms SET
                title           = :title,
                description     = :desc,
                price_per_month = :price,
                deposit_amount  = :deposit,
                area_size       = :area,
                capacity        = :capacity,
                room_type       = :room_type,
                furnish_level   = :furnish,
                available_from  = :avail_from,
                edit_pending    = 0,
                is_approved     = 1,
                updated_at      = NOW()
            WHERE room_id = :rid
        ")->execute([
            ':title'      => $data['title'],
            ':desc'       => $data['description']     ?? null,
            ':price'      => $data['price_per_month'],
            ':deposit'    => $data['deposit_amount']  ?? null,
            ':area'       => $data['area_size'],
            ':capacity'   => $data['capacity'],
            ':room_type'  => $data['room_type'],
            ':furnish'    => $data['furnish_level']   ?? null,
            ':avail_from' => $data['available_from']  ?? date('Y-m-d'),
            ':rid'        => $roomId,
        ]);

        // 2. room_addresses
        if (!empty($data['address'])) {
            $addr = $data['address'];
            $this->db->prepare("
                UPDATE room_addresses SET
                    street_address = :street,
                    city_name      = :city,
                    district_name  = :district,
                    ward_name      = :ward,
                    latitude       = :lat,
                    longitude      = :lng
                WHERE room_id = :rid
            ")->execute([
                ':street'   => $addr['street_address'],
                ':city'     => $addr['city_name'],
                ':district' => $addr['district_name'],
                ':ward'     => $addr['ward_name'],
                ':lat'      => $addr['latitude']  ?? null,
                ':lng'      => $addr['longitude'] ?? null,
                ':rid'      => $roomId,
            ]);
        }

        // 3. room_images – xoá cũ, insert lại
        if (!empty($data['images']) && is_array($data['images'])) {
            $this->db->prepare("DELETE FROM room_images WHERE room_id = :rid")
                     ->execute([':rid' => $roomId]);

            $landlordRow = $this->db->prepare(
                "SELECT landlord_id FROM rooms WHERE room_id = :rid"
            );
            $landlordRow->execute([':rid' => $roomId]);
            $landlordBin = $landlordRow->fetchColumn();

            foreach ($data['images'] as $i => $imgUrl) {
                $this->db->prepare("
                    INSERT INTO room_images
                        (room_id, image_url, image_order, is_primary, uploaded_by)
                    VALUES (:rid, :url, :ord, :primary, :uid)
                ")->execute([
                    ':rid'     => $roomId,
                    ':url'     => $imgUrl,
                    ':ord'     => $i,
                    ':primary' => $i === 0 ? 1 : 0,
                    ':uid'     => $landlordBin,
                ]);
            }
        }

        // 4. room_amenities – xoá cũ, insert lại
        if (isset($data['amenity_ids']) && is_array($data['amenity_ids'])) {
            $this->db->prepare("DELETE FROM room_amenities WHERE room_id = :rid")
                     ->execute([':rid' => $roomId]);
            foreach ($data['amenity_ids'] as $amenityId) {
                $this->db->prepare("
                    INSERT IGNORE INTO room_amenities (room_id, amenity_id)
                    VALUES (:rid, :aid)
                ")->execute([':rid' => $roomId, ':aid' => (int) $amenityId]);
            }
        }
    }
}