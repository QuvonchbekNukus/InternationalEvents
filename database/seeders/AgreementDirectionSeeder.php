<?php

namespace Database\Seeders;

use App\Models\AgreementDirection;
use Illuminate\Database\Seeder;

class AgreementDirectionSeeder extends Seeder
{
    /**
     * Seed the application's agreement directions.
     */
    public function run(): void
    {
        $agreementDirections = [
            [
                'name_ru' => 'Технология',
                'name_uz' => 'Texnologiya',
            ],
            [
                'name_ru' => 'Безопасность',
                'name_uz' => 'Xavfsizlik',
            ],
            [
                'name_ru' => 'Кибербезопасность',
                'name_uz' => 'Kiberxavfsizlik',
            ],
            [
                'name_ru' => 'Образование',
                'name_uz' => "Ta'lim",
            ],
            [
                'name_ru' => 'Обмен опытом',
                'name_uz' => 'Tajriba almashinuvi',
            ],
        ];

        foreach ($agreementDirections as $agreementDirection) {
            AgreementDirection::query()->updateOrCreate(
                ['name_uz' => $agreementDirection['name_uz']],
                $agreementDirection
            );
        }
    }
}
