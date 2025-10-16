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
        Schema::create('link_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_collapsible')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['catalog_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_groups');
    }
};

