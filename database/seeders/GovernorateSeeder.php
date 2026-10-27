<?php

namespace Database\Seeders;

use App\Models\Governorate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GovernorateSeeder extends Seeder
{
    public function run(): void
    {
        $governorates = [
            'دمشق',
            'ريف دمشق',
            'حلب',
            'حمص',
            'حماة',
            'اللاذقية',
            'طرطوس',
            'إدلب',
            'درعا',
            'السويداء',
            'القنيطرة',
            'دير الزور',
            'الرقة',
            'الحسكة',
        ];

        foreach ($governorates as $name) {
            Governorate::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name) ?: 'governorate-' . md5($name)]
            );
        }
    }
}
