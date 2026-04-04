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
                'birthday' => '1984-02-17',
                'position_ru' => 'Начальник управления международного сотрудничества',
                'position_uz' => 'Xalqaro hamkorlik boshqarmasi boshlig\'i',
                'email' => 'n.alibekov@mvd.kz',
                'phone' => '+77015550101',
                'description' => 'Rasmiy delegatsiyalar va idoraviy hamkorlik masalalari uchun asosiy aloqa shaxsi.',
                'is_primary' => true,
            ],
            [
                'partner_organization_name' => "Qirg'iz Respublikasi Elchixonasi",
                'full_name_ru' => 'Токтогазиев Азамат Кубанычбекович',
                'full_name_uz' => 'Toktogaziyev Azamat Kubanichbekovich',
                'birthday' => '1988-06-09',
                'position_ru' => 'Советник посольства',
                'position_uz' => 'Elchixona maslahatchisi',
                'email' => 'azamat.t@kgembassy.uz',
                'phone' => '+998711234501',
                'description' => 'Protokol va uchrashuvlarni muvofiqlashtirish bo\'yicha javobgar shaxs.',
                'is_primary' => true,
            ],
            [
                'partner_organization_name' => 'Jandarmeriya va Sohil qoriqlash akademiyasi',
                'full_name_ru' => 'Мехмет Демир',
                'full_name_uz' => 'Mehmet Demir',
                'birthday' => '1979-11-24',
                'position_ru' => 'Координатор международных программ',
                'position_uz' => 'Xalqaro dasturlar koordinatori',
                'email' => 'mehmet.demir@jsga.edu.tr',
                'phone' => '+903124440303',
                'description' => 'Ta\'lim va trening dasturlari bo\'yicha asosiy kontakt.',
                'is_primary' => true,
            ],
            [
                'partner_organization_name' => 'Pekin jamoat xavfsizligi universiteti',
                'full_name_ru' => 'Ли Вэй',
                'full_name_uz' => 'Li Vey',
                'birthday' => '1986-04-03',
                'position_ru' => 'Руководитель отдела международных связей',
                'position_uz' => 'Xalqaro aloqalar bo\'limi rahbari',
                'email' => 'li.wei@ppsuc.edu.cn',
                'phone' => '+861065550404',
                'description' => 'Rejalashtirilgan seminar va almashinuv loyihalari bo\'yicha muloqot nuqtasi.',
                'is_primary' => true,
            ],
            [
                'partner_organization_name' => 'Milliy gvardiya federal xizmati',
                'full_name_ru' => 'Иван Петров',
                'full_name_uz' => 'Ivan Petrov',
                'birthday' => '1982-09-14',
                'position_ru' => 'Старший офицер по международному взаимодействию',
                'position_uz' => 'Xalqaro hamkorlik bo\'yicha katta ofitser',
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
                    'birthday' => $partnerContact['birthday'],
                    'position_ru' => $partnerContact['position_ru'],
                    'position_uz' => $partnerContact['position_uz'],
                    'email' => $partnerContact['email'],
                    'phone' => $partnerContact['phone'],
                    'description' => $partnerContact['description'],
                    'is_primary' => $partnerContact['is_primary'],
                ]
            );
        }
    }
}
