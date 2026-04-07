<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class UploadedFileProcessor
{
    /**
     * @return array{file_name: string, file_path: string, file_ext: ?string, file_size: int, mime_type: string}
     */
    public function store(UploadedFile $file, string $disk, string $directory): array
    {
        if (! config('uploads.images_optimize_async', true) && $this->isConvertibleImage($file)) {
            try {
                return $this->storeAsWebp($file, $disk, $directory);
            } catch (\Throwable) {
                // Keep upload flow reliable even when image conversion fails.
            }
        }

        $path = $file->store($directory, $disk);
        $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';

        return [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_ext' => $file->getClientOriginalExtension() ?: null,
            'file_size' => (int) $file->getSize(),
            'mime_type' => $mimeType,
        ];
    }

    private function isConvertibleImage(UploadedFile $file): bool
    {
        if (! $this->supportsWebpConversion()) {
            return false;
        }

        $mimeType = strtolower($this->detectedMimeType($file));

        return in_array($mimeType, [
            'image/jpeg',
            'image/png',
            'image/webp',
        ], true);
    }

    private function supportsWebpConversion(): bool
    {
        if (function_exists('imagetypes') && defined('IMG_WEBP') && ((int) imagetypes() & IMG_WEBP) !== IMG_WEBP) {
            return false;
        }

        foreach ([
            'imagecreatefromjpeg',
            'imagecreatefrompng',
            'imagecreatefromwebp',
            'imagecreatetruecolor',
            'imagecopyresampled',
            'imagewebp',
            'imagesx',
            'imagesy',
        ] as $function) {
            if (! function_exists($function)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{file_name: string, file_path: string, file_ext: string, file_size: int, mime_type: string}
     */
    private function storeAsWebp(UploadedFile $file, string $disk, string $directory): array
    {
        $sourcePath = $file->getPathname();
        $source = $this->createImageResource($sourcePath, $this->detectedMimeType($file));

        if (! $source) {
            throw new RuntimeException('Image resource could not be created.');
        }

        $maxDimension = max(1, (int) config('uploads.images.max_dimension', 2560));
        $quality = max(1, min(100, (int) config('uploads.images.webp_quality', 82)));

        $width = imagesx($source);
        $height = imagesy($source);
        [$targetWidth, $targetHeight] = $this->scaledDimensions($width, $height, $maxDimension);

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if (! $canvas) {
            imagedestroy($source);
            throw new RuntimeException('Image canvas could not be created.');
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $width,
            $height
        );

        $tempPath = tempnam(sys_get_temp_dir(), 'upl_webp_');

        if ($tempPath === false || ! imagewebp($canvas, $tempPath, $quality)) {
            imagedestroy($canvas);
            imagedestroy($source);
            throw new RuntimeException('Image could not be converted to webp.');
        }

        imagedestroy($canvas);
        imagedestroy($source);

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeOriginal = Str::slug($originalName) ?: 'image';
        $storedFileName = $safeOriginal.'-'.Str::random(10).'.webp';
        $relativePath = trim($directory, '/').'/'.$storedFileName;

        $resource = fopen($tempPath, 'rb');
        if (! $resource) {
            @unlink($tempPath);
            throw new RuntimeException('Temporary webp file could not be opened.');
        }

        Storage::disk($disk)->put($relativePath, $resource);
        fclose($resource);
        @unlink($tempPath);

        $size = Storage::disk($disk)->size($relativePath);
        $publicName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'.webp';

        return [
            'file_name' => $publicName,
            'file_path' => $relativePath,
            'file_ext' => 'webp',
            'file_size' => (int) $size,
            'mime_type' => 'image/webp',
        ];
    }

    /**
     * @return array{0:int,1:int}
     */
    private function scaledDimensions(int $width, int $height, int $maxDimension): array
    {
        if ($width <= $maxDimension && $height <= $maxDimension) {
            return [$width, $height];
        }

        if ($width >= $height) {
            $ratio = $maxDimension / $width;
        } else {
            $ratio = $maxDimension / $height;
        }

        return [
            max(1, (int) round($width * $ratio)),
            max(1, (int) round($height * $ratio)),
        ];
    }

    private function createImageResource(string $path, ?string $mimeType): mixed
    {
        return match (strtolower((string) $mimeType)) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default => null,
        };
    }

    private function detectedMimeType(UploadedFile $file): string
    {
        $serverMimeType = strtolower((string) $file->getMimeType());
        $clientMimeType = strtolower((string) $file->getClientMimeType());

        if (str_starts_with($serverMimeType, 'image/')) {
            return $serverMimeType;
        }

        if (str_starts_with($clientMimeType, 'image/')) {
            return $clientMimeType;
        }

        if ($serverMimeType !== '') {
            return $serverMimeType;
        }

        return $clientMimeType;
    }
}
