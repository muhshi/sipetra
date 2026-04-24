<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use N3XT0R\LaravelPassportAuthorizationCore\Models\PassportScopeAction;
use N3XT0R\LaravelPassportAuthorizationCore\Models\PassportScopeResource;

class PassportScopeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $scopes = [
            'profile:read' => 'Informasi profil dasar (nama, email, avatar, tipe user)',
            'identity_pegawai:read' => 'Identitas khusus pegawai (NIP Baru/Lama)',
            'identity_mitra:read' => 'Identitas khusus mitra (SOBAT ID, NIK)',
            'employee:read' => 'Data detil kepegawaian (Jabatan, Golongan, Masa Kerja, Agama, dsb)',
            'contact:read' => 'Data kontak (Nomor HP, Alamat)',
            'roles:read' => 'Hak akses (Roles & Permissions)',
        ];

        foreach ($scopes as $scope => $description) {
            [$resourceName, $actionName] = explode(':', $scope);
            
            $resource = PassportScopeResource::firstOrCreate(
                ['name' => $resourceName],
                ['description' => $description]
            );

            PassportScopeAction::firstOrCreate(
                ['name' => $actionName]
            );
        }
    }
}
