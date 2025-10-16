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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('key', 50)->unique();
            $table->json('palette')->nullable()->comment('JSON of colors: primary, background, text, accent');
            $table->string('preview_image', 255)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};

