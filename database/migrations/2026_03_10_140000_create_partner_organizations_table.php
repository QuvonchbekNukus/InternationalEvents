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
        Schema::create('partner_organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->string('name_ru');
            $table->string('name_uz');
            $table->string('name_cryl');
            $table->string('short_name', 100)->nullable();
            $table->foreignId('organization_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('website')->nullable();
            $table->enum('status', ['faol', 'rejada', 'tugallangan'])->default('faol');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_organizations');
    }
};
