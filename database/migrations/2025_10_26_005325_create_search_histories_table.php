<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('location_name');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->json('address_components')->nullable(); // Store detailed address
            $table->string('search_type')->default('manual'); // manual, map_click, geolocation
            $table->integer('search_count')->default(1); // Track frequency
            $table->timestamp('last_searched_at');
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'last_searched_at']);
            $table->index(['user_id', 'search_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};