<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('title_key')->nullable()->after('title');
            $table->string('message_key')->nullable()->after('message');
            $table->json('message_params')->nullable()->after('message_key');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['title_key', 'message_key', 'message_params']);
        });
    }
};
