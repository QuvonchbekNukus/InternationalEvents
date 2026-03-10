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
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->string('agreement_number')->nullable()->unique();
            $table->string('title_ru');
            $table->string('title_uz');
            $table->string('title_cryl');
            $table->string('short_title_ru')->nullable();
            $table->string('short_title_uz')->nullable();
            $table->string('short_title_cryl')->nullable();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->foreignId('partner_organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agreement_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agreement_direction_id')->nullable()->constrained()->nullOnDelete();
            $table->date('signed_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft', 'active', 'expired', 'terminated', 'completed'])->default('draft');
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('responsible_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
