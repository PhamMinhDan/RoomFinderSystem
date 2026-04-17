<?php
namespace Controllers;
use Services\CloudinaryService;

class UploadController
{
    private CloudinaryService $service;

    const MAX_SIZE = 10485760; // 10MB

    const ALLOWED = [
        "image/jpeg",
        "image/png",
        "image/gif",
        "image/webp",
        "video/mp4",
        "video/webm",
        "video/quicktime"
    ];

    public function __construct()
    {
        $this->service = new CloudinaryService();
    }

    public function upload()
    {
        if (!isset($_FILES['file'])) {
            return $this->json(["error" => "No file"]);
        }

        $file = $_FILES['file'];
        $secureId = $_POST['secureId'] ?? uniqid();

        if ($file['size'] > self::MAX_SIZE) {
            return $this->json(["error" => "File > 10MB"], 413);
        }

       $mime = \mime_content_type($file['tmp_name']);


        if (!in_array($mime, self::ALLOWED)) {
            return $this->json(["error" => "Invalid file type"], 400);
        }

        $type = str_starts_with($mime, 'video') ? "VIDEO" : "IMAGE";

        try {
            $url = $this->service->upload(
                $file['tmp_name'],
                $mime,
                $secureId
            );

            return $this->json([
                "url" => $url,
                "type" => $type,
                "mime" => $mime,
                "size" => $file['size']
            ]);

        } catch (\Exception $e) {
            return $this->json(["error" => $e->getMessage()], 500);
        }
    }

    private function json($data, $status = 200)
    {
        http_response_code($status);
        header("Content-Type: application/json");
        echo json_encode($data);
        exit;
    }
}
