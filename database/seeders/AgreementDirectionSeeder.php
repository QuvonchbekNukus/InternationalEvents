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
                'name_cryl' => 'Технология',
            ],
            [
                'name_ru' => 'Безопасность',
                'name_uz' => 'Xavfsizlik',
                'name_cryl' => 'Хавфсизлик',
            ],
            [
                'name_ru' => 'Кибербезопасность',
                'name_uz' => 'Kiberxavfsizlik',
                'name_cryl' => 'Киберхавфсизлик',
            ],
            [
                'name_ru' => 'Образование',
                'name_uz' => "Ta'lim",
                'name_cryl' => 'Таълим',
            ],
            [
                'name_ru' => 'Обмен опытом',
                'name_uz' => 'Tajriba almashinuvi',
                'name_cryl' => 'Тажриба алмашинуви',
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
