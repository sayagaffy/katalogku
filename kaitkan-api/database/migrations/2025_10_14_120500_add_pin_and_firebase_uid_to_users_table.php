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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'pin_hash')) {
                $table->string('pin_hash', 255)->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'firebase_uid')) {
                $table->string('firebase_uid', 128)->nullable()->unique()->after('verified_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'firebase_uid')) {
                $table->dropUnique(['firebase_uid']);
                $table->dropColumn('firebase_uid');
            }
            if (Schema::hasColumn('users', 'pin_hash')) {
                $table->dropColumn('pin_hash');
            }
        });
    }
};

