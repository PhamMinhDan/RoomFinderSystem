<?php

namespace Controllers;

use Core\SessionManager;
use Models\User;
use Repositories\UserRepository;
use Services\AuthGoogleService;
use Throwable;

class AuthGoogleController
{
    private AuthGoogleService $googleService;
    private UserRepository    $userRepo;

    public function __construct()
    {
        $this->googleService = new AuthGoogleService();
        $this->userRepo      = new UserRepository();
    }

    // ── GET /auth/google ──────────────────────────────────────
    public function redirectToGoogle(): void
    {
        SessionManager::start();

        $state = bin2hex(random_bytes(16));
        SessionManager::setOAuthState($state);

        // Flush session xuống disk trước khi redirect
        // tránh state mismatch khi Google callback về
        session_write_close();

        $url = $this->googleService->buildAuthUrl($state);
        header('Location: ' . $url, true, 302);
        exit;
    }

    // ── GET /auth/google/callback ─────────────────────────────
    public function handleGoogleCallback(): void
    {
        SessionManager::start();

        if (isset($_GET['error'])) {
            $this->redirectWithError('Đăng nhập Google bị huỷ hoặc thất bại.');
            return;
        }

        $code  = $_GET['code']  ?? '';
        $state = $_GET['state'] ?? '';

        if (!$code || !$state) {
            $this->redirectWithError('Thiếu tham số từ Google.');
            return;
        }

        if (!SessionManager::verifyOAuthState($state)) {
            $this->redirectWithError('Yêu cầu không hợp lệ (state mismatch).');
            return;
        }

        try {
            // Lấy thông tin từ Google
            $googleUser = $this->googleService->handleCallback($code);

            // Tìm hoặc tạo User model
            $user = $this->findOrCreateUser($googleUser);

            // Kiểm tra bị ban
            if ($user->isBanned) {
                $reason = $user->banReason ?: 'Vi phạm quy định sử dụng dịch vụ';
                $this->redirectWithError("Tài khoản của bạn đã bị khoá: $reason");
                return;
            }

            // Cập nhật last_login + avatar
            $this->userRepo->updateLoginInfo($user->userId, $googleUser['picture']);

            if (!empty($googleUser['picture'])) {
                $user->avatarUrl = $googleUser['picture'];
            }

            SessionManager::setUser($user->toSessionArray());

            if ($this->wantsJson()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'user'    => $user->toArray(),   
                ]);
                exit;
            }

            // Redirect theo role
            $redirectUrl = $this->getRedirectUrlByRole($user->roleName);
            header('Location: ' . $redirectUrl, true, 302);
            exit;

        } catch (Throwable $e) {
            error_log('Google callback error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $this->redirectWithError('Đăng nhập thất bại. Vui lòng thử lại.');
        }
    }

    // ── POST /auth/logout ─────────────────────────────────────
    public function logout(): void
    {
        SessionManager::logout();

        if ($this->wantsJson()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Đã đăng xuất']);
            exit;
        }

        header('Location: /', true, 302);
        exit;
    }

    public function me(): void
    {
        header('Content-Type: application/json');

        $sessionUser = SessionManager::getUser();
        if (!$sessionUser) {
            http_response_code(401);
            echo json_encode(['loggedIn' => false]);
            exit;
        }

        // Load lại từ DB để có đầy đủ fields (kể cả address)
        $user = $this->userRepo->findById($sessionUser['user_id']);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['loggedIn' => false]);
            exit;
        }

        echo json_encode([
            'loggedIn' => true,
            'user'     => $user->toArray(),  
        ]);
        exit;
    }

    // ── Helpers ───────────────────────────────────────────────

    private function findOrCreateUser(array $googleUser): User
    {
        $user = $this->userRepo->findByGoogleId($googleUser['id']);
        if ($user) return $user;

        $user = $this->userRepo->findByEmail($googleUser['email']);
        if ($user) {
            $this->userRepo->linkGoogleAccount($user->userId, $googleUser);
            return $this->userRepo->findByEmail($googleUser['email']);
        }

        $newUuid = $this->userRepo->createGoogleUser($googleUser);
        return $this->userRepo->findById($newUuid);
    }

    private function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json');
    }

    private function redirectWithError(string $message): void
    {
        if ($this->wantsJson()) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }

        $encoded = urlencode($message);
        header("Location: /?auth_error=$encoded", true, 302);
        exit;
    }

    private function getRedirectUrlByRole(?string $role): string
    {
        return match($role) {
            'ADMIN'    => '/admin/dashboard',
            'LANDLORD' => '/',
            'RENTER'   => '/',
            default    => '/'
        };
    }
}