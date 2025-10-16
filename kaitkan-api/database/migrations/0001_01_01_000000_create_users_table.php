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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('whatsapp', 20)->unique()->comment('Format: 081234567890');
            $table->string('username', 50)->unique()->nullable()->comment('For catalog URL');
            $table->string('password');
            $table->string('avatar', 255)->nullable()->comment('Profile picture URL');
            $table->timestamp('verified_at')->nullable()->comment('OTP verification timestamp');
            $table->rememberToken();
            $table->timestamps();

            // Indexes
            $table->index('whatsapp');
            $table->index('username');
            $table->index('verified_at');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
