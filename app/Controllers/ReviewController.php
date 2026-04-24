<?php

namespace Controllers;

use Core\SessionManager;
use Services\ReviewService;

class ReviewController
{
    private ReviewService $service;

    public function __construct()
    {
        $this->service = new ReviewService();
    }

    public function index(int $roomId): void
    {
        try {
            $page    = max(1, (int) ($_GET['page']     ?? 1));
            $perPage = min(20, max(1, (int) ($_GET['per_page'] ?? 5)));

            $result = $this->service->getReviews($roomId, $page, $perPage);
            $this->json($result);
        } catch (\Exception $e) {
            error_log(sprintf(
                "[ReviewController::index] %s roomId=%d: %s  File: %s:%d",
                get_class($e), $roomId, $e->getMessage(), $e->getFile(), $e->getLine()
            ));
            $this->json(['error' => 'Không thể tải đánh giá'], 500);
        }
    }

    public function store(int $roomId): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();

        if (!$user) {
            $this->json(['error' => 'Bạn cần đăng nhập để đánh giá'], 401);
            return;
        }

        $raw   = file_get_contents('php://input');
        $input = json_decode($raw, true) ?? [];

        error_log(sprintf(
            "[ReviewController::store] START roomId=%d userId=%s input=%s",
            $roomId,
            $user['user_id'],
            json_encode($input, JSON_UNESCAPED_UNICODE)
        ));

        try {
            $review = $this->service->create($roomId, $user['user_id'], $input);
            error_log("[ReviewController::store] SUCCESS roomId={$roomId} reviewId={$review['review_id']}");
            $this->json(['success' => true, 'review' => $review], 201);

        } catch (\InvalidArgumentException $e) {
            error_log("[ReviewController::store] ValidationError roomId={$roomId} userId={$user['user_id']}: " . $e->getMessage());
            $this->json(['error' => $e->getMessage()], 422);

        } catch (\RuntimeException $e) {
            error_log("[ReviewController::store] RuntimeError roomId={$roomId} userId={$user['user_id']}: " . $e->getMessage());
            $this->json(['error' => $e->getMessage()], 409);

        } catch (\Exception $e) {
            error_log(sprintf(
                "[ReviewController::store] UNHANDLED %s roomId=%d userId=%s: %s\n  File: %s:%d\n  Trace:\n%s",
                get_class($e),
                $roomId,
                $user['user_id'],
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            ));
            $this->json(['error' => 'Đã có lỗi xảy ra, vui lòng thử lại'], 500);
        }
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}