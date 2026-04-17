<?php

namespace Services;

use Cloudinary\Api\Upload\UploadApi;

class CloudinaryService
{
    public function upload($fileTmp, $mimeType, $secureId)
    {
        $isVideo = strpos($mimeType, 'video') === 0;

        $options = [
            "public_id" => $secureId,
            "chunk_size" => 6000000
        ];

        if ($isVideo) {
            $options["resource_type"] = "video";
            $options["quality"] = "auto";
        } else {
            $options["resource_type"] = "image";
            $options["quality"] = "auto:good";
            $options["fetch_format"] = "auto";
            $options["crop"] = "limit";
            $options["width"] = 2000;
            $options["height"] = 2000;
        }

        $uploadApi = new UploadApi();
        $result = $uploadApi->upload($fileTmp, $options);

        return $result["secure_url"];
    }
}
