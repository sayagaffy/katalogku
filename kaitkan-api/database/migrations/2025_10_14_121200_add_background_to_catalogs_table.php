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
        Schema::table('catalogs', function (Blueprint $table) {
            if (!Schema::hasColumn('catalogs', 'bg_image_webp')) {
                $table->string('bg_image_webp', 255)->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('catalogs', 'bg_image_jpg')) {
                $table->string('bg_image_jpg', 255)->nullable()->after('bg_image_webp');
            }
            if (!Schema::hasColumn('catalogs', 'bg_overlay_opacity')) {
                $table->decimal('bg_overlay_opacity', 3, 2)->default(0.0)->after('bg_image_jpg');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogs', function (Blueprint $table) {
            if (Schema::hasColumn('catalogs', 'bg_image_webp')) {
                $table->dropColumn('bg_image_webp');
            }
            if (Schema::hasColumn('catalogs', 'bg_image_jpg')) {
                $table->dropColumn('bg_image_jpg');
            }
            if (Schema::hasColumn('catalogs', 'bg_overlay_opacity')) {
                $table->dropColumn('bg_overlay_opacity');
            }
        });
    }
};

