<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalogs', function (Blueprint $table) {
            if (!Schema::hasColumn('catalogs', 'social_icons_position')) {
                $table->string('social_icons_position', 10)->default('top')->after('bg_overlay_opacity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('catalogs', function (Blueprint $table) {
            if (Schema::hasColumn('catalogs', 'social_icons_position')) {
                $table->dropColumn('social_icons_position');
            }
        });
    }
};

