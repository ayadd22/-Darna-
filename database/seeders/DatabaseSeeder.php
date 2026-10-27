<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            GovernorateSeeder::class,
            OfficeSeeder::class,
            OwnerSeeder::class,
            PropertySeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@darna.test'],
            [
                'name' => 'Darna Admin',
                'password' => 'ChangeMe123!',
                'office_id' => null,
            ]
        );

        $admin->role = UserRoleEnum::ADMIN;
        $admin->save();
    }
}
