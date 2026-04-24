<?php

namespace Controllers;

use Core\SessionManager;
use Services\RoomService;
use Services\IdentityVerificationService;
use Models\IdentityVerification;

/**
 * RoomController – chỉ xử lý HTTP: parse request, validate input, trả JSON.
 * Không chứa bất kỳ SQL hay business logic nào.
 */
class RoomController
{
    private RoomService $roomService;
    private IdentityVerificationService $identityService;

    public function __construct()
    {
        $this->roomService     = new RoomService();
        $this->identityService = new IdentityVerificationService();
    }

    /** GET /api/check-post-eligibility */
    public function checkEligibility(): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();

        if (!$user) {
            $this->json(['eligible' => false, 'reason' => 'not_logged_in'], 401);
            return;
        }

        $status = $this->identityService->getStatus($user['user_id']);

        if (!$status) {
            $this->json(['eligible' => false, 'reason' => 'not_verified', 'redirect' => '/verify-identity']);
            return;
        }

        if ($status['status'] === IdentityVerification::STATUS_PENDING) {
            $this->json([
                'eligible'       => false,
                'reason'         => 'identity_pending',
                'redirect'       => '/verify-identity',
                'pending_status' => $status['status'],
            ]);
            return;
        }

        if ($status['status'] === IdentityVerification::STATUS_REJECTED) {
            $this->json([
                'eligible'      => false,
                'reason'        => 'identity_rejected',
                'redirect'      => '/verify-identity',
                'reject_reason' => $status['reject_reason'] ?? '',
            ]);
            return;
        }

        $this->json(['eligible' => true]);
    }

    /** POST /api/rooms */
    public function store(): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();

        if (!$user) {
            $this->json(['error' => 'Chưa đăng nhập'], 401);
            return;
        }

        $idStatus = $this->identityService->getStatus($user['user_id']);
        if (!$idStatus || $idStatus['status'] !== IdentityVerification::STATUS_APPROVED) {
            $this->json(['error' => 'Bạn cần xác thực danh tính trước khi đăng tin'], 403);
            return;
        }

        $input  = $this->parseJsonBody();
        $errors = $this->validateRoomInput($input);

        if ($errors) {
            $this->json(['error' => implode(', ', $errors)], 422);
            return;
        }

        try {
            $roomId = $this->roomService->create($user['user_id'], $input);
            $this->json([
                'success' => true,
                'message' => 'Đăng tin thành công! Bài đăng đang chờ phê duyệt từ quản trị viên.',
                'room_id' => $roomId,
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** GET /api/rooms/public */
    public function publicList(): void
    {
        $rooms = $this->roomService->getPublicList($_GET);
        $this->json(['data' => $rooms, 'total' => count($rooms)]);
    }

    /** GET /api/rooms/{id} */
    public function show(int $id): void
    {
        $room = $this->roomService->getPublicDetail($id);
        if (!$room) {
            $this->json(['error' => 'Không tìm thấy phòng'], 404);
            return;
        }
        $this->json(['data' => $room]);
    }

    /** GET /api/landlord/rooms */
    public function landlordList(): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        if (!$user) {
            $this->json(['error' => 'Chưa đăng nhập'], 401);
            return;
        }

        $rooms = $this->roomService->getLandlordRooms($user['user_id']);
        $this->json(['data' => $rooms]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function validateRoomInput(array $input): array
    {
        $errors = [];
        if (empty($input['title']))           $errors[] = 'Tiêu đề không được để trống';
        if (empty($input['price_per_month'])) $errors[] = 'Giá thuê không được để trống';
        if (empty($input['area_size']))       $errors[] = 'Diện tích không được để trống';
        if (empty($input['room_type']))       $errors[] = 'Loại phòng không được để trống';
        if (empty($input['capacity']))        $errors[] = 'Sức chứa không được để trống';
        if (empty($input['images']) || !is_array($input['images']) || count($input['images']) === 0) {
            $errors[] = 'Cần ít nhất 1 ảnh phòng';
        }
        if (empty($input['address']['city_name']))      $errors[] = 'Tỉnh/thành phố không được để trống';
        if (empty($input['address']['district_name']))  $errors[] = 'Quận/huyện không được để trống';
        if (empty($input['address']['ward_name']))      $errors[] = 'Phường/xã không được để trống';
        if (empty($input['address']['street_address'])) $errors[] = 'Địa chỉ cụ thể không được để trống';
        return $errors;
    }

    public function getAmenities(): void
{
    try {
        $roomRepo = new \Repositories\RoomRepository();
        $amenities = $roomRepo->getAllAmenities();

        header('Content-Type: application/json');
        echo json_encode(['data' => $amenities], JSON_UNESCAPED_UNICODE);
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

    private function parseJsonBody(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}