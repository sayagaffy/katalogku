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
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('whatsapp', 20)->comment('Target phone number');
            $table->string('code', 6)->comment('6-digit OTP');
            $table->timestamp('expires_at')->comment('5 minutes from created');
            $table->timestamp('verified_at')->nullable()->comment('NULL = not used yet');
            $table->string('ip_address', 45)->nullable()->comment('Request origin IP');
            $table->timestamps();

            // Indexes
            $table->index(['whatsapp', 'expires_at']);
            $table->index('code');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
