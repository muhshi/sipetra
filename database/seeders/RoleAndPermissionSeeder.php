<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar Role
        $roles = [
            'super_admin',
            'kepala',
            'ketua_tim',
            'operator',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        // Contoh: Memberikan akses dasar ke operator
        $operator = Role::findByName('operator');
        $operator->syncPermissions([
            // Tambahkan permission jika diperlukan
        ]);
    }
}
