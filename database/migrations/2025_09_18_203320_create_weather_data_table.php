<?php
// database/migrations/xxxx_xx_xx_create_weather_data_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('weather_data', function (Blueprint $table) {
            $table->id();
            $table->string('city_name');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->json('weather_data');
            $table->timestamp('data_timestamp');
            $table->timestamps();
            
            $table->index(['city_name', 'data_timestamp']);
            $table->unique(['city_name', 'data_timestamp']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('weather_data');
    }
};