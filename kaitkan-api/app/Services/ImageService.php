<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    protected function disk(): string
    {
        return config('filesystems.image_disk', 'public');
    }

    /**
     * Upload and process image (create WebP and JPG versions).
     *
     * @param UploadedFile $file
     * @param string $directory Directory path (e.g., 'products', 'avatars')
     * @param int $maxWidth Maximum width in pixels
     * @param int $maxHeight Maximum height in pixels
     * @return array ['webp' => 'path/to/image.webp', 'jpg' => 'path/to/image.jpg']
     * @throws \Exception
     */
    public function uploadAndProcess(
        UploadedFile $file,
        string $directory = 'products',
        int $maxWidth = 800,
        int $maxHeight = 800
    ): array {
        // Validate file
        $this->validateImage($file);

        $disk = $this->disk();
        $filename = (string) Str::uuid();
        $webpPath = "{$directory}/{$filename}.webp";
        $jpgPath = "{$directory}/{$filename}.jpg";

        // Ensure directory (local only)
        if (config("filesystems.disks.{$disk}.driver") === 'local') {
            if (!Storage::disk($disk)->exists($directory)) {
                Storage::disk($disk)->makeDirectory($directory);
            }
        }

        // Read & process image once
        $image = Image::read($file->getRealPath());
        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->scale(width: $maxWidth, height: $maxHeight);
        }

        // Save to temp files then upload via Storage (works for local & s3)
        $tmpDir = sys_get_temp_dir();
        $webpTmp = $tmpDir . DIRECTORY_SEPARATOR . $filename . '.webp';
        $jpgTmp = $tmpDir . DIRECTORY_SEPARATOR . $filename . '.jpg';

        $image->toWebp(80)->save($webpTmp);
        $image->toJpeg(85)->save($jpgTmp);

        // Put to storage with public visibility when supported
        $streamWebp = fopen($webpTmp, 'r');
        $streamJpg = fopen($jpgTmp, 'r');
        Storage::disk($disk)->put($webpPath, $streamWebp, ['visibility' => 'public']);
        Storage::disk($disk)->put($jpgPath, $streamJpg, ['visibility' => 'public']);
        if (is_resource($streamWebp)) fclose($streamWebp);
        if (is_resource($streamJpg)) fclose($streamJpg);
        @unlink($webpTmp);
        @unlink($jpgTmp);

        return [
            'webp' => $webpPath,
            'jpg' => $jpgPath,
        ];
    }

    /**
     * Delete image files (both WebP and JPG).
     *
     * @param string|null $webpPath
     * @param string|null $jpgPath
     * @return bool
     */
    public function deleteImage(?string $webpPath, ?string $jpgPath): bool
    {
        $disk = $this->disk();
        $deleted = true;

        if ($webpPath && Storage::disk($disk)->exists($webpPath)) {
            $deleted = Storage::disk($disk)->delete($webpPath) && $deleted;
        }

        if ($jpgPath && Storage::disk($disk)->exists($jpgPath)) {
            $deleted = Storage::disk($disk)->delete($jpgPath) && $deleted;
        }

        return $deleted;
    }

    /**
     * Validate uploaded image file.
     *
     * @param UploadedFile $file
     * @return void
     * @throws \Exception
     */
    protected function validateImage(UploadedFile $file): void
    {
        // Check file size (max 10MB from config)
        $maxSize = config('filesystems.max_upload_size', 10240) * 1024; // Convert KB to bytes
        if ($file->getSize() > $maxSize) {
            throw new \Exception('Ukuran file terlalu besar. Maksimal ' . ($maxSize / 1024 / 1024) . 'MB');
        }

        // Check mime type
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \Exception('Format file tidak didukung. Gunakan JPG, PNG, atau WebP');
        }

        // Check if file is valid image
        try {
            $imageInfo = getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                throw new \Exception('File bukan gambar yang valid');
            }
        } catch (\Exception $e) {
            throw new \Exception('File bukan gambar yang valid');
        }
    }

    /**
     * Get full URL for image path.
     *
     * @param string|null $path
     * @return string|null
     */
    public function getImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $disk = $this->disk();
        $cdn = config('filesystems.cdn_url');

        if ($cdn && $disk !== 'public') {
            return rtrim($cdn, '/') . '/' . ltrim($path, '/');
        }

        return Storage::disk($disk)->url($path);
    }

    /**
     * Upload avatar image (smaller size).
     *
     * @param UploadedFile $file
     * @return array
     * @throws \Exception
     */
    public function uploadAvatar(UploadedFile $file): array
    {
        return $this->uploadAndProcess($file, 'avatars', 300, 300);
    }

    /**
     * Upload product image.
     *
     * @param UploadedFile $file
     * @return array
     * @throws \Exception
     */
    public function uploadProduct(UploadedFile $file): array
    {
        return $this->uploadAndProcess($file, 'products', 1000, 1000);
    }

    /**
     * Upload catalog banner/avatar.
     *
     * @param UploadedFile $file
     * @return array
     * @throws \Exception
     */
    public function uploadCatalog(UploadedFile $file): array
    {
        return $this->uploadAndProcess($file, 'catalogs', 500, 500);
    }

    /**
     * Upload link thumbnail image.
     *
     * @param UploadedFile $file
     * @return array
     * @throws \Exception
     */
    public function uploadLinkThumbnail(UploadedFile $file): array
    {
        return $this->uploadAndProcess($file, 'links', 600, 600);
    }

    /**
     * Compress and store image for a product (max 1000x1000).
     * Saves both WebP (80%) and JPG (85%). If a product ID is provided,
     * images are stored under products/{productId}.
     *
     * @param UploadedFile $file
     * @param int|null $productId
     * @return array{webp:string,jpg:string}
     * @throws \Exception
     */
    public function compressAndStore(UploadedFile $file, ?int $productId = null): array
    {
        $dir = $productId ? 'products/' . $productId : 'products';
        return $this->uploadAndProcess($file, $dir, 1000, 1000);
    }
}
