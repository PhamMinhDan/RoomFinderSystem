<?php

namespace Models;

class User
{
    // ── Identity ──────────────────────────────────────────────
    public ?string $userId            = null;   
    public ?string $username          = null;
    public ?string $email             = null;
    public ?string $fullName          = null;
    public ?string $phoneNumber       = null;
    public ?string $avatarUrl         = null;
    public ?string $bio               = null;

    // ── Role ──────────────────────────────────────────────────
    public ?string $roleName          = null; 

    // ── Address ───────────────────────────────────────────────
    public ?int    $addressId         = null;
    public ?string $streetAddress     = null;
    public ?string $cityName          = null;
    public ?string $districtName      = null;
    public ?string $wardName          = null;
    public ?float  $latitude          = null;
    public ?float  $longitude         = null;
    public ?bool   $isPrimary         = null;

    // ── Status ────────────────────────────────────────────────
    public bool    $isActive          = true;
    public bool    $isBanned          = false;
    public ?string $banReason         = null;
    public ?string $bannedAt          = null;   

    // ── Identity Verification ─────────────────────────────────
    public bool    $identityVerified  = false;
    public ?string $identityVerifiedAt = null;  

    // ── OAuth / Auth ──────────────────────────────────────────
    public ?string $authProvider      = null;   
    public ?string $googleId          = null;
    public bool    $isOAuthUser       = false;
    public bool    $oauthEmailVerified = false;

    // ── Audit ─────────────────────────────────────────────────
    public ?string $lastLogin         = null;
    public ?string $createdAt         = null;  
    public ?string $updatedAt         = null;   

    public int     $tokenVersion      = 0;
    public int     $version           = 0;


    public static function fromDbRow(array $row): self
    {
        $user = new self();

        $user->userId             = self::binToUuid($row['user_id']   ?? '');
        $user->username           = $row['username']                  ?? null;
        $user->email              = $row['email']                     ?? null;
        $user->fullName           = $row['full_name']                 ?? null;
        $user->phoneNumber        = $row['phone_number']              ?? null;
        $user->avatarUrl          = $row['avatar_url']                ?? null;
        $user->bio                = $row['bio']                       ?? null;

        // Role
        $user->roleName           = $row['role_name']                 ?? 'RENTER';

        // Address
        $user->addressId          = isset($row['address_id'])  ? (int)$row['address_id']  : null;
        $user->streetAddress      = $row['street_address']            ?? null;
        $user->cityName           = $row['city_name']                 ?? null;
        $user->districtName       = $row['district_name']             ?? null;
        $user->wardName           = $row['ward_name']                 ?? null;
        $user->latitude           = isset($row['latitude'])   ? (float)$row['latitude']   : null;
        $user->longitude          = isset($row['longitude'])  ? (float)$row['longitude']  : null;
        $user->isPrimary          = isset($row['is_primary'])  ? (bool)$row['is_primary']  : null;

        // Status
        $user->isActive           = (bool)($row['is_active']          ?? true);
        $user->isBanned           = (bool)($row['is_banned']          ?? false);
        $user->banReason          = $row['ban_reason']                ?? null;
        $user->bannedAt           = $row['banned_at']                 ?? null;

        // Identity
        $user->identityVerified   = (bool)($row['identity_verified']  ?? false);
        $user->identityVerifiedAt = $row['identity_verified_at']      ?? null;

        // OAuth
        $user->authProvider       = $row['auth_provider']             ?? null;
        $user->googleId           = $row['google_id']                 ?? null;
        $user->isOAuthUser        = (bool)($row['is_oauth_user']      ?? false);
        $user->oauthEmailVerified = (bool)($row['oauth_email_verified'] ?? false);

        // Audit
        $user->lastLogin          = $row['last_login']                ?? null;
        $user->createdAt          = $row['created_at']                ?? null;
        $user->updatedAt          = $row['updated_at']                ?? null;

        // Internal
        $user->tokenVersion       = (int)($row['token_version']       ?? 0);
        $user->version            = (int)($row['version']             ?? 0);

        return $user;
    }
    public function toArray(bool $includePrivate = false): array
    {
        $data = [
            // Identity
            'user_id'               => $this->userId,
            'username'              => $this->username,
            'email'                 => $this->email,
            'full_name'             => $this->fullName,
            'phone_number'          => $this->phoneNumber,
            'avatar_url'            => $this->avatarUrl,
            'bio'                   => $this->bio,

            // Role
            'role_name'             => $this->roleName,

            // Address
            'address'               => $this->buildAddressArray(),

            // Identity Verification
            'identity_verified'     => $this->identityVerified,
            'identity_verified_at'  => $this->identityVerifiedAt,

            // Status
            'is_active'             => $this->isActive,
            'is_banned'             => $this->isBanned,
            'ban_reason'            => $this->banReason,
            'banned_at'             => $this->bannedAt,

            // OAuth
            'auth_provider'         => $this->authProvider,
            'is_oauth_user'         => $this->isOAuthUser,

            // Audit
            'last_login'            => $this->lastLogin,
            'created_at'            => $this->createdAt,
        ];

        // Các field nhạy cảm — chỉ expose khi cần (vd: admin)
        if ($includePrivate) {
            $data['google_id']            = $this->googleId;
            $data['oauth_email_verified'] = $this->oauthEmailVerified;
            $data['token_version']        = $this->tokenVersion;
            $data['version']              = $this->version;
            $data['updated_at']           = $this->updatedAt;
        }

        return $data;
    }
    public function toSessionArray(): array
    {
        return [
            'user_id'           => $this->userId,
            'username'          => $this->username,
            'full_name'         => $this->fullName,
            'email'             => $this->email,
            'phone_number'      => $this->phoneNumber,
            'avatar_url'        => $this->avatarUrl,
            'role'              => $this->roleName,
            'is_banned'         => $this->isBanned,
            'identity_verified' => $this->identityVerified,
            'auth_provider'     => $this->authProvider,
            'is_oauth_user'     => $this->isOAuthUser,
        ];
    }

    // ─────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────
    private function buildAddressArray(): ?array
    {
        if ($this->addressId === null) return null;

        return [
            'address_id'     => $this->addressId,
            'street_address' => $this->streetAddress,
            'city_name'      => $this->cityName,
            'district_name'  => $this->districtName,
            'ward_name'      => $this->wardName,
            'latitude'       => $this->latitude,
            'longitude'      => $this->longitude,
            'is_primary'     => $this->isPrimary,
        ];
    }

    private static function binToUuid(string $bin): string
    {
        if (strlen($bin) === 36 && substr_count($bin, '-') === 4) return $bin;
        if (strlen($bin) === 32) return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($bin, 4));
        $hex = bin2hex($bin);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($hex, 4));
    }
}