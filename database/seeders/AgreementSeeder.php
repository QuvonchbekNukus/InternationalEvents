<?php

namespace Database\Seeders;

use App\Models\Agreement;
use App\Models\AgreementDirection;
use App\Models\AgreementType;
use App\Models\Country;
use App\Models\Department;
use App\Models\PartnerOrganization;
use App\Models\User;
use Illuminate\Database\Seeder;

class AgreementSeeder extends Seeder
{
    /**
     * Seed the application's agreements.
     */
    public function run(): void
    {
        $countryIds = Country::query()->pluck('id', 'iso3');
        $partnerOrganizations = PartnerOrganization::query()->get(['id', 'country_id', 'name_uz'])->keyBy('name_uz');
        $agreementTypeIds = AgreementType::query()->pluck('id', 'name_uz');
        $agreementDirectionIds = AgreementDirection::query()->pluck('id', 'name_uz');
        $userIds = User::query()->pluck('id', 'phone');
        $departmentIds = Department::query()->pluck('id', 'code');

        $agreements = [
            [
                'agreement_number' => 'MG-2026/001',
                'country_iso3' => 'KAZ',
                'partner_organization_name' => "Qozog'iston Respublikasi Ichki ishlar vazirligi",
                'agreement_type_name' => 'Memorandum',
                'agreement_direction_name' => 'Xavfsizlik',
                'title_ru' => 'Меморандум о сотрудничестве в сфере приграничной безопасности',
                'title_uz' => "Chegaraoldi xavfsizligi bo'yicha hamkorlik memorandumi",
                'title_cryl' => 'Чегараолди хавфсизлиги бўйича ҳамкорлик меморандуми',
                'short_title_ru' => 'Приграничный меморандум',
                'short_title_uz' => 'Chegaraoldi memorandumi',
                'short_title_cryl' => 'Чегараолди меморандуми',
                'signed_date' => '2026-01-15',
                'start_date' => '2026-01-15',
                'end_date' => '2027-01-14',
                'status' => 'active',
                'responsible_user_phone' => '998900000001',
                'responsible_department_code' => 'XAB',
                'description' => "Chegaraoldi hududlarda qo'shma nazorat, delegatsiyalar almashinuvi va tezkor axborot almashish tartibini belgilaydi.",
            ],
            [
                'agreement_number' => 'MG-2025/002',
                'country_iso3' => 'KGZ',
                'partner_organization_name' => "Qirg'iz Respublikasi Elchixonasi",
                'agreement_type_name' => 'Protokol',
                'agreement_direction_name' => 'Tajriba almashinuvi',
                'title_ru' => 'Протокол о сотрудничестве по обмену опытом и подготовке кадров',
                'title_uz' => "Tajriba almashinuvi va kadrlar tayyorlash bo'yicha protokol",
                'title_cryl' => 'Тажриба алмашинуви ва кадрлар тайёрлаш бўйича протокол',
                'short_title_ru' => 'Протокол по обмену опытом',
                'short_title_uz' => 'Tajriba protokoli',
                'short_title_cryl' => 'Тажриба протоколи',
                'signed_date' => '2025-03-20',
                'start_date' => '2025-03-20',
                'end_date' => '2026-03-01',
                'status' => 'completed',
                'responsible_user_phone' => '998900000002',
                'responsible_department_code' => 'XAB',
                'description' => "O'quv tashriflari, seminarlar va qisqa muddatli xizmat safarlarini tashkil etish bo'yicha yakunlangan hamkorlik hujjati.",
            ],
            [
                'agreement_number' => 'MG-2026/003',
                'country_iso3' => 'TUR',
                'partner_organization_name' => 'Jandarmeriya va Sohil qoriqlash akademiyasi',
                'agreement_type_name' => 'Shartnoma',
                'agreement_direction_name' => "Ta'lim",
                'title_ru' => 'Договор о совместных образовательных программах и повышении квалификации',
                'title_uz' => "Qo'shma ta'lim dasturlari va malaka oshirish bo'yicha shartnoma",
                'title_cryl' => 'Қўшма таълим дастурлари ва малака ошириш бўйича шартнома',
                'short_title_ru' => 'Образовательный договор',
                'short_title_uz' => "Ta'lim shartnomasi",
                'short_title_cryl' => 'Таълим шартномаси',
                'signed_date' => '2026-02-10',
                'start_date' => '2026-03-01',
                'end_date' => '2028-03-01',
                'status' => 'active',
                'responsible_user_phone' => '998900000004',
                'responsible_department_code' => 'HQB',
                'description' => "Instruktorlar almashinuvi, qo'shma kurslar va o'quv-metodik materiallar ishlab chiqishni nazarda tutadi.",
            ],
            [
                'agreement_number' => 'MG-2026/004',
                'country_iso3' => 'CHN',
                'partner_organization_name' => 'Pekin jamoat xavfsizligi universiteti',
                'agreement_type_name' => "Yo'l xaritasi",
                'agreement_direction_name' => 'Kiberxavfsizlik',
                'title_ru' => 'Дорожная карта по сотрудничеству в сфере кибербезопасности',
                'title_uz' => "Kiberxavfsizlik yo'nalishidagi hamkorlik bo'yicha yo'l xaritasi",
                'title_cryl' => 'Киберхавфсизлик йўналишидаги ҳамкорлик бўйича йўл харитаси',
                'short_title_ru' => 'Кибердорожная карта',
                'short_title_uz' => 'Kiber yo`l xaritasi',
                'short_title_cryl' => 'Кибер йўл харитаси',
                'signed_date' => null,
                'start_date' => '2026-06-01',
                'end_date' => '2027-06-01',
                'status' => 'draft',
                'responsible_user_phone' => '998900000016',
                'responsible_department_code' => 'XAB',
                'description' => "Kiberxavfsizlik laboratoriyalarini bog'lash, ekspertlar almashinuvi va qo'shma treninglar bo'yicha reja loyihasi.",
            ],
            [
                'agreement_number' => 'MG-2024/005',
                'country_iso3' => 'RUS',
                'partner_organization_name' => 'Milliy gvardiya federal xizmati',
                'agreement_type_name' => 'Bitim',
                'agreement_direction_name' => 'Texnologiya',
                'title_ru' => 'Соглашение о технологическом сотрудничестве и модернизации оборудования',
                'title_uz' => "Texnologik hamkorlik va uskunalarni modernizatsiya qilish bo'yicha bitim",
                'title_cryl' => 'Технологик ҳамкорлик ва ускуналарни модернизация қилиш бўйича битим',
                'short_title_ru' => 'Технологическое соглашение',
                'short_title_uz' => 'Texnologik bitim',
                'short_title_cryl' => 'Технологик битим',
                'signed_date' => '2024-04-12',
                'start_date' => '2024-05-01',
                'end_date' => '2025-05-01',
                'status' => 'expired',
                'responsible_user_phone' => '998900000012',
                'responsible_department_code' => 'HQB',
                'description' => "Texnik uskunalar, aloqa vositalari va monitoring tizimlarini sinovdan o'tkazish bo'yicha muddati tugagan bitim.",
            ],
        ];

        foreach ($agreements as $agreementData) {
            $countryId = $countryIds[$agreementData['country_iso3']] ?? null;

            if (! $countryId) {
                continue;
            }

            $partnerOrganization = $partnerOrganizations[$agreementData['partner_organization_name']] ?? null;
            $responsibleUserId = $userIds[$agreementData['responsible_user_phone']] ?? null;

            Agreement::query()->updateOrCreate(
                ['agreement_number' => $agreementData['agreement_number']],
                [
                    'title_ru' => $agreementData['title_ru'],
                    'title_uz' => $agreementData['title_uz'],
                    'title_cryl' => $agreementData['title_cryl'],
                    'short_title_ru' => $agreementData['short_title_ru'],
                    'short_title_uz' => $agreementData['short_title_uz'],
                    'short_title_cryl' => $agreementData['short_title_cryl'],
                    'country_id' => $countryId,
                    'partner_organization_id' => $partnerOrganization?->country_id === $countryId ? $partnerOrganization->id : null,
                    'agreement_type_id' => $agreementTypeIds[$agreementData['agreement_type_name']] ?? null,
                    'agreement_direction_id' => $agreementDirectionIds[$agreementData['agreement_direction_name']] ?? null,
                    'signed_date' => $agreementData['signed_date'],
                    'start_date' => $agreementData['start_date'],
                    'end_date' => $agreementData['end_date'],
                    'status' => $agreementData['status'],
                    'responsible_user_id' => $responsibleUserId,
                    'responsible_department_id' => $departmentIds[$agreementData['responsible_department_code']] ?? null,
                    'description' => $agreementData['description'],
                    'created_by' => $responsibleUserId,
                    'updated_by' => $responsibleUserId,
                ]
            );
        }
    }
}
