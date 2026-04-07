<?php

return [
    'images_optimize_async' => env('UPLOAD_IMAGE_OPTIMIZE_ASYNC', true),
    'images_optimize_after_response' => env('UPLOAD_IMAGE_OPTIMIZE_AFTER_RESPONSE', true),

    'images' => [
        // Resize only when either side is larger than this value.
        'max_dimension' => env('UPLOAD_IMAGE_MAX_DIMENSION', 2560),
        // WebP quality 1..100 (balance between quality and size).
        'webp_quality' => env('UPLOAD_IMAGE_WEBP_QUALITY', 82),
    ],
];
