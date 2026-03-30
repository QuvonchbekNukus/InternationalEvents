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
        if (Schema::hasColumn('partner_organizations', 'organization_info_document_id')) {
            return;
        }

        Schema::table('partner_organizations', function (Blueprint $table) {
            $table->foreignId('organization_info_document_id')
                ->nullable()
                ->after('organization_type_id')
                ->constrained('documents')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('partner_organizations', 'organization_info_document_id')) {
            return;
        }

        Schema::table('partner_organizations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_info_document_id');
        });
    }
};
