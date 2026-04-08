<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('partner_contacts')
            ->select(['id', 'email', 'phone'])
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    $updates = [];

                    foreach (['email', 'phone'] as $column) {
                        $value = $row->{$column};

                        if (! is_string($value) || $value === '') {
                            continue;
                        }

                        try {
                            $decrypted = Crypt::decryptString($value);
                        } catch (\Throwable) {
                            continue;
                        }

                        $updates[$column] = $decrypted;
                    }

                    if ($updates !== []) {
                        DB::table('partner_contacts')
                            ->where('id', $row->id)
                            ->update($updates);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: plaintext data should stay plaintext.
    }
};
