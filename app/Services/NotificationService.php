<?php

namespace Services;

use Core\Database;
use PDO;

class NotificationService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }


    public function send(string $userIdBin, string $title, string $content, string $type, string $redirectUrl = ''): bool
    {
        $sql = "INSERT INTO notifications (user_id, title, content, type, redirect_url, created_at) 
                VALUES (:uid, :title, :content, :type, :url, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'uid'   => $userIdBin,
            'title' => $title,
            'content' => $content,
            'type'  => $type,
            'url'   => $redirectUrl
        ]);


        return $result;
    }

    public function getForUser(string $userIdBin, int $limit = 10): array
    {
        $sql = "SELECT notification_id, title, content, type, redirect_url, is_read, created_at 
                FROM notifications WHERE user_id = :uid 
                ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userIdBin);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead(int $notificationId, string $userIdBin): void
    {
        $sql = "UPDATE notifications SET is_read = TRUE 
                WHERE notification_id = :nid AND user_id = :uid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nid' => $notificationId, 'uid' => $userIdBin]);
    }

  
    public function getUnreadCount(string $userIdBin): int
    {
        $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = FALSE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userIdBin]);
        return (int)$stmt->fetchColumn();
    }
}