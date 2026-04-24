<?php

namespace Controllers;

use Core\SessionManager;
use Services\EditRequestService;

/**
 * RoomEditController
 * Xử lý 2 endpoint:
 *   GET  /api/landlord/rooms/{id}/edit-data    – trả dữ liệu phòng để pre-fill form
 *   POST /api/landlord/rooms/{id}/edit-request  – nhận yêu cầu chỉnh sửa từ chủ nhà
 */
class RoomEditController
{
    private EditRequestService $editService;

    public function __construct()
    {
        $this->editService = new EditRequestService();
    }

    // ─────────────────────────────────────────────────────────────────────
    //  GET /api/landlord/rooms/{id}/edit-data
    // ─────────────────────────────────────────────────────────────────────

    public function getEditData(int $roomId): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();

        if (!$user) {
            $this->json(['error' => 'Chưa đăng nhập'], 401);
            return;
        }

        $data = $this->editService->getRoomForEdit($roomId, $user['user_id']);

        if (!$data) {
            $this->json(['error' => 'Không tìm thấy tin đăng hoặc bạn không có quyền truy cập.'], 404);
            return;
        }

        $this->json(['data' => $data]);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  POST /api/landlord/rooms/{id}/edit-request
    // ─────────────────────────────────────────────────────────────────────

    public function submitEdit(int $roomId): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();

        if (!$user) {
            $this->json(['error' => 'Chưa đăng nhập'], 401);
            return;
        }

        $input  = $this->parseJsonBody();
        $errors = $this->validateInput($input);

        if ($errors) {
            $this->json(['error' => implode(', ', $errors)], 422);
            return;
        }

        try {
            $this->editService->submitEditRequest($user['user_id'], $roomId, $input);
            $this->json([
                'success' => true,
                'message' => 'Yêu cầu chỉnh sửa đã được gửi! Tin của bạn sẽ tạm ẩn và được Admin xem xét trong 1–24 giờ.',
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function validateInput(array $input): array
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