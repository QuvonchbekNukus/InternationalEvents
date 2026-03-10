<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Rank;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            RankSeeder::class,
            CountrySeeder::class,
            AgreementDirectionSeeder::class,
            AgreementTypeSeeder::class,
            OrganizationTypeSeeder::class,
            VisitTypeSeeder::class,
            PartnerOrganizationSeeder::class,
            PartnerContactSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            VisitSeeder::class,
        ]);

        $user = User::query()->updateOrCreate(
            ['phone' => '998996822712'],
            [
                'first_name' => 'Test',
                'middle_name' => 'Testlovich',
                'last_name' => 'User',
                'password' => 'password',
                'department_id' => Department::query()->where('code', 'XAB')->value('id'),
                'rank_id' => Rank::query()->where('name_uz', 'Leytenant')->value('id') ?? 1,
                'is_active' => true,
            ]
        );

        $user->syncRoles(['super-admin']);
    }
}
