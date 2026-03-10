<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;

class EventTypeSeeder extends Seeder
{
    /**
     * Seed the application's event types.
     */
    public function run(): void
    {
        $eventTypes = [
            [
                'name_ru' => 'Семинар',
                'name_uz' => 'Seminar',
                'name_cryl' => 'Семинар',
            ],
            [
                'name_ru' => 'Конференция',
                'name_uz' => 'Konferensiya',
                'name_cryl' => 'Конференция',
            ],
            [
                'name_ru' => 'Форум',
                'name_uz' => 'Forum',
                'name_cryl' => 'Форум',
            ],
            [
                'name_ru' => 'Круглый стол',
                'name_uz' => 'Davra suhbati',
                'name_cryl' => 'Давра суҳбати',
            ],
            [
                'name_ru' => 'Рабочая встреча',
                'name_uz' => 'Ishchi uchrashuv',
                'name_cryl' => 'Ишчи учрашув',
            ],
        ];

        foreach ($eventTypes as $eventType) {
            EventType::query()->updateOrCreate(
                ['name_uz' => $eventType['name_uz']],
                $eventType
            );
        }
    }
}
