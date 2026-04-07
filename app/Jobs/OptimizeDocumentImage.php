<?php

namespace App\Jobs;

use App\Models\Document;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OptimizeDocumentImage
{
    use Dispatchable;

    public function __construct(private readonly int $documentId) {}

    public function handle(): void
    {
        $document = Document::query()->find($this->documentId);

        if (! $document || ! $document->file_path) {
            return;
        }

        if (! $this->isOptimizableImage($document)) {
            return;
        }

        $disk = Storage::disk('documents');

        if (! $disk->exists($document->file_path)) {
            return;
        }

        $sourcePath = $disk->path($document->file_path);
        try {
            $source = $this->createImageResource($sourcePath, $document->mime_type, $document->file_ext);
        } catch (\Throwable) {
            return;
        }

        if (! $source) {
            return;
        }

        $maxDimension = max(1, (int) config('uploads.images.max_dimension', 2560));
        $quality = max(1, min(100, (int) config('uploads.images.webp_quality', 82)));

        $width = imagesx($source);
        $height = imagesy($source);
        [$targetWidth, $targetHeight] = $this->scaledDimensions($width, $height, $maxDimension);
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if (! $canvas) {
            imagedestroy($source);
            return;
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $tempPath = tempnam(sys_get_temp_dir(), 'doc_webp_');
        if ($tempPath === false || ! imagewebp($canvas, $tempPath, $quality)) {
            imagedestroy($canvas);
            imagedestroy($source);
            return;
        }

        imagedestroy($canvas);
        imagedestroy($source);

        $baseName = pathinfo($document->file_name ?: 'image', PATHINFO_FILENAME);
        $safeBase = Str::slug($baseName) ?: 'image';
        $directory = trim((string) pathinfo($document->file_path, PATHINFO_DIRNAME), '/.');
        $webpName = $safeBase.'-'.Str::random(10).'.webp';
        $webpPath = ($directory !== '' ? $directory.'/' : '').$webpName;

        $resource = fopen($tempPath, 'rb');
        if (! $resource) {
            @unlink($tempPath);
            return;
        }

        $disk->put($webpPath, $resource);
        fclose($resource);
        @unlink($tempPath);

        $newSize = (int) $disk->size($webpPath);
        $oldPath = $document->file_path;
        $publicName = pathinfo($document->file_name ?: $baseName, PATHINFO_FILENAME).'.webp';

        $document->update([
            'file_name' => $publicName,
            'file_path' => $webpPath,
            'file_ext' => 'webp',
            'file_size' => $newSize,
            'mime_type' => 'image/webp',
        ]);

        if ($oldPath && $oldPath !== $webpPath) {
            $disk->delete($oldPath);
        }
    }

    private function isOptimizableImage(Document $document): bool
    {
        if (! $this->supportsWebpConversion()) {
            return false;
        }

        $mime = strtolower((string) $document->mime_type);
        $ext = strtolower((string) $document->file_ext);

        if ($mime === 'image/webp' || $ext === 'webp') {
            return false;
        }

        if (str_starts_with($mime, 'image/')) {
            return in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true);
        }

        return in_array($ext, ['jpg', 'jpeg', 'png'], true);
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

    private function createImageResource(string $path, ?string $mimeType, ?string $extension): mixed
    {
        $mime = strtolower((string) $mimeType);
        $ext = strtolower((string) $extension);

        if ($mime === 'image/jpeg' || in_array($ext, ['jpg', 'jpeg'], true)) {
            return @imagecreatefromjpeg($path);
        }

        if ($mime === 'image/png' || $ext === 'png') {
            return @imagecreatefrompng($path);
        }

        if ($mime === 'image/webp' || $ext === 'webp') {
            return @imagecreatefromwebp($path);
        }

        return null;
    }

    /**
     * @return array{0:int,1:int}
     */
    private function scaledDimensions(int $width, int $height, int $maxDimension): array
    {
        if ($width <= $maxDimension && $height <= $maxDimension) {
            return [$width, $height];
        }

        $ratio = $width >= $height
            ? $maxDimension / max(1, $width)
            : $maxDimension / max(1, $height);

        return [
            max(1, (int) round($width * $ratio)),
            max(1, (int) round($height * $ratio)),
        ];
    }
}
