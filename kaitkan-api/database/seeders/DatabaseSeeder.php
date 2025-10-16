<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call our dedicated seeders in order
        $this->call([
            UserSeeder::class,
            CatalogSeeder::class,
            ProductSeeder::class,
            ThemeSeeder::class,
        ]);
    }
}
