<?php

namespace Database\Seeders;

use App\Models\Catalog;
use App\Models\User;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create catalog for test user (081234567890)
        $testUser = User::where('whatsapp', '081234567890')->first();
        if ($testUser) {
            Catalog::create([
                'user_id' => $testUser->id,
                'name' => 'Toko Fashion Cantik',
                'username' => 'tokofashion',
                'description' => 'Menjual berbagai fashion wanita trendy dengan harga terjangkau. Kualitas terjamin!',
                'category' => 'fashion',
                'whatsapp' => $testUser->whatsapp,
                'theme' => 'default',
                'is_published' => true,
            ]);
        }

        // Create catalog for admin user (081111111111)
        $adminUser = User::where('whatsapp', '081111111111')->first();
        if ($adminUser) {
            Catalog::create([
                'user_id' => $adminUser->id,
                'name' => 'Toko Elektronik Murah',
                'username' => 'tokoelektronik',
                'description' => 'Pusat elektronik terlengkap dengan harga bersaing. Garansi resmi!',
                'category' => 'elektronik',
                'whatsapp' => $adminUser->whatsapp,
                'theme' => 'blue',
                'is_published' => true,
            ]);
        }
    }
}
