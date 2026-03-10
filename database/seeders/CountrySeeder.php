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
                'name_cryl' => 'Қозоғистон',
                'iso2' => 'KZ',
                'iso3' => 'KAZ',
                'region_ru' => 'Центральная Азия',
                'region_uz' => 'Markaziy Osiyo',
                'region_cryl' => 'Марказий Осиё',
                'latitude' => 48.0196000,
                'longitude' => 66.9237000,
                'default_zoom' => 4.8,
                'cooperation_status' => 'active',
                'boundary_geojson_path' => null,
                'flag_path' => null,
                'notes' => "Markaziy Osiyodagi muhim hamkor davlatlardan biri.",
            ],
            [
                'name_ru' => 'Кыргызстан',
                'name_uz' => "Qirg'iziston",
                'name_cryl' => 'Қирғизистон',
                'iso2' => 'KG',
                'iso3' => 'KGZ',
                'region_ru' => 'Центральная Азия',
                'region_uz' => 'Markaziy Osiyo',
                'region_cryl' => 'Марказий Осиё',
                'latitude' => 41.2044000,
                'longitude' => 74.7661000,
                'default_zoom' => 5.8,
                'cooperation_status' => 'active',
                'boundary_geojson_path' => null,
                'flag_path' => null,
                'notes' => "Mintaqaviy hamkorlik va tajriba almashinuvi yo'nalishida faol hamkor.",
            ],
            [
                'name_ru' => 'Турция',
                'name_uz' => 'Turkiya',
                'name_cryl' => 'Туркия',
                'iso2' => 'TR',
                'iso3' => 'TUR',
                'region_ru' => 'Европа и Азия',
                'region_uz' => 'Yevropa va Osiyo',
                'region_cryl' => 'Европа ва Осиё',
                'latitude' => 38.9637000,
                'longitude' => 35.2433000,
                'default_zoom' => 5.3,
                'cooperation_status' => 'active',
                'boundary_geojson_path' => null,
                'flag_path' => null,
                'notes' => "Xalqaro tadbirlar va delegatsiyalar almashinuviga oid hamkorlik yo'lga qo'yilgan.",
            ],
            [
                'name_ru' => 'Китай',
                'name_uz' => 'Xitoy',
                'name_cryl' => 'Хитой',
                'iso2' => 'CN',
                'iso3' => 'CHN',
                'region_ru' => 'Азия',
                'region_uz' => 'Osiyo',
                'region_cryl' => 'Осиё',
                'latitude' => 35.8617000,
                'longitude' => 104.1954000,
                'default_zoom' => 4.2,
                'cooperation_status' => 'planned',
                'boundary_geojson_path' => null,
                'flag_path' => null,
                'notes' => "Texnik hamkorlik va rejalashtirilgan uchrashuvlar bosqichida.",
            ],
            [
                'name_ru' => 'Россия',
                'name_uz' => 'Rossiya',
                'name_cryl' => 'Россия',
                'iso2' => 'RU',
                'iso3' => 'RUS',
                'region_ru' => 'Европа и Азия',
                'region_uz' => 'Yevropa va Osiyo',
                'region_cryl' => 'Европа ва Осиё',
                'latitude' => 61.5240000,
                'longitude' => 105.3188000,
                'default_zoom' => 3.6,
                'cooperation_status' => 'completed',
                'boundary_geojson_path' => null,
                'flag_path' => null,
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
