<?php

namespace Database\Seeders;

use App\Models\Catalog;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure storage directory exists
        if (!Storage::disk('public')->exists('products')) {
            Storage::disk('public')->makeDirectory('products');
        }

        // Get catalogs
        $fashionCatalog = Catalog::where('username', 'tokofashion')->first();
        $elektronikCatalog = Catalog::where('username', 'tokoelektronik')->first();

        // Fashion products
        if ($fashionCatalog) {
            $fashionProducts = [
                [
                    'name' => 'Dress Floral Cantik',
                    'price' => 125000,
                    'category' => 'fashion',
                    'description' => 'Dress floral dengan bahan katun premium. Nyaman dipakai seharian. Tersedia ukuran S, M, L, XL.',
                    'color' => '#FF6B9D',
                ],
                [
                    'name' => 'Blouse Polos Elegant',
                    'price' => 89000,
                    'category' => 'fashion',
                    'description' => 'Blouse polos cocok untuk kerja atau acara formal. Bahan tidak mudah kusut.',
                    'color' => '#4ECDC4',
                ],
                [
                    'name' => 'Celana Jeans Slim Fit',
                    'price' => 150000,
                    'category' => 'fashion',
                    'description' => 'Celana jeans premium dengan potongan slim fit. Bahan stretch yang nyaman.',
                    'color' => '#2C3E50',
                ],
                [
                    'name' => 'Rok Midi A-Line',
                    'price' => 95000,
                    'category' => 'fashion',
                    'description' => 'Rok midi dengan model A-line yang cocok untuk berbagai acara.',
                    'color' => '#95E1D3',
                ],
                [
                    'name' => 'Kaos Oversized Premium',
                    'price' => 65000,
                    'category' => 'fashion',
                    'description' => 'Kaos oversized dengan bahan cotton combed 30s. Adem dan tidak luntur.',
                    'color' => '#F38181',
                ],
            ];

            foreach ($fashionProducts as $index => $productData) {
                $this->createProductWithImage($fashionCatalog, $productData, $index + 1);
            }
        }

        // Elektronik products
        if ($elektronikCatalog) {
            $elektronikProducts = [
                [
                    'name' => 'Earphone Bluetooth TWS',
                    'price' => 250000,
                    'category' => 'elektronik',
                    'description' => 'Earphone bluetooth dengan kualitas suara jernih. Battery life 8 jam. Garansi 1 tahun.',
                    'color' => '#34495E',
                ],
                [
                    'name' => 'Power Bank 20000mAh',
                    'price' => 180000,
                    'category' => 'elektronik',
                    'description' => 'Power bank kapasitas besar dengan fast charging. Cocok untuk traveling.',
                    'color' => '#E74C3C',
                ],
                [
                    'name' => 'Smart Watch Fitness',
                    'price' => 450000,
                    'category' => 'elektronik',
                    'description' => 'Smart watch dengan fitur monitor kesehatan lengkap. Water resistant IP68.',
                    'color' => '#3498DB',
                ],
                [
                    'name' => 'Keyboard Mechanical RGB',
                    'price' => 550000,
                    'category' => 'elektronik',
                    'description' => 'Keyboard mechanical dengan lampu RGB. Cocok untuk gaming dan typing.',
                    'color' => '#9B59B6',
                ],
                [
                    'name' => 'Mouse Wireless Gaming',
                    'price' => 320000,
                    'category' => 'elektronik',
                    'description' => 'Mouse gaming wireless dengan DPI tinggi dan respon cepat.',
                    'color' => '#16A085',
                ],
            ];

            foreach ($elektronikProducts as $index => $productData) {
                $this->createProductWithImage($elektronikCatalog, $productData, $index + 1);
            }
        }
    }

    /**
     * Create product with placeholder image
     */
    private function createProductWithImage(Catalog $catalog, array $data, int $sortOrder): void
    {
        // Use placeholder image paths (will use via.placeholder.com or similar)
        $filename = 'product_' . uniqid();

        // Create product with placeholder paths
        Product::create([
            'catalog_id' => $catalog->id,
            'name' => $data['name'],
            'price' => $data['price'],
            'category' => $data['category'],
            'description' => $data['description'],
            'image_webp' => "products/{$filename}.webp",
            'image_jpg' => "products/{$filename}.jpg",
            'in_stock' => true,
            'sort_order' => $sortOrder,
        ]);
    }

}
