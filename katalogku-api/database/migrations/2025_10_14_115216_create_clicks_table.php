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
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable()->comment('Visitor IP (IPv4 or IPv6)');
            $table->text('user_agent')->nullable()->comment('Browser info');
            $table->string('referrer', 500)->nullable()->comment('Source URL (instagram, tiktok, etc)');
            $table->timestamp('clicked_at')->useCurrent();

            // Indexes
            $table->index(['product_id', 'clicked_at']);
            $table->index('clicked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};
