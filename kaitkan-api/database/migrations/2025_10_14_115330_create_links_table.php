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
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_id')->constrained()->onDelete('cascade');
            $table->foreignId('link_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 150);
            $table->string('url', 500);
            $table->enum('type', ['general', 'social', 'product', 'shop_collection'])->default('general');
            $table->string('icon', 50)->nullable()->comment('Icon key e.g., feather:instagram');
            $table->string('thumbnail_webp', 255)->nullable();
            $table->string('thumbnail_jpg', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['catalog_id', 'sort_order']);
            $table->index(['catalog_id', 'type']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};

