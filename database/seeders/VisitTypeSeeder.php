<?php

namespace Database\Seeders;

use App\Models\VisitType;
use Illuminate\Database\Seeder;

class VisitTypeSeeder extends Seeder
{
    /**
     * Seed the application's visit types.
     */
    public function run(): void
    {
        $visitTypes = [
            [
                'name_ru' => 'Официальный визит',
                'name_uz' => 'Rasmiy tashrif',
            ],
            [
                'name_ru' => 'Рабочий визит',
                'name_uz' => 'Ishchi tashrif',
            ],
            [
                'name_ru' => 'Дружеский визит',
                'name_uz' => "Do'stona tashrif",
            ],
            [
                'name_ru' => 'Государственный визит',
                'name_uz' => 'Davlat tashrifi',
            ],
            [
                'name_ru' => 'Неофициальный визит',
                'name_uz' => 'Norasmiy tashrif',
            ],
            [
                'name_ru' => 'Ответный визит',
                'name_uz' => 'Javob tashrifi',
            ],
        ];

        foreach ($visitTypes as $visitType) {
            VisitType::query()->updateOrCreate(
                ['name_uz' => $visitType['name_uz']],
                $visitType
            );
        }
    }
}
