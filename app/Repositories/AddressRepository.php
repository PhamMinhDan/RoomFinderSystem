<?php

namespace Repositories;

use Core\Database;
use PDO;

class AddressRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(string $userId, array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO addresses
                (user_id, street_address, city_name, district_name, ward_name, latitude, longitude, is_primary)
            VALUES
                (UUID_TO_BIN(:user_id), :street_address, :city_name, :district_name, :ward_name, :latitude, :longitude, 1)
        ');

        $stmt->execute([
            ':user_id'        => $userId,
            ':street_address' => $data['street_address'] ?? null,
            ':city_name'      => $data['city_name'],
            ':district_name'  => $data['district_name'],
            ':ward_name'      => $data['ward_name'],
            ':latitude'       => $data['latitude']  ?? null,
            ':longitude'      => $data['longitude'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }


    public function update(int $addressId, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE addresses SET
                street_address = :street_address,
                city_name      = :city_name,
                district_name  = :district_name,
                ward_name      = :ward_name,
                latitude       = :latitude,
                longitude      = :longitude,
                updated_at     = NOW()
            WHERE address_id = :address_id
        ');

        $stmt->execute([
            ':street_address' => $data['street_address'] ?? null,
            ':city_name'      => $data['city_name'],
            ':district_name'  => $data['district_name'],
            ':ward_name'      => $data['ward_name'],
            ':latitude'       => $data['latitude']  ?? null,
            ':longitude'      => $data['longitude'] ?? null,
            ':address_id'     => $addressId,
        ]);
    }
}