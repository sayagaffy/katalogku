<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('catalogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name', 100)->comment('Display name: Toko Baju Sarah');
            $table->string('username', 50)->unique()->comment('URL slug: bajusarah');
            $table->text('description')->nullable()->comment('About the store');
            $table->string('category', 50)->nullable()->comment('Primary category');
            $table->string('whatsapp', 20)->nullable()->comment('Contact WhatsApp (override user)');
            $table->string('avatar', 255)->nullable()->comment('Store logo/avatar');
            $table->string('theme', 20)->default('default')->comment('Color theme (future)');
            $table->boolean('is_published')->default(true)->comment('Public visibility');
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogs');
    }
};
