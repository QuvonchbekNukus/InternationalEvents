<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Department;
use App\Models\PartnerOrganization;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitType;
use Illuminate\Database\Seeder;

class VisitSeeder extends Seeder
{
    /**
     * Seed the application's visits.
     */
    public function run(): void
    {
        $countryIds = Country::query()->pluck('id', 'iso3');
        $visitTypeIds = VisitType::query()->pluck('id', 'name_uz');
        $partnerOrganizationIds = PartnerOrganization::query()->pluck('id', 'name_uz');
        $userIds = User::query()->pluck('id', 'phone');
        $departmentIds = Department::query()->pluck('id', 'code');

        $visits = [
            [
                'country_iso3' => 'KAZ',
                'visit_type_name' => 'Rasmiy tashrif',
                'partner_organization_name' => "Qozog'iston Respublikasi Ichki ishlar vazirligi",
                'responsible_user_phone' => '998900000001',
                'responsible_department_code' => 'XAB',
                'title_ru' => 'Официальный визит делегации МВД Республики Казахстан',
                'title_uz' => "Qozog'iston Respublikasi IIV delegatsiyasining rasmiy tashrifi",
                'title_cryl' => 'Қозоғистон Республикаси ИИВ делегациясининг расмий ташрифи',
                'city' => 'Toshkent',
                'address' => "Milliy gvardiya markaziy apparati, Toshkent shahri",
                'latitude' => 41.3110810,
                'longitude' => 69.2405620,
                'start_date' => '2026-04-15',
                'end_date' => '2026-04-17',
                'direction' => 'incoming',
                'status' => 'planned',
                'purpose_ru' => 'Обсуждение плана совместных мероприятий и обмен опытом.',
                'purpose_uz' => "Qo'shma tadbirlar rejasini kelishish va tajriba almashish.",
                'purpose_cryl' => 'Қўшма тадбирлар режасини келишиш ва тажриба алмашиш.',
                'result_summary_ru' => null,
                'result_summary_uz' => null,
                'result_summary_cryl' => null,
                'description' => "Protokol uchrashuvlari va qo'shma mashg'ulot obyektlariga tashrif rejalashtirilgan.",
            ],
            [
                'country_iso3' => 'TUR',
                'visit_type_name' => 'Ishchi tashrif',
                'partner_organization_name' => 'Jandarmeriya va Sohil qoriqlash akademiyasi',
                'responsible_user_phone' => '998900000004',
                'responsible_department_code' => 'HQB',
                'title_ru' => 'Рабочий визит в Академию жандармерии и береговой охраны',
                'title_uz' => 'Jandarmeriya va Sohil qoriqlash akademiyasiga ishchi tashrif',
                'title_cryl' => 'Жандармерия ва Соҳил қўриқлаш академиясига ишчи ташриф',
                'city' => 'Anqara',
                'address' => 'Beytepe mahallasi, Cankaya',
                'latitude' => 39.9207700,
                'longitude' => 32.8541100,
                'start_date' => '2026-02-10',
                'end_date' => '2026-02-14',
                'direction' => 'outgoing',
                'status' => 'completed',
                'purpose_ru' => 'Изучение программ подготовки и согласование календаря стажировок.',
                'purpose_uz' => "Tayyorlov dasturlarini o'rganish va stajirovkalar jadvalini kelishish.",
                'purpose_cryl' => 'Тайёрлов дастурларини ўрганиш ва стажировкалар жадвалини келишиш.',
                'result_summary_ru' => 'Стороны согласовали квоты на обучение и обмен преподавателями.',
                'result_summary_uz' => "Tomonlar o'quv kvotalari va o'qituvchilar almashinuvini kelishib oldi.",
                'result_summary_cryl' => 'Томонлар ўқув квоталари ва ўқитувчилар алмашинувини келишиб олди.',
                'description' => "Akademiya o'quv poligonlari va laboratoriyalari bilan tanishuv o'tkazildi.",
            ],
            [
                'country_iso3' => 'KGZ',
                'visit_type_name' => "Do'stona tashrif",
                'partner_organization_name' => "Qirg'iz Respublikasi Elchixonasi",
                'responsible_user_phone' => '998900000002',
                'responsible_department_code' => 'XAB',
                'title_ru' => 'Дружественный визит представителей Посольства Кыргызской Республики',
                'title_uz' => "Qirg'iz Respublikasi elchixonasi vakillarining do'stona tashrifi",
                'title_cryl' => 'Қирғиз Республикаси элчихонаси вакилларининг дўстона ташрифи',
                'city' => 'Toshkent',
                'address' => "Yakkasaroy tumani, Bobur ko'chasi 12",
                'latitude' => 41.2995000,
                'longitude' => 69.2401000,
                'start_date' => '2026-01-22',
                'end_date' => '2026-01-22',
                'direction' => 'incoming',
                'status' => 'completed',
                'purpose_ru' => 'Укрепление рабочих контактов и согласование совместных церемониальных мероприятий.',
                'purpose_uz' => "Ishchi aloqalarni mustahkamlash va qo'shma marosim tadbirlarini kelishish.",
                'purpose_cryl' => 'Ишчи алоқаларни мустаҳкамлаш ва қўшма маросим тадбирларини келишиш.',
                'result_summary_ru' => 'Согласован протокол дальнейшего взаимодействия на первое полугодие.',
                'result_summary_uz' => "Keyingi yarim yillik uchun hamkorlik protokoli kelishib olindi.",
                'result_summary_cryl' => 'Кейинги ярим йиллик учун ҳамкорлик протоколи келишиб олинди.',
                'description' => "Qisqa uchrashuv va delegatsiyalar tashrifi grafiklari muhokama qilindi.",
            ],
            [
                'country_iso3' => 'CHN',
                'visit_type_name' => 'Davlat tashrifi',
                'partner_organization_name' => 'Pekin jamoat xavfsizligi universiteti',
                'responsible_user_phone' => '998900000012',
                'responsible_department_code' => 'HQB',
                'title_ru' => 'Государственный визит в Пекинский университет общественной безопасности',
                'title_uz' => 'Pekin jamoat xavfsizligi universitetiga davlat tashrifi',
                'title_cryl' => 'Пекин жамоат хавфсизлиги университетига давлат ташрифи',
                'city' => 'Pekin',
                'address' => 'Muxidi tumani, Andingmen kochasi 83',
                'latitude' => 39.9042000,
                'longitude' => 116.4074000,
                'start_date' => '2026-05-18',
                'end_date' => '2026-05-23',
                'direction' => 'outgoing',
                'status' => 'planned',
                'purpose_ru' => 'Проведение переговоров по академическому обмену и цифровой безопасности.',
                'purpose_uz' => "Akademik almashinuv va raqamli xavfsizlik bo'yicha muzokaralar o'tkazish.",
                'purpose_cryl' => 'Академик алмашинув ва рақамли хавфсизлик бўйича музокаралар ўтказиш.',
                'result_summary_ru' => null,
                'result_summary_uz' => null,
                'result_summary_cryl' => null,
                'description' => "Safar doirasida laboratoriyalar, simulyatsiya markazlari va muzokara sessiyalari rejalashtirilgan.",
            ],
            [
                'country_iso3' => 'RUS',
                'visit_type_name' => 'Javob tashrifi',
                'partner_organization_name' => 'Milliy gvardiya federal xizmati',
                'responsible_user_phone' => '998900000007',
                'responsible_department_code' => 'KB',
                'title_ru' => 'Ответный визит представителей Федеральной службы войск национальной гвардии',
                'title_uz' => 'Milliy gvardiya federal xizmati vakillarining javob tashrifi',
                'title_cryl' => 'Миллий гвардия федерал хизмати вакилларининг жавоб ташрифи',
                'city' => 'Toshkent',
                'address' => "Do'stlik xiyoboni, rasmiy qabullar uyi",
                'latitude' => 41.3200000,
                'longitude' => 69.2800000,
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-03',
                'direction' => 'incoming',
                'status' => 'cancelled',
                'purpose_ru' => 'Обсуждение итогов предыдущих переговоров и обмен аналитическими материалами.',
                'purpose_uz' => "Avvalgi muzokaralar yakunlarini muhokama qilish va analitik materiallar almashish.",
                'purpose_cryl' => 'Аввалги музокаралар якунларини муҳокама қилиш ва аналитик материаллар алмашиш.',
                'result_summary_ru' => 'Визит отменен по причине изменения графика принимающей стороны.',
                'result_summary_uz' => "Qabul qiluvchi tomon jadvali o'zgargani sababli tashrif bekor qilindi.",
                'result_summary_cryl' => 'Қабул қилувчи томон жадвали ўзгаргани сабабли ташриф бекор қилинди.',
                'description' => "Delegatsiya tarkibini qayta shakllantirish bo'yicha yangi sana kutilmoqda.",
            ],
        ];

        foreach ($visits as $visitData) {
            $countryId = $countryIds[$visitData['country_iso3']] ?? null;

            if (! $countryId) {
                continue;
            }

            $responsibleUserId = $userIds[$visitData['responsible_user_phone']] ?? null;

            Visit::query()->updateOrCreate(
                [
                    'country_id' => $countryId,
                    'title_uz' => $visitData['title_uz'],
                    'start_date' => $visitData['start_date'],
                ],
                [
                    'title_ru' => $visitData['title_ru'],
                    'title_cryl' => $visitData['title_cryl'],
                    'visit_type_id' => $visitTypeIds[$visitData['visit_type_name']] ?? null,
                    'partner_organization_id' => $partnerOrganizationIds[$visitData['partner_organization_name']] ?? null,
                    'city' => $visitData['city'],
                    'address' => $visitData['address'],
                    'latitude' => $visitData['latitude'],
                    'longitude' => $visitData['longitude'],
                    'end_date' => $visitData['end_date'],
                    'direction' => $visitData['direction'],
                    'status' => $visitData['status'],
                    'responsible_user_id' => $responsibleUserId,
                    'responsible_department_id' => $departmentIds[$visitData['responsible_department_code']] ?? null,
                    'purpose_ru' => $visitData['purpose_ru'],
                    'purpose_uz' => $visitData['purpose_uz'],
                    'purpose_cryl' => $visitData['purpose_cryl'],
                    'result_summary_ru' => $visitData['result_summary_ru'],
                    'result_summary_uz' => $visitData['result_summary_uz'],
                    'result_summary_cryl' => $visitData['result_summary_cryl'],
                    'description' => $visitData['description'],
                    'created_by' => $responsibleUserId,
                    'updated_by' => $responsibleUserId,
                ]
            );
        }
    }
}
