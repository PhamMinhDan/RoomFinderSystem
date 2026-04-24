<?php

declare(strict_types=1);

namespace Controllers;

use Core\SessionManager;
use Repositories\FavouriteRepository;

class FavouriteController
{
    private FavouriteRepository $repo;

    public function __construct()
    {
        $this->repo = new FavouriteRepository();
    }

    public function toggle(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        SessionManager::start();
        $user = SessionManager::getUser();

        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để lưu phòng.']);
            return;
        }

        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $roomId = isset($body['room_id']) ? (int) $body['room_id'] : 0;

        if ($roomId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'room_id không hợp lệ.']);
            return;
        }

        $userId = $user['user_id'];

        try {
            $isFaved = $this->repo->exists($userId, $roomId);

            if ($isFaved) {
                $this->repo->remove($userId, $roomId);
                echo json_encode([
                    'success' => true,
                    'faved'   => false,
                    'message' => 'Đã bỏ lưu phòng.',
                ]);
            } else {
                $this->repo->add($userId, $roomId);
                echo json_encode([
                    'success' => true,
                    'faved'   => true,
                    'message' => 'Đã lưu phòng yêu thích!',
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại.']);
        }
    }

    public function index(): void
    {
        ini_set('display_errors', '1');
        error_reporting(E_ALL);
        
        if (ob_get_length()) ob_clean(); 

        header('Content-Type: application/json; charset=utf-8');

        $user = \Core\SessionManager::getUser();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User session not found']);
            exit;
        }

        try {
            if (!isset($this->repo)) {
                throw new \Exception("FavouriteRepository không được khởi tạo.");
            }

            $userId = $user['user_id'];
            $rooms = $this->repo->listByUser($userId);

            if (empty($rooms)) {
                echo json_encode([
                    'success' => true, 
                    'data' => [], 
                    'debug_info' => 'Query thành công nhưng không có dữ liệu cho user: ' . bin2hex($userId)
                ]);
                exit;
            }

            echo json_encode(['success' => true, 'data' => $rooms]);
            exit;

        } catch (\Throwable $e) {
            http_response_code(200); 
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi phát sinh: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            exit;
        }
    }

    public function ids(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        SessionManager::start();
        $user = SessionManager::getUser();

        if (!$user) {
            echo json_encode(['success' => true, 'ids' => []]);
            return;
        }

        try {
            $ids = $this->repo->idsByUser($user['user_id']);
            echo json_encode(['success' => true, 'ids' => $ids]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'ids' => []]);
        }
    }
}