<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use Illuminate\Database\Seeder;

class OrganizationTypeSeeder extends Seeder
{
    /**
     * Seed the application's organization types.
     */
    public function run(): void
    {
        $organizationTypes = [
            [
                'name_ru' => 'Министерство',
                'name_uz' => 'Vazirlik',
                'name_cryl' => 'Вазирлик',
            ],
            [
                'name_ru' => 'Посольство',
                'name_uz' => 'Elchixona',
                'name_cryl' => 'Элчихона',
            ],
            [
                'name_ru' => 'Компания',
                'name_uz' => 'Kompaniya',
                'name_cryl' => 'Компания',
            ],
            [
                'name_ru' => 'Университет',
                'name_uz' => 'Universitet',
                'name_cryl' => 'Университет',
            ],
            [
                'name_ru' => 'Международная организация',
                'name_uz' => 'Xalqaro tashkilot',
                'name_cryl' => 'Халкаро ташкилот',
            ],
        ];

        foreach ($organizationTypes as $organizationType) {
            OrganizationType::query()->updateOrCreate(
                ['name_uz' => $organizationType['name_uz']],
                $organizationType
            );
        }
    }
}
