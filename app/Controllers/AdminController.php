<?php

namespace Controllers;

use Core\SessionManager;
use Services\AdminService;
use Services\EditRequestService;

/**
 * AdminController – chỉ xử lý auth guard + parse request + trả JSON.
 */
class AdminController
{
    private AdminService $adminService;

    public function __construct()
    {
        $this->adminService = new AdminService();
    }

    // ── Identity ──────────────────────────────────────────────────────────

    public function identityList(): void
    {
        $this->requireAdmin();
        $this->json(['data' => $this->adminService->getPendingIdentities()]);
    }

    public function identityApprove(): void
    {
        $this->requireAdmin();
        $input = $this->parseJsonBody();
        $id    = (int) ($input['verification_id'] ?? 0);

        if (!$id) {
            $this->json(['error' => 'Thiếu verification_id'], 422);
            return;
        }

        try {
            $this->adminService->approveIdentity($id);
            $this->json(['success' => true, 'message' => 'Đã duyệt xác thực danh tính']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function identityReject(): void
    {
        $this->requireAdmin();
        $input  = $this->parseJsonBody();
        $id     = (int) ($input['verification_id'] ?? 0);
        $reason = trim($input['reason'] ?? '');

        if (!$id || !$reason) {
            $this->json(['error' => 'Thiếu verification_id hoặc lý do'], 422);
            return;
        }

        try {
            $this->adminService->rejectIdentity($id, $reason);
            $this->json(['success' => true, 'message' => 'Đã từ chối xác thực']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── Rooms ─────────────────────────────────────────────────────────────

    public function roomPendingList(): void
    {
        $this->requireAdmin();
        $this->json(['data' => $this->adminService->getPendingRooms()]);
    }

    public function roomApprove(): void
    {
        $this->requireAdmin();
        $input  = $this->parseJsonBody();
        $roomId = (int) ($input['room_id'] ?? 0);

        if (!$roomId) {
            $this->json(['error' => 'Thiếu room_id'], 422);
            return;
        }

        try {
            $this->adminService->approveRoom($roomId);
            $this->json(['success' => true, 'message' => 'Đã duyệt bài đăng']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function roomReject(): void
    {
        $this->requireAdmin();
        $input  = $this->parseJsonBody();
        $roomId = (int) ($input['room_id'] ?? 0);
        $reason = trim($input['reason'] ?? '');

        if (!$roomId || !$reason) {
            $this->json(['error' => 'Thiếu room_id hoặc lý do'], 422);
            return;
        }

        try {
            $this->adminService->rejectRoom($roomId, $reason);
            $this->json(['success' => true, 'message' => 'Đã từ chối bài đăng']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── Edit Requests ──────────────────────────────────────────────────────

    /** GET /api/admin/edit-requests/pending */
    public function editRequestList(): void
    {
        $this->requireAdmin();
        try {
            $svc = new EditRequestService();
            $this->json(['data' => $svc->getPendingEditRequests()]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** POST /api/admin/edit-requests/approve */
    public function editRequestApprove(): void
    {
        $this->requireAdmin();
        $input     = $this->parseJsonBody();
        $requestId = (int) ($input['request_id'] ?? 0);

        if (!$requestId) {
            $this->json(['error' => 'Thiếu request_id'], 422);
            return;
        }

        try {
            $svc = new EditRequestService();
            $svc->approveEditRequest($requestId);
            $this->json(['success' => true, 'message' => 'Đã duyệt yêu cầu chỉnh sửa']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** POST /api/admin/edit-requests/reject */
    public function editRequestReject(): void
    {
        $this->requireAdmin();
        $input     = $this->parseJsonBody();
        $requestId = (int) ($input['request_id'] ?? 0);
        $reason    = trim($input['reason'] ?? '');

        if (!$requestId || !$reason) {
            $this->json(['error' => 'Thiếu request_id hoặc lý do'], 422);
            return;
        }

        try {
            $svc = new EditRequestService();
            $svc->rejectEditRequest($requestId, $reason);
            $this->json(['success' => true, 'message' => 'Đã từ chối yêu cầu chỉnh sửa']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function requireAdmin(): array
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        if (!$user || ($user['role'] ?? '') !== 'ADMIN') {
            $this->json(['error' => 'Không có quyền truy cập'], 403);
            exit;
        }
        return $user;
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