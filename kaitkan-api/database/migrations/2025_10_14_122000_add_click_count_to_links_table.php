<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('links', function (Blueprint $table) {
            if (!Schema::hasColumn('links', 'click_count')) {
                $table->unsignedInteger('click_count')->default(0)->after('sort_order');
                $table->index('click_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            if (Schema::hasColumn('links', 'click_count')) {
                $table->dropIndex(['click_count']);
                $table->dropColumn('click_count');
            }
        });
    }
};

