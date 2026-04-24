<?php

namespace Services;

use Repositories\ReviewRepository;

class ReviewService
{
    private ReviewRepository $repo;

    public function __construct()
    {
        $this->repo = new ReviewRepository();
    }

    public function getReviews(int $roomId, int $page = 1, int $perPage = 5): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $total    = $this->repo->countByRoom($roomId);
        $reviews  = $this->repo->findByRoom($roomId, $perPage, $offset);
        $dist     = $this->repo->getRatingDistribution($roomId);

        foreach ($reviews as &$rv) {
            $rv['images'] = $rv['image_urls']
                ? (json_decode($rv['image_urls'], true) ?? [])
                : [];
            unset($rv['image_urls']);
        }

        return [
            'reviews'      => $reviews,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
            'distribution' => $dist,
        ];
    }


    public function create(int $roomId, string $userId, array $data): array
    {
        $rating  = (float) ($data['rating'] ?? 0);
        $comment = trim($data['comment'] ?? '');
        $images  = $data['images'] ?? [];   // array of URL strings

        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Điểm đánh giá phải từ 1 đến 5');
        }

        if (empty($comment) && empty($images)) {
            throw new \InvalidArgumentException('Vui lòng nhập nội dung hoặc đính kèm ảnh');
        }

        if ($this->repo->hasReviewed($roomId, $userId)) {
            throw new \RuntimeException('Bạn đã đánh giá phòng này rồi');
        }

        $imageJson = !empty($images) ? json_encode(array_values($images)) : null;

        $reviewId = $this->repo->create(
            $roomId,
            $userId,
            $rating,
            $comment ?: null,
            $imageJson
        );

        $review = $this->repo->findById($reviewId);
        $review['images'] = $review['image_urls']
            ? (json_decode($review['image_urls'], true) ?? [])
            : [];
        unset($review['image_urls']);

        return $review;
    }
}