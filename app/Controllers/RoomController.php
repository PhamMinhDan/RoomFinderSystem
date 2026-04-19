<?php

namespace Controllers;

use Core\SessionManager;
use Services\RoomService;
use Services\IdentityVerificationService;

class RoomController
{
    private RoomService $roomService;
    private IdentityVerificationService $identityService;

    public function __construct()
    {
        $this->roomService     = new RoomService();
        $this->identityService = new IdentityVerificationService();
    }

    /**
     * GET /api/check-post-eligibility
     * Kiểm tra người dùng đã xác thực chưa (dùng trước khi vào trang đăng tin)
     */
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
            $this->json([
                'eligible' => false,
                'reason'   => 'not_verified',
                'redirect' => '/verify-identity',
            ]);
            return;
        }

        if (!$status || $status['status'] === 'pending') {
            $this->json([
                'eligible'       => false,
                'reason'         => 'identity_pending',
                'redirect'       => '/verify-identity',
                'pending_status' => $status['status'] ?? null,
            ]);
            return;
        }

        if ($status['status'] === 'rejected') {
            $this->json([
                'eligible'      => false,
                'reason'        => 'identity_rejected',
                'redirect'      => '/verify-identity',
                'reject_reason' => $status['reject_reason'] ?? '',
            ]);
            return;
        }

        // approved
        $this->json(['eligible' => true]);
    }

    /**
     * POST /api/rooms
     * Tạo bài đăng phòng trọ mới (status = pending)
     */
    public function store(): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();

        if (!$user) {
            $this->json(['error' => 'Chưa đăng nhập'], 401);
            return;
        }

        // Kiểm tra xác thực danh tính
        $idStatus = $this->identityService->getStatus($user['user_id']);
        if (!$idStatus || $idStatus['status'] !== 'approved') {
            $this->json(['error' => 'Bạn cần xác thực danh tính trước khi đăng tin'], 403);
            return;
        }

        $input = $this->parseJsonBody();

        // Validate
        $errors = [];
        if (empty($input['title']))            $errors[] = 'Tiêu đề không được để trống';
        if (empty($input['price_per_month']))  $errors[] = 'Giá thuê không được để trống';
        if (empty($input['area_size']))        $errors[] = 'Diện tích không được để trống';
        if (empty($input['room_type']))        $errors[] = 'Loại phòng không được để trống';
        if (empty($input['capacity']))         $errors[] = 'Sức chứa không được để trống';
        if (empty($input['images']) || !is_array($input['images']) || count($input['images']) === 0) {
            $errors[] = 'Cần ít nhất 1 ảnh phòng';
        }
        if (empty($input['address']['city_name']))    $errors[] = 'Tỉnh/thành phố không được để trống';
        if (empty($input['address']['district_name'])) $errors[] = 'Quận/huyện không được để trống';
        if (empty($input['address']['ward_name']))    $errors[] = 'Phường/xã không được để trống';
        if (empty($input['address']['street_address'])) $errors[] = 'Địa chỉ cụ thể không được để trống';

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

    /**
     * GET /api/rooms/public
     * Lấy danh sách phòng đã duyệt, còn hạn (dùng cho homepage, search)
     */
    public function publicList(): void
    {
        $params = $_GET;
        $rooms  = $this->roomService->getPublicList($params);
        $this->json(['data' => $rooms, 'total' => count($rooms)]);
    }

    /**
     * GET /api/rooms/{id}
     * Chi tiết một phòng
     */
    public function show(int $id): void
    {
        $room = $this->roomService->getPublicDetail($id);
        if (!$room) {
            $this->json(['error' => 'Không tìm thấy phòng'], 404);
            return;
        }
        $this->json(['data' => $room]);
    }

    /**
     * GET /api/landlord/rooms
     * Lấy danh sách tin của landlord hiện tại (có phân loại theo status)
     */
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

    // ── ADMIN ROUTES ──────────────────────────────────────────────────────

    /**
     * GET /api/admin/rooms/pending
     * Admin: danh sách phòng chờ duyệt
     */
    public function adminPendingList(): void
    {
        SessionManager::start();
        $this->requireAdmin();

        $rooms = $this->roomService->getPendingRooms();
        $this->json(['data' => $rooms]);
    }

    /**
     * POST /api/admin/rooms/approve
     * Admin: duyệt bài đăng (+15 ngày display_until)
     */
    public function adminApprove(): void
    {
        SessionManager::start();
        $adminUser = $this->requireAdmin();

        $input  = $this->parseJsonBody();
        $roomId = (int)($input['room_id'] ?? 0);

        if (!$roomId) {
            $this->json(['error' => 'Thiếu room_id'], 422);
            return;
        }

        try {
            $this->roomService->approve($roomId, $adminUser['user_id']);
            $this->json(['success' => true, 'message' => 'Đã duyệt bài đăng. Bài sẽ hiển thị trong 15 ngày.']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/admin/rooms/reject
     * Admin: từ chối bài đăng
     */
    public function adminReject(): void
    {
        SessionManager::start();
        $adminUser = $this->requireAdmin();

        $input  = $this->parseJsonBody();
        $roomId = (int)($input['room_id'] ?? 0);
        $reason = trim($input['reason'] ?? '');

        if (!$roomId || !$reason) {
            $this->json(['error' => 'Thiếu room_id hoặc lý do từ chối'], 422);
            return;
        }

        try {
            $this->roomService->reject($roomId, $adminUser['user_id'], $reason);
            $this->json(['success' => true, 'message' => 'Đã từ chối bài đăng']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function requireAdmin(): array
    {
        $user = SessionManager::getUser();
        if (!$user || ($user['role'] ?? '') !== 'ADMIN') {
            $this->json(['error' => 'Không có quyền truy cập'], 403);
            exit;
        }
        return $user;
    }

    private function parseJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?? [];
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}