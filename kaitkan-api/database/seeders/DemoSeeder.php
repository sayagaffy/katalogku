<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure storage symlink is correct (ignore errors if already exists)
        try {
            Artisan::call('storage:link');
        } catch (\Throwable $e) {
            // noop
        }

        // Seed base data using existing seeders
        $this->call([
            UserSeeder::class,
            CatalogSeeder::class,
            ProductSeeder::class,
        ]);

        // Ensure demo images exist for each product path
        if (!Storage::disk('public')->exists('products')) {
            Storage::disk('public')->makeDirectory('products');
        }

        $products = Product::all();
        foreach ($products as $idx => $product) {
            // Create simple placeholder image buffers
            $jpgData = $this->makeSolidImage('jpg', $idx);
            $webpData = $this->makeSolidImage('webp', $idx);

            if ($product->image_jpg && !Storage::disk('public')->exists($product->image_jpg)) {
                Storage::disk('public')->put($product->image_jpg, $jpgData);
            }
            if ($product->image_webp && !Storage::disk('public')->exists($product->image_webp)) {
                Storage::disk('public')->put($product->image_webp, $webpData);
            }
        }
    }

    /**
     * Generate a simple solid-color image in memory (JPEG or WebP).
     */
    private function makeSolidImage(string $format = 'jpg', int $seed = 0): string
    {
        $width = 800; $height = 800;
        $im = imagecreatetruecolor($width, $height);

        // Pick a deterministic color per product
        $colors = [
            [0xF3, 0x81, 0x81], // pink
            [0x95, 0xE1, 0xD3], // mint
            [0x34, 0x49, 0x5E], // dark blue
            [0x9B, 0x59, 0xB6], // purple
            [0x16, 0xA0, 0x85], // green
            [0xE7, 0x4C, 0x3C], // red
            [0x2C, 0x3E, 0x50], // slate
            [0x4E, 0xCD, 0xC4], // teal
        ];
        $c = $colors[$seed % count($colors)];
        $bg = imagecolorallocate($im, $c[0], $c[1], $c[2]);
        imagefilledrectangle($im, 0, 0, $width, $height, $bg);

        // Draw a simple white rectangle as a visual cue
        $white = imagecolorallocate($im, 255, 255, 255);
        imagerectangle($im, 60, 60, $width-60, $height-60, $white);

        ob_start();
        if ($format === 'webp' && function_exists('imagewebp')) {
            imagewebp($im, null, 80);
        } else {
            imagejpeg($im, null, 85);
        }
        imagedestroy($im);
        return (string) ob_get_clean();
    }
}

