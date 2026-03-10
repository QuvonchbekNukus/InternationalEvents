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
        Schema::create('partner_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_organization_id')->constrained()->restrictOnDelete();
            $table->string('full_name_ru');
            $table->string('full_name_uz');
            $table->string('full_name_cryl');
            $table->string('position_ru')->nullable();
            $table->string('position_uz')->nullable();
            $table->string('position_cryl')->nullable();
            $table->text('email')->nullable();
            $table->text('phone')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['partner_organization_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_contacts');
    }
};
