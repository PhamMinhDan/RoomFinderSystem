<?php

namespace Controllers;

use Core\SessionManager;
use Services\NotificationService;

class NotificationController
{
    private NotificationService $notifService;

    public function __construct()
    {
        $this->notifService = new NotificationService();
    }


    public function index(): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();

        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $userIdBin = hex2bin(str_replace('-', '', $user['user_id']));
        
        $items = $this->notifService->getForUser($userIdBin, 10);
        $unreadCount = $this->notifService->getUnreadCount($userIdBin);

        $this->json([
            'items' => $items,
            'unread_count' => $unreadCount
        ]);
    }

    public function markAsRead(): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();
        $notificationId = (int)($_GET['id'] ?? 0);

        if (!$user || $notificationId <= 0) {
            $this->json(['error' => 'Yêu cầu không hợp lệ'], 400);
            return;
        }

        $userIdBin = hex2bin(str_replace('-', '', $user['user_id']));
        $this->notifService->markAsRead($notificationId, $userIdBin);

        $this->json(['success' => true]);
    }

    public function unreadCount(): void
    {
        SessionManager::start();
        $user = SessionManager::getUser();

        if (!$user) {
            $this->json(['count' => 0]);
            return;
        }

        $userIdBin = hex2bin(str_replace('-', '', $user['user_id']));
        $count = $this->notifService->getUnreadCount($userIdBin);

        $this->json(['count' => $count]);
    }

     private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}