<?php

namespace Database\Seeders;

use App\Models\Owner;
use Illuminate\Database\Seeder;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        $owners = [
            [
                'name' => 'أحمد الخطيب',
                'phone' => '0933000001',
            ],
            [
                'name' => 'محمد العلي',
                'phone' => '0933000002',
            ],
            [
                'name' => 'سارة الحسن',
                'phone' => '0933000003',
            ],
            [
                'name' => 'خالد الأحمد',
                'phone' => '0933000004',
            ],
            [
                'name' => 'نور الدين محمود',
                'phone' => '0933000005',
            ],
        ];

        foreach ($owners as $owner) {
            Owner::updateOrCreate(
                ['phone' => $owner['phone']],
                $owner
            );
        }
    }
}
