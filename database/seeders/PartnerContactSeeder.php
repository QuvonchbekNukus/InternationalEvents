<?php

namespace Database\Seeders;

use App\Models\PartnerContact;
use App\Models\PartnerOrganization;
use Illuminate\Database\Seeder;

class PartnerContactSeeder extends Seeder
{
    /**
     * Seed the application's partner contacts.
     */
    public function run(): void
    {
        $partnerOrganizationIds = PartnerOrganization::query()->pluck('id', 'name_uz');

        $partnerContacts = [
            [
                'partner_organization_name' => "Qozog'iston Respublikasi Ichki ishlar vazirligi",
                'full_name_ru' => 'Алибеков Нурлан Серикович',
                'full_name_uz' => 'Alibekov Nurlan Serikovich',
                'full_name_cryl' => 'Алибеков Нурлан Серикович',
                'position_ru' => 'Начальник управления международного сотрудничества',
                'position_uz' => 'Xalqaro hamkorlik boshqarmasi boshlig\'i',
                'position_cryl' => 'Халкаро ҳамкорлик бошқармаси бошлиғи',
                'email' => 'n.alibekov@mvd.kz',
                'phone' => '+77015550101',
                'description' => 'Rasmiy delegatsiyalar va idoraviy hamkorlik masalalari uchun asosiy aloqa shaxsi.',
                'is_primary' => true,
            ],
            [
                'partner_organization_name' => "Qirg'iz Respublikasi Elchixonasi",
                'full_name_ru' => 'Токтогазиев Азамат Кубанычбекович',
                'full_name_uz' => 'Toktogaziyev Azamat Kubanichbekovich',
                'full_name_cryl' => 'Токтогазиев Азамат Кубанычбекович',
                'position_ru' => 'Советник посольства',
                'position_uz' => 'Elchixona maslahatchisi',
                'position_cryl' => 'Элчихона маслаҳатчиси',
                'email' => 'azamat.t@kgembassy.uz',
                'phone' => '+998711234501',
                'description' => 'Protokol va uchrashuvlarni muvofiqlashtirish bo\'yicha javobgar shaxs.',
                'is_primary' => true,
            ],
            [
                'partner_organization_name' => 'Jandarmeriya va Sohil qoriqlash akademiyasi',
                'full_name_ru' => 'Мехмет Демир',
                'full_name_uz' => 'Mehmet Demir',
                'full_name_cryl' => 'Меҳмет Демир',
                'position_ru' => 'Координатор международных программ',
                'position_uz' => 'Xalqaro dasturlar koordinatori',
                'position_cryl' => 'Халкаро дастурлар координатори',
                'email' => 'mehmet.demir@jsga.edu.tr',
                'phone' => '+903124440303',
                'description' => 'Ta\'lim va trening dasturlari bo\'yicha asosiy kontakt.',
                'is_primary' => true,
            ],
            [
                'partner_organization_name' => 'Pekin jamoat xavfsizligi universiteti',
                'full_name_ru' => 'Ли Вэй',
                'full_name_uz' => 'Li Vey',
                'full_name_cryl' => 'Ли Вэй',
                'position_ru' => 'Руководитель отдела международных связей',
                'position_uz' => 'Xalqaro aloqalar bo\'limi rahbari',
                'position_cryl' => 'Халкаро алоқалар бўлими раҳбари',
                'email' => 'li.wei@ppsuc.edu.cn',
                'phone' => '+861065550404',
                'description' => 'Rejalashtirilgan seminar va almashinuv loyihalari bo\'yicha muloqot nuqtasi.',
                'is_primary' => true,
            ],
            [
                'partner_organization_name' => 'Milliy gvardiya federal xizmati',
                'full_name_ru' => 'Иван Петров',
                'full_name_uz' => 'Ivan Petrov',
                'full_name_cryl' => 'Иван Петров',
                'position_ru' => 'Старший офицер по международному взаимодействию',
                'position_uz' => 'Xalqaro hamkorlik bo\'yicha katta ofitser',
                'position_cryl' => 'Халкаро ҳамкорлик бўйича катта офицер',
                'email' => 'i.petrov@rosguard.gov.ru',
                'phone' => '+74951234567',
                'description' => 'Yakunlangan qoshma dasturlar bo\'yicha arxiv va aloqa uchun mas\'ul.',
                'is_primary' => true,
            ],
        ];

        foreach ($partnerContacts as $partnerContact) {
            $partnerOrganizationId = $partnerOrganizationIds[$partnerContact['partner_organization_name']] ?? null;

            if (! $partnerOrganizationId) {
                continue;
            }

            PartnerContact::query()->updateOrCreate(
                [
                    'partner_organization_id' => $partnerOrganizationId,
                    'full_name_uz' => $partnerContact['full_name_uz'],
                ],
                [
                    'full_name_ru' => $partnerContact['full_name_ru'],
                    'full_name_cryl' => $partnerContact['full_name_cryl'],
                    'position_ru' => $partnerContact['position_ru'],
                    'position_uz' => $partnerContact['position_uz'],
                    'position_cryl' => $partnerContact['position_cryl'],
                    'email' => $partnerContact['email'],
                    'phone' => $partnerContact['phone'],
                    'description' => $partnerContact['description'],
                    'is_primary' => $partnerContact['is_primary'],
                ]
            );
        }
    }
}
