<?php

namespace Services;

use Repositories\EditRequestRepository;
use Repositories\RoomRepository;
use Repositories\AdminRepository;
use Services\NotificationService;

class EditRequestService
{
    private EditRequestRepository $editRepo;
    private RoomRepository        $roomRepo;
    private AdminRepository       $adminRepo;

    public function __construct()
    {
        $this->editRepo  = new EditRequestRepository();
        $this->roomRepo  = new RoomRepository();
        $this->adminRepo = new AdminRepository();
    }

    // ── Landlord: Lấy dữ liệu phòng để hiển thị form edit ────────────────

    public function getRoomForEdit(int $roomId, string $landlordId): ?array
    {
        $room = $this->editRepo->findRoomForEdit($roomId, $landlordId);
        if (!$room) return null;

        $room['images']    = $this->roomRepo->findImages($roomId);
        $room['amenities'] = $this->roomRepo->findAmenities($roomId);
        $room['has_pending_edit'] = $this->editRepo->hasPendingRequest($roomId);

        return $room;
    }

    // ── Landlord: Gửi yêu cầu chỉnh sửa ─────────────────────────────────

    public function submitEditRequest(string $landlordId, int $roomId, array $input): void
    {
        // Kiểm tra ownership
        $room = $this->editRepo->findRoomForEdit($roomId, $landlordId);
        if (!$room) {
            throw new \RuntimeException('Không tìm thấy tin đăng hoặc bạn không có quyền chỉnh sửa.');
        }

        // Không cho submit nếu đã có yêu cầu đang chờ
        if ($this->editRepo->hasPendingRequest($roomId)) {
            throw new \RuntimeException('Tin đăng này đã có yêu cầu chỉnh sửa đang chờ duyệt. Vui lòng chờ Admin xử lý trước.');
        }

        $this->editRepo->create($roomId, $landlordId, $input);

        $this->editRepo->setRoomEditPending($roomId, true);

        $admins = $this->adminRepo->getAllAdminIds();
        $notif  = new NotificationService();
        foreach ($admins as $adminId) {
            $notif->send(
                $adminId,
                'Yêu cầu chỉnh sửa tin',
                "Chủ nhà yêu cầu chỉnh sửa tin: \"{$room['title']}\"",
                'admin_edit_request',
                '/admin/dashboard#pending'
            );
        }
    }

    // ── Admin: Lấy danh sách yêu cầu chờ duyệt ───────────────────────────

    public function getPendingEditRequests(): array
    {
        $rows = $this->editRepo->findPending();
        foreach ($rows as &$row) {
            $row['new_data'] = json_decode($row['new_data'], true);
        }
        return $rows;
    }

    // ── Admin: Duyệt chỉnh sửa ────────────────────────────────────────────

    public function approveEditRequest(int $requestId): void
    {
        $req = $this->editRepo->findById($requestId);
        if (!$req) {
            throw new \RuntimeException('Không tìm thấy yêu cầu chỉnh sửa.');
        }
        if ($req['status'] !== 'pending') {
            throw new \RuntimeException('Yêu cầu này đã được xử lý rồi.');
        }

        $newData = json_decode($req['new_data'], true);
        $roomId  = (int) $req['room_id'];

        $db = (new \Core\Database())->getInstance();
        $db->beginTransaction();
        try {
            $this->editRepo->applyToRoom($roomId, $newData);   // set edit_pending=0, is_approved=1
            $this->editRepo->setStatus($requestId, 'approved');
            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }

        // Thông báo chủ nhà
        $notif = new NotificationService();
        $notif->send(
            $req['room_landlord_bin'],
            'Chỉnh sửa tin đã được duyệt! ✅',
            "Tin \"{$req['current_title']}\" của bạn đã được phê duyệt và hiển thị trở lại.",
            'edit_approved',
            "/room/{$roomId}"
        );
    }

    // ── Admin: Từ chối chỉnh sửa ──────────────────────────────────────────

    public function rejectEditRequest(int $requestId, string $reason): void
    {
        $req = $this->editRepo->findById($requestId);
        if (!$req) {
            throw new \RuntimeException('Không tìm thấy yêu cầu chỉnh sửa.');
        }
        if ($req['status'] !== 'pending') {
            throw new \RuntimeException('Yêu cầu này đã được xử lý rồi.');
        }

        $roomId = (int) $req['room_id'];

        $this->editRepo->setStatus($requestId, 'rejected', $reason);

        $this->editRepo->setRoomEditPending($roomId, false);

        $notif = new NotificationService();
        $notif->send(
            $req['room_landlord_bin'],
            'Yêu cầu chỉnh sửa bị từ chối ❌',
            "Lý do: {$reason}. Tin của bạn vẫn hiển thị với nội dung cũ.",
            'edit_rejected',
            '/landlord/listings'
        );
    }
}