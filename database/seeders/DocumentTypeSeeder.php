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
            ],
            [
                'name_ru' => 'Письмо',
                'name_uz' => 'Xat',
            ],
            [
                'name_ru' => 'Меморандум',
                'name_uz' => 'Memorandum',
            ],
            [
                'name_ru' => 'Протокол',
                'name_uz' => 'Bayonnoma',
            ],
            [
                'name_ru' => 'Справка',
                'name_uz' => 'Ma`lumotnoma',
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
