<?php

namespace Repositories;

use Core\Database;
use Models\User;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Base query (JOIN roles + addresses) ───────────────────
    private function baseQuery(): string
    {
        return '
            SELECT
                u.*,
                r.role_name,
                a.address_id,
                a.street_address,
                a.city_name,
                a.district_name,
                a.ward_name,
                a.latitude,
                a.longitude,
                a.is_primary
            FROM users u
            LEFT JOIN roles     r ON r.role_id    = u.role_id
            LEFT JOIN addresses a ON a.address_id = u.address_id
        ';
    }

    // ── Finders ──────────────────────────────────────────────

    public function findByGoogleId(string $googleId): ?User
    {
        $stmt = $this->db->prepare($this->baseQuery() . 'WHERE u.google_id = ? LIMIT 1');
        $stmt->execute([$googleId]);
        $row = $stmt->fetch();
        return $row ? User::fromDbRow($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare($this->baseQuery() . 'WHERE u.email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ? User::fromDbRow($row) : null;
    }

    public function findById(string $userId): ?User
    {
        $stmt = $this->db->prepare($this->baseQuery() . 'WHERE u.user_id = UUID_TO_BIN(?) LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ? User::fromDbRow($row) : null;
    }

    // ── Write ─────────────────────────────────────────────────
    public function createGoogleUser(array $data): string
    {
        $uuid   = $this->generateUuid();
        $roleId = $this->getDefaultRoleId('RENTER');

        $stmt = $this->db->prepare('
            INSERT INTO users
                (user_id, role_id, username, email, full_name, avatar_url,
                 google_id, auth_provider, is_oauth_user, oauth_email_verified,
                 is_active, is_banned, identity_verified, token_version, version,
                 created_at, updated_at, last_login)
            VALUES
                (UUID_TO_BIN(:uuid), :role_id, :username, :email, :full_name, :avatar_url,
                 :google_id, "GOOGLE", 1, :email_verified,
                 1, 0, 0, 0, 0,
                 NOW(), NOW(), NOW())
        ');

        $stmt->execute([
            ':uuid'           => $uuid,
            ':role_id'        => $roleId,
            ':username'       => $this->generateUsername($data['email']),
            ':email'          => $data['email'],
            ':full_name'      => $data['name'] ?: explode('@', $data['email'])[0],
            ':avatar_url'     => $data['picture'] ?: null,
            ':google_id'      => $data['id'],
            ':email_verified' => (int)$data['email_verified'],
        ]);

        return $uuid;
    }

    /**
     * Link Google account vào user đã có (tìm qua email).
     */
    public function linkGoogleAccount(string $userId, array $googleData): void
    {
        $stmt = $this->db->prepare('
            UPDATE users SET
                google_id            = :google_id,
                auth_provider        = "GOOGLE",
                is_oauth_user        = 1,
                oauth_email_verified = :email_verified,
                avatar_url           = COALESCE(NULLIF(:avatar_url, ""), avatar_url),
                updated_at           = NOW()
            WHERE user_id = UUID_TO_BIN(:user_id)
        ');
        $stmt->execute([
            ':google_id'      => $googleData['id'],
            ':email_verified' => (int)$googleData['email_verified'],
            ':avatar_url'     => $googleData['picture'] ?: '',
            ':user_id'        => $userId,
        ]);
    }

    /**
     * Cập nhật avatar & last_login mỗi lần login.
     */
    public function updateLoginInfo(string $userId, string $avatarUrl): void
    {
        $stmt = $this->db->prepare('
            UPDATE users SET
                last_login = NOW(),
                avatar_url = COALESCE(NULLIF(:avatar_url, ""), avatar_url),
                updated_at = NOW()
            WHERE user_id = UUID_TO_BIN(:user_id)
        ');
        $stmt->execute([
            ':avatar_url' => $avatarUrl,
            ':user_id'    => $userId,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────

    private function getDefaultRoleId(string $roleName): string
    {
        $stmt = $this->db->prepare('SELECT role_id FROM roles WHERE role_name = ? LIMIT 1');
        $stmt->execute([$roleName]);
        $row = $stmt->fetch();
        if (!$row) throw new \RuntimeException("Role '$roleName' not found in DB");
        return $row['role_id'];
    }

    private function generateUsername(string $email): string
    {
        $base     = preg_replace('/[^a-z0-9]/', '', strtolower(explode('@', $email)[0]));
        $base     = $base ?: 'user';
        $username = $base;
        $i        = 1;

        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE username = ? LIMIT 1');
        while (true) {
            $stmt->execute([$username]);
            if (!$stmt->fetch()) break;
            $username = $base . $i++;
        }
        return $username;
    }

    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}