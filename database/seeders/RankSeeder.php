<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    /**
     * Seed the application's ranks.
     */
    public function run(): void
    {
        $ranks = [
            [
                'name_ru' => 'Лейтенант',
                'name_uz' => 'Leytenant',
                'name_cryl' => 'Лейтенант',
            ],
            [
                'name_ru' => 'Капитан',
                'name_uz' => 'Kapitan',
                'name_cryl' => 'Капитан',
            ],
            [
                'name_ru' => 'Майор',
                'name_uz' => 'Mayor',
                'name_cryl' => 'Майор',
            ],
        ];

        foreach ($ranks as $rank) {
            Rank::updateOrCreate(
                ['name_uz' => $rank['name_uz']],
                $rank
            );
        }
    }
}
