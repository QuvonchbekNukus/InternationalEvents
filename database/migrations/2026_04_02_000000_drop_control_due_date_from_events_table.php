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
        if (! Schema::hasColumn('events', 'control_due_date')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('control_due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('events', 'control_due_date')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->date('control_due_date')->nullable();
        });
    }
};
