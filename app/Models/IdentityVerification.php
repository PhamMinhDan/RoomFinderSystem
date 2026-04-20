<?php

namespace Models;

class IdentityVerification extends BaseModel
{
    protected static string $table      = 'identity_verifications';
    protected static string $primaryKey = 'verification_id';

    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const DOC_CCCD           = 'cccd';
    const DOC_PASSPORT       = 'passport';
    const DOC_DRIVER_LICENSE = 'driver_license';

    const ALLOWED_DOC_TYPES = [
        self::DOC_CCCD,
        self::DOC_PASSPORT,
        self::DOC_DRIVER_LICENSE,
    ];

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}