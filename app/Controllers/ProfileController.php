<?php

namespace Controllers;

use Core\SessionManager;
use Repositories\UserRepository;
use Repositories\AddressRepository;
use Throwable;

class ProfileController
{
    private UserRepository    $userRepo;
    private AddressRepository $addressRepo;

    public function __construct()
    {
        $this->userRepo    = new UserRepository();
        $this->addressRepo = new AddressRepository();
    }

    // ── GET /profile ──────────────────────────────────────────
    public function index(): void
    {
        SessionManager::start();
        $sessionUser = SessionManager::getUser();
        if (!$sessionUser) {
            header('Location: /?auth_error=' . urlencode('Vui lòng đăng nhập.'));
            exit;
        }
        require dirname(__DIR__) . '/app/Views/pages/profile.php';
    }

    // ── GET /api/profile ─────────────────────────────────────
    public function show(): void
    {
        header('Content-Type: application/json');
        SessionManager::start();

        $sessionUser = SessionManager::getUser();
        if (!$sessionUser) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập.']);
            exit;
        }

        $user = $this->userRepo->findById($sessionUser['user_id']);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng.']);
            exit;
        }

        echo json_encode(['success' => true, 'user' => $user->toArray()]);
        exit;
    }

    // ── PATCH /api/profile ────────────────────────────────────
    public function update(): void
    {
        header('Content-Type: application/json');
        SessionManager::start();

        $sessionUser = SessionManager::getUser();
        if (!$sessionUser) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập.']);
            exit;
        }

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        // Validate
        $errors = [];

        $fullName = trim($body['full_name'] ?? '');
        if ($fullName === '') {
            $errors['full_name'] = 'Họ tên không được để trống.';
        } elseif (mb_strlen($fullName) > 255) {
            $errors['full_name'] = 'Họ tên tối đa 255 ký tự.';
        }

        $phone = trim($body['phone_number'] ?? '');
        if ($phone !== '' && !preg_match('/^(0|\+84)[0-9]{9,10}$/', $phone)) {
            $errors['phone_number'] = 'Số điện thoại không hợp lệ.';
        }

        $bio = trim($body['bio'] ?? '');
        if (mb_strlen($bio) > 500) {
            $errors['bio'] = 'Bio tối đa 500 ký tự.';
        }

        if ($errors) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        try {
            $this->userRepo->updateProfile($sessionUser['user_id'], [
                'full_name'    => $fullName,
                'phone_number' => $phone ?: null,
                'bio'          => $bio ?: null,
            ]);

            $user = $this->userRepo->findById($sessionUser['user_id']);
            SessionManager::setUser($user->toSessionArray());

            echo json_encode(['success' => true, 'user' => $user->toArray()]);
        } catch (Throwable $e) {
            error_log('Profile update error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại. Vui lòng thử lại.']);
        }
        exit;
    }

    // ── PUT /api/profile/address ──────────────────────────────
    public function updateAddress(): void
    {
        header('Content-Type: application/json');
        SessionManager::start();

        $sessionUser = SessionManager::getUser();
        if (!$sessionUser) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập.']);
            exit;
        }

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $errors = [];

        $cityName     = trim($body['city_name']     ?? '');
        $districtName = trim($body['district_name'] ?? '');
        $wardName     = trim($body['ward_name']     ?? '');
        $streetAddr   = trim($body['street_address'] ?? '');
        $lat          = $body['latitude']  ?? null;
        $lng          = $body['longitude'] ?? null;

        if ($cityName === '')     $errors['city_name']     = 'Vui lòng chọn tỉnh/thành phố.';
        if ($districtName === '') $errors['district_name'] = 'Vui lòng chọn quận/huyện.';
        if ($wardName === '')     $errors['ward_name']     = 'Vui lòng chọn phường/xã.';

        if ($lat !== null && ($lat < -90  || $lat > 90))  $errors['latitude']  = 'Vĩ độ không hợp lệ.';
        if ($lng !== null && ($lng < -180 || $lng > 180)) $errors['longitude'] = 'Kinh độ không hợp lệ.';

        if ($errors) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        try {
            $userId = $sessionUser['user_id'];
            $user   = $this->userRepo->findById($userId);

            $addressData = [
                'city_name'      => $cityName,
                'district_name'  => $districtName,
                'ward_name'      => $wardName,
                'street_address' => $streetAddr ?: null,
                'latitude'       => $lat !== null ? (float)$lat : null,
                'longitude'      => $lng !== null ? (float)$lng : null,
            ];

            if ($user && $user->addressId) {
                $this->addressRepo->update($user->addressId, $addressData);
                $addressId = $user->addressId;
            } else {
                $addressId = $this->addressRepo->create($userId, $addressData);
                $this->userRepo->setAddressId($userId, $addressId);
            }

            $updatedUser = $this->userRepo->findById($userId);
            echo json_encode(['success' => true, 'user' => $updatedUser->toArray()]);
        } catch (Throwable $e) {
            error_log('Address update error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Cập nhật địa chỉ thất bại.']);
        }
        exit;
    }
}