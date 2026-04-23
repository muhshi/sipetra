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
            'profile:read',
            'identity_pegawai:read',
            'identity_mitra:read',
            'employee:read',
            'contact:read', // Merging phone and address to just contact:read
            'roles:read',
        ];

        $resources = [];
        $actions = [];

        foreach ($scopes as $scope) {
            [$resourceName, $actionName] = explode(':', $scope);
            $resources[] = $resourceName;
            $actions[] = $actionName;
        }

        $resources = array_unique($resources);
        $actions = array_unique($actions);

        foreach ($resources as $resource) {
            PassportScopeResource::firstOrCreate(['name' => $resource]);
        }

        foreach ($actions as $action) {
            PassportScopeAction::firstOrCreate(['name' => $action]);
        }
    }
}
