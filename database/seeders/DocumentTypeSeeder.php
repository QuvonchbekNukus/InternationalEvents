<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Seed the application's document types.
     */
    public function run(): void
    {
        $documentTypes = [
            [
                'name_ru' => 'Приказ',
                'name_uz' => 'Buyruq',
                'name_cryl' => 'Буйруқ',
            ],
            [
                'name_ru' => 'Письмо',
                'name_uz' => 'Xat',
                'name_cryl' => 'Хат',
            ],
            [
                'name_ru' => 'Меморандум',
                'name_uz' => 'Memorandum',
                'name_cryl' => 'Меморандум',
            ],
            [
                'name_ru' => 'Протокол',
                'name_uz' => 'Bayonnoma',
                'name_cryl' => 'Баённома',
            ],
            [
                'name_ru' => 'Справка',
                'name_uz' => 'Ma`lumotnoma',
                'name_cryl' => 'Маълумотнома',
            ],
        ];

        foreach ($documentTypes as $documentType) {
            DocumentType::query()->updateOrCreate(
                ['name_uz' => $documentType['name_uz']],
                $documentType
            );
        }
    }
}
