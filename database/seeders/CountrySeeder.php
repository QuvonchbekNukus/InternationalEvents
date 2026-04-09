<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Seed the application's countries.
     */
    public function run(): void
    {
        $countries = [
            [
                'name_ru' => 'Казахстан',
                'name_uz' => "Qozog'iston",
                'iso2' => 'KZ',
                'iso3' => 'KAZ',
                'region_ru' => 'Центральная Азия',
                'region_uz' => 'Markaziy Osiyo',
                'cooperation_status' => 'faol',
                'boundary_geojson_path' => null,
                'flag_path' => null,
                'partnership_history' => null,
                'notes' => "Markaziy Osiyodagi muhim hamkor davlatlardan biri.",
            ],
            [
                'name_ru' => 'Кыргызстан',
                'name_uz' => "Qirg'iziston",
                'iso2' => 'KG',
                'iso3' => 'KGZ',
                'region_ru' => 'Центральная Азия',
                'region_uz' => 'Markaziy Osiyo',
                'cooperation_status' => 'faol',
                'boundary_geojson_path' => null,
                'flag_path' => null,
                'partnership_history' => null,
                'notes' => "Mintaqaviy hamkorlik va tajriba almashinuvi yo'nalishida faol hamkor.",
            ],
            [
                'name_ru' => 'Турция',
                'name_uz' => 'Turkiya',
                'iso2' => 'TR',
                'iso3' => 'TUR',
                'region_ru' => 'Европа и Азия',
                'region_uz' => 'Yevropa va Osiyo',
                'cooperation_status' => 'faol',
                'boundary_geojson_path' => null,
                'flag_path' => null,
                'partnership_history' => null,
                'notes' => "Xalqaro tadbirlar va delegatsiyalar almashinuviga oid hamkorlik yo'lga qo'yilgan.",
            ],
            [
                'name_ru' => 'Китай',
                'name_uz' => 'Xitoy',
                'iso2' => 'CN',
                'iso3' => 'CHN',
                'region_ru' => 'Азия',
                'region_uz' => 'Osiyo',
                'cooperation_status' => 'rejada',
                'boundary_geojson_path' => null,
                'flag_path' => null,
                'partnership_history' => null,
                'notes' => "Texnik hamkorlik va rejalashtirilgan uchrashuvlar bosqichida.",
            ],
            [
                'name_ru' => 'Россия',
                'name_uz' => 'Rossiya',
                'iso2' => 'RU',
                'iso3' => 'RUS',
                'region_ru' => 'Европа и Азия',
                'region_uz' => 'Yevropa va Osiyo',
                'cooperation_status' => 'tugatilgan',
                'boundary_geojson_path' => null,
                'flag_path' => null,
                'partnership_history' => null,
                'notes' => "Ayrim hamkorlik bosqichlari yakunlangan va arxiv uchun saqlanadi.",
            ],
        ];

        foreach ($countries as $country) {
            Country::query()->updateOrCreate(
                ['iso3' => $country['iso3']],
                $country
            );
        }
    }
}
