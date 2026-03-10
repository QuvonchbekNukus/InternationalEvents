<?php

namespace Database\Seeders;

use App\Models\AgreementType;
use Illuminate\Database\Seeder;

class AgreementTypeSeeder extends Seeder
{
    /**
     * Seed the application's agreement types.
     */
    public function run(): void
    {
        $agreementTypes = [
            [
                'name_ru' => 'Меморандум',
                'name_uz' => 'Memorandum',
                'name_cryl' => 'Меморандум',
            ],
            [
                'name_ru' => 'Соглашение',
                'name_uz' => 'Bitim',
                'name_cryl' => 'Битим',
            ],
            [
                'name_ru' => 'Договор',
                'name_uz' => 'Shartnoma',
                'name_cryl' => 'Шартнома',
            ],
            [
                'name_ru' => 'Протокол',
                'name_uz' => 'Protokol',
                'name_cryl' => 'Протокол',
            ],
            [
                'name_ru' => 'Дорожная карта',
                'name_uz' => "Yo'l xaritasi",
                'name_cryl' => 'Йўл харитаси',
            ],
        ];

        foreach ($agreementTypes as $agreementType) {
            AgreementType::query()->updateOrCreate(
                ['name_uz' => $agreementType['name_uz']],
                $agreementType
            );
        }
    }
}
