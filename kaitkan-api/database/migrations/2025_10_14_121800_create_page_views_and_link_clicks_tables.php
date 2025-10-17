<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('page_views')) {
            Schema::create('page_views', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('catalog_id');
                $table->string('ip_hash', 64);
                $table->string('user_agent', 255)->nullable();
                $table->string('referrer', 500)->nullable();
                $table->string('utm_source', 100)->nullable();
                $table->string('utm_medium', 100)->nullable();
                $table->string('utm_campaign', 100)->nullable();
                $table->timestamp('visited_at')->index();
                $table->timestamps();
                $table->index(['catalog_id', 'visited_at']);
            });
        }

        if (!Schema::hasTable('link_clicks')) {
            Schema::create('link_clicks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('catalog_id');
                $table->unsignedBigInteger('link_id');
                $table->string('ip_hash', 64)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->string('referrer', 500)->nullable();
                $table->timestamp('clicked_at')->index();
                $table->timestamps();
                $table->index(['catalog_id', 'clicked_at']);
                $table->index(['link_id', 'clicked_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('link_clicks');
        Schema::dropIfExists('page_views');
    }
};

