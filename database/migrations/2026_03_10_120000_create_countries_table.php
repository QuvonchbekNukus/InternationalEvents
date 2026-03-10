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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name_ru');
            $table->string('name_uz')->nullable();
            $table->string('name_cryl')->nullable();
            $table->string('iso2', 2)->unique();
            $table->string('iso3', 3)->nullable()->unique();
            $table->string('region_ru')->nullable();
            $table->string('region_uz')->nullable();
            $table->string('region_cryl')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('default_zoom', 4, 1)->nullable();
            $table->enum('cooperation_status', ['faol', 'rejada', 'tugatilgan'])->default('active');
            $table->string('boundary_geojson_path')->nullable();
            $table->string('flag_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('cooperation_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
