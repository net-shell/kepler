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
        Schema::create('data_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // 'database', 'url', 'api'
            $table->json('config'); // Connection details, auth, etc.
            $table->integer('cache_ttl')->default(3600); // Cache TTL in seconds
            $table->timestamp('last_cached_at')->nullable();
            $table->json('cached_data')->nullable(); // Cached response
            $table->boolean('enabled')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_sources');
    }
};
