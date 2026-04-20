<?php

namespace Models;

/**
 * Room – ánh xạ bảng `rooms`
 */
class Room extends BaseModel
{
    protected static string $table      = 'rooms';
    protected static string $primaryKey = 'room_id';

    // ── Availability status ──────────────────────────────────────
    const STATUS_AVAILABLE   = 'available';
    const STATUS_RENTED      = 'rented';
    const STATUS_MAINTENANCE = 'maintenance';

    // ── Room type ────────────────────────────────────────────────
    const TYPE_SINGLE   = 'single';
    const TYPE_SHARED   = 'shared';
    const TYPE_MINI_APT = 'mini_apartment';

    // ── Furnish level ────────────────────────────────────────────
    const FURNISH_NONE  = 'none';
    const FURNISH_BASIC = 'basic';
    const FURNISH_FULL  = 'full';

    // ── Display status (computed, không có trong DB) ─────────────
    const DISPLAY_PENDING  = 'pending';
    const DISPLAY_ACTIVE   = 'active';
    const DISPLAY_REJECTED = 'rejected';
    const DISPLAY_EXPIRED  = 'expired';

    /** Tính display_status từ các cột is_approved, rejected_by_admin, display_until */
    public function getDisplayStatus(): string
    {
        if ($this->rejected_by_admin) return self::DISPLAY_REJECTED;
        if (!$this->is_approved)      return self::DISPLAY_PENDING;
        if ($this->display_until && strtotime($this->display_until) < time()) {
            return self::DISPLAY_EXPIRED;
        }
        return self::DISPLAY_ACTIVE;
    }

    public function isPubliclyVisible(): bool
    {
        return $this->getDisplayStatus() === self::DISPLAY_ACTIVE;
    }
}