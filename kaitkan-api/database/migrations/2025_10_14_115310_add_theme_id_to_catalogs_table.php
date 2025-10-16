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
            if (!Schema::hasColumn('catalogs', 'theme_id')) {
                $table->foreignId('theme_id')
                    ->nullable()
                    ->after('theme')
                    ->constrained('themes')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogs', function (Blueprint $table) {
            if (Schema::hasColumn('catalogs', 'theme_id')) {
                $table->dropForeign(['theme_id']);
                $table->dropColumn('theme_id');
            }
        });
    }
};

