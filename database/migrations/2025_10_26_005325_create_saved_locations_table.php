<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // User-defined name
            $table->string('location_name'); // Actual location name
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->json('address_components')->nullable();
            $table->string('emoji')->default('📍'); // User can customize
            $table->text('notes')->nullable(); // Personal notes
            $table->integer('visit_count')->default(0); // Track usage
            $table->timestamp('last_visited_at')->nullable();
            $table->integer('sort_order')->default(0); // For custom ordering
            $table->timestamps();

            // Prevent duplicate saved locations
            $table->unique(['user_id', 'latitude', 'longitude']);
            
            // Indexes
            $table->index(['user_id', 'sort_order']);
            $table->index(['user_id', 'last_visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_locations');
    }
};