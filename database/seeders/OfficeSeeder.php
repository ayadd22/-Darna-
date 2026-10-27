<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            [
                'name' => 'مكتب دمشق العقاري',
                'email' => 'damascus.office@darna.test',
                'phone' => '0944000001',
                'address' => 'دمشق',
                'is_active' => true,
            ],
            [
                'name' => 'مكتب حلب العقاري',
                'email' => 'aleppo.office@darna.test',
                'phone' => '0944000002',
                'address' => 'حلب',
                'is_active' => true,
            ],
            [
                'name' => 'مكتب اللاذقية العقاري',
                'email' => 'latakia.office@darna.test',
                'phone' => '0944000003',
                'address' => 'اللاذقية',
                'is_active' => true,
            ],
        ];

        foreach ($offices as $office) {
            Office::updateOrCreate(
                ['email' => $office['email']],
                $office
            );
        }
    }
}
