<?php

namespace Controllers;

use Core\SessionManager;
use Services\IdentityVerificationService;
use Models\IdentityVerification;
use Repositories\UserRepository;

class IdentityVerificationController
{
    private IdentityVerificationService $service;
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->service  = new IdentityVerificationService();
        $this->userRepo = new UserRepository();
    }

    /** POST /api/identity/submit */
    public function submit(): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        if (!$user) {
            $this->json(['error' => 'Chưa đăng nhập'], 401);
            return;
        }

        $phone     = trim($_POST['phone_number']      ?? '');
        $docType   = trim($_POST['document_type']     ?? '');
        $frontUrl  = trim($_POST['front_image_url']   ?? '');
        $backUrl   = trim($_POST['back_image_url']    ?? '');
        $selfieUrl = trim($_POST['selfie_image_url']  ?? '');

        $errors = [];
        if (!$phone)     $errors[] = 'Số điện thoại không được để trống';
        if (!$docType)   $errors[] = 'Loại giấy tờ không được để trống';
        if (!$frontUrl)  $errors[] = 'Ảnh mặt trước không được để trống';
        if (!$backUrl)   $errors[] = 'Ảnh mặt sau không được để trống';
        if (!$selfieUrl) $errors[] = 'Ảnh xác thực khuôn mặt không được để trống';

        if ($errors) {
            $this->json(['error' => implode(', ', $errors)], 422);
            return;
        }

        if (!in_array($docType, IdentityVerification::ALLOWED_DOC_TYPES, true)) {
            $this->json(['error' => 'Loại giấy tờ không hợp lệ'], 422);
            return;
        }

        if (!preg_match('/^(0|\+84)[0-9]{8,10}$/', $phone)) {
            $this->json(['error' => 'Số điện thoại không hợp lệ'], 422);
            return;
        }

        try {
            $result = $this->service->submit($user['user_id'], [
                'phone_number'     => $phone,
                'document_type'    => $docType,
                'front_image_url'  => $frontUrl,
                'back_image_url'   => $backUrl,
                'selfie_image_url' => $selfieUrl,
            ]);

            // Nếu user chưa có số điện thoại trong profile thì lưu luôn
            if (empty($user['phone_number'])) {
                $this->userRepo->updatePhone($user['user_id'], $phone);
            }

            $this->json(['success' => true, 'message' => 'Đã gửi yêu cầu xác thực. Vui lòng chờ phê duyệt.', 'data' => $result]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** GET /api/identity/status */
    public function status(): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        if (!$user) {
            $this->json(['error' => 'Chưa đăng nhập'], 401);
            return;
        }

        $this->json(['data' => $this->service->getStatus($user['user_id'])]);
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}