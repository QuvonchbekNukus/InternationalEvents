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
        if (Schema::hasColumn('partner_organizations', 'partnership_history')) {
            return;
        }

        Schema::table('partner_organizations', function (Blueprint $table) {
            $table->text('partnership_history')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('partner_organizations', 'partnership_history')) {
            return;
        }

        Schema::table('partner_organizations', function (Blueprint $table) {
            $table->dropColumn('partnership_history');
        });
    }
};
