<?php

namespace Repositories;

class ReviewRepository extends BaseRepository
{

    public function countByRoom(int $roomId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM reviews WHERE room_id = :rid AND is_active = 1"
        );
        $stmt->execute([':rid' => $roomId]);
        return (int) $stmt->fetchColumn();
    }


    public function findByRoom(int $roomId, int $limit = 5, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT
                rv.review_id,
                rv.rating,
                rv.comment,
                rv.image_urls,
                rv.created_at,
                BIN_TO_UUID(u.user_id) AS user_id,
                u.full_name            AS reviewer_name,
                u.avatar_url           AS reviewer_avatar
            FROM reviews rv
            JOIN users u ON u.user_id = rv.user_id
            WHERE rv.room_id = :rid AND rv.is_active = 1
            ORDER BY rv.created_at DESC
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute([':rid' => $roomId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function getRatingDistribution(int $roomId): array
    {
        $stmt = $this->db->prepare("
            SELECT FLOOR(rating) AS star, COUNT(*) AS cnt
            FROM reviews
            WHERE room_id = :rid AND is_active = 1
            GROUP BY FLOOR(rating)
        ");
        $stmt->execute([':rid' => $roomId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($rows as $r) {
            $dist[(int) $r['star']] = (int) $r['cnt'];
        }
        return $dist;
    }

    public function hasReviewed(int $roomId, string $userId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM reviews WHERE room_id = :rid AND user_id = :uid AND is_active = 1 LIMIT 1"
        );
        $stmt->execute([
            ':rid' => $roomId,
            ':uid' => $this->uuidToBin($userId),
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(int $roomId, string $userId, float $rating, ?string $comment, ?string $imageUrlsJson): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO reviews (room_id, user_id, rating, comment, image_urls)
                VALUES (:rid, :uid, :rating, :comment, :images)
            ");
            $stmt->execute([
                ':rid'     => $roomId,
                ':uid'     => $this->uuidToBin($userId),
                ':rating'  => $rating,
                ':comment' => $comment,
                ':images'  => $imageUrlsJson,
            ]);
            $reviewId = (int) $this->db->lastInsertId();

            $this->db->prepare("
                UPDATE rooms
                SET total_reviews  = total_reviews + 1,
                    average_rating = (
                        SELECT AVG(rating) FROM reviews
                        WHERE room_id = :rid AND is_active = 1
                    )
                WHERE room_id = :rid2
            ")->execute([':rid' => $roomId, ':rid2' => $roomId]);

            $this->db->commit();
            return $reviewId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log(sprintf(
                "[ReviewRepository::create] EXCEPTION %s: %s\n  File: %s:%d\n  roomId=%d userId=%s rating=%s",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $roomId,
                $userId,
                $rating
            ));
            throw $e;
        }
    }

    public function findById(int $reviewId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                rv.review_id,
                rv.rating,
                rv.comment,
                rv.image_urls,
                rv.created_at,
                BIN_TO_UUID(u.user_id) AS user_id,
                u.full_name            AS reviewer_name,
                u.avatar_url           AS reviewer_avatar
            FROM reviews rv
            JOIN users u ON u.user_id = rv.user_id
            WHERE rv.review_id = :id
        ");
        $stmt->execute([':id' => $reviewId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}