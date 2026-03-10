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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('title_ru');
            $table->string('title_uz');
            $table->string('title_cryl');
            $table->foreignId('visit_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->foreignId('partner_organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('direction', ['incoming', 'outgoing'])->nullable();
            $table->enum('status', ['planned', 'ongoing', 'completed', 'cancelled'])->default('planned');
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('responsible_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->text('purpose_ru')->nullable();
            $table->text('purpose_uz')->nullable();
            $table->text('purpose_cryl')->nullable();
            $table->text('result_summary_ru')->nullable();
            $table->text('result_summary_uz')->nullable();
            $table->text('result_summary_cryl')->nullable();
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
        Schema::dropIfExists('visits');
    }
};
