<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Seed the application's departments.
     */
    public function run(): void
    {
        $departments = [
            [
                'name_ru' => 'Управление международных связей',
                'name_uz' => 'Xalqaro aloqalar boshqarmasi',
                'name_cryl' => 'Халкаро алокалар бошкармаси',
                'code' => 'XAB',
                'description' => 'Xalqaro hamkorlik va tashqi aloqalarni yuritadi.',
            ],
            [
                'name_ru' => 'Правовое управление',
                'name_uz' => 'Huquqiy boshqarma',
                'name_cryl' => 'Хукукий бошкарма',
                'code' => 'HQB',
                'description' => 'Huquqiy hujjatlar va normativ masalalarni yuritadi.',
            ],
            [
                'name_ru' => 'Управление кадров',
                'name_uz' => 'Kadrlar boshqarmasi',
                'name_cryl' => 'Кадрлар бошкармаси',
                'code' => 'KB',
                'description' => 'Shaxsiy tarkib va kadrlar bilan ishlaydi.',
            ],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['code' => $department['code']],
                $department
            );
        }
    }
}
