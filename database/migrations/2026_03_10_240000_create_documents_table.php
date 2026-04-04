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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title_ru')->nullable();
            $table->string('title_uz')->nullable();
            $table->string('document_number')->nullable();
            $table->foreignId('document_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_ext', 20)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('partner_organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agreement_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['qoralama', 'faol', 'nazoratda', 'arxivlangan'])->default('faol');
            $table->boolean('is_confidential')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // `documents` mavjud bo‘lgach: hamkor tashkilotning “ma’lumotnoma” hujjatiga FK (aylanma bog‘lanish).
        if (! Schema::hasColumn('partner_organizations', 'organization_info_document_id')) {
            Schema::table('partner_organizations', function (Blueprint $table) {
                $table->foreignId('organization_info_document_id')
                    ->nullable()
                    ->after('organization_type_id')
                    ->constrained('documents')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('partner_organizations', 'organization_info_document_id')) {
            Schema::table('partner_organizations', function (Blueprint $table) {
                $table->dropConstrainedForeignId('organization_info_document_id');
            });
        }

        Schema::dropIfExists('documents');
    }
};
