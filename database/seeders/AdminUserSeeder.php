<?php

namespace Database\Seeders;

use App\Enums\IdentityType;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('admin'),
                'identity_type' => IdentityType::Admin,
                'is_active' => true,
            ]
        );

        $admin->assignRole('super_admin');
    }
}
