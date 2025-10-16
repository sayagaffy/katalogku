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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_id')->constrained()->onDelete('cascade');
            $table->string('category', 50)->default('lainnya')->comment('elektronik, fashion, makanan, etc');
            $table->string('name', 200)->comment('Product name');
            $table->string('slug', 220)->nullable()->comment('URL-friendly name (future)');
            $table->decimal('price', 15, 2)->comment('Price in Rupiah');
            $table->string('image_webp', 255)->comment('Primary image (WebP format)');
            $table->string('image_jpg', 255)->comment('Fallback image (JPG format)');
            $table->text('description')->nullable()->comment('Product description');
            $table->string('external_link', 500)->nullable()->comment('Link to Shopee/Tokopedia (optional)');
            $table->boolean('in_stock')->default(true)->comment('Stock availability');
            $table->integer('sort_order')->default(0)->comment('Manual ordering within catalog');
            $table->unsignedInteger('view_count')->default(0)->comment('Number of views (denormalized)');
            $table->unsignedInteger('click_count')->default(0)->comment('Number of WhatsApp clicks (denormalized)');
            $table->timestamps();

            // Indexes
            $table->index(['catalog_id', 'category']);
            $table->index(['catalog_id', 'sort_order']);
            $table->index('in_stock');
            // Fulltext search (MySQL only)
            $table->fullText(['name', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
