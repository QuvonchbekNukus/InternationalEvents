<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\OrganizationType;
use App\Models\PartnerOrganization;
use Illuminate\Database\Seeder;

class PartnerOrganizationSeeder extends Seeder
{
    /**
     * Seed the application's partner organizations.
     */
    public function run(): void
    {
        $countryIds = Country::query()->pluck('id', 'iso3');
        $organizationTypeIds = OrganizationType::query()->pluck('id', 'name_uz');

        $partnerOrganizations = [
            [
                'country_iso3' => 'KAZ',
                'organization_type_name' => 'Vazirlik',
                'name_ru' => 'Министерство внутренних дел Республики Казахстан',
                'name_uz' => "Qozog'iston Respublikasi Ichki ishlar vazirligi",
                'short_name' => 'Qozogiston IIV',
                'address' => 'Astana shahri, Tauelsizdik kochasi 1',
                'city' => 'Astana',
                'website' => 'gov.kz/memleket/entities/qriim',
                'status' => 'faol',
                'notes' => 'Huquq-tartibot va delegatsiyalar almashinuvi boyicha hamkor.',
                'partnership_history' => "2019-yildan buyon protokol uchrashuvlari, o'quv tashriflari va tajriba almashinuvi yo'nalishlarida muntazam hamkorlik olib boriladi.",
            ],
            [
                'country_iso3' => 'KGZ',
                'organization_type_name' => 'Elchixona',
                'name_ru' => 'Посольство Кыргызской Республики',
                'name_uz' => "Qirg'iz Respublikasi Elchixonasi",
                'short_name' => 'Qirgiziston Elchixonasi',
                'address' => 'Yakkasaroy tumani, Bobur kochasi 12',
                'city' => 'Toshkent',
                'website' => 'kyrgyzembassy.uz',
                'status' => 'faol',
                'notes' => 'Protokol tadbirlari va rasmiy tashriflarni muvofiqlashtiradi.',
                'partnership_history' => "Elchixona bilan rasmiy uchrashuvlar, delegatsiyalar tashrifi va ikki tomonlama tadbirlar bo'yicha doimiy ishchi aloqalar shakllangan.",
            ],
            [
                'country_iso3' => 'TUR',
                'organization_type_name' => 'Universitet',
                'name_ru' => 'Академия жандармерии и береговой охраны',
                'name_uz' => 'Jandarmeriya va Sohil qoriqlash akademiyasi',
                'short_name' => 'JSGA',
                'address' => 'Beytepe mahallasi, Cankaya',
                'city' => 'Anqara',
                'website' => 'jsga.edu.tr',
                'status' => 'faol',
                'notes' => 'Talim, trening va malaka oshirish yonalishida hamkorlik qiladi.',
                'partnership_history' => "Akademiya bilan malaka oshirish, o'quv kurslari va qo'shma seminarlar doirasida tajriba almashish ishlari olib borilgan.",
            ],
            [
                'country_iso3' => 'CHN',
                'organization_type_name' => 'Universitet',
                'name_ru' => 'Пекинский университет общественной безопасности',
                'name_uz' => 'Pekin jamoat xavfsizligi universiteti',
                'short_name' => 'PPSU',
                'address' => 'Muxidi tumani, Andingmen kochasi 83',
                'city' => 'Pekin',
                'website' => 'ppsuc.edu.cn',
                'status' => 'rejada',
                'notes' => 'Qoshma seminar va akademik almashinuv rejalashtirilgan.',
                'partnership_history' => "Hamkorlik bo'yicha dastlabki muzokaralar o'tkazilgan, akademik almashinuv va maxsus seminarlarni yo'lga qo'yish rejalashtirilgan.",
            ],
            [
                'country_iso3' => 'RUS',
                'organization_type_name' => 'Xalqaro tashkilot',
                'name_ru' => 'Федеральная служба войск национальной гвардии',
                'name_uz' => 'Milliy gvardiya federal xizmati',
                'short_name' => 'Rosgvardiya',
                'address' => 'Krasnokazarmennaya kochasi 9A',
                'city' => 'Moskva',
                'website' => 'rosguard.gov.ru',
                'status' => 'tugallangan',
                'notes' => 'Ayrim qoshma dasturlar yakunlangan va arxivda saqlanadi.',
                'partnership_history' => "Oldingi yillarda qo'shma dasturlar va delegatsion uchrashuvlar o'tkazilgan, hozirda hamkorlik natijalari arxiv materiallari ko'rinishida saqlanmoqda.",
            ],
        ];

        foreach ($partnerOrganizations as $partnerOrganization) {
            $countryId = $countryIds[$partnerOrganization['country_iso3']] ?? null;

            if (! $countryId) {
                continue;
            }

            PartnerOrganization::query()->updateOrCreate(
                [
                    'country_id' => $countryId,
                    'name_uz' => $partnerOrganization['name_uz'],
                ],
                [
                    'name_ru' => $partnerOrganization['name_ru'],
                    'short_name' => $partnerOrganization['short_name'],
                    'organization_type_id' => $organizationTypeIds[$partnerOrganization['organization_type_name']] ?? null,
                    'address' => $partnerOrganization['address'],
                    'city' => $partnerOrganization['city'],
                    'website' => $partnerOrganization['website'],
                    'status' => $partnerOrganization['status'],
                    'notes' => $partnerOrganization['notes'],
                    'partnership_history' => $partnerOrganization['partnership_history'],
                ]
            );
        }
    }
}
